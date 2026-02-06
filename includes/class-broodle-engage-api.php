<?php
/**
 * Broodle WhatsApp API client class
 *
 * @package BroodleEngageConnector
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Broodle WhatsApp API client class
 * 
 * Uses Broodle WhatsApp API (engage.broodle.one) to send WhatsApp template messages
 */
class Broodle_Engage_API {

    /**
     * API base URL
     */
    const API_BASE_URL = 'https://engage.broodle.one';

    /**
     * HTTP timeout in seconds
     */
    const HTTP_TIMEOUT = 30;

    /**
     * API access token
     *
     * @var string
     */
    private $api_key;

    /**
     * Account ID
     *
     * @var int
     */
    private $account_id;

    /**
     * WhatsApp Inbox ID
     *
     * @var int
     */
    private $inbox_id;

    /**
     * Constructor
     *
     * @param string $api_key API access token.
     */
    public function __construct( $api_key = '' ) {
        $settings = Broodle_Engage_Settings::get_settings();
        
        if ( empty( $api_key ) ) {
            $this->api_key = $settings['api_key'] ?? '';
        } else {
            $this->api_key = $api_key;
        }
        
        $this->account_id = absint( $settings['chatwoot_account_id'] ?? 0 );
        $this->inbox_id = absint( $settings['chatwoot_inbox_id'] ?? 0 );
    }

    /**
     * Send template message via Broodle WhatsApp API
     *
     * @param string $phone_number Recipient phone number.
     * @param string $template_name Template name.
     * @param array  $template_vars Template variables.
     * @param string $media_uri Optional media URI.
     * @param string $template_lang Template language code (e.g. 'en', 'en_US').
     * @param string $template_body Template body text with placeholders.
     * @return array|WP_Error
     */
    public function send_template_message( $phone_number, $template_name, $template_vars = array(), $media_uri = '', $template_lang = '', $template_body = '' ) {
        // Validate credentials
        if ( empty( $this->api_key ) ) {
            return new WP_Error( 'missing_credentials', __( 'API access token is not configured.', 'broodle-engage-connector' ) );
        }

        if ( empty( $this->account_id ) ) {
            return new WP_Error( 'missing_account_id', __( 'Account ID is not configured.', 'broodle-engage-connector' ) );
        }

        if ( empty( $this->inbox_id ) ) {
            return new WP_Error( 'missing_inbox_id', __( 'WhatsApp Inbox ID is not configured.', 'broodle-engage-connector' ) );
        }

        if ( empty( $phone_number ) ) {
            return new WP_Error( 'missing_phone', __( 'Phone number is required.', 'broodle-engage-connector' ) );
        }

        if ( empty( $template_name ) ) {
            return new WP_Error( 'missing_template', __( 'Template name is required.', 'broodle-engage-connector' ) );
        }

        // Format phone number
        $formatted_phone = $this->format_phone_number( $phone_number );
        if ( is_wp_error( $formatted_phone ) ) {
            return $formatted_phone;
        }

        // Step 1: Find or create contact
        $contact = $this->find_or_create_contact( $formatted_phone );
        if ( is_wp_error( $contact ) ) {
            return $contact;
        }

        $contact_id = $contact['id'];

        // Step 2: Ensure contact has inbox association
        $source_id = $this->ensure_contact_inbox( $contact_id, $formatted_phone );
        if ( is_wp_error( $source_id ) ) {
            return $source_id;
        }

        // Step 3: Try to find an existing conversation for this contact in the same inbox
        $existing_conversation_id = $this->find_existing_conversation( $contact_id );

        if ( $existing_conversation_id ) {
            // Send template message to existing conversation
            $result = $this->send_template_to_conversation(
                $existing_conversation_id,
                $template_name,
                $template_vars,
                $media_uri,
                $template_lang,
                $template_body
            );

            if ( ! is_wp_error( $result ) ) {
                return $result;
            }

            // If sending to existing conversation failed, fall through to create new one
            error_log( 'Broodle Engage: Failed to send to existing conversation #' . $existing_conversation_id . ', creating new one. Error: ' . $result->get_error_message() );
        }

        // Step 4: Create new conversation with template message
        $result = $this->create_conversation_with_template(
            $contact_id,
            $source_id,
            $template_name,
            $template_vars,
            $media_uri,
            $template_lang,
            $template_body
        );

        return $result;
    }

    /**
     * Find existing contact or create new one
     *
     * @param string $phone_number Formatted phone number.
     * @return array|WP_Error Contact data or error.
     */
    private function find_or_create_contact( $phone_number ) {
        // First, try to search for existing contact
        $search_result = $this->search_contact_by_phone( $phone_number );
        
        if ( ! is_wp_error( $search_result ) && ! empty( $search_result ) ) {
            return $search_result;
        }

        // Contact not found, create new one
        return $this->create_contact( $phone_number );
    }

    /**
     * Search for contact by phone number
     *
     * @param string $phone_number Phone number to search.
     * @return array|WP_Error Contact data or error.
     */
    private function search_contact_by_phone( $phone_number ) {
        // Remove + for search (API search has issues with +)
        $search_phone = ltrim( $phone_number, '+' );
        
        $endpoint = "/api/v1/accounts/{$this->account_id}/contacts/search";
        $url = self::API_BASE_URL . $endpoint . '?q=' . urlencode( $search_phone );

        $response = $this->make_request( $url, 'GET' );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        // Check if we found any contacts
        if ( isset( $response['payload'] ) && is_array( $response['payload'] ) && ! empty( $response['payload'] ) ) {
            // Find exact match by phone number
            foreach ( $response['payload'] as $contact ) {
                $contact_phone = $contact['phone_number'] ?? '';
                // Normalize both for comparison
                $normalized_contact = preg_replace( '/[^\d]/', '', $contact_phone );
                $normalized_search = preg_replace( '/[^\d]/', '', $phone_number );
                
                if ( $normalized_contact === $normalized_search ) {
                    return $contact;
                }
            }
        }

        return new WP_Error( 'contact_not_found', __( 'Contact not found.', 'broodle-engage-connector' ) );
    }

    /**
     * Create a new contact
     *
     * @param string $phone_number Phone number.
     * @param string $name Optional contact name.
     * @return array|WP_Error Contact data or error.
     */
    private function create_contact( $phone_number, $name = '' ) {
        $endpoint = "/api/v1/accounts/{$this->account_id}/contacts";
        $url = self::API_BASE_URL . $endpoint;

        $data = array(
            'inbox_id'     => $this->inbox_id,
            'phone_number' => $phone_number,
            'name'         => ! empty( $name ) ? $name : $phone_number,
        );

        $response = $this->make_request( $url, 'POST', $data );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        // Handle response - API returns contact in payload
        if ( isset( $response['payload'] ) && isset( $response['payload']['contact'] ) ) {
            return $response['payload']['contact'];
        }
        
        if ( isset( $response['id'] ) ) {
            return $response;
        }

        return new WP_Error( 'contact_creation_failed', __( 'Failed to create contact.', 'broodle-engage-connector' ) );
    }

    /**
     * Ensure contact has inbox association and return source_id
     *
     * @param int    $contact_id Contact ID.
     * @param string $phone_number Phone number for source_id.
     * @return string|WP_Error Source ID or error.
     */
    private function ensure_contact_inbox( $contact_id, $phone_number ) {
        // First, check if contact already has this inbox
        $contact = $this->get_contact( $contact_id );
        
        if ( ! is_wp_error( $contact ) && isset( $contact['contact_inboxes'] ) ) {
            foreach ( $contact['contact_inboxes'] as $contact_inbox ) {
                if ( isset( $contact_inbox['inbox']['id'] ) && 
                     absint( $contact_inbox['inbox']['id'] ) === $this->inbox_id ) {
                    return $contact_inbox['source_id'];
                }
            }
        }

        // Create contact inbox association
        $endpoint = "/api/v1/accounts/{$this->account_id}/contacts/{$contact_id}/contact_inboxes";
        $url = self::API_BASE_URL . $endpoint;

        // For WhatsApp, source_id should be the phone number without +
        $source_id = ltrim( $phone_number, '+' );

        $data = array(
            'inbox_id'  => $this->inbox_id,
            'source_id' => $source_id,
        );

        $response = $this->make_request( $url, 'POST', $data );

        if ( is_wp_error( $response ) ) {
            // If already exists, try to get it from contact
            if ( strpos( $response->get_error_message(), 'already' ) !== false ||
                 strpos( $response->get_error_message(), 'exists' ) !== false ) {
                return $source_id;
            }
            return $response;
        }

        return $response['source_id'] ?? $source_id;
    }

    /**
     * Get contact details
     *
     * @param int $contact_id Contact ID.
     * @return array|WP_Error Contact data or error.
     */
    private function get_contact( $contact_id ) {
        $endpoint = "/api/v1/accounts/{$this->account_id}/contacts/{$contact_id}";
        $url = self::API_BASE_URL . $endpoint;

        return $this->make_request( $url, 'GET' );
    }

    /**
     * Find an existing conversation for a contact in the current WhatsApp inbox.
     *
     * @param int $contact_id Contact ID.
     * @return int|false Conversation ID if found, false otherwise.
     */
    private function find_existing_conversation( $contact_id ) {
        $endpoint = "/api/v1/accounts/{$this->account_id}/contacts/{$contact_id}/conversations";
        $url = self::API_BASE_URL . $endpoint;

        $response = $this->make_request( $url, 'GET' );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        $conversations = $response['payload'] ?? $response;
        if ( ! is_array( $conversations ) ) {
            return false;
        }

        // Look for a conversation in the same inbox, prefer open ones
        $best_match = null;
        foreach ( $conversations as $conversation ) {
            $conv_inbox_id = $conversation['inbox_id'] ?? 0;
            if ( absint( $conv_inbox_id ) !== $this->inbox_id ) {
                continue;
            }

            $status = $conversation['status'] ?? '';

            // Prefer open conversations
            if ( $status === 'open' ) {
                return $conversation['id'];
            }

            // Accept resolved conversations as fallback (they get re-opened on new message)
            if ( null === $best_match && in_array( $status, array( 'resolved', 'pending' ), true ) ) {
                $best_match = $conversation['id'];
            }
        }

        return $best_match ? $best_match : false;
    }

    /**
     * Send a template message to an existing conversation.
     *
     * @param int    $conversation_id Conversation ID.
     * @param string $template_name Template name.
     * @param array  $template_vars Template variables.
     * @param string $media_uri Optional media URI.
     * @param string $template_lang Template language code.
     * @param string $template_body Template body text with placeholders.
     * @return array|WP_Error Result or error.
     */
    private function send_template_to_conversation( $conversation_id, $template_name, $template_vars, $media_uri = '', $template_lang = '', $template_body = '' ) {
        $endpoint = "/api/v1/accounts/{$this->account_id}/conversations/{$conversation_id}/messages";
        $url = self::API_BASE_URL . $endpoint;

        // Build template_params the same way as for new conversations
        $template_params = $this->build_template_params( $template_name, $template_vars, $media_uri, $template_lang );
        $content = $this->build_content_message( $template_name, $template_vars, $template_body );

        $data = array(
            'content'          => $content,
            'message_type'     => 'outgoing',
            'template_params'  => $template_params,
        );

        $response = $this->make_request( $url, 'POST', $data );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        // Parse message-level response
        if ( isset( $response['error'] ) || isset( $response['errors'] ) ) {
            $error_msg = isset( $response['error'] )
                ? ( is_array( $response['error'] ) ? ( $response['error']['message'] ?? wp_json_encode( $response['error'] ) ) : $response['error'] )
                : ( is_array( $response['errors'] ) ? implode( ', ', $response['errors'] ) : $response['errors'] );
            return new WP_Error( 'api_error', $error_msg );
        }

        $message_id = $response['id'] ?? '';

        return array(
            'success'         => true,
            'conversation_id' => $conversation_id,
            'message_id'      => $message_id,
            'status'          => 'sent',
            'status_message'  => sprintf( 'Message sent to conversation #%d', $conversation_id ),
            'response_data'   => $response,
        );
    }

    /**
     * Create conversation with template message
     *
     * @param int    $contact_id Contact ID.
     * @param string $source_id Source ID for the contact inbox.
     * @param string $template_name Template name.
     * @param array  $template_vars Template variables.
     * @param string $media_uri Optional media URI.
     * @param string $template_lang Template language code.
     * @param string $template_body Template body text with placeholders.
     * @return array|WP_Error Result or error.
     */
    private function create_conversation_with_template( $contact_id, $source_id, $template_name, $template_vars, $media_uri = '', $template_lang = '', $template_body = '' ) {
        $endpoint = "/api/v1/accounts/{$this->account_id}/conversations";
        $url = self::API_BASE_URL . $endpoint;

        // Build template_params for Broodle WhatsApp
        $template_params = $this->build_template_params( $template_name, $template_vars, $media_uri, $template_lang );

        // Build content message (displayed in inbox)
        $content = $this->build_content_message( $template_name, $template_vars, $template_body );

        $data = array(
            'source_id'  => $source_id,
            'inbox_id'   => $this->inbox_id,
            'contact_id' => $contact_id,
            'status'     => 'open',
            'message'    => array(
                'content'         => $content,
                'template_params' => $template_params,
            ),
        );

        $response = $this->make_request( $url, 'POST', $data );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        return $this->parse_send_response( $response );
    }

    /**
     * Build template params for Broodle WhatsApp API
     *
     * @param string $template_name Template name.
     * @param array  $template_vars Template variables.
     * @param string $media_uri Optional media URI.
     * @param string $template_lang Template language code override.
     * @return array Template params structure.
     */
    private function build_template_params( $template_name, $template_vars, $media_uri = '', $template_lang = '' ) {
        $settings = Broodle_Engage_Settings::get_settings();
        $template_category = $settings['template_category'] ?? 'UTILITY';

        // Use per-template language if provided, otherwise fall back to global setting.
        if ( empty( $template_lang ) ) {
            $template_lang = $settings['template_language'] ?? 'en_US';
        }

        $params = array(
            'name'     => $template_name,
            'category' => $template_category,
            'language' => $template_lang,
        );

        // Look up button variable info from template config
        $template_config = $settings['template_config'] ?? array();
        $button_variables = array();
        $body_var_count = null;

        foreach ( $template_config as $status => $config ) {
            if ( ( $config['template_name'] ?? '' ) === $template_name ) {
                $button_variables = $config['button_variables'] ?? array();
                if ( isset( $config['body_variable_count'] ) ) {
                    $body_var_count = intval( $config['body_variable_count'] );
                }
                break;
            }
        }

        // Determine body-only variable count
        if ( null === $body_var_count ) {
            $body_var_count = count( $template_vars );
        }

        // Build processed_params
        $processed_params = array();

        // Add body parameters (only body variables, not button-only ones)
        if ( ! empty( $template_vars ) && is_array( $template_vars ) ) {
            $body_params = array();
            $index = 1;
            foreach ( $template_vars as $i => $value ) {
                if ( $i >= $body_var_count ) {
                    break;
                }
                if ( ! empty( $value ) || $value === '0' ) {
                    $body_params[ (string) $index ] = (string) $value;
                    $index++;
                }
            }
            if ( ! empty( $body_params ) ) {
                $processed_params['body'] = $body_params;
            }
        }

        // Add header with media if provided
        if ( ! empty( $media_uri ) && filter_var( $media_uri, FILTER_VALIDATE_URL ) ) {
            $media_type = $this->detect_media_type( $media_uri );
            $processed_params['header'] = array(
                'media_url'  => $media_uri,
                'media_type' => $media_type,
            );
        }

        // Add button parameters if template has URL buttons with variables
        if ( ! empty( $button_variables ) && is_array( $button_variables ) ) {
            $buttons = array();
            foreach ( $button_variables as $btn_var ) {
                $var_num   = intval( $btn_var['var_num'] ?? 0 );
                $var_index = $var_num - 1;
                $value     = isset( $template_vars[ $var_index ] ) ? (string) $template_vars[ $var_index ] : '';
                $buttons[] = array(
                    'type'      => $btn_var['type'] ?? 'url',
                    'parameter' => $value,
                );
            }
            if ( ! empty( $buttons ) ) {
                $processed_params['buttons'] = $buttons;
            }
        }

        if ( ! empty( $processed_params ) ) {
            $params['processed_params'] = $processed_params;
        }

        return $params;
    }

    /**
     * Detect media type from URL
     *
     * @param string $url Media URL.
     * @return string Media type (image, video, document).
     */
    private function detect_media_type( $url ) {
        $extension = strtolower( pathinfo( parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION ) );
        
        $image_extensions = array( 'jpg', 'jpeg', 'png', 'gif', 'webp' );
        $video_extensions = array( 'mp4', 'avi', 'mov', 'webm' );
        
        if ( in_array( $extension, $image_extensions, true ) ) {
            return 'image';
        }
        
        if ( in_array( $extension, $video_extensions, true ) ) {
            return 'video';
        }
        
        return 'document';
    }

    /**
     * Build content message from template name and variables
     * This message is displayed in the inbox
     *
     * @param string $template_name Template name.
     * @param array  $template_vars Template variables.
     * @return string Content message.
     */
    private function build_content_message( $template_name, $template_vars = array(), $template_body = '' ) {
        // Use the actual template body text if provided
        $message = '';

        if ( ! empty( $template_body ) ) {
            $message = $template_body;
        } else {
            // Fallback: look up from saved template_config
            $settings = Broodle_Engage_Settings::get_settings();
            $template_config = $settings['template_config'] ?? array();
            foreach ( $template_config as $status => $config ) {
                if ( ( $config['template_name'] ?? '' ) === $template_name && ! empty( $config['template_body'] ) ) {
                    $message = $config['template_body'];
                    break;
                }
            }

            // Second fallback: legacy template_messages
            if ( empty( $message ) ) {
                $template_messages = $settings['template_messages'] ?? array();
                $message = $template_messages[ $template_name ] ?? '';
            }
        }

        if ( ! empty( $message ) ) {
            
            // Replace placeholders {{1}}, {{2}}, etc. with actual variable values
            if ( ! empty( $template_vars ) && is_array( $template_vars ) ) {
                $index = 1;
                foreach ( $template_vars as $value ) {
                    if ( ! empty( $value ) && $value !== '---' ) {
                        $message = str_replace( '{{' . $index . '}}', $value, $message );
                    }
                    $index++;
                }
            }
            
            // Remove any remaining unreplaced placeholders
            $message = preg_replace( '/\{\{\d+\}\}/', '', $message );
            
            return trim( $message );
        }
        
        // Last resort fallback: create a generic message with template name and variables
        $message_parts = array();
        $template_display = str_replace( '_', ' ', ucwords( $template_name, '_' ) );
        $message_parts[] = "📋 {$template_display}";
        
        if ( ! empty( $template_vars ) && is_array( $template_vars ) ) {
            $filtered_vars = array_filter( $template_vars, function( $v ) {
                return ! empty( $v ) && $v !== '---';
            });
            
            if ( ! empty( $filtered_vars ) ) {
                $message_parts[] = implode( ' | ', $filtered_vars );
            }
        }
        
        return implode( "\n", $message_parts );
    }

    /**
     * Test API connection
     *
     * @param string $api_key Optional API key for testing.
     * @return bool|WP_Error
     */
    public function test_connection( $api_key = '' ) {
        $test_api_key = ! empty( $api_key ) ? $api_key : $this->api_key;

        if ( empty( $test_api_key ) ) {
            return new WP_Error( 'missing_credentials', __( 'API access token is required.', 'broodle-engage-connector' ) );
        }

        // Test by fetching user profile - works with any valid API token
        $endpoint = "/api/v1/profile";
        $url = self::API_BASE_URL . $endpoint;

        $response = $this->make_request( $url, 'GET', array(), $test_api_key );

        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            if ( strpos( $error_message, '401' ) !== false || strpos( $error_message, '403' ) !== false ) {
                return new WP_Error( 'invalid_api_key', __( 'Invalid API access token.', 'broodle-engage-connector' ) );
            }
            return $response;
        }

        // Check if we got valid profile data
        if ( isset( $response['id'] ) || isset( $response['email'] ) || isset( $response['name'] ) ) {
            return true;
        }

        return new WP_Error( 'connection_failed', __( 'API connection test failed.', 'broodle-engage-connector' ) );
    }

    /**
     * Test full account and inbox access
     *
     * @return array Result with status and messages
     */
    public function test_full_access() {
        $results = array(
            'profile' => false,
            'account' => false,
            'inbox' => false,
            'messages' => array(),
        );

        // Step 1: Test profile access
        $profile_result = $this->test_connection();
        if ( is_wp_error( $profile_result ) ) {
            $results['messages'][] = 'Profile: ' . $profile_result->get_error_message();
            return $results;
        }
        $results['profile'] = true;
        $results['messages'][] = 'Profile: Connected successfully';

        // Step 2: Test account access
        if ( empty( $this->account_id ) ) {
            $results['messages'][] = 'Account: Account ID not configured';
            return $results;
        }

        $account_endpoint = "/api/v1/accounts/{$this->account_id}/contacts?page=1&per_page=1";
        $account_url = self::API_BASE_URL . $account_endpoint;
        $account_response = $this->make_request( $account_url, 'GET' );

        if ( is_wp_error( $account_response ) ) {
            $error_msg = $account_response->get_error_message();
            if ( strpos( $error_msg, '401' ) !== false || strpos( $error_msg, 'not authorized' ) !== false ) {
                $results['messages'][] = 'Account: Your API token does not have access to Account ID ' . $this->account_id . '. Please verify the Account ID is correct and your user has access to this account.';
            } else {
                $results['messages'][] = 'Account: ' . $error_msg;
            }
            return $results;
        }
        $results['account'] = true;
        $results['messages'][] = 'Account: Access verified for Account ID ' . $this->account_id;

        // Step 3: Test inbox access
        if ( empty( $this->inbox_id ) ) {
            $results['messages'][] = 'Inbox: Inbox ID not configured';
            return $results;
        }

        $inbox_endpoint = "/api/v1/accounts/{$this->account_id}/inboxes/{$this->inbox_id}";
        $inbox_url = self::API_BASE_URL . $inbox_endpoint;
        $inbox_response = $this->make_request( $inbox_url, 'GET' );

        if ( is_wp_error( $inbox_response ) ) {
            $results['messages'][] = 'Inbox: ' . $inbox_response->get_error_message();
            return $results;
        }
        $results['inbox'] = true;
        $inbox_name = $inbox_response['name'] ?? 'Unknown';
        $results['messages'][] = 'Inbox: Connected to "' . $inbox_name . '" (ID: ' . $this->inbox_id . ')';

        return $results;
    }

    /**
     * Make HTTP request to Broodle API
     *
     * @param string $url Full URL.
     * @param string $method HTTP method (GET, POST, PUT, DELETE).
     * @param array  $data Request data for POST/PUT.
     * @param string $api_key Optional API key override.
     * @return array|WP_Error
     */
    private function make_request( $url, $method = 'GET', $data = array(), $api_key = '' ) {
        $api_key = ! empty( $api_key ) ? $api_key : $this->api_key;

        $headers = array(
            'Content-Type'     => 'application/json',
            'api_access_token' => $api_key,
            'User-Agent'       => 'Broodle-Engage-Connector/' . BROODLE_ENGAGE_VERSION . ' (WordPress)',
        );

        $args = array(
            'method'    => $method,
            'headers'   => $headers,
            'timeout'   => self::HTTP_TIMEOUT,
            'sslverify' => true,
        );

        if ( in_array( $method, array( 'POST', 'PUT', 'PATCH' ), true ) && ! empty( $data ) ) {
            $args['body'] = wp_json_encode( $data );
        }

        // Add filters for customization
        $args = apply_filters( 'broodle_engage_api_request_args', $args, $url, $data );

        $response = wp_remote_request( $url, $args );

        if ( is_wp_error( $response ) ) {
            return new WP_Error(
                'http_request_failed',
                sprintf(
                    /* translators: %s: Error message */
                    __( 'HTTP request failed: %s', 'broodle-engage-connector' ),
                    $response->get_error_message()
                )
            );
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        $response_body = wp_remote_retrieve_body( $response );

        // Log the request for debugging
        $this->log_api_request( $url, $args, $response_code, $response_body );

        if ( $response_code < 200 || $response_code >= 300 ) {
            return new WP_Error(
                'api_error',
                sprintf(
                    /* translators: %1$d: HTTP status code, %2$s: Response body */
                    __( 'API request failed with status %1$d: %2$s', 'broodle-engage-connector' ),
                    $response_code,
                    $response_body
                )
            );
        }

        $decoded_response = json_decode( $response_body, true );

        if ( null === $decoded_response && ! empty( $response_body ) ) {
            return new WP_Error(
                'invalid_response',
                sprintf(
                    /* translators: %s: Raw response body */
                    __( 'Invalid JSON response from API. Raw response: %s', 'broodle-engage-connector' ),
                    substr( $response_body, 0, 200 ) . ( strlen( $response_body ) > 200 ? '...' : '' )
                )
            );
        }

        return $decoded_response ?? array();
    }

    /**
     * Parse send response from Broodle API
     *
     * @param array $response API response.
     * @return array|WP_Error
     */
    private function parse_send_response( $response ) {
        // Check for error in response
        if ( isset( $response['error'] ) ) {
            $error_msg = is_array( $response['error'] ) 
                ? ( $response['error']['message'] ?? wp_json_encode( $response['error'] ) )
                : $response['error'];
            return new WP_Error( 'api_error', $error_msg );
        }

        if ( isset( $response['errors'] ) ) {
            $error_msg = is_array( $response['errors'] ) 
                ? implode( ', ', $response['errors'] )
                : $response['errors'];
            return new WP_Error( 'api_error', $error_msg );
        }

        // Success - conversation created
        if ( isset( $response['id'] ) ) {
            $conversation_id = $response['id'];
            $message_id = '';
            
            // Try to get message ID from messages array
            if ( isset( $response['messages'] ) && is_array( $response['messages'] ) && ! empty( $response['messages'] ) ) {
                $last_message = end( $response['messages'] );
                $message_id = $last_message['id'] ?? '';
            }

            return array(
                'success'         => true,
                'conversation_id' => $conversation_id,
                'message_id'      => $message_id,
                'status'          => 'sent',
                'status_message'  => sprintf( 'Conversation #%d created', $conversation_id ),
                'response_data'   => $response,
            );
        }

        return new WP_Error(
            'unexpected_response',
            __( 'Unexpected API response format.', 'broodle-engage-connector' ) . ' Response: ' . wp_json_encode( $response )
        );
    }

    /**
     * Format phone number for WhatsApp
     *
     * @param string $phone_number Raw phone number.
     * @return string|WP_Error
     */
    private function format_phone_number( $phone_number ) {
        // Remove all non-digit characters except +
        $cleaned = preg_replace( '/[^\d+]/', '', $phone_number );

        if ( empty( $cleaned ) ) {
            return new WP_Error( 'invalid_phone', __( 'Invalid phone number format.', 'broodle-engage-connector' ) );
        }

        // If no country code, add default
        if ( 0 !== strpos( $cleaned, '+' ) ) {
            $default_country_code = Broodle_Engage_Settings::get_setting( 'country_code', '+1' );
            $cleaned = $default_country_code . $cleaned;
        }

        // Validate format (allow 10-15 digits after +)
        if ( ! preg_match( '/^\+\d{10,15}$/', $cleaned ) ) {
            return new WP_Error( 'invalid_phone_format', __( 'Phone number must be 10-15 digits with country code.', 'broodle-engage-connector' ) );
        }

        return $cleaned;
    }

    /**
     * Log API request for debugging
     *
     * @param string $url Request URL.
     * @param array  $args Request arguments.
     * @param int    $response_code Response code.
     * @param string $response_body Response body.
     */
    private function log_api_request( $url, $args, $response_code, $response_body ) {
        if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
            return;
        }

        $log_data = array(
            'url'           => $url,
            'method'        => $args['method'],
            'response_code' => $response_code,
            'response_body' => substr( $response_body, 0, 500 ),
            'timestamp'     => current_time( 'mysql' ),
        );

        // Optionally log to error_log in debug mode
        if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
            error_log( 'Broodle Engage API Request: ' . wp_json_encode( $log_data ) );
        }
    }

    /**
     * Get API status
     *
     * @return array
     */
    public function get_api_status() {
        $test_result = $this->test_connection();

        return array(
            'connected'    => ! is_wp_error( $test_result ),
            'error'        => is_wp_error( $test_result ) ? $test_result->get_error_message() : null,
            'last_checked' => current_time( 'mysql' ),
            'api_url'      => self::API_BASE_URL,
        );
    }

    /**
     * Validate template variables
     *
     * @param array $template_vars Template variables.
     * @return array Sanitized template variables.
     */
    public function validate_template_vars( $template_vars ) {
        if ( ! is_array( $template_vars ) ) {
            return array();
        }

        $sanitized = array();
        foreach ( $template_vars as $value ) {
            $clean_value = sanitize_text_field( (string) $value );
            if ( $clean_value === null ) {
                $clean_value = '';
            }
            $sanitized[] = $clean_value;
        }

        return $sanitized;
    }

    /**
     * Get rate limit information
     *
     * @return array
     */
    public function get_rate_limit_info() {
        return array(
            'limit'      => 1000,
            'remaining'  => 950,
            'reset_time' => time() + 3600,
        );
    }

    /**
     * Check if rate limited
     *
     * @return bool
     */
    public function is_rate_limited() {
        $rate_limit = $this->get_rate_limit_info();
        return $rate_limit['remaining'] <= 0;
    }

    /**
     * Get WhatsApp templates from Broodle API inbox
     *
     * @return array|WP_Error Array of templates or error
     */
    public function get_whatsapp_templates() {
        if ( empty( $this->api_key ) ) {
            return new WP_Error( 'missing_credentials', __( 'API access token is not configured.', 'broodle-engage-connector' ) );
        }

        if ( empty( $this->account_id ) ) {
            return new WP_Error( 'missing_account_id', __( 'Account ID is not configured.', 'broodle-engage-connector' ) );
        }

        if ( empty( $this->inbox_id ) ) {
            return new WP_Error( 'missing_inbox_id', __( 'WhatsApp Inbox ID is not configured.', 'broodle-engage-connector' ) );
        }

        $url = sprintf(
            '%s/api/v1/accounts/%d/inboxes/%d',
            self::API_BASE_URL,
            $this->account_id,
            $this->inbox_id
        );

        $response = wp_remote_get(
            $url,
            array(
                'headers' => array(
                    'api_access_token' => $this->api_key,
                    'Content-Type'     => 'application/json',
                ),
                'timeout' => self::HTTP_TIMEOUT,
            )
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( $status_code !== 200 ) {
            return new WP_Error(
                'api_error',
                sprintf( __( 'Failed to fetch templates: %s', 'broodle-engage-connector' ), $data['error'] ?? 'Unknown error' )
            );
        }

        // Extract message_templates from inbox data
        $templates = $data['message_templates'] ?? array();
        
        // Process templates to get useful info
        $processed = array();
        foreach ( $templates as $template ) {
            $body_text = '';
            $header_text = '';
            $footer_text = '';
            $variables = array();
            
            foreach ( $template['components'] ?? array() as $component ) {
                if ( $component['type'] === 'BODY' ) {
                    $body_text = $component['text'] ?? '';
                } elseif ( $component['type'] === 'HEADER' && isset( $component['text'] ) ) {
                    $header_text = $component['text'];
                } elseif ( $component['type'] === 'FOOTER' && isset( $component['text'] ) ) {
                    $footer_text = $component['text'];
                }
            }
            
            // Extract variables from body text
            preg_match_all( '/\{\{(\d+)\}\}/', $body_text . $header_text, $matches );
            if ( ! empty( $matches[1] ) ) {
                $variables = array_unique( $matches[1] );
                sort( $variables, SORT_NUMERIC );
            }
            
            $processed[] = array(
                'id'         => $template['id'] ?? '',
                'name'       => $template['name'] ?? '',
                'status'     => $template['status'] ?? '',
                'category'   => $template['category'] ?? '',
                'language'   => $template['language'] ?? '',
                'header'     => $header_text,
                'body'       => $body_text,
                'footer'     => $footer_text,
                'variables'  => $variables,
                'components' => $template['components'] ?? array(),
            );
        }
        
        return $processed;
    }
}
