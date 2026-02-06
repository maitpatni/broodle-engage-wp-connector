<?php
/**
 * Notifications handler class
 *
 * @package BroodleEngageConnector
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Notifications handler class
 */
class Broodle_Engage_Notifications {

    /**
     * Constructor
     */
    public function __construct() {
        // Initialize safely after WooCommerce is fully loaded
        add_action( 'init', array( $this, 'safe_init' ), 20 );

        // Safe hooks that don't depend on WooCommerce
        add_action( 'broodle_engage_cleanup_logs', array( $this, 'cleanup_old_logs' ) );
        add_action( 'broodle_engage_retry_notification', array( $this, 'retry_notification' ), 10, 2 );
    }

    /**
     * Safe initialization after WordPress and WooCommerce are loaded
     */
    public function safe_init() {
        // STABILITY: Comprehensive WordPress and WooCommerce availability checks

        // Check WordPress core functions
        if ( ! function_exists( 'wp_schedule_single_event' ) || ! function_exists( 'current_time' ) ) {
            return; // WordPress core not fully loaded
        }

        // Only proceed if WooCommerce is active and available
        if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'wc_get_order' ) ) {
            return;
        }

        // STABILITY: Additional WooCommerce function checks
        if ( ! function_exists( 'wc_get_order_statuses' ) || ! function_exists( 'wc_get_orders' ) ) {
            return; // Essential WooCommerce functions not available
        }

        // Check if HPOS is enabled with error handling
        $hpos_enabled = false;
        try {
            $hpos_enabled = class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) &&
                           method_exists( 'Automattic\WooCommerce\Utilities\OrderUtil', 'custom_orders_table_usage_is_enabled' ) &&
                           \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
        } catch ( Exception $e ) {
            // HPOS check failed, default to false (legacy mode)
            $hpos_enabled = false;
        }

        // STABILITY: Use low priority to avoid conflicts with other plugins
        // Primary hook for order status changes - works for both HPOS and legacy
        add_action( 'woocommerce_order_status_changed', array( $this, 'handle_order_status_change' ), 999, 4 );

        // Add backup hooks for specific statuses with low priority
        add_action( 'woocommerce_order_status_cancelled', array( $this, 'handle_order_cancelled' ), 999, 2 );
        add_action( 'woocommerce_order_status_failed', array( $this, 'handle_order_failed' ), 999, 2 );
        add_action( 'woocommerce_order_status_processing', array( $this, 'handle_order_processing' ), 999, 2 );
        add_action( 'woocommerce_order_status_shipped', array( $this, 'handle_order_shipped' ), 999, 2 );

        // Legacy fallback for non-HPOS installations
        if ( ! $hpos_enabled ) {
            add_action( 'transition_post_status', array( $this, 'catch_post_status_transition' ), 9999, 3 );
        }

        // Add cron hook for delayed notifications
        add_action( 'broodle_engage_send_delayed_notification', array( $this, 'handle_delayed_notification' ), 10, 3 );

        // Add hook to check for pending delayed notifications occasionally (not on every page load)
        add_action( 'wp_loaded', array( $this, 'check_pending_delayed_notifications' ), 999 );
    }

    /**
     * Handle order status changes and trigger notifications
     *
     * @param int    $order_id Order ID.
     * @param string $old_status Old status.
     * @param string $new_status New status.
     * @param object $order Order object.
     */
    public function handle_order_status_change( $order_id, $old_status, $new_status, $order ) {
        // STABILITY: Early safety checks to prevent any WooCommerce disruption
        if ( ! function_exists( 'wc_get_order' ) || ! class_exists( 'WooCommerce' ) ) {
            return; // WooCommerce not available, exit silently
        }

        // Prevent conflicts by checking if we should process this order
        if ( ! $this->should_process_order( $order_id, $order ) ) {
            return;
        }

        // Wrap everything in try-catch to prevent breaking WooCommerce
        try {

        // Get settings to check status mapping
        $settings = Broodle_Engage_Settings::get_settings();
        $status_mapping = $settings['status_mapping'] ?? array();

        // Handle processing status (always triggers processing notification)
        if ( $new_status === 'processing' ) {
            $this->handle_order_processing_safe( $order_id, $order );
        }

        // Check if this status change should trigger any mapped notifications
        foreach ( $status_mapping as $notification_type => $mapped_status ) {
            // Check exact match first
            if ( $new_status === $mapped_status && ! empty( $mapped_status ) ) {
                $this->schedule_or_send_notification( $order_id, $notification_type, $order );
            }
            // Also check with wc- prefix (some systems might include it)
            elseif ( $new_status === "wc-{$mapped_status}" && ! empty( $mapped_status ) ) {
                $this->schedule_or_send_notification( $order_id, $notification_type, $order );
            }
            // Check without wc- prefix (in case mapped status has it)
            elseif ( "wc-{$new_status}" === $mapped_status && ! empty( $mapped_status ) ) {
                $this->schedule_or_send_notification( $order_id, $notification_type, $order );
            }
            // Check for ParcelPanel statuses (pp- prefix)
            elseif ( ( strpos( $mapped_status, 'pp-' ) === 0 && $new_status === $mapped_status ) ||
                     ( strpos( $new_status, 'pp-' ) === 0 && $new_status === $mapped_status ) ) {
                $this->schedule_or_send_notification( $order_id, $notification_type, $order );
            }
        }

        // Handle other standard statuses
        $standard_status_map = array(
            // Standard WooCommerce statuses
            'cancelled' => 'order_cancelled',
            'failed' => 'order_failed',
            'refunded' => 'order_refunded',
            'completed' => 'order_completed',

            // ParcelPanel statuses (without wc- prefix)
            'shipped' => 'order_shipped',
            'partial-shipped' => 'order_shipped',
            'delivered' => 'order_delivered',

            // Common shipping statuses (without wc- prefix)
            'out-for-delivery' => 'order_shipped',
            'dispatched' => 'order_shipped',
            'in-transit' => 'order_shipped',
            'ready-for-pickup' => 'order_shipped',
            'pickup-ready' => 'order_shipped',
            'picked-up' => 'order_delivered',
        );

        if ( isset( $standard_status_map[ $new_status ] ) ) {
            $notification_type = $standard_status_map[ $new_status ];
            $this->schedule_or_send_notification( $order_id, $notification_type, $order );
        }

        } catch ( Exception $e ) {
            // STABILITY: Silently handle exceptions to prevent breaking WooCommerce
            // Never throw exceptions that could break order processing
            return;
        } catch ( Error $e ) {
            // STABILITY: Handle fatal errors gracefully
            // Never let PHP errors break WooCommerce functionality
            return;
        } catch ( Throwable $e ) {
            // STABILITY: Catch all possible throwables (PHP 7+)
            // Ultimate safety net to prevent any disruption
            return;
        }
    }

    /**
     * Schedule or send notification based on delay settings
     *
     * @param int      $order_id Order ID.
     * @param string   $notification_type Notification type.
     * @param WC_Order $order Order object.
     */
    public function schedule_or_send_notification( $order_id, $notification_type, $order ) {
        try {
            $settings = Broodle_Engage_Settings::get_settings();

        // Check if this notification type has a delay configured
        $delay_minutes = intval( $settings['template_delays'][ $notification_type ] ?? 0 );

        if ( $delay_minutes > 0 ) {
            // Schedule delayed notification
            $timestamp = time() + ( $delay_minutes * 60 ); // Convert minutes to seconds
            $scheduled_time = date( 'Y-m-d H:i:s', $timestamp );

            // Get phone number for logging
            $phone = $this->get_phone_number( $order );

            // Log the scheduled notification immediately
            $log_id = Broodle_Engage_Logger::log_scheduled( $order_id, $phone, $notification_type, $scheduled_time, $delay_minutes );

            // Use WordPress cron to schedule the notification
            $result = wp_schedule_single_event( $timestamp, 'broodle_engage_send_delayed_notification', array(
                $order_id,
                $notification_type,
                $log_id
            ) );

            if ( $result === false ) {
                // Update log to show scheduling failed
                if ( $log_id ) {
                    Broodle_Engage_Logger::update_log_status( $log_id, Broodle_Engage_Logger::LOG_ERROR, array(), array(), 'Failed to schedule delayed notification' );
                }

                // Fallback to immediate sending
                $this->send_notification_safe( $order_id, $notification_type, $order );
            }

        } else {
            // Send immediately
            $this->send_notification_safe( $order_id, $notification_type, $order );
        }

        } catch ( Exception $e ) {
            // STABILITY: Fallback to immediate sending with safety
            try {
                $this->send_notification_safe( $order_id, $notification_type, $order );
            } catch ( Exception $fallback_error ) {
                // STABILITY: Even fallback failed, exit gracefully
                return;
            } catch ( Throwable $fallback_error ) {
                // STABILITY: Ultimate safety for fallback
                return;
            }
        } catch ( Throwable $e ) {
            // STABILITY: Handle any throwable in main try block
            return;
        }
    }

    /**
     * Handle delayed notification execution
     *
     * @param array $args Arguments containing order_id and notification_type.
     */
    public function handle_delayed_notification( $order_id, $notification_type = '', $log_id = 0 ) {
        try {
            // Handle both old array format and new individual parameters
            if ( is_array( $order_id ) ) {
                $args = $order_id;
                $order_id = $args['order_id'] ?? 0;
                $notification_type = $args['notification_type'] ?? '';
                $log_id = $args['log_id'] ?? 0;
            }

        if ( ! $order_id || ! $notification_type ) {
            return;
        }

        // Get the order
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            // Update log to show order not found
            if ( $log_id ) {
                Broodle_Engage_Logger::update_log_status( $log_id, Broodle_Engage_Logger::LOG_ERROR, array(), array(), 'Order not found when executing delayed notification' );
            }
            return;
        }

        // Send the notification and capture the result
        $result = $this->send_notification_safe( $order_id, $notification_type, $order );

        // Update the scheduled log entry with the actual result
        if ( $log_id ) {
            if ( is_wp_error( $result ) ) {
                // Notification failed
                Broodle_Engage_Logger::update_log_status(
                    $log_id,
                    Broodle_Engage_Logger::LOG_ERROR,
                    array(),
                    array(),
                    'Delayed notification failed: ' . $result->get_error_message()
                );
            } else {
                // Notification succeeded
                $response_data = is_array( $result ) ? $result : array( 'status' => 'sent' );
                Broodle_Engage_Logger::update_log_status(
                    $log_id,
                    Broodle_Engage_Logger::LOG_SUCCESS,
                    $response_data,
                    $response_data,
                    'Delayed notification sent successfully'
                );
            }
        }

        } catch ( Exception $e ) {
            if ( $log_id ) {
                Broodle_Engage_Logger::update_log_status( $log_id, Broodle_Engage_Logger::LOG_ERROR, array(), array(), 'Error executing delayed notification: ' . $e->getMessage() );
            }
        }
    }

    /**
     * Check for pending delayed notifications (fallback method)
     * This runs on every page load as a backup to WordPress cron
     */
    public function check_pending_delayed_notifications() {
        try {
            // Only run this occasionally to avoid performance issues
            $last_check = get_option( 'broodle_engage_last_delayed_check', 0 );
            $current_time = time();

        // Check every 5 minutes maximum
        if ( $current_time - $last_check < 300 ) {
            return;
        }

        update_option( 'broodle_engage_last_delayed_check', $current_time );

        // Get all scheduled events for our hook
        $scheduled_events = _get_cron_array();
        $processed_count = 0;

        foreach ( $scheduled_events as $timestamp => $events ) {
            // Only process events that are due
            if ( $timestamp > $current_time ) {
                continue;
            }

            if ( isset( $events['broodle_engage_send_delayed_notification'] ) ) {
                foreach ( $events['broodle_engage_send_delayed_notification'] as $event_key => $event ) {
                    $args = $event['args'];

                    // Execute the delayed notification
                    call_user_func_array( 'do_action', array_merge( array( 'broodle_engage_send_delayed_notification' ), $args ) );

                    // Remove the processed event
                    wp_unschedule_event( $timestamp, 'broodle_engage_send_delayed_notification', $args );

                    $processed_count++;
                }
            }
        }

        if ( $processed_count > 0 ) {
        }

        } catch ( Exception $e ) {
        }
    }

    /**
     * Handle completed order notification logic
     *
     * @param int      $order_id Order ID.
     * @param WC_Order $order Order object.
     */
    private function handle_completed_order_notification( $order_id, $order ) {
        $settings = Broodle_Engage_Settings::get_settings();

        // Check if shipped notification is enabled and template exists
        $shipped_enabled = ( $settings['enabled_notifications']['order_shipped'] ?? 'no' ) === 'yes';
        $shipped_template = $settings['templates']['order_shipped'] ?? '';

        // Check if completed notification is enabled and template exists
        $completed_enabled = ( $settings['enabled_notifications']['order_completed'] ?? 'no' ) === 'yes';
        $completed_template = $settings['templates']['order_completed'] ?? '';

        // Check if shipped notification was already sent
        $shipped_already_sent = false;
        if ( ! empty( $shipped_template ) ) {
            $shipped_already_sent = Broodle_Engage_Logger::is_notification_sent( $order_id, $shipped_template );
        }


        // Priority logic:
        // 1. If shipped notification is enabled and not sent yet, send shipped notification
        // 2. Otherwise, send completed notification if enabled
        if ( $shipped_enabled && ! empty( $shipped_template ) && ! $shipped_already_sent ) {
            $this->send_notification_safe( $order_id, 'order_shipped', $order );
        } elseif ( $completed_enabled && ! empty( $completed_template ) ) {
            $this->send_notification_safe( $order_id, 'order_completed', $order );
        }
    }







    /**
     * Check order save for status changes
     *
     * @param int $post_id Post ID.
     */
    public function check_order_save( $post_id ) {
        if ( get_post_type( $post_id ) === 'shop_order' ) {

            $order = wc_get_order( $post_id );
            if ( $order ) {
                $status = $order->get_status();

                // Check if status changed by comparing with stored meta (HPOS compatible)
                $old_status = $order->get_meta( '_broodle_engage_last_status', true );
                if ( $old_status && $old_status !== $status ) {
                    $this->handle_order_status_change( $post_id, $old_status, $status, $order );
                }

                // Store current status for next comparison (HPOS compatible)
                // Use update_post_meta to avoid infinite loops from $order->save()
                if ( $this->is_hpos_enabled() ) {
                    $order->update_meta_data( '_broodle_engage_last_status', $status );
                    // Don't call $order->save() here to prevent infinite loops
                    // The meta will be saved when WooCommerce saves the order
                } else {
                    // For legacy orders, use update_post_meta directly
                    update_post_meta( $post_id, '_broodle_engage_last_status', $status );
                }
            }
        }
    }

    /**
     * Catch post status transitions (legacy)
     *
     * @param string  $new_status New status.
     * @param string  $old_status Old status.
     * @param WP_Post $post Post object.
     */
    public function catch_post_status_transition( $new_status, $old_status, $post ) {
        if ( $post->post_type === 'shop_order' ) {

            // Remove wc- prefix for consistency
            $clean_old = str_replace( 'wc-', '', $old_status );
            $clean_new = str_replace( 'wc-', '', $new_status );

            // Manually trigger our status change handler
            if ( $clean_new !== $clean_old ) {
                $order = wc_get_order( $post->ID );
                if ( $order ) {
                    $this->handle_order_status_change( $post->ID, $clean_old, $clean_new, $order );
                }
            }
        }
    }



    /**
     * Handle order received notification
     *
     * @param int      $order_id Order ID.
     * @param WC_Order $order Order object.
     */
    public function handle_order_received( $order_id, $order = null ) {
        try {
            $this->send_notification( $order_id, 'order_received', $order );
        } catch ( Exception $e ) {
        }
    }

    /**
     * Safe handler for order processing notification
     *
     * @param int      $order_id Order ID.
     * @param WC_Order $order Order object.
     */
    public function handle_order_processing_safe( $order_id, $order = null ) {

        // Send notification immediately but safely (cron might not work reliably)
        $this->send_processing_notification_async( $order_id );
    }

    /**
     * Asynchronous processing notification handler
     *
     * @param int $order_id Order ID.
     */
    public function send_processing_notification_async( $order_id ) {

        try {
            // Double-check that WooCommerce is still available
            if ( ! function_exists( 'wc_get_order' ) ) {
                return;
            }

            $order = wc_get_order( $order_id );
            if ( ! $order ) {
                return;
            }

            $order_status = $order->get_status();

            // Only send if order is still in processing status
            if ( $order_status === 'processing' ) {
                $this->send_notification_safe( $order_id, 'order_processing', $order );
            } else {
            }
        } catch ( Exception $e ) {
        }
    }

    /**
     * Handle order processing notification (legacy method for compatibility)
     *
     * @param int      $order_id Order ID.
     * @param WC_Order $order Order object.
     */
    public function handle_order_processing( $order_id, $order = null ) {
        // Redirect to safe handler
        $this->handle_order_processing_safe( $order_id, $order );
    }

    /**
     * Handle order shipped notification
     *
     * @param int      $order_id Order ID.
     * @param WC_Order $order Order object.
     */
    public function handle_order_shipped( $order_id, $order = null ) {
        $this->send_notification( $order_id, 'order_shipped', $order );
    }

    /**
     * Handle order delivered notification
     *
     * @param int      $order_id Order ID.
     * @param WC_Order $order Order object.
     */
    public function handle_order_delivered( $order_id, $order = null ) {
        $this->send_notification( $order_id, 'order_delivered', $order );
    }

    /**
     * Handle order completed notification
     *
     * @param int      $order_id Order ID.
     * @param WC_Order $order Order object.
     */
    public function handle_order_completed( $order_id, $order = null ) {
        $this->send_notification( $order_id, 'order_completed', $order );
    }

    /**
     * Handle order cancelled notification
     *
     * @param int      $order_id Order ID.
     * @param WC_Order $order Order object.
     */
    public function handle_order_cancelled( $order_id, $order = null ) {
        $this->send_notification_safe( $order_id, 'order_cancelled', $order );
    }

    /**
     * Handle order failed notification
     *
     * @param int      $order_id Order ID.
     * @param WC_Order $order Order object.
     */
    public function handle_order_failed( $order_id, $order = null ) {
        $this->send_notification_safe( $order_id, 'order_failed', $order );
    }

    /**
     * Handle order refunded notification
     *
     * @param int      $order_id Order ID.
     * @param WC_Order $order Order object.
     */
    public function handle_order_refunded( $order_id, $order = null ) {
        $this->send_notification( $order_id, 'order_refunded', $order );
    }

    /**
     * Handle custom order status changes
     *
     * @param int    $order_id Order ID.
     * @param string $old_status Old status.
     * @param string $new_status New status.
     * @param object $order Order object.
     */
    public function handle_custom_status_change( $order_id, $old_status, $new_status, $order ) {
        // Prevent conflicts by checking if we should process this order
        if ( ! $this->should_process_order( $order_id, $order ) ) {
            return;
        }
        // Map custom statuses to our notification types
        // Support common shipping plugins and their status conventions
        $status_map = array(
            // ParcelPanel statuses
            'wc-shipped' => 'order_shipped',
            'wc-partial-shipped' => 'order_shipped',
            'wc-delivered' => 'order_delivered',

            // Common shipping plugin statuses
            'wc-out-for-delivery' => 'order_shipped',
            'wc-dispatched' => 'order_shipped',
            'wc-in-transit' => 'order_shipped',
            'wc-ready-for-pickup' => 'order_shipped',
            'wc-pickup-ready' => 'order_shipped',
            'wc-picked-up' => 'order_delivered',

            // Advanced Shipment Tracking statuses
            'wc-ast-shipped' => 'order_shipped',
            'wc-ast-delivered' => 'order_delivered',
            'wc-ast-out-for-delivery' => 'order_shipped',
            'wc-ast-in-transit' => 'order_shipped',
            'wc-ast-return-to-sender' => 'order_cancelled',

            // ShipStation and other plugin statuses
            'wc-shipstation-shipped' => 'order_shipped',
            'wc-ss-shipped' => 'order_shipped',
            'wc-delivered-to-customer' => 'order_delivered',
        );

        // Check with wc- prefix first
        $notification_type = $status_map[ 'wc-' . $new_status ] ?? null;

        // If not found, try without the prefix (some plugins don't use the wc- prefix)
        if ( !$notification_type && isset($status_map[$new_status]) ) {
            $notification_type = $status_map[$new_status];
        }

        if ( $notification_type ) {
            $this->send_notification_safe( $order_id, $notification_type, $order );
        }
    }

    /**
     * Send WhatsApp notification
     *
     * @param int      $order_id Order ID.
     * @param string   $notification_type Notification type.
     * @param WC_Order $order Order object.
     */
    private function send_notification( $order_id, $notification_type, $order = null ) {
        try {
            // Safety check - ensure WooCommerce is available
            if ( ! function_exists( 'wc_get_order' ) ) {
                return;
            }

            // Get order object if not provided
            if ( ! $order ) {
                $order = wc_get_order( $order_id );
            }

            if ( ! $order ) {
                return;
            }

        // Check if notifications are enabled for this type
        $settings = Broodle_Engage_Settings::get_settings();
        if ( 'yes' !== ( $settings['enabled_notifications'][ $notification_type ] ?? 'no' ) ) {
            return;
        }

        // Get template name
        $template_name = $settings['templates'][ $notification_type ] ?? '';
        if ( empty( $template_name ) ) {
            return;
        }

        // Check if notification already sent
        if ( Broodle_Engage_Logger::is_notification_sent( $order_id, $template_name ) ) {
            return;
        }

        // Get customer phone number
        $phone_number = $this->get_customer_phone( $order );
        if ( empty( $phone_number ) ) {
            Broodle_Engage_Logger::log(
                $order_id,
                '',
                $template_name,
                Broodle_Engage_Logger::LOG_ERROR,
                array(),
                array(),
                __( 'Customer phone number not found.', 'broodle-engage-connector' )
            );
            return;
        }

        // Prepare template variables (including coupon code if configured)
        $template_vars = $this->prepare_template_variables( $order, $notification_type );

        // Log the attempt with detailed template variable information
        $log_id = Broodle_Engage_Logger::log(
            $order_id,
            $phone_number,
            $template_name,
            Broodle_Engage_Logger::LOG_PENDING,
            array(
                'template_vars' => $template_vars,
                'template_vars_count' => count( $template_vars ),
                'notification_type' => $notification_type,
                'selected_variables' => $settings['template_variables'][ $notification_type ] ?? array()
            ),
            array()
        );

            // Send the notification
            $this->send_whatsapp_message( $order_id, $phone_number, $template_name, $template_vars, $log_id );
        } catch ( Exception $e ) {
            // Log error but don't break the order process
        }
    }

    /**
     * Safe version of send notification with extra error handling
     *
     * @param int      $order_id Order ID.
     * @param string   $notification_type Notification type.
     * @param WC_Order $order Order object.
     */
    private function send_notification_safe( $order_id, $notification_type, $order = null ) {

        try {
            // Extra safety checks
            if ( ! function_exists( 'wc_get_order' ) ) {
                return;
            }

            // Get order object if not provided
            if ( ! $order ) {
                $order = wc_get_order( $order_id );
            }

            if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
                return;
            }

            // Check if notifications are enabled for this type
            $settings = Broodle_Engage_Settings::get_settings();

            // NEW: Check new template_config first
            $template_config = $settings['template_config'][ $notification_type ] ?? null;
            
            if ( $template_config ) {
                // Use new configuration structure
                if ( empty( $template_config['enabled'] ) ) {
                    return;
                }
                $template_name = $template_config['template_name'] ?? '';
                $template_lang = $template_config['template_lang'] ?? '';
                $template_body = $template_config['template_body'] ?? '';
                $variable_map = $template_config['variable_map'] ?? array();
                $custom_text_values = $template_config['custom_text'] ?? array();
                $config_image_id = $template_config['image_id'] ?? 0;
                $use_product_image = ! empty( $template_config['use_product_image'] );
            } else {
                // Fallback to old configuration structure
                $notification_enabled = $settings['enabled_notifications'][ $notification_type ] ?? 'no';
                if ( 'yes' !== $notification_enabled ) {
                    return;
                }
                $template_name = $settings['templates'][ $notification_type ] ?? '';
                $template_lang = '';
                $template_body = '';
                $variable_map = null;
                $custom_text_values = array();
                $config_image_id = 0;
                $use_product_image = false;
            }

            if ( empty( $template_name ) ) {
                // Use appropriate fallback template based on notification type
                $fallback_templates = array(
                    'order_failed' => 'order_failed_default',
                    'order_cancelled' => 'order_cancelled_default',
                    'order_processing' => 'order_confirmation',
                    'order_completed' => 'order_confirmation',
                    'order_shipped' => 'order_shipped_default',
                );
                $template_name = $fallback_templates[ $notification_type ] ?? 'order_confirmation';
            }

            // Check if notification already sent
            if ( Broodle_Engage_Logger::is_notification_sent( $order_id, $template_name ) ) {
                return;
            }

            // Get customer phone number
            $phone_number = $this->get_customer_phone( $order );

            if ( empty( $phone_number ) ) {
                return;
            }

            // Prepare template variables using new mapping if available
            $template_vars = $this->prepare_template_variables( $order, $notification_type, $variable_map, $custom_text_values );

            // Log the attempt
            $log_id = Broodle_Engage_Logger::log(
                $order_id,
                $phone_number,
                $template_name,
                Broodle_Engage_Logger::LOG_PENDING,
                array(
                    'template_vars' => $template_vars,
                    'template_vars_count' => count( $template_vars ),
                    'notification_type' => $notification_type,
                    'variable_map' => $variable_map ?? array(),
                    'custom_text_values' => $custom_text_values,
                ),
                array()
            );

            if ( ! $log_id ) {
                return;
            }

            // Check if this notification type has an image attached
            $image_url = '';

            // If use_product_image is enabled, get the featured image of the first product
            if ( $use_product_image ) {
                $image_url = $this->get_product_featured_image_url( $order );
            }

            // Otherwise use uploaded image
            if ( empty( $image_url ) && ! empty( $config_image_id ) ) {
                $image_url = wp_get_attachment_url( $config_image_id );
            }
            
            // Fallback to old settings location
            if ( empty( $image_url ) && isset( $settings['template_images'][ $notification_type ] ) && ! empty( $settings['template_images'][ $notification_type ] ) ) {
                $image_id = $settings['template_images'][ $notification_type ];
                $image_url = wp_get_attachment_url( $image_id );
            }

            // Send the notification
            $this->send_whatsapp_message( $order_id, $phone_number, $template_name, $template_vars, $log_id, $image_url, $template_lang, $template_body );

        } catch ( Exception $e ) {
            // Log error with full context
        } catch ( Error $e ) {
            // Catch fatal errors too
        }
    }

    /**
     * Send WhatsApp message
     *
     * @param int    $order_id Order ID.
     * @param string $phone_number Phone number.
     * @param string $template_name Template name.
     * @param array  $template_vars Template variables.
     * @param int    $log_id Log ID.
     * @param string $image_url Optional image URL.
     * @param string $template_lang Template language code.
     */
    private function send_whatsapp_message( $order_id, $phone_number, $template_name, $template_vars, $log_id, $image_url = '', $template_lang = '', $template_body = '' ) {
        try {
            $api = new Broodle_Engage_API();

            // Check rate limiting
            if ( $api->is_rate_limited() ) {
                $this->schedule_retry( $order_id, $log_id, 'Rate limited' );
                return;
            }

            $response = $api->send_template_message( $phone_number, $template_name, $template_vars, $image_url, $template_lang, $template_body );

        if ( is_wp_error( $response ) ) {
            // Log error and schedule retry
            Broodle_Engage_Logger::update_log_status(
                $log_id,
                Broodle_Engage_Logger::LOG_ERROR,
                array(),
                array(), // No API response for errors
                $response->get_error_message()
            );

            $this->schedule_retry( $order_id, $log_id, $response->get_error_message() );
        } else {
            // Log success with API response
            Broodle_Engage_Logger::update_log_status(
                $log_id,
                Broodle_Engage_Logger::LOG_SUCCESS,
                $response,
                $response // Store the full API response
            );

            // Fire action for successful notification
            do_action( 'broodle_engage_notification_sent', $order_id, $phone_number, $template_name, $response );
        }
        } catch ( Exception $e ) {
            // STABILITY: Handle exceptions without breaking order processing

            // Safely update log with error
            if ( $log_id ) {
                try {
                    Broodle_Engage_Logger::update_log_status(
                        $log_id,
                        Broodle_Engage_Logger::LOG_ERROR,
                        array(),
                        array(),
                        $e->getMessage()
                    );
                } catch ( Exception $log_error ) {
                    // Even logging failed, but don't break anything
                    return;
                }
            }
        } catch ( Throwable $e ) {
            // STABILITY: Ultimate safety net for any throwable
            if ( $log_id ) {
                try {
                    Broodle_Engage_Logger::update_log_status(
                        $log_id,
                        Broodle_Engage_Logger::LOG_ERROR,
                        array(),
                        array(),
                        'Unexpected error occurred'
                    );
                } catch ( Exception $log_error ) {
                    // Silently fail to prevent any disruption
                }
            }
            return;
        }
    }

    /**
     * Get customer phone number from order
     *
     * @param WC_Order $order Order object.
     * @return string
     */
    private function get_customer_phone( $order ) {
        $phone_field = Broodle_Engage_Settings::get_setting( 'phone_field', 'billing_phone' );

        $phone = '';
        switch ( $phone_field ) {
            case 'billing_phone':
                $phone = $order->get_billing_phone();
                break;
            case 'shipping_phone':
                $phone = $order->get_shipping_phone();
                break;
            default:
                // Try custom field - HPOS compatible
                $phone = $order->get_meta( $phone_field, true );
                break;
        }

        // Fallback to billing phone if shipping phone is empty
        if ( empty( $phone ) && 'shipping_phone' === $phone_field ) {
            $phone = $order->get_billing_phone();
        }

        return $phone;
    }

    /**
     * Prepare template variables for order
     *
     * @param WC_Order $order Order object.
     * @param string   $notification_type Notification type.
     * @param array    $variable_map Optional variable mapping from new config.
     * @return array
     */
    private function prepare_template_variables( $order, $notification_type, $variable_map = null, $custom_text_values = array() ) {
        // Get settings
        $settings = Broodle_Engage_Settings::get_settings();
        
        // NEW: If variable_map provided from new template_config, use it
        if ( ! empty( $variable_map ) && is_array( $variable_map ) ) {
            return $this->prepare_variables_from_mapping( $order, $variable_map, $custom_text_values );
        }
        
        // LEGACY: Use old system
        $selected_variables = $settings['template_variables'][ $notification_type ] ?? array( 'full_name', 'order_id', 'order_total', '', '' );

        $template_vars = array();

        // Get settings for coupon code configuration
        $settings = Broodle_Engage_Settings::get_settings();
        $coupon_code = '';
        $coupon_position = 0;

        if ( isset( $settings['template_coupon_codes'][ $notification_type ] ) && ! empty( $settings['template_coupon_codes'][ $notification_type ] ) ) {
            $coupon_code = trim( $settings['template_coupon_codes'][ $notification_type ] );
            $coupon_position = intval( $settings['template_coupon_positions'][ $notification_type ] ?? 1 );

            // If coupon code is effectively empty after trimming, use "---"
            if ( empty( $coupon_code ) ) {
                $coupon_code = '---';
            }
        }

        // Process regular variables first - ALWAYS maintain array positions
        $regular_vars = array();
        foreach ( $selected_variables as $index => $variable_type ) {
            if ( empty( $variable_type ) ) {
                // Use "---" for empty slots to maintain array positions
                $regular_vars[ $index ] = '---';
                continue;
            }

            $value = $this->get_variable_value( $order, $variable_type );
            // CRITICAL FIX: Always add the value, use "---" if empty, to maintain template positions
            $regular_vars[ $index ] = ! empty( $value ) ? (string) $value : '---';
        }

        // Now build the final template vars array with coupon at specified position
        $template_vars = array();

        if ( ! empty( $coupon_code ) && $coupon_position >= 1 && $coupon_position <= 5 ) {
            // FIXED: Simplified coupon insertion logic to prevent index mismatches
            $final_vars = array();

            // First, create array with regular variables
            for ( $i = 0; $i < 5; $i++ ) {
                $final_vars[ $i ] = isset( $regular_vars[ $i ] ) ? $regular_vars[ $i ] : '---';
            }

            // Then insert coupon at specified position (1-based to 0-based conversion)
            $coupon_index = $coupon_position - 1;
            if ( $coupon_index >= 0 && $coupon_index < 5 ) {
                // Shift existing variables to make room for coupon
                array_splice( $final_vars, $coupon_index, 0, array( $coupon_code ) );
                // Keep only first 5 elements
                $template_vars = array_slice( $final_vars, 0, 5 );
            } else {
                $template_vars = array_slice( $final_vars, 0, 5 );
            }
        } else {
            // No coupon code, use regular variables and ensure exactly 5 elements
            $template_vars = array();
            for ( $i = 0; $i < 5; $i++ ) {
                $template_vars[ $i ] = isset( $regular_vars[ $i ] ) ? $regular_vars[ $i ] : '---';
            }
        }

        // Log the final template variables array

        // CRITICAL FIX: Remove empty trailing elements to prevent default template values
        $template_vars = $this->clean_template_variables( $template_vars );

        // Filter variables for customization
        $template_vars = apply_filters( 'broodle_engage_template_variables', $template_vars, $order, $notification_type );

        return $template_vars;
    }

    /**
     * Clean template variables to prevent default values being used
     *
     * @param array $template_vars Template variables array.
     * @return array Cleaned template variables.
     */
    private function clean_template_variables( $template_vars ) {
        if ( ! is_array( $template_vars ) ) {
            return array();
        }

        // Remove empty trailing elements to prevent WhatsApp from using default template values
        $cleaned_vars = array();
        $last_non_empty_index = -1;

        // Find the last non-empty variable (excluding "---" placeholders)
        for ( $i = count( $template_vars ) - 1; $i >= 0; $i-- ) {
            $value = trim( (string) $template_vars[ $i ] );
            if ( ! empty( $value ) && $value !== '---' ) {
                $last_non_empty_index = $i;
                break;
            }
        }

        // Include all variables up to the last non-empty one
        for ( $i = 0; $i <= $last_non_empty_index; $i++ ) {
            $cleaned_vars[] = (string) $template_vars[ $i ];
        }

        // Ensure we have at least one variable to prevent API errors
        if ( empty( $cleaned_vars ) ) {
            $cleaned_vars = array( '---' );
        }

        return $cleaned_vars;
    }

    /**
     * Get value for a specific template variable
     *
     * @param WC_Order $order Order object.
     * @param string   $variable_type Variable type.
     * @return string
     */
    private function get_variable_value( $order, $variable_type ) {
        switch ( $variable_type ) {
            case 'first_name':
                $value = $order->get_billing_first_name();
                return ! empty( $value ) ? $value : 'Customer';

            case 'last_name':
                $value = $order->get_billing_last_name();
                return ! empty( $value ) ? $value : '';

            case 'full_name':
                $first_name = $order->get_billing_first_name();
                $last_name = $order->get_billing_last_name();
                $full_name = trim( $first_name . ' ' . $last_name );

                if ( ! empty( $full_name ) ) {
                    return $full_name;
                } elseif ( ! empty( $first_name ) ) {
                    return $first_name;
                } else {
                    return 'Customer'; // Fallback to prevent empty name
                }

            case 'order_id':
                $order_number = $order->get_order_number();
                return ! empty( $order_number ) ? (string) $order_number : (string) $order->get_id();

            case 'order_total':
                return $this->get_clean_order_total( $order );

            case 'order_items':
                $items = array();
                foreach ( $order->get_items() as $item ) {
                    $item_name = $item->get_name();
                    if ( ! empty( $item_name ) ) {
                        $items[] = $item_name;
                    }
                }
                return ! empty( $items ) ? implode( ', ', $items ) : 'Order Items';

            case 'payment_url':
                $payment_url = $order->get_checkout_payment_url();
                return $this->add_engage_tracking( ! empty( $payment_url ) ? $payment_url : get_site_url() );

            case 'product_url':
                return $this->add_engage_tracking( $this->get_product_url( $order ) );

            default:
                return '';
        }
    }

    /**
     * Prepare template variables from new mapping configuration
     *
     * @param WC_Order $order Order object.
     * @param array    $variable_map Variable mapping from template_config.
     * @param array    $custom_text_values Custom text values from config.
     * @return array Template variables in order.
     */
    private function prepare_variables_from_mapping( $order, $variable_map, $custom_text_values = array() ) {
        $template_vars = array();
        
        // Sort by variable number (var_1, var_2, etc.)
        ksort( $variable_map );
        
        foreach ( $variable_map as $var_key => $mapping ) {
            if ( empty( $mapping ) ) {
                continue;
            }
            
            // Extract variable number from key (var_1 -> 1)
            $var_num = intval( str_replace( 'var_', '', $var_key ) );
            if ( $var_num < 1 ) {
                continue;
            }
            
            // Get the value based on mapping, passing custom text if mapping is custom_text
            $custom_text_for_var = $custom_text_values[ $var_key ] ?? '';
            $value = $this->get_mapped_variable_value( $order, $mapping, $custom_text_for_var );
            
            // Ensure we fill the array in correct positions (0-indexed)
            $template_vars[ $var_num - 1 ] = $value;
        }
        
        // Re-index the array to ensure consecutive values
        $final_vars = array();
        for ( $i = 0; $i < count( $template_vars ) + 1; $i++ ) {
            if ( isset( $template_vars[ $i ] ) && ! empty( $template_vars[ $i ] ) ) {
                $final_vars[] = $template_vars[ $i ];
            }
        }
        
        return $final_vars;
    }

    /**
     * Get value for a mapped variable type
     *
     * @param WC_Order $order Order object.
     * @param string   $mapping Variable mapping name.
     * @param string   $custom_text Custom text value if mapping is custom_text.
     * @return string
     */
    private function get_mapped_variable_value( $order, $mapping, $custom_text = '' ) {
        switch ( $mapping ) {
            case 'custom_text':
                // Return the custom text value provided
                return ! empty( $custom_text ) ? $custom_text : '---';
            
            case 'customer_name':
            case 'full_name':
                $first_name = $order->get_billing_first_name();
                $last_name = $order->get_billing_last_name();
                $full_name = trim( $first_name . ' ' . $last_name );
                return ! empty( $full_name ) ? $full_name : 'Customer';
                
            case 'customer_first_name':
            case 'first_name':
                $value = $order->get_billing_first_name();
                return ! empty( $value ) ? $value : 'Customer';
                
            case 'customer_last_name':
            case 'last_name':
                return $order->get_billing_last_name();
                
            case 'customer_email':
                return $order->get_billing_email();
                
            case 'order_id':
            case 'order_number':
                $order_number = $order->get_order_number();
                return ! empty( $order_number ) ? (string) $order_number : (string) $order->get_id();
                
            case 'order_total':
                return $this->get_clean_order_total( $order );
                
            case 'order_total_raw':
                return (string) $order->get_total();
                
            case 'order_date':
                return $order->get_date_created() ? $order->get_date_created()->date_i18n( get_option( 'date_format' ) ) : '';
                
            case 'order_status':
                return wc_get_order_status_name( $order->get_status() );
                
            case 'product_names':
            case 'order_items':
                $items = array();
                foreach ( $order->get_items() as $item ) {
                    $item_name = $item->get_name();
                    if ( ! empty( $item_name ) ) {
                        $items[] = $item_name;
                    }
                }
                return ! empty( $items ) ? implode( ', ', $items ) : 'Order Items';
                
            case 'product_count':
                return (string) $order->get_item_count();
                
            case 'shipping_address':
                return $order->get_formatted_shipping_address() ?: $order->get_formatted_billing_address();
                
            case 'billing_address':
                return $order->get_formatted_billing_address();
                
            case 'payment_method':
                return $order->get_payment_method_title();
                
            case 'shipping_method':
                return $order->get_shipping_method();
                
            case 'tracking_url':
                // Try common tracking meta fields
                $tracking_url = $order->get_meta( '_tracking_url' ) 
                    ?: $order->get_meta( 'tracking_url' )
                    ?: $order->get_meta( '_wc_shipment_tracking_items' );
                if ( is_array( $tracking_url ) && ! empty( $tracking_url[0]['tracking_link'] ) ) {
                    return $this->add_engage_tracking( $tracking_url[0]['tracking_link'] );
                }
                return $this->add_engage_tracking( $tracking_url ?: get_site_url() . '/track-order/' );
                
            case 'tracking_number':
                $tracking = $order->get_meta( '_tracking_number' ) 
                    ?: $order->get_meta( 'tracking_number' );
                return $tracking ?: '';
                
            case 'coupon_code':
                $coupons = $order->get_coupon_codes();
                return ! empty( $coupons ) ? implode( ', ', $coupons ) : '';
                
            case 'product_url':
                return $this->add_engage_tracking( $this->get_product_url( $order ) );
                
            case 'cart_url':
                return $this->add_engage_tracking( wc_get_cart_url() );
                
            case 'shop_url':
                return $this->add_engage_tracking( wc_get_page_permalink( 'shop' ) );
                
            case 'my_account_url':
                return $this->add_engage_tracking( wc_get_page_permalink( 'myaccount' ) );
                
            case 'site_name':
                return get_bloginfo( 'name' );
                
            default:
                // Try to get from get_variable_value for backward compatibility
                return $this->get_variable_value( $order, $mapping );
        }
    }

    /**
     * Get clean order total without HTML tags
     *
     * @param WC_Order $order Order object.
     * @return string
     */
    private function get_clean_order_total( $order ) {
        // Get formatted total and strip HTML tags
        $formatted_total = $order->get_formatted_order_total();
        $clean_total = strip_tags( $formatted_total );

        // Remove any extra whitespace and decode HTML entities
        $clean_total = trim( html_entity_decode( $clean_total, ENT_QUOTES, 'UTF-8' ) );

        // Remove duplicate currency symbols (e.g., ₹₹999.00 -> ₹999.00)
        $currency_symbol = get_woocommerce_currency_symbol();
        $clean_total = preg_replace('/(' . preg_quote( $currency_symbol, '/' ) . '){2,}/', $currency_symbol, $clean_total);

        // CRITICAL FIX: Use "---" when no amount data is available
        if ( empty( $clean_total ) ) {
            $raw_total = $order->get_total();
            if ( $raw_total > 0 ) {
                $clean_total = $currency_symbol . number_format( (float) $raw_total, 2 );
            } else {
                $clean_total = '---'; // Use --- when no amount data
            }
        }

        return $clean_total;
    }

    /**
     * Get product URL from order
     *
     * @param WC_Order $order Order object.
     * @return string
     */
    private function get_product_url( $order ) {
        // Get the first product from the order
        $items = $order->get_items();

        if ( empty( $items ) ) {
            // If no items, return shop URL
            return wc_get_page_permalink( 'shop' );
        }

        // Get the first item
        $first_item = reset( $items );
        $product_id = $first_item->get_product_id();

        if ( $product_id ) {
            $product_url = get_permalink( $product_id );
            if ( $product_url ) {
                return $product_url;
            }
        }

        // Fallback to shop URL if product URL not found
        return wc_get_page_permalink( 'shop' );
    }

    /**
     * Add engage tracking parameter to a URL.
     *
     * Appends ?engage=whatsapp (or &engage=whatsapp) so clicks
     * from WhatsApp messages can be identified in analytics.
     *
     * @param string $url The original URL.
     * @return string URL with engage tracking parameter.
     */
    private function add_engage_tracking( $url ) {
        if ( empty( $url ) || ! is_string( $url ) ) {
            return $url;
        }

        // Only add to valid HTTP(S) URLs
        if ( strpos( $url, 'http' ) !== 0 ) {
            return $url;
        }

        return add_query_arg( 'engage', 'whatsapp', $url );
    }

    /**
     * Get the featured image URL of the first product in the order
     *
     * @param WC_Order $order Order object.
     * @return string Featured image URL or empty string.
     */
    private function get_product_featured_image_url( $order ) {
        $items = $order->get_items();

        if ( empty( $items ) ) {
            return '';
        }

        $first_item = reset( $items );
        $product_id = $first_item->get_product_id();

        if ( ! $product_id ) {
            return '';
        }

        $thumbnail_id = get_post_thumbnail_id( $product_id );

        if ( ! $thumbnail_id ) {
            // Try parent product for variations
            $product = wc_get_product( $product_id );
            if ( $product && $product->is_type( 'variation' ) ) {
                $parent_id = $product->get_parent_id();
                if ( $parent_id ) {
                    $thumbnail_id = get_post_thumbnail_id( $parent_id );
                }
            }
        }

        if ( $thumbnail_id ) {
            $image_url = wp_get_attachment_url( $thumbnail_id );
            if ( $image_url ) {
                return $image_url;
            }
        }

        return '';
    }

    /**
     * Schedule retry for failed notification
     *
     * @param int    $order_id Order ID.
     * @param int    $log_id Log ID.
     * @param string $error_message Error message.
     */
    private function schedule_retry( $order_id, $log_id, $error_message ) {
        $settings = Broodle_Engage_Settings::get_settings();
        $retry_attempts = $settings['retry_attempts'];
        $retry_delay = $settings['retry_delay'];

        if ( $retry_attempts > 0 ) {
            // Get current retry count from log
            $log = Broodle_Engage_Logger::get_log( $log_id );
            $retry_count = $log ? ( $log->retry_count ?? 0 ) : 0;

            if ( $retry_count < $retry_attempts ) {
                // Schedule retry
                wp_schedule_single_event(
                    time() + $retry_delay,
                    'broodle_engage_retry_notification',
                    array( $order_id, $log_id )
                );

                // Update log status
                Broodle_Engage_Logger::update_log_status(
                    $log_id,
                    Broodle_Engage_Logger::LOG_RETRY,
                    array( 'retry_count' => $retry_count + 1 ),
                    array(), // No API response for retry
                    $error_message
                );
            }
        }
    }

    /**
     * Retry failed notification
     *
     * @param int $order_id Order ID.
     * @param int $log_id Log ID.
     */
    public function retry_notification( $order_id, $log_id ) {
        $log = Broodle_Engage_Logger::get_log( $log_id );
        if ( ! $log ) {
            return;
        }

        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        // Prepare template variables
        $template_vars = json_decode( $log->response_data, true );
        $template_vars = $template_vars['template_vars'] ?? array();

        // Retry sending
        $this->send_whatsapp_message( $order_id, $log->phone_number, $log->template_name, $template_vars, $log_id );
    }

    /**
     * Cleanup old logs
     */
    public function cleanup_old_logs() {
        $deleted = Broodle_Engage_Logger::cleanup_old_logs();
        if ( $deleted > 0 ) {
        }
    }

    /**
     * Send test notification
     *
     * @param string $phone_number Phone number.
     * @param string $template_name Template name.
     * @param array  $template_vars Template variables.
     * @return array|WP_Error
     */
    public function send_test_notification( $phone_number, $template_name, $template_vars = array() ) {
        $api = new Broodle_Engage_API();

        // Use default test variables if none provided
        if ( empty( $template_vars ) ) {
            $template_vars = array(
                'Test Customer',
                '12345',
                '$99.99',
                '---', // Use --- for missing data
                '---', // Use --- for missing data
            );
        }

        return $api->send_template_message( $phone_number, $template_name, $template_vars );
    }



    /**
     * Check if HPOS is enabled
     *
     * @return bool
     */
    private function is_hpos_enabled() {
        return class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) &&
               method_exists( 'Automattic\WooCommerce\Utilities\OrderUtil', 'custom_orders_table_usage_is_enabled' ) &&
               \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
    }

    /**
     * Get all available order statuses from WooCommerce and plugins
     *
     * @return array
     */
    private function get_all_available_order_statuses() {
        $statuses = array();

        // Get WooCommerce order statuses
        if ( function_exists( 'wc_get_order_statuses' ) ) {
            $wc_statuses = wc_get_order_statuses();
            foreach ( $wc_statuses as $key => $label ) {
                $clean_key = str_replace( 'wc-', '', $key );
                $statuses[ $clean_key ] = $label;
            }
        }

        // Get all registered post statuses that might be order statuses
        $post_statuses = get_post_stati( array(), 'objects' );
        foreach ( $post_statuses as $status_key => $status_obj ) {
            if ( strpos( $status_key, 'wc-' ) === 0 ) {
                $clean_key = str_replace( 'wc-', '', $status_key );
                if ( ! isset( $statuses[ $clean_key ] ) ) {
                    $statuses[ $clean_key ] = $status_obj->label ?? ucfirst( $clean_key );
                }
            }
        }

        return $statuses;
    }

    /**
     * Check if we should process this order to prevent conflicts
     *
     * @param int $order_id Order ID.
     * @param object $order Order object.
     * @return bool
     */
    private function should_process_order( $order_id, $order = null ) {
        // STABILITY: Comprehensive validation to prevent any issues

        // Basic validation
        if ( empty( $order_id ) || ! is_numeric( $order_id ) ) {
            return false;
        }

        // Ensure WooCommerce functions are available
        if ( ! function_exists( 'wc_get_order' ) ) {
            return false;
        }

        // Get order if not provided
        if ( ! $order ) {
            try {
                $order = wc_get_order( $order_id );
            } catch ( Exception $e ) {
                return false; // Failed to get order, skip processing
            }
        }

        // Validate order object thoroughly
        if ( ! $order || ! is_object( $order ) || ! method_exists( $order, 'get_status' ) ) {
            return false;
        }

        // Additional WC_Order validation
        if ( ! is_a( $order, 'WC_Order' ) && ! is_a( $order, 'WC_Abstract_Order' ) ) {
            return false;
        }

        // Skip if order is in draft, auto-draft, or trash status
        try {
            $status = $order->get_status();
            if ( in_array( $status, array( 'auto-draft', 'draft', 'trash' ), true ) ) {
                return false;
            }
        } catch ( Exception $e ) {
            return false; // Failed to get status, skip processing
        }

        // STABILITY: Skip processing during payment gateway operations
        // This prevents interference with Razorpay and other payment gateways
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            // Check if this is a payment-related AJAX request
            $action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';
            $payment_actions = array( 'wc_razorpay', 'razorpay', 'payment', 'checkout' );
            foreach ( $payment_actions as $payment_action ) {
                if ( strpos( $action, $payment_action ) !== false ) {
                    return false; // Skip during payment processing
                }
            }
        }

        // STABILITY: Allow filtering by other plugins with error handling
        try {
            return apply_filters( 'broodle_engage_should_process_order', true, $order_id, $order );
        } catch ( Exception $e ) {
            // If filter throws exception, default to true to maintain functionality
            return true;
        }
    }



}
