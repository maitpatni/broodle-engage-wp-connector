<?php
/**
 * Plugin Name: Broodle Engage Connector
 * Plugin URI: https://broodle.host/engage
 * Description: Send WooCommerce order notifications to customers via WhatsApp using Broodle WhatsApp API. Supports order received, shipped, delivered, and failed/cancelled notifications.
 * Version: 3.1.1
 * Author: Broodle
 * Author URI: https://broodle.host
 * Text Domain: broodle-engage-connector
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.7
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 9.9
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package BroodleEngageConnector
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'BROODLE_ENGAGE_VERSION', '3.1.1' );
define( 'BROODLE_ENGAGE_PLUGIN_FILE', __FILE__ );
define( 'BROODLE_ENGAGE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BROODLE_ENGAGE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BROODLE_ENGAGE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main plugin class
 */
class Broodle_Engage_Connector {

    /**
     * Plugin instance
     *
     * @var Broodle_Engage_Connector
     */
    private static $instance = null;

    /**
     * Get plugin instance
     *
     * @return Broodle_Engage_Connector
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        add_action( 'plugins_loaded', array( $this, 'init' ) );
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
    }

    /**
     * Initialize plugin
     */
    public function init() {
        // Check if WooCommerce is active
        if ( ! $this->is_woocommerce_active() ) {
            add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
            return;
        }

        // Declare HPOS compatibility
        add_action( 'before_woocommerce_init', array( $this, 'declare_hpos_compatibility' ) );

        // Include required files
        $this->includes();

        // Initialize components
        $this->init_hooks();
    }

    /**
     * Include required files
     */
    private function includes() {
        require_once BROODLE_ENGAGE_PLUGIN_DIR . 'includes/class-broodle-engage-admin.php';
        require_once BROODLE_ENGAGE_PLUGIN_DIR . 'includes/class-broodle-engage-api.php';
        require_once BROODLE_ENGAGE_PLUGIN_DIR . 'includes/class-broodle-engage-notifications.php';
        require_once BROODLE_ENGAGE_PLUGIN_DIR . 'includes/class-broodle-engage-logger.php';
        require_once BROODLE_ENGAGE_PLUGIN_DIR . 'includes/class-broodle-engage-settings.php';
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Initialize admin interface
        if ( is_admin() ) {
            new Broodle_Engage_Admin();
        }

        // Initialize notifications handler
        new Broodle_Engage_Notifications();

        // Clean up any duplicate shipped statuses
        add_action( 'init', array( $this, 'cleanup_duplicate_statuses' ), 15 );

        // Add settings link to plugins page
        add_filter( 'plugin_action_links_' . BROODLE_ENGAGE_PLUGIN_BASENAME, array( $this, 'add_settings_link' ) );
    }

    /**
     * Check if WooCommerce is active
     *
     * @return bool
     */
    private function is_woocommerce_active() {
        return class_exists( 'WooCommerce' );
    }

    /**
     * Declare HPOS compatibility
     */
    public function declare_hpos_compatibility() {
        if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
        }
    }

    /**
     * Display notice if WooCommerce is not active
     */
    public function woocommerce_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p>
                <?php
                printf(
                    /* translators: %s: Plugin name */
                    esc_html__( '%s requires WooCommerce to be installed and active.', 'broodle-engage-connector' ),
                    '<strong>' . esc_html__( 'Broodle Engage Connector', 'broodle-engage-connector' ) . '</strong>'
                );
                ?>
            </p>
        </div>
        <?php
    }



    /**
     * Clean up any duplicate shipped statuses created by this plugin
     */
    public function cleanup_duplicate_statuses() {
        // Only run if WooCommerce is active
        if ( ! $this->is_woocommerce_active() ) {
            return;
        }

        // IMPORTANT: Do not remove existing shipped statuses as this can hide orders
        // The plugin should work with existing statuses, not remove them
        // This function is kept for compatibility but disabled to prevent order visibility issues

        // Original problematic code commented out:
        /*
        global $wp_post_statuses;

        if ( isset( $wp_post_statuses['wc-shipped'] ) ) {
            // Check if this was created by our plugin (not by another plugin)
            $status_obj = $wp_post_statuses['wc-shipped'];

            // Only remove if it looks like our basic registration
            if ( isset( $status_obj->label ) && $status_obj->label === 'Shipped' ) {
                unset( $wp_post_statuses['wc-shipped'] );
            }
        }
        */
    }

    /**
     * Add settings link to plugins page
     *
     * @param array $links Plugin action links.
     * @return array
     */
    public function add_settings_link( $links ) {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url( 'admin.php?page=broodle-engage-connector' ),
            esc_html__( 'Settings', 'broodle-engage-connector' )
        );
        $signup_link = sprintf(
            '<a href="%s" target="_blank" style="color: #0E5ECE; font-weight: 500;">%s</a>',
            'https://broodle.host/engage',
            esc_html__( 'Sign Up for Broodle Engage', 'broodle-engage-connector' )
        );
        $login_link = sprintf(
            '<a href="%s" target="_blank" style="color: #0E5ECE; font-weight: 500;">%s</a>',
            'https://engage.broodle.one',
            esc_html__( 'Login to Dashboard', 'broodle-engage-connector' )
        );
        array_unshift( $links, $settings_link );
        $links[] = $signup_link;
        $links[] = $login_link;
        return $links;
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        $this->create_tables();

        // Set default options
        $this->set_default_options();

        // Schedule cleanup cron
        if ( ! wp_next_scheduled( 'broodle_engage_cleanup_logs' ) ) {
            wp_schedule_event( time(), 'daily', 'broodle_engage_cleanup_logs' );
        }
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear scheduled cron
        wp_clear_scheduled_hook( 'broodle_engage_cleanup_logs' );
    }

    /**
     * Create database tables
     */
    private function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Notification logs table
        $table_name = $wpdb->prefix . 'broodle_engage_logs';
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            order_id bigint(20) NOT NULL,
            phone_number varchar(20) NOT NULL,
            template_name varchar(100) NOT NULL,
            status varchar(20) NOT NULL,
            response_data text,
            api_response text,
            error_message text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY order_id (order_id),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        // Check if we need to add the api_response column to existing installations
        $this->maybe_add_api_response_column();
    }

    /**
     * Add api_response column to existing installations
     */
    private function maybe_add_api_response_column() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'broodle_engage_logs';

        // Check if api_response column exists
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $column_exists = $wpdb->get_results(
            $wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                "SHOW COLUMNS FROM `" . esc_sql( $table_name ) . "` LIKE %s",
                'api_response'
            )
        );

        // Add column if it doesn't exist
        if ( empty( $column_exists ) ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $wpdb->query(
                "ALTER TABLE `" . esc_sql( $table_name ) . "` ADD COLUMN api_response text AFTER response_data"
            );
        }
    }

    /**
     * Set default plugin options
     */
    private function set_default_options() {
        $default_options = array(
            'api_key' => '',
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
            'phone_field' => 'billing_phone',
            'country_code' => '+1',
            'log_retention_days' => 30,
            'retry_attempts' => 3,
            'retry_delay' => 300, // 5 minutes
        );

        add_option( 'broodle_engage_settings', $default_options );
    }
}

// Initialize plugin
Broodle_Engage_Connector::get_instance();
