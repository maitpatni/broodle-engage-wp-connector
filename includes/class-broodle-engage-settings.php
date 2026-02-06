<?php
/**
 * Settings management class
 *
 * @package BroodleEngageConnector
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Settings management class
 */
class Broodle_Engage_Settings {

    /**
     * Option name for plugin settings
     */
    const OPTION_NAME = 'broodle_engage_settings';

    /**
     * Get plugin settings
     *
     * @return array
     */
    public static function get_settings() {
        $defaults = array(
            // Broodle WhatsApp API Settings
            'api_key' => '',                    // API Access Token
            'chatwoot_account_id' => '',        // Account ID
            'chatwoot_inbox_id' => '',          // WhatsApp Inbox ID
            'template_language' => 'en_US',        // Default template language
            'template_category' => 'UTILITY',   // Default template category
            
            // Template configurations
            'templates' => array(
                'order_received' => '',
                'order_processing' => '',
                'order_shipped' => '',
                'order_delivered' => '',
                'order_completed' => '',
                'order_cancelled' => '',
                'order_failed' => '',
                'order_refunded' => '',
            ),
            'enabled_notifications' => array(
                'order_received' => 'yes',
                'order_processing' => 'yes',
                'order_shipped' => 'yes',
                'order_delivered' => 'yes',
                'order_completed' => 'yes',
                'order_cancelled' => 'yes',
                'order_failed' => 'yes',
                'order_refunded' => 'no',
            ),
            'template_variables' => array(
                'order_received' => array( 'full_name', 'order_id', 'order_total', '', '' ),
                'order_processing' => array( 'full_name', 'order_id', 'order_total', '', '' ),
                'order_shipped' => array( 'full_name', 'order_id', 'order_total', '', '' ),
                'order_delivered' => array( 'full_name', 'order_id', 'order_total', '', '' ),
                'order_completed' => array( 'full_name', 'order_id', 'order_total', '', '' ),
                'order_cancelled' => array( 'full_name', 'order_id', 'order_total', '', '' ),
                'order_failed' => array( 'full_name', 'order_id', 'order_total', '', '' ),
                'order_refunded' => array( 'full_name', 'order_id', 'order_total', '', '' ),
            ),
            'status_mapping' => array(
                'order_shipped' => 'shipped',
                'order_delivered' => 'delivered',
            ),
            'template_images' => array(
                'order_failed' => '',
                'order_cancelled' => '',
            ),
            'template_coupon_codes' => array(
                'order_failed' => '',
                'order_cancelled' => '',
                'abandoned_cart_recovery' => '',
            ),
            'template_coupon_positions' => array(
                'order_failed' => '1',
                'order_cancelled' => '1',
                'abandoned_cart_recovery' => '1',
            ),
            'template_delays' => array(
                'order_failed' => '0',
                'order_cancelled' => '0',
            ),
            // Template message content (displayed in inbox)
            'template_messages' => array(
                'hello_world' => 'Hello World! 👋',
                'order_received' => 'Hi {{1}}, your order #{{2}} for {{3}} has been received. Thank you for shopping with us!',
                'order_processing' => 'Hi {{1}}, your order #{{2}} is now being processed. Total: {{3}}',
                'order_shipped' => 'Hi {{1}}, great news! Your order #{{2}} has been shipped. Total: {{3}}',
                'order_delivered' => 'Hi {{1}}, your order #{{2}} has been delivered. Total: {{3}}. Enjoy!',
                'order_completed' => 'Hi {{1}}, your order #{{2}} is complete. Total: {{3}}. Thank you!',
                'order_cancelled' => 'Hi {{1}}, your order #{{2}} has been cancelled. Amount: {{3}}',
                'order_failed' => 'Hi {{1}}, unfortunately your order #{{2}} payment failed. Amount: {{3}}',
                'order_refunded' => 'Hi {{1}}, your order #{{2}} has been refunded. Amount: {{3}}',
            ),
            'phone_field' => 'billing_phone',
            'country_code' => '+1',
            'log_retention_days' => 30,
            'retry_attempts' => 3,
            'retry_delay' => 300,
        );

        $settings = get_option( self::OPTION_NAME, $defaults );
        return wp_parse_args( $settings, $defaults );
    }

    /**
     * Update plugin settings
     *
     * @param array $settings Settings array.
     * @return bool
     */
    public static function update_settings( $settings ) {
        $current_settings = self::get_settings();
        $updated_settings = wp_parse_args( $settings, $current_settings );
        
        // Sanitize settings
        $updated_settings = self::sanitize_settings( $updated_settings );
        
        return update_option( self::OPTION_NAME, $updated_settings );
    }

    /**
     * Get specific setting value
     *
     * @param string $key Setting key.
     * @param mixed  $default Default value.
     * @return mixed
     */
    public static function get_setting( $key, $default = null ) {
        $settings = self::get_settings();
        return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
    }

    /**
     * Update specific setting
     *
     * @param string $key Setting key.
     * @param mixed  $value Setting value.
     * @return bool
     */
    public static function update_setting( $key, $value ) {
        $settings = self::get_settings();
        $settings[ $key ] = $value;
        return self::update_settings( $settings );
    }

    /**
     * Sanitize settings
     *
     * @param array $settings Settings array.
     * @return array
     */
    public static function sanitize_settings( $settings ) {
        $sanitized = array();

        // Sanitize API credentials
        $sanitized['api_key'] = sanitize_text_field( $settings['api_key'] ?? '' );
        $sanitized['chatwoot_account_id'] = absint( $settings['chatwoot_account_id'] ?? 0 );
        $sanitized['chatwoot_inbox_id'] = absint( $settings['chatwoot_inbox_id'] ?? 0 );
        $sanitized['template_language'] = sanitize_text_field( $settings['template_language'] ?? 'en_US' );
        $sanitized['template_category'] = sanitize_text_field( $settings['template_category'] ?? 'UTILITY' );

        // Sanitize templates
        $sanitized['templates'] = array();
        if ( isset( $settings['templates'] ) && is_array( $settings['templates'] ) ) {
            foreach ( $settings['templates'] as $key => $template ) {
                $sanitized['templates'][ sanitize_key( $key ) ] = sanitize_text_field( $template );
            }
        }

        // Sanitize enabled notifications
        $sanitized['enabled_notifications'] = array();
        if ( isset( $settings['enabled_notifications'] ) && is_array( $settings['enabled_notifications'] ) ) {
            foreach ( $settings['enabled_notifications'] as $key => $enabled ) {
                $sanitized['enabled_notifications'][ sanitize_key( $key ) ] = ( 'yes' === $enabled ) ? 'yes' : 'no';
            }
        }

        // Sanitize template variables
        $sanitized['template_variables'] = array();
        if ( isset( $settings['template_variables'] ) && is_array( $settings['template_variables'] ) ) {
            foreach ( $settings['template_variables'] as $status => $variables ) {
                if ( is_array( $variables ) ) {
                    $sanitized['template_variables'][ sanitize_key( $status ) ] = array();
                    for ( $i = 0; $i < 5; $i++ ) {
                        $sanitized['template_variables'][ sanitize_key( $status ) ][ $i ] = sanitize_key( $variables[ $i ] ?? '' );
                    }
                }
            }
        }

        // Sanitize status mapping
        $sanitized['status_mapping'] = array();
        if ( isset( $settings['status_mapping'] ) && is_array( $settings['status_mapping'] ) ) {
            foreach ( $settings['status_mapping'] as $notification_type => $wc_status ) {
                $sanitized['status_mapping'][ sanitize_key( $notification_type ) ] = sanitize_key( $wc_status );
            }
        }

        // Sanitize template images
        $sanitized['template_images'] = array();
        if ( isset( $settings['template_images'] ) && is_array( $settings['template_images'] ) ) {
            foreach ( $settings['template_images'] as $notification_type => $image_id ) {
                $sanitized['template_images'][ sanitize_key( $notification_type ) ] = absint( $image_id );
            }
        }

        // Sanitize template coupon codes
        $sanitized['template_coupon_codes'] = array();
        if ( isset( $settings['template_coupon_codes'] ) && is_array( $settings['template_coupon_codes'] ) ) {
            foreach ( $settings['template_coupon_codes'] as $notification_type => $coupon_code ) {
                $sanitized['template_coupon_codes'][ sanitize_key( $notification_type ) ] = sanitize_text_field( $coupon_code );
            }
        }

        // Sanitize template coupon positions
        $sanitized['template_coupon_positions'] = array();
        if ( isset( $settings['template_coupon_positions'] ) && is_array( $settings['template_coupon_positions'] ) ) {
            foreach ( $settings['template_coupon_positions'] as $notification_type => $position ) {
                $sanitized['template_coupon_positions'][ sanitize_key( $notification_type ) ] = absint( $position );
            }
        }

        // Sanitize template delays
        $sanitized['template_delays'] = array();
        if ( isset( $settings['template_delays'] ) && is_array( $settings['template_delays'] ) ) {
            foreach ( $settings['template_delays'] as $notification_type => $delay ) {
                $sanitized['template_delays'][ sanitize_key( $notification_type ) ] = absint( $delay );
            }
        }

        // Sanitize other settings
        // Preserve template_config (saved via AJAX on Templates tab)
        if ( isset( $settings['template_config'] ) && is_array( $settings['template_config'] ) ) {
            $sanitized['template_config'] = array();
            foreach ( $settings['template_config'] as $status => $tc ) {
                if ( ! is_array( $tc ) ) {
                    continue;
                }
                $sanitized['template_config'][ sanitize_key( $status ) ] = array(
                    'enabled'       => ! empty( $tc['enabled'] ) && 'false' !== $tc['enabled'],
                    'template_name' => sanitize_text_field( $tc['template_name'] ?? '' ),
                    'template_lang' => sanitize_text_field( $tc['template_lang'] ?? 'en' ),
                    'template_body' => sanitize_textarea_field( $tc['template_body'] ?? '' ),
                    'variable_map'  => array_map( 'sanitize_text_field', (array) ( $tc['variable_map'] ?? array() ) ),
                    'custom_text'   => array_map( 'sanitize_text_field', (array) ( $tc['custom_text'] ?? array() ) ),
                    'image_id'      => absint( $tc['image_id'] ?? 0 ),
                );
            }
        }

        // Preserve custom notification statuses (saved via AJAX on Templates tab)
        if ( isset( $settings['custom_notification_statuses'] ) && is_array( $settings['custom_notification_statuses'] ) ) {
            $sanitized['custom_notification_statuses'] = array();
            foreach ( $settings['custom_notification_statuses'] as $cs ) {
                if ( ! is_array( $cs ) ) {
                    continue;
                }
                $sanitized['custom_notification_statuses'][] = array(
                    'id'          => sanitize_key( $cs['id'] ?? '' ),
                    'name'        => sanitize_text_field( $cs['name'] ?? '' ),
                    'description' => sanitize_text_field( $cs['description'] ?? '' ),
                    'icon'        => sanitize_text_field( $cs['icon'] ?? '' ),
                    'wc_status'   => sanitize_key( $cs['wc_status'] ?? '' ),
                    'event_type'  => sanitize_key( $cs['event_type'] ?? '' ),
                );
            }
        }

        // Preserve template messages
        if ( isset( $settings['template_messages'] ) && is_array( $settings['template_messages'] ) ) {
            $sanitized['template_messages'] = array();
            foreach ( $settings['template_messages'] as $key => $msg ) {
                $sanitized['template_messages'][ sanitize_key( $key ) ] = sanitize_text_field( $msg );
            }
        }

        $sanitized['phone_field'] = sanitize_text_field( $settings['phone_field'] ?? 'billing_phone' );
        $sanitized['country_code'] = sanitize_text_field( $settings['country_code'] ?? '+1' );
        $sanitized['log_retention_days'] = absint( $settings['log_retention_days'] ?? 30 );
        $sanitized['retry_attempts'] = absint( $settings['retry_attempts'] ?? 3 );
        $sanitized['retry_delay'] = absint( $settings['retry_delay'] ?? 300 );

        return $sanitized;
    }

    /**
     * Get available order status options
     *
     * @return array
     */
    public static function get_order_status_options() {
        $statuses = array(
            'order_received' => __( 'Order Received', 'broodle-engage-connector' ),
            'order_processing' => __( 'Order Processing', 'broodle-engage-connector' ),
            'order_shipped' => __( 'Order Shipped', 'broodle-engage-connector' ),
            'order_delivered' => __( 'Order Delivered', 'broodle-engage-connector' ),
            'order_completed' => __( 'Order Completed', 'broodle-engage-connector' ),
            'order_cancelled' => __( 'Order Cancelled', 'broodle-engage-connector' ),
            'order_failed' => __( 'Order Failed', 'broodle-engage-connector' ),
            'order_refunded' => __( 'Order Refunded', 'broodle-engage-connector' ),
        );

        return apply_filters( 'broodle_engage_order_status_options', $statuses );
    }

    /**
     * Get phone field options
     *
     * @return array
     */
    public static function get_phone_field_options() {
        $fields = array(
            'billing_phone' => __( 'Billing Phone', 'broodle-engage-connector' ),
            'shipping_phone' => __( 'Shipping Phone', 'broodle-engage-connector' ),
        );

        return apply_filters( 'broodle_engage_phone_field_options', $fields );
    }

    /**
     * Validate API credentials
     *
     * @param string $api_key API key.
     * @return bool|WP_Error
     */
    public static function validate_api_credentials( $api_key ) {
        if ( empty( $api_key ) ) {
            return new WP_Error( 'missing_api_key', __( 'API key is required.', 'broodle-engage-connector' ) );
        }

        // Test API connection
        $api = new Broodle_Engage_API();
        $test_result = $api->test_connection( $api_key );

        if ( is_wp_error( $test_result ) ) {
            return $test_result;
        }

        return true;
    }

    /**
     * Reset settings to defaults
     *
     * @return bool
     */
    public static function reset_to_defaults() {
        delete_option( self::OPTION_NAME );
        return true;
    }

    /**
     * Get available template variables
     *
     * @return array
     */
    public static function get_template_variable_options() {
        return array(
            '' => __( 'None', 'broodle-engage-connector' ),
            'first_name' => __( 'First Name', 'broodle-engage-connector' ),
            'last_name' => __( 'Last Name', 'broodle-engage-connector' ),
            'full_name' => __( 'Full Name', 'broodle-engage-connector' ),
            'order_id' => __( 'Order ID', 'broodle-engage-connector' ),
            'order_total' => __( 'Order Total Amount', 'broodle-engage-connector' ),
            'order_items' => __( 'Order Item Names', 'broodle-engage-connector' ),
            'payment_url' => __( 'Payment URL', 'broodle-engage-connector' ),
            'product_url' => __( 'Product URL', 'broodle-engage-connector' ),
        );
    }
}
