<?php
/**
 * Admin interface class
 *
 * @package BroodleEngageConnector
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin interface class
 */
class Broodle_Engage_Admin {

    /**
     * Check if current user has permission to access plugin
     */
    private function user_can_access() {
        return current_user_can( 'manage_woocommerce' ) ||
               current_user_can( 'manage_options' ) ||
               current_user_can( 'edit_others_posts' ); // Editor capability
    }

    /**
     * Get WhatsApp icon for menu
     *
     * Returns a pre-encoded SVG data URI for the WhatsApp icon.
     */
    private function get_whatsapp_icon() {
        // Pre-encoded SVG to avoid runtime base64_encode usage.
        return 'data:image/svg+xml;base64,CiAgICAgICAgICAgIDxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB2aWV3Qm94PSIwIDAgMjQgMjQiIGZpbGw9IiMyNWQzNjYiPgogICAgICAgICAgICAgICAgPHBhdGggZD0iTTE3LjQ3MiAxNC4zODJjLS4yOTctLjE0OS0xLjc1OC0uODY3LTIuMDMtLjk2Ny0uMjczLS4wOTktLjQ3MS0uMTQ4LS42Ny4xNS0uMTk3LjI5Ny0uNzY3Ljk2Ni0uOTQgMS4xNjQtLjE3My4xOTktLjM0Ny4yMjMtLjY0NC4wNzUtLjI5Ny0uMTUtMS4yNTUtLjQ2My0yLjM5LTEuNDc1LS44ODMtLjc4OC0xLjQ4LTEuNzYxLTEuNjUzLTIuMDU5LS4xNzMtLjI5Ny0uMDE4LS40NTguMTMtLjYwNi4xMzQtLjEzMy4yOTgtLjM0Ny40NDYtLjUyLjE0OS0uMTc0LjE5OC0uMjk4LjI5OC0uNDk3LjA5OS0uMTk4LjA1LS4zNzEtLjAyNS0uNTItLjA3NS0uMTQ5LS42NjktMS42MTItLjkxNi0yLjIwNy0uMjQyLS41NzktLjQ4Ny0uNS0uNjY5LS41MS0uMTczLS4wMDgtLjM3MS0uMDEtLjU3LS4wMS0uMTk4IDAtLjUyLjA3NC0uNzkyLjM3Mi0uMjcyLjI5Ny0xLjA0IDEuMDE2LTEuMDQgMi40NzkgMCAxLjQ2MiAxLjA2NSAyLjg3NSAxLjIxMyAzLjA3NC4xNDkuMTk4IDIuMDk2IDMuMiA1LjA3NyA0LjQ4Ny43MDkuMzA2IDEuMjYyLjQ4OSAxLjY5NC42MjUuNzEyLjIyNyAxLjM2LjE5NSAxLjg3MS4xMTguNTcxLS4wODUgMS43NTgtLjcxOSAyLjAwNi0xLjQxMy4yNDgtLjY5NC4yNDgtMS4yODkuMTczLTEuNDEzLS4wNzQtLjEyNC0uMjcyLS4xOTgtLjU3LS4zNDdtLTUuNDIxIDcuNDAzaC0uMDA0YTkuODcgOS44NyAwIDAxLTUuMDMxLTEuMzc4bC0uMzYxLS4yMTQtMy43NDEuOTgyLjk5OC0zLjY0OC0uMjM1LS4zNzRhOS44NiA5Ljg2IDAgMDEtMS41MS01LjI2Yy4wMDEtNS40NSA0LjQzNi05Ljg4NCA5Ljg4OC05Ljg4NCAyLjY0IDAgNS4xMjIgMS4wMyA2Ljk4OCAyLjg5OGE5LjgyNSA5LjgyNSAwIDAxMi44OTMgNi45OTRjLS4wMDMgNS40NS00LjQzNyA5Ljg4NC05Ljg4NSA5Ljg4NG04LjQxMy0xOC4yOTdBMTEuODE1IDExLjgxNSAwIDAwMTIuMDUgMEM1LjQ5NSAwIC4xNiA1LjMzNS4xNTcgMTEuODkyYzAgMi4wOTYuNTQ3IDQuMTQyIDEuNTg4IDUuOTQ1TC4wNTcgMjRsNi4zMDUtMS42NTRhMTEuODgyIDExLjg4MiAwIDAwNS42ODMgMS40NDhoLjAwNWM2LjU1NCAwIDExLjg5LTUuMzM1IDExLjg5My0xMS44OTNBMTEuODIxIDExLjgyMSAwIDAwMjAuNDY1IDMuNDg4Ii8+CiAgICAgICAgICAgIDwvc3ZnPgogICAgICAgIA==';
    }

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
        add_action( 'wp_ajax_broodle_engage_test_api', array( $this, 'ajax_test_api' ) );
        add_action( 'wp_ajax_broodle_engage_send_test_message', array( $this, 'ajax_send_test_message' ) );
        add_action( 'wp_ajax_broodle_engage_quick_test', array( $this, 'ajax_quick_test' ) );
        add_action( 'wp_ajax_broodle_engage_test_failed_notification', array( $this, 'ajax_test_failed_notification' ) );
        add_action( 'wp_ajax_broodle_engage_fetch_templates', array( $this, 'ajax_fetch_templates' ) );
        add_action( 'wp_ajax_broodle_engage_save_template_config', array( $this, 'ajax_save_template_config' ) );





        // Add diagnostic notice for order statuses (only on plugin pages)
        add_action( 'admin_notices', array( $this, 'show_status_diagnostic' ) );

        // Add dashboard widget
        add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widget' ) );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __( 'Broodle Engage', 'broodle-engage-connector' ),
            __( 'Broodle Engage', 'broodle-engage-connector' ),
            'manage_woocommerce',
            'broodle-engage-connector',
            array( $this, 'admin_page' ),
            $this->get_whatsapp_icon(),
            56 // Position after WooCommerce (which is at 55.5)
        );

        // Add submenu pages with proper callback handling
        add_submenu_page(
            'broodle-engage-connector',
            __( 'Settings', 'broodle-engage-connector' ),
            __( 'Settings', 'broodle-engage-connector' ),
            'manage_woocommerce',
            'broodle-engage-connector',
            array( $this, 'admin_page' )
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting(
            'broodle_engage_settings_group',
            'broodle_engage_settings',
            array( $this, 'sanitize_settings' )
        );
    }

    /**
     * Enqueue admin scripts and styles
     *
     * @param string $hook_suffix Current admin page hook suffix.
     */
    public function enqueue_admin_scripts( $hook_suffix ) {
        if ( 'toplevel_page_broodle-engage-connector' !== $hook_suffix ) {
            return;
        }

        wp_enqueue_script(
            'broodle-wa-admin',
            BROODLE_ENGAGE_PLUGIN_URL . 'assets/js/admin.js',
            array( 'jquery' ),
            BROODLE_ENGAGE_VERSION,
            true
        );

        wp_enqueue_style(
            'broodle-wa-admin',
            BROODLE_ENGAGE_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            BROODLE_ENGAGE_VERSION
        );

        // Add custom menu styling and WhatsApp icon
        wp_add_inline_style( 'broodle-wa-admin', '
            #adminmenu .menu-icon-broodle-engage-connector div.wp-menu-image img {
                width: 20px;
                height: 20px;
                opacity: 0.6;
            }
            #adminmenu .menu-icon-broodle-engage-connector:hover div.wp-menu-image img,
            #adminmenu .menu-icon-broodle-engage-connector.wp-has-current-submenu div.wp-menu-image img {
                opacity: 1;
            }
            .dashicons-whatsapp:before {
                content: "💬";
                font-family: "Apple Color Emoji", "Segoe UI Emoji", "Noto Color Emoji", sans-serif;
                font-size: 18px;
                line-height: 1;
            }



            /* Fix main menu layout issues */
            .broodle-wa-admin .nav-tab-wrapper {
                border-bottom: 1px solid #ccd0d4;
                margin: 0;
                padding-top: 9px;
                padding-bottom: 0;
                line-height: inherit;
            }

            .broodle-wa-admin .tab-content {
                background: #fff;
                border: 1px solid #ccd0d4;
                border-top: none;
                padding: 20px;
                margin: 0;
            }

            .broodle-wa-admin .nav-tab {
                border: 1px solid #ccd0d4;
                border-bottom: none;
                background: #f1f1f1;
                color: #666;
                margin-left: 0.25em;
                padding: 10px 15px;
                text-decoration: none;
                font-weight: 600;
            }

            .broodle-wa-admin .nav-tab:hover {
                background-color: #fff;
                color: #464646;
            }

            .broodle-wa-admin .nav-tab-active {
                background: #fff;
                border-bottom: 1px solid #fff;
                color: #000;
                margin-bottom: -1px;
                padding-bottom: 11px;
            }

            .broodle-wa-admin .wrap {
                margin: 10px 20px 0 2px;
            }

            /* Responsive design for mobile */
            @media (max-width: 782px) {
                .broodle-wa-admin .stats-grid {
                    grid-template-columns: 1fr !important;
                    gap: 15px !important;
                }

                .broodle-wa-admin .nav-tab {
                    margin-bottom: 5px;
                    display: block;
                    text-align: center;
                }

                .broodle-wa-admin .wrap {
                    margin: 10px 10px 0 10px;
                }
            }

            @media (max-width: 600px) {
                .broodle-wa-admin .stats-grid {
                    grid-template-columns: 1fr !important;
                }
            }

            /* Settings page specific styling */
            .broodle-wa-admin .form-table {
                background: #fff;
                border: 1px solid #ccd0d4;
                border-radius: 4px;
                padding: 30px;
                margin-top: 20px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            }

            .broodle-wa-admin .form-table th {
                width: 220px;
                padding: 20px 20px 20px 20px !important;
                vertical-align: top;
                font-weight: 600;
                color: #23282d;
                text-align: left;
            }

            .broodle-wa-admin .form-table td {
                padding: 20px 20px 20px 10px !important;
            }

            .broodle-wa-admin .form-table tr {
                border-bottom: 1px solid #f0f0f1;
            }

            .broodle-wa-admin .form-table tr:last-child {
                border-bottom: none;
            }

            /* Override WordPress default form-table styles */
            .broodle-wa-admin .form-table th,
            .broodle-wa-admin .form-table td {
                border: none !important;
            }

            .broodle-wa-admin .form-table th {
                padding-left: 20px !important;
                box-sizing: border-box;
            }

            .broodle-wa-admin .form-table td {
                padding-right: 20px !important;
                box-sizing: border-box;
            }

            /* Ensure proper spacing before submit button */
            .broodle-wa-admin form .submit {
                margin-top: 0 !important;
            }

            .broodle-wa-admin .form-table {
                margin-bottom: 0 !important;
            }

            /* API Key Section Styling */
            .broodle-api-key-section {
                max-width: 700px;
                background: #fff;
                padding: 20px;
                border: 1px solid #e5e5e5;
                border-radius: 6px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            }

            .api-key-row {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 30px;
                padding-bottom: 25px;
                border-bottom: 2px solid #e5e5e5;
            }

            .broodle-api-input {
                width: 450px !important;
                font-family: Consolas, Monaco, monospace !important;
                font-size: 13px !important;
                padding: 10px 14px !important;
                border: 1px solid #ddd !important;
                border-radius: 4px !important;
                background-color: #fafafa !important;
            }

            .broodle-toggle-btn {
                min-width: 70px !important;
                padding: 10px 14px !important;
                font-size: 13px !important;
                height: auto !important;
                line-height: 1.2 !important;
                border-radius: 4px !important;
            }

            .api-test-section {
                margin-top: 20px;
                padding-top: 20px;
                background: #f9f9f9;
                padding: 20px;
                border-radius: 5px;
                border: 1px solid #e0e0e0;
            }

            .api-test-row {
                display: flex;
                align-items: center;
                gap: 15px;
                flex-wrap: wrap;
            }

            .api-test-row .button {
                margin: 0 !important;
                vertical-align: middle;
                padding: 10px 18px !important;
                font-weight: 500 !important;
            }

            /* API test result */
            #api-test-result {
                padding: 10px 15px;
                border-radius: 5px;
                font-size: 13px;
                font-weight: 500;
                min-width: 200px;
                display: inline-block;
                vertical-align: middle;
                margin-left: 15px;
                transition: all 0.3s ease;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }

            #api-test-result.success {
                background-color: #d4edda;
                border: 1px solid #c3e6cb;
                color: #155724;
            }

            #api-test-result.error {
                background-color: #f8d7da;
                border: 1px solid #f5c6cb;
                color: #721c24;
            }

            #api-test-result.loading {
                background-color: #d1ecf1;
                border: 1px solid #bee5eb;
                color: #0c5460;
            }

            #api-test-result:empty {
                display: none !important;
            }

            #api-test-result.success:before {
                content: "✓ ";
                font-weight: bold;
                margin-right: 5px;
            }

            #api-test-result.error:before {
                content: "✗ ";
                font-weight: bold;
                margin-right: 5px;
            }

            #api-test-result.loading:before {
                content: "⟳ ";
                font-weight: bold;
                margin-right: 5px;
                animation: broodle-spin 1s linear infinite;
            }

            @keyframes broodle-spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }

            /* Submit button styling */
            .broodle-wa-admin .submit {
                padding: 25px 30px 20px 30px !important;
                margin: 0 !important;
                background: #f9f9f9 !important;
                border-top: 1px solid #e0e0e0 !important;
                border-radius: 0 0 4px 4px !important;
                margin-top: 0 !important;
                text-align: left !important;
            }

            /* Override WordPress default submit button container */
            .broodle-wa-admin p.submit {
                padding: 25px 30px 20px 30px !important;
                margin: 0 !important;
                background: #f9f9f9 !important;
                border-top: 1px solid #e0e0e0 !important;
                border-radius: 0 0 4px 4px !important;
                text-align: left !important;
            }

            .broodle-wa-admin .submit .button-primary,
            .broodle-wa-admin p.submit .button-primary {
                padding: 12px 24px !important;
                font-size: 14px !important;
                font-weight: 600 !important;
                border-radius: 4px !important;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important;
                margin: 0 !important;
            }

            /* Ensure no margin on submit button container */
            .broodle-wa-admin form > p.submit:last-child {
                margin-bottom: 0 !important;
                margin-top: 0 !important;
            }

            /* Form field descriptions */
            .broodle-wa-admin .form-table .description {
                margin-top: 8px;
                color: #646970;
                font-size: 13px;
                line-height: 1.4;
            }

            /* Select and input field styling */
            .broodle-wa-admin .form-table select,
            .broodle-wa-admin .form-table input[type="text"],
            .broodle-wa-admin .form-table input[type="number"] {
                padding: 8px 12px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 14px;
                min-width: 250px;
            }

            .broodle-wa-admin .form-table select:focus,
            .broodle-wa-admin .form-table input[type="text"]:focus,
            .broodle-wa-admin .form-table input[type="number"]:focus {
                border-color: #0073aa;
                box-shadow: 0 0 0 1px #0073aa;
                outline: none;
            }

            /* Mobile responsive for settings */
            @media (max-width: 768px) {
                .broodle-wa-admin .form-table {
                    padding: 20px;
                }

                .broodle-wa-admin .form-table th {
                    width: auto;
                    display: block;
                    padding: 15px 20px 5px 20px !important;
                }

                .broodle-wa-admin .form-table td {
                    display: block;
                    padding: 5px 20px 15px 20px !important;
                }

                .broodle-api-key-section {
                    padding: 15px;
                }

                .api-key-row {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 12px;
                    margin-bottom: 30px;
                }

                .broodle-api-input {
                    width: 100% !important;
                    max-width: 350px !important;
                }

                .api-test-section {
                    margin-top: 25px;
                    padding: 15px;
                }

                .api-test-row {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 12px;
                }

                #api-test-result {
                    margin-left: 0;
                    margin-top: 8px;
                    width: 100%;
                    max-width: 350px;
                }

                .broodle-wa-admin .form-table select,
                .broodle-wa-admin .form-table input[type="text"],
                .broodle-wa-admin .form-table input[type="number"] {
                    min-width: 100%;
                    max-width: 350px;
                }
            }
        ' );

        // Enqueue media scripts for image selection
        wp_enqueue_media();
        wp_enqueue_script( 'wp-media' );

        wp_localize_script(
            'broodle-wa-admin',
            'broodle_engage_admin',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'broodle_engage_admin_nonce' ),
                'strings' => array(
                    'testing_api' => __( 'Testing API connection...', 'broodle-engage-connector' ),
                    'api_test_success' => __( 'API connection successful!', 'broodle-engage-connector' ),
                    'api_test_failed' => __( 'API connection failed. Please check your credentials.', 'broodle-engage-connector' ),
                    'sending_test' => __( 'Sending test message...', 'broodle-engage-connector' ),
                    'test_sent' => __( 'Test message sent successfully!', 'broodle-engage-connector' ),
                    'test_failed' => __( 'Failed to send test message.', 'broodle-engage-connector' ),
                ),
            )
        );
    }

    /**
     * Admin page callback
     */
    public function admin_page() {
        // Check user permissions
        if ( ! $this->user_can_access() ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'broodle-engage-connector' ) );
        }

        $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'settings';
        ?>
        <div class="wrap broodle-wa-admin">
            <h1 style="display: flex; align-items: center;">
                <img src="<?php echo esc_url( BROODLE_ENGAGE_PLUGIN_URL . 'assets/images/broodle-logo.png' ); ?>"
                     alt="Broodle Logo"
                     style="max-width: 80px; height: auto; margin-right: 12px; vertical-align: middle;" />
                <?php esc_html_e( 'Broodle Engage Connector', 'broodle-engage-connector' ); ?>
            </h1>

            <h2 class="nav-tab-wrapper">
                <a href="?page=broodle-engage-connector&tab=settings" class="nav-tab <?php echo esc_attr( 'settings' === $active_tab ? 'nav-tab-active' : '' ); ?>">
                    <?php esc_html_e( 'Settings', 'broodle-engage-connector' ); ?>
                </a>
                <a href="?page=broodle-engage-connector&tab=templates" class="nav-tab <?php echo esc_attr( 'templates' === $active_tab ? 'nav-tab-active' : '' ); ?>">
                    <?php esc_html_e( 'Templates', 'broodle-engage-connector' ); ?>
                </a>
                <a href="?page=broodle-engage-connector&tab=logs" class="nav-tab <?php echo esc_attr( 'logs' === $active_tab ? 'nav-tab-active' : '' ); ?>">
                    <?php esc_html_e( 'Logs', 'broodle-engage-connector' ); ?>
                </a>
                <a href="?page=broodle-engage-connector&tab=help" class="nav-tab <?php echo esc_attr( 'help' === $active_tab ? 'nav-tab-active' : '' ); ?>">
                    <?php esc_html_e( 'Help', 'broodle-engage-connector' ); ?>
                </a>
            </h2>

            <div class="tab-content">
                <?php
                switch ( $active_tab ) {
                    case 'templates':
                        $this->render_templates_tab();
                        break;
                    case 'logs':
                        $this->render_logs_tab();
                        break;
                    case 'help':
                        $this->render_help_tab();
                        break;
                    default:
                        $this->render_settings_tab();
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render settings tab
     */
    private function render_settings_tab() {
        $settings = Broodle_Engage_Settings::get_settings();
        ?>
        <style>
        /* Settings Page Layout Optimization */
        .wrap { max-width: none !important; margin-right: 20px !important; }
        .tab-content { max-width: none !important; }

        /* Settings Page Header — Blue Gradient matching Templates page */
        .settings-page-header {
            background: linear-gradient(135deg, #0E5ECE 0%, #0a4aa3 100%) !important;
            color: white !important;
            padding: 30px !important;
            border-radius: 16px !important;
            margin-bottom: 30px !important;
            box-shadow: 0 8px 32px rgba(14, 94, 206, 0.25) !important;
            position: relative !important;
            overflow: hidden !important;
        }
        .settings-page-header::before {
            content: '' !important;
            position: absolute !important;
            top: -50% !important;
            right: -20% !important;
            width: 60% !important;
            height: 200% !important;
            background: rgba(255,255,255,0.05) !important;
            transform: rotate(15deg) !important;
            pointer-events: none !important;
        }
        .settings-page-title {
            margin: 0 0 10px 0 !important;
            font-size: 26px !important;
            font-weight: 600 !important;
            color: white !important;
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            position: relative !important;
            z-index: 1 !important;
        }
        .settings-page-title .dashicons {
            font-size: 32px !important;
            width: 32px !important;
            height: 32px !important;
            color: white !important;
        }
        .settings-page-description {
            margin: 0 !important;
            color: rgba(255,255,255,0.9) !important;
            font-size: 15px !important;
            line-height: 1.5 !important;
            max-width: 800px !important;
            position: relative !important;
            z-index: 1 !important;
        }

        /* Enhanced Form Table */
        .settings-form-table {
            background: #fff !important;
            border: 1px solid #e1e5e9 !important;
            border-radius: 8px !important;
            padding: 0 !important;
            margin: 20px 0 !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
            overflow: hidden !important;
        }
        .settings-form-table th {
            background: #f8f9fa !important;
            border-bottom: 1px solid #e1e5e9 !important;
            padding: 20px 24px !important;
            font-weight: 600 !important;
            color: #1d2327 !important;
            font-size: 14px !important;
            width: 25% !important;
            vertical-align: top !important;
        }
        .settings-form-table td {
            padding: 20px 24px !important;
            border-bottom: 1px solid #f0f0f1 !important;
            vertical-align: top !important;
        }
        .settings-form-table tr:last-child th,
        .settings-form-table tr:last-child td {
            border-bottom: none !important;
        }
        .settings-form-table tr:hover {
            background: #f8f9fa !important;
        }

        /* API Key Section Enhancement */
        .broodle-api-key-section {
            display: flex !important;
            flex-direction: column !important;
            gap: 16px !important;
        }
        .api-key-row {
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            flex-wrap: wrap !important;
        }
        .broodle-api-input {
            flex: 1 !important;
            min-width: 300px !important;
            max-width: 500px !important;
            padding: 10px 12px !important;
            border: 1px solid #8c8f94 !important;
            border-radius: 4px !important;
            font-size: 14px !important;
            transition: border-color 0.2s ease, box-shadow 0.2s ease !important;
        }
        .broodle-api-input:focus {
            border-color: #2271b1 !important;
            box-shadow: 0 0 0 1px #2271b1 !important;
            outline: none !important;
        }
        .broodle-toggle-btn {
            padding: 8px 16px !important;
            font-size: 13px !important;
            border-radius: 4px !important;
            transition: all 0.2s ease !important;
        }
        .broodle-toggle-btn:hover {
            background: #f0f6fc !important;
            border-color: #2271b1 !important;
            color: #2271b1 !important;
        }

        /* API Test Section */
        .api-test-section {
            background: #f6f7f7 !important;
            border: 1px solid #e1e5e9 !important;
            border-radius: 6px !important;
            padding: 16px !important;
        }
        .api-test-row {
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            flex-wrap: wrap !important;
        }
        .api-test-section .button {
            padding: 8px 16px !important;
            font-size: 13px !important;
            border-radius: 4px !important;
            transition: all 0.2s ease !important;
        }
        .api-test-section .button:hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
        }
        #api-test-result {
            margin-left: 12px !important;
            font-weight: 500 !important;
            padding: 4px 8px !important;
            border-radius: 4px !important;
        }
        #api-test-result.success {
            background: #d1e7dd !important;
            color: #0f5132 !important;
            border: 1px solid #badbcc !important;
        }
        #api-test-result.error {
            background: #f8d7da !important;
            color: #721c24 !important;
            border: 1px solid #f5c2c7 !important;
        }

        /* Form Input Enhancements */
        .settings-form-table input[type="text"],
        .settings-form-table input[type="number"],
        .settings-form-table input[type="password"],
        .settings-form-table select {
            padding: 8px 12px !important;
            border: 1px solid #8c8f94 !important;
            border-radius: 4px !important;
            font-size: 14px !important;
            transition: border-color 0.2s ease, box-shadow 0.2s ease !important;
        }
        .settings-form-table input[type="text"]:focus,
        .settings-form-table input[type="number"]:focus,
        .settings-form-table input[type="password"]:focus,
        .settings-form-table select:focus {
            border-color: #2271b1 !important;
            box-shadow: 0 0 0 1px #2271b1 !important;
            outline: none !important;
        }
        .settings-form-table .small-text {
            width: 120px !important;
        }

        /* Description Text Enhancement */
        .settings-form-table .description {
            margin-top: 8px !important;
            color: #646970 !important;
            font-size: 13px !important;
            line-height: 1.4 !important;
        }
        .settings-form-table .description a {
            color: #2271b1 !important;
            text-decoration: none !important;
        }
        .settings-form-table .description a:hover {
            text-decoration: underline !important;
        }

        /* Submit Button Enhancement */
        .settings-submit-section {
            background: #fff !important;
            border: 1px solid #e1e5e9 !important;
            border-radius: 8px !important;
            padding: 20px 24px !important;
            margin: 20px 0 !important;
            text-align: right !important;
        }
        .settings-submit-section .button-primary {
            padding: 10px 24px !important;
            font-size: 14px !important;
            font-weight: 600 !important;
            border-radius: 4px !important;
            transition: all 0.2s ease !important;
        }
        .settings-submit-section .button-primary:hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 2px 4px rgba(34, 113, 177, 0.3) !important;
        }

        /* Responsive Design for Settings Page */
        @media (max-width: 1200px) {
            .settings-form-table th {
                width: 30% !important;
            }
            .broodle-api-input {
                min-width: 250px !important;
                max-width: 400px !important;
            }
        }

        @media (max-width: 782px) {
            .wrap { margin-right: 10px !important; }
            .settings-page-header { padding: 24px !important; margin-bottom: 20px !important; border-radius: 12px !important; }
            .settings-page-title {
                font-size: 20px !important;
                flex-direction: column !important;
                text-align: center !important;
                gap: 8px !important;
            }
            .settings-page-title .dashicons { font-size: 24px !important; }
            .settings-page-description {
                text-align: center !important;
                font-size: 13px !important;
            }

            /* Mobile Table Layout */
            .settings-form-table,
            .settings-form-table tbody,
            .settings-form-table tr,
            .settings-form-table td,
            .settings-form-table th {
                display: block !important;
                width: 100% !important;
            }
            .settings-form-table th {
                background: #f8f9fa !important;
                border-bottom: 1px solid #e1e5e9 !important;
                padding: 16px 20px 8px 20px !important;
                font-size: 15px !important;
                text-align: left !important;
            }
            .settings-form-table td {
                padding: 8px 20px 20px 20px !important;
                border-bottom: 2px solid #f0f0f1 !important;
            }

            .api-key-row {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 12px !important;
            }
            .broodle-api-input {
                min-width: auto !important;
                max-width: none !important;
                width: 100% !important;
            }
            .api-test-row {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 12px !important;
            }
            .api-test-section .button {
                width: 100% !important;
                text-align: center !important;
            }
            #api-test-result {
                margin-left: 0 !important;
                margin-top: 8px !important;
                text-align: center !important;
            }

            .settings-form-table .small-text {
                width: 100% !important;
                max-width: 200px !important;
            }
            .settings-submit-section {
                text-align: center !important;
                padding: 16px 20px !important;
            }
        }

        @media (max-width: 600px) {
            .settings-form-table th,
            .settings-form-table td {
                padding: 12px 16px !important;
            }
            .api-test-section {
                padding: 12px !important;
            }
            .settings-page-title {
                font-size: 18px !important;
            }
        }

        @media (max-width: 480px) {
            .wrap { margin-right: 5px !important; }
            .settings-page-header { padding: 16px !important; border-radius: 10px !important; }
            .settings-form-table th,
            .settings-form-table td {
                padding: 10px 12px !important;
            }
            .api-test-section {
                padding: 10px !important;
            }
            .settings-submit-section {
                padding: 12px 16px !important;
            }
        }
        </style>

        <div class="settings-page-header">
            <h2 class="settings-page-title">
                <span class="dashicons dashicons-whatsapp"></span>
                <?php esc_html_e( 'Broodle WhatsApp API Settings', 'broodle-engage-connector' ); ?>
            </h2>
            <p class="settings-page-description">
                <?php esc_html_e( 'Configure your Broodle WhatsApp API connection to send automated order notifications. Enter your API Access Token, Account ID, and WhatsApp Inbox ID to get started.', 'broodle-engage-connector' ); ?>
            </p>
        </div>

        <form method="post" action="options.php">
            <?php
            settings_fields( 'broodle_engage_settings_group' );
            wp_nonce_field( 'broodle_engage_settings_nonce', 'broodle_engage_settings_nonce' );
            ?>

            <table class="form-table settings-form-table">
                <tr>
                    <th scope="row" colspan="2" style="background: #e8f4f8; padding: 15px 24px !important;">
                        <h3 style="margin: 0; color: #1d2327; font-size: 16px;">
                            <span class="dashicons dashicons-admin-network" style="color: #2271b1;"></span>
                            <?php esc_html_e( 'Broodle WhatsApp API Configuration', 'broodle-engage-connector' ); ?>
                        </h3>
                    </th>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="api_key"><?php esc_html_e( 'API Access Token', 'broodle-engage-connector' ); ?></label>
                    </th>
                    <td>
                        <div class="broodle-api-key-section">
                            <div class="api-key-row">
                                <input type="password" id="api_key" name="broodle_engage_settings[api_key]" value="<?php echo esc_attr( $settings['api_key'] ); ?>" class="broodle-api-input" />
                                <button type="button" id="toggle-api-key" class="button button-secondary broodle-toggle-btn" onclick="broodleToggleApiKey()">
                                    <?php esc_html_e( 'Show', 'broodle-engage-connector' ); ?>
                                </button>
                            </div>
                            <div class="api-test-section">
                                <div class="api-test-row">
                                    <button type="button" id="test-api-connection" class="button button-secondary">
                                        <?php esc_html_e( 'Test Connection', 'broodle-engage-connector' ); ?>
                                    </button>
                                    <span id="api-test-result"></span>
                                </div>
                                <div class="api-test-row" style="margin-top: 12px;">
                                    <input type="text" id="quick-test-phone" placeholder="+919876543210" style="width: 180px; padding: 8px 12px; border: 1px solid #8c8f94; border-radius: 4px;" />
                                    <button type="button" id="quick-test-message" class="button button-secondary">
                                        <?php esc_html_e( 'Send Test Message', 'broodle-engage-connector' ); ?>
                                    </button>
                                    <span id="quick-test-result"></span>
                                </div>
                                <p class="description" style="margin-top: 8px;"><?php esc_html_e( 'Enter phone number with country code to send a test "hello_world" template message.', 'broodle-engage-connector' ); ?></p>
                            </div>
                        </div>

                        <script type="text/javascript">
                        function broodleToggleApiKey() {
                            var input = document.getElementById('api_key');
                            var button = document.getElementById('toggle-api-key');

                            if (input.type === 'password') {
                                input.type = 'text';
                                button.textContent = 'Hide';
                            } else {
                                input.type = 'password';
                                button.textContent = 'Show';
                            }
                        }
                        </script>
                        <p class="description">
                            <?php esc_html_e( 'Your Broodle API Access Token. Get it from your Broodle dashboard at', 'broodle-engage-connector' ); ?>
                            <a href="https://wa.broodle.one" target="_blank">wa.broodle.one</a>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="chatwoot_account_id"><?php esc_html_e( 'Account ID', 'broodle-engage-connector' ); ?></label>
                    </th>
                    <td>
                        <input type="number" id="chatwoot_account_id" name="broodle_engage_settings[chatwoot_account_id]" value="<?php echo esc_attr( $settings['chatwoot_account_id'] ?? '' ); ?>" class="regular-text" min="1" />
                        <p class="description"><?php esc_html_e( 'Your Broodle Account ID. Find it in your Broodle dashboard.', 'broodle-engage-connector' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="chatwoot_inbox_id"><?php esc_html_e( 'WhatsApp Inbox ID', 'broodle-engage-connector' ); ?></label>
                    </th>
                    <td>
                        <input type="number" id="chatwoot_inbox_id" name="broodle_engage_settings[chatwoot_inbox_id]" value="<?php echo esc_attr( $settings['chatwoot_inbox_id'] ?? '' ); ?>" class="regular-text" min="1" />
                        <p class="description"><?php esc_html_e( 'The ID of your WhatsApp inbox. Find it in Settings → Inboxes in your Broodle dashboard.', 'broodle-engage-connector' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="template_language"><?php esc_html_e( 'Template Language', 'broodle-engage-connector' ); ?></label>
                    </th>
                    <td>
                        <select id="template_language" name="broodle_engage_settings[template_language]">
                            <option value="en" <?php selected( $settings['template_language'] ?? 'en_US', 'en' ); ?>>English (en)</option>
                            <option value="en_US" <?php selected( $settings['template_language'] ?? 'en_US', 'en_US' ); ?>>English US (en_US)</option>
                            <option value="en_GB" <?php selected( $settings['template_language'] ?? 'en_US', 'en_GB' ); ?>>English UK (en_GB)</option>
                            <option value="es" <?php selected( $settings['template_language'] ?? 'en_US', 'es' ); ?>>Spanish (es)</option>
                            <option value="pt_BR" <?php selected( $settings['template_language'] ?? 'en_US', 'pt_BR' ); ?>>Portuguese BR (pt_BR)</option>
                            <option value="fr" <?php selected( $settings['template_language'] ?? 'en_US', 'fr' ); ?>>French (fr)</option>
                            <option value="de" <?php selected( $settings['template_language'] ?? 'en_US', 'de' ); ?>>German (de)</option>
                            <option value="it" <?php selected( $settings['template_language'] ?? 'en_US', 'it' ); ?>>Italian (it)</option>
                            <option value="ar" <?php selected( $settings['template_language'] ?? 'en_US', 'ar' ); ?>>Arabic (ar)</option>
                            <option value="hi" <?php selected( $settings['template_language'] ?? 'en_US', 'hi' ); ?>>Hindi (hi)</option>
                            <option value="id" <?php selected( $settings['template_language'] ?? 'en_US', 'id' ); ?>>Indonesian (id)</option>
                        </select>
                        <p class="description"><?php esc_html_e( 'Default language code for WhatsApp templates.', 'broodle-engage-connector' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="template_category"><?php esc_html_e( 'Template Category', 'broodle-engage-connector' ); ?></label>
                    </th>
                    <td>
                        <select id="template_category" name="broodle_engage_settings[template_category]">
                            <option value="UTILITY" <?php selected( $settings['template_category'] ?? 'UTILITY', 'UTILITY' ); ?>>Utility</option>
                            <option value="MARKETING" <?php selected( $settings['template_category'] ?? 'UTILITY', 'MARKETING' ); ?>>Marketing</option>
                            <option value="AUTHENTICATION" <?php selected( $settings['template_category'] ?? 'UTILITY', 'AUTHENTICATION' ); ?>>Authentication</option>
                        </select>
                        <p class="description"><?php esc_html_e( 'Default category for WhatsApp templates. Use UTILITY for order notifications.', 'broodle-engage-connector' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row" colspan="2" style="background: #f0f6fc; padding: 15px 24px !important;">
                        <h3 style="margin: 0; color: #1d2327; font-size: 16px;">
                            <span class="dashicons dashicons-admin-generic" style="color: #2271b1;"></span>
                            <?php esc_html_e( 'General Settings', 'broodle-engage-connector' ); ?>
                        </h3>
                    </th>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="phone_field"><?php esc_html_e( 'Phone Number Field', 'broodle-engage-connector' ); ?></label>
                    </th>
                    <td>
                        <select id="phone_field" name="broodle_engage_settings[phone_field]">
                            <?php foreach ( Broodle_Engage_Settings::get_phone_field_options() as $value => $label ) : ?>
                                <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $settings['phone_field'], $value ); ?>>
                                    <?php echo esc_html( $label ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php esc_html_e( 'Choose which phone number field to use for WhatsApp notifications.', 'broodle-engage-connector' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="country_code"><?php esc_html_e( 'Default Country Code', 'broodle-engage-connector' ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="country_code" name="broodle_engage_settings[country_code]" value="<?php echo esc_attr( $settings['country_code'] ); ?>" class="small-text" placeholder="+1" />
                        <p class="description"><?php esc_html_e( 'Default country code to use if not specified in phone number.', 'broodle-engage-connector' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="retry_attempts"><?php esc_html_e( 'Retry Attempts', 'broodle-engage-connector' ); ?></label>
                    </th>
                    <td>
                        <input type="number" id="retry_attempts" name="broodle_engage_settings[retry_attempts]" value="<?php echo esc_attr( $settings['retry_attempts'] ); ?>" min="0" max="10" class="small-text" />
                        <p class="description"><?php esc_html_e( 'Number of times to retry failed notifications.', 'broodle-engage-connector' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="retry_delay"><?php esc_html_e( 'Retry Delay (seconds)', 'broodle-engage-connector' ); ?></label>
                    </th>
                    <td>
                        <input type="number" id="retry_delay" name="broodle_engage_settings[retry_delay]" value="<?php echo esc_attr( $settings['retry_delay'] ); ?>" min="60" max="3600" class="small-text" />
                        <p class="description"><?php esc_html_e( 'Delay between retry attempts in seconds.', 'broodle-engage-connector' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="log_retention_days"><?php esc_html_e( 'Log Retention (days)', 'broodle-engage-connector' ); ?></label>
                    </th>
                    <td>
                        <input type="number" id="log_retention_days" name="broodle_engage_settings[log_retention_days]" value="<?php echo esc_attr( $settings['log_retention_days'] ); ?>" min="1" max="365" class="small-text" />
                        <p class="description"><?php esc_html_e( 'Number of days to keep notification logs.', 'broodle-engage-connector' ); ?></p>
                    </td>
                </tr>
            </table>

            <div class="settings-submit-section">
                <?php submit_button( __( 'Save Settings', 'broodle-engage-connector' ), 'primary', 'submit', false ); ?>
            </div>
        </form>
        <?php
    }

    /**
     * Render templates tab - Professional UI with Broodle Engage branding
     */
    private function render_templates_tab() {
        $settings = Broodle_Engage_Settings::get_settings();
        $template_config = $settings['template_config'] ?? array();
        $custom_statuses = $settings['custom_notification_statuses'] ?? array();
        $variable_options = self::get_variable_options();
        ?>
        <style>
        /* Broodle Engage Brand Colors - #0E5ECE */
        :root {
            --brand-primary: #0E5ECE;
            --brand-primary-dark: #0a4aa3;
            --brand-primary-light: #3d7be0;
            --brand-primary-translucent: rgba(14, 94, 206, 0.1);
            --brand-primary-translucent-2: rgba(14, 94, 206, 0.15);
            --brand-primary-translucent-3: rgba(14, 94, 206, 0.25);
            --brand-gradient: linear-gradient(135deg, #0E5ECE 0%, #0a4aa3 100%);
            --brand-gradient-light: linear-gradient(135deg, rgba(14, 94, 206, 0.1) 0%, rgba(14, 94, 206, 0.05) 100%);
            --success-color: #00a32a;
            --warning-color: #dba617;
            --error-color: #dc3232;
            --text-primary: #1d2327;
            --text-secondary: #646970;
            --border-color: #e1e5e9;
            --bg-light: #f8f9fa;
        }

        /* Modern Templates Page Styling */
        .templates-container {
            max-width: 1200px;
        }
        
        /* Header with Brand Gradient */
        .templates-header {
            background: var(--brand-gradient);
            color: white;
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(14, 94, 206, 0.25);
            position: relative;
            overflow: hidden;
        }
        .templates-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 60%;
            height: 200%;
            background: rgba(255,255,255,0.05);
            transform: rotate(15deg);
            pointer-events: none;
        }
        .templates-header h2 {
            margin: 0 0 10px 0;
            font-size: 26px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
            z-index: 1;
            color: white;
        }
        .templates-header h2 .dashicons {
            font-size: 32px;
            width: 32px;
            height: 32px;
        }
        .templates-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 15px;
            position: relative;
            z-index: 1;
        }

        /* Connection Status Card */
        .connection-status {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px 25px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        .connection-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .status-indicator {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: var(--error-color);
            box-shadow: 0 0 0 3px rgba(220, 50, 50, 0.2);
        }
        .status-indicator.connected {
            background: var(--success-color);
            box-shadow: 0 0 12px rgba(0, 163, 42, 0.4);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(0, 163, 42, 0.4); }
            50% { box-shadow: 0 0 0 8px rgba(0, 163, 42, 0); }
        }
        .connection-text h4 {
            margin: 0 0 4px 0;
            font-size: 15px;
            font-weight: 600;
            color: var(--text-primary);
        }
        .connection-text p {
            margin: 0;
            color: var(--text-secondary);
            font-size: 13px;
        }
        .templates-count {
            background: var(--brand-primary-translucent);
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            color: var(--brand-primary);
            font-size: 14px;
        }
        .refresh-btn {
            background: var(--brand-primary-translucent) !important;
            border: 1px solid var(--brand-primary) !important;
            color: var(--brand-primary) !important;
            border-radius: 8px !important;
            padding: 8px 16px !important;
            font-weight: 500 !important;
            transition: all 0.2s ease !important;
        }
        .refresh-btn:hover {
            background: var(--brand-primary) !important;
            color: white !important;
        }

        /* Order Status Cards */
        .status-cards {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .status-card {
            background: white;
            border: 2px solid var(--border-color);
            border-radius: 14px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .status-card:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }
        .status-card.enabled {
            border-color: var(--brand-primary);
            box-shadow: 0 4px 20px rgba(14, 94, 206, 0.15);
        }
        .status-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            background: var(--bg-light);
            border-bottom: 1px solid var(--border-color);
        }
        .status-card.enabled .status-card-header {
            background: var(--brand-gradient-light);
        }
        .status-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .status-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            transition: all 0.3s ease;
            color: var(--text-secondary);
        }
        .status-icon .dashicons {
            font-size: 24px;
            width: 24px;
            height: 24px;
        }
        .status-card.enabled .status-icon {
            background: var(--brand-gradient);
            color: white;
            box-shadow: 0 4px 12px rgba(14, 94, 206, 0.3);
        }
        .status-details h3 {
            margin: 0 0 4px 0;
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
        }
        .status-details p {
            margin: 0;
            font-size: 13px;
            color: var(--text-secondary);
        }
        .status-card .delete-status-btn {
            background: none;
            border: none;
            color: var(--error-color);
            cursor: pointer;
            padding: 8px;
            margin-left: 10px;
            opacity: 0.6;
            transition: opacity 0.2s;
        }
        .status-card .delete-status-btn:hover {
            opacity: 1;
        }

        /* Toggle Switch - Brand Blue */
        .toggle-switch {
            position: relative;
            width: 56px;
            height: 30px;
        }
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #B0B5BC;
            border-radius: 30px;
            transition: 0.3s;
        }
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 24px;
            width: 24px;
            left: 3px;
            bottom: 3px;
            background: white;
            border-radius: 50%;
            transition: 0.3s;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }
        .toggle-switch input:checked + .toggle-slider {
            background: var(--brand-primary);
        }
        .toggle-switch input:checked + .toggle-slider:before {
            transform: translateX(26px);
        }

        /* Status Card Body */
        .status-card-body {
            padding: 24px;
            display: none;
            background: white;
        }
        .status-card.expanded .status-card-body {
            display: block;
        }

        /* Expand/Collapse Button */
        .expand-btn {
            background: transparent;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 6px 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
            color: var(--text-secondary);
            transition: all 0.2s ease;
            margin-right: 10px;
        }
        .expand-btn:hover {
            background: var(--brand-primary-translucent);
            border-color: var(--brand-primary);
            color: var(--brand-primary);
        }
        .expand-btn .dashicons {
            font-size: 16px;
            width: 16px;
            height: 16px;
            transition: transform 0.3s ease;
        }
        .status-card.expanded .expand-btn .dashicons {
            transform: rotate(180deg);
        }
        .expand-btn .expand-text {
            font-size: 12px;
            font-weight: 500;
        }
        .status-card.expanded .expand-btn .expand-text {
            display: none;
        }
        .expand-btn .collapse-text {
            display: none;
            font-size: 12px;
            font-weight: 500;
        }
        .status-card.expanded .expand-btn .collapse-text {
            display: inline;
        }

        /* Section Styling */
        .config-section {
            background: var(--bg-light);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .config-section:last-child {
            margin-bottom: 0;
        }
        .config-section-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0 0 15px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .config-section-title .dashicons {
            color: var(--brand-primary);
            font-size: 18px;
            width: 18px;
            height: 18px;
        }

        /* Template Selection */
        .template-select {
            width: 100%;
            max-width: 450px;
            padding: 14px 40px 14px 16px;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            color: #1d2327;
            background: white url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2220%22%20height%3D%2220%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20fill%3D%22%230E5ECE%22%20d%3D%22M5%206l5%205%205-5%202%201-7%207-7-7%202-1z%22%2F%3E%3C%2Fsvg%3E') no-repeat right 12px center;
            background-size: 18px;
            cursor: pointer;
            transition: all 0.2s ease;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }
        .template-select:hover {
            border-color: var(--brand-primary);
            background-color: #f8fafc;
        }
        .template-select:focus {
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 4px var(--brand-primary-translucent);
            outline: none;
            background-color: white;
        }
        .template-select option {
            padding: 12px;
            font-weight: 500;
        }

        /* Template Preview */
        .template-preview {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            overflow: hidden;
            margin-top: 15px;
            display: none;
        }
        .template-preview.visible {
            display: block;
        }
        .preview-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background: var(--brand-primary-translucent);
            border-bottom: 1px solid var(--border-color);
        }
        .preview-header .dashicons {
            color: var(--brand-primary);
        }
        .preview-header h4 {
            margin: 0;
            font-size: 13px;
            font-weight: 600;
            flex-grow: 1;
        }
        .preview-badge {
            background: var(--brand-primary);
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .preview-content {
            padding: 16px;
        }
        
        /* Template Image Preview */
        .preview-image {
            background: var(--bg-light);
            border: 1px dashed var(--border-color);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .preview-image .dashicons {
            font-size: 28px;
            width: 28px;
            height: 28px;
            color: var(--brand-primary);
        }
        .preview-image-info h5 {
            margin: 0 0 4px 0;
            font-size: 13px;
            font-weight: 600;
        }
        .preview-image-info p {
            margin: 0;
            font-size: 12px;
            color: var(--text-secondary);
        }
        
        /* Message Preview Box */
        .preview-message {
            background: var(--bg-light);
            border-radius: 8px;
            padding: 16px;
            font-size: 13px;
            line-height: 1.7;
            white-space: pre-wrap;
            color: var(--text-primary);
            border-left: 4px solid var(--brand-primary);
        }
        .preview-message .variable-highlight {
            background: linear-gradient(135deg, #FFF3CD 0%, #FFE69C 100%);
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 600;
            color: #856404;
            border: 1px solid #FFE69C;
        }
        .preview-footer {
            font-size: 12px;
            color: var(--text-secondary);
            font-style: italic;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed var(--border-color);
        }
        
        /* Template Buttons Preview */
        .preview-buttons {
            margin-top: 15px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .preview-button {
            background: var(--brand-primary-translucent);
            border: 1px solid var(--brand-primary);
            color: var(--brand-primary);
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .preview-button .dashicons {
            font-size: 16px;
            width: 16px;
            height: 16px;
        }
        .preview-button.has-variable {
            border-style: dashed;
        }

        /* Variable Mapping Grid */
        .variable-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 16px;
        }
        .variable-item {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 14px;
            transition: all 0.2s ease;
        }
        .variable-item:hover {
            border-color: var(--brand-primary);
            box-shadow: 0 2px 8px var(--brand-primary-translucent);
        }
        .variable-item-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .variable-item label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .variable-item label .var-num {
            background: var(--brand-primary);
            color: white;
            padding: 3px 10px;
            border-radius: 6px;
            font-family: 'Monaco', 'Consolas', monospace;
            font-size: 12px;
        }
        .variable-item select {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid var(--border-color);
            border-radius: 6px;
            font-size: 13px;
            background: white;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .variable-item select:focus {
            border-color: var(--brand-primary);
            outline: none;
        }
        
        /* Custom Text Input */
        .custom-text-input {
            margin-top: 10px;
            display: none;
        }
        .custom-text-input.visible {
            display: block;
        }
        .custom-text-input input {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid var(--border-color);
            border-radius: 6px;
            font-size: 13px;
            transition: all 0.2s ease;
        }
        .custom-text-input input:focus {
            border-color: var(--brand-primary);
            outline: none;
        }
        .custom-text-input label {
            display: block;
            font-size: 12px;
            color: var(--text-secondary);
            margin-bottom: 6px;
        }

        /* Image Selection Section */
        .image-selection {
            margin-top: 15px;
            padding: 15px;
            background: white;
            border: 2px dashed var(--border-color);
            border-radius: 10px;
            display: none;
        }
        .image-selection.visible {
            display: block;
        }
        .image-selection-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }
        .image-selection-header .dashicons {
            color: var(--brand-primary);
        }
        .image-selection-header h5 {
            margin: 0;
            font-size: 13px;
            font-weight: 600;
        }
        .image-preview-box {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .image-thumb {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            background: var(--bg-light);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }
        .image-thumb img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }
        .image-thumb .dashicons {
            color: var(--text-secondary);
            font-size: 32px;
            width: 32px;
            height: 32px;
        }
        .image-selection-row {
            display: flex;
            align-items: flex-start;
            gap: 15px;
        }
        .image-preview-box {
            flex: 1;
        }
        .image-preview-box.disabled {
            opacity: 0.35;
            pointer-events: none;
        }
        .use-product-image-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 12px 14px;
            background: #f0f6ff;
            border: 2px solid #cde0f7;
            border-radius: 10px;
            cursor: pointer;
            font-size: 11px;
            color: #1d2327;
            text-align: center;
            min-width: 110px;
            transition: all 0.2s;
            line-height: 1.3;
        }
        .use-product-image-label:hover {
            background: #e0edff;
            border-color: var(--brand-primary);
        }
        .use-product-image-label.active {
            background: var(--brand-primary-translucent);
            border-color: var(--brand-primary);
            color: var(--brand-primary);
            font-weight: 600;
        }
        .use-product-image-label input[type="checkbox"] {
            margin: 0;
            accent-color: var(--brand-primary);
        }
        .use-product-image-label .dashicons {
            font-size: 20px;
            width: 20px;
            height: 20px;
            color: var(--brand-primary);
        }

        /* Add Notification Type Button */
        .add-status-section {
            margin-top: 25px;
            padding: 25px;
            background: var(--brand-primary-translucent);
            border: 2px dashed var(--brand-primary);
            border-radius: 12px;
            text-align: center;
        }
        .add-status-btn {
            background: var(--brand-gradient) !important;
            color: white !important;
            border: none !important;
            padding: 14px 28px !important;
            border-radius: 8px !important;
            font-size: 15px !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
            box-shadow: 0 4px 12px rgba(14, 94, 206, 0.3) !important;
        }
        .add-status-btn:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 20px rgba(14, 94, 206, 0.4) !important;
        }
        .add-status-btn .dashicons {
            vertical-align: middle;
            margin-right: 8px;
        }

        /* No Templates Notice */
        .no-templates-notice {
            background: linear-gradient(135deg, #FFF8E1 0%, #FFF3CD 100%);
            border: 1px solid #FFE082;
            border-left: 4px solid var(--warning-color);
            padding: 20px 25px;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        .no-templates-notice h4 {
            margin: 0 0 8px 0;
            color: #7C6200;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .no-templates-notice p {
            margin: 0;
            color: #6C5701;
        }

        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            display: none;
        }
        .loading-overlay.visible {
            display: flex;
        }
        .loading-content {
            text-align: center;
        }
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid var(--border-color);
            border-top: 4px solid var(--brand-primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        .loading-text {
            color: var(--text-secondary);
            font-size: 14px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Save Section */
        .save-section {
            margin-top: 30px;
            padding: 25px;
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        .save-btn {
            background: var(--brand-gradient) !important;
            color: white !important;
            border: none !important;
            padding: 14px 35px !important;
            border-radius: 8px !important;
            font-size: 15px !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
            box-shadow: 0 4px 12px rgba(14, 94, 206, 0.3) !important;
        }
        .save-btn:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 20px rgba(14, 94, 206, 0.4) !important;
        }
        .save-btn:disabled {
            background: #B0B5BC !important;
            cursor: not-allowed !important;
            transform: none !important;
            box-shadow: none !important;
        }
        .save-status {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: var(--success-color);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .save-status.visible {
            opacity: 1;
        }

        /* Modal for Adding Status */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 100000;
        }
        .modal-overlay.visible {
            display: flex;
        }
        .modal-content {
            background: white;
            border-radius: 16px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .modal-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .modal-header h3 .dashicons {
            color: var(--brand-primary);
        }
        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--text-secondary);
            padding: 5px;
        }
        .modal-close:hover {
            color: var(--text-primary);
        }
        .modal-body label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-primary);
        }
        .modal-body input,
        .modal-body textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .modal-body input:focus,
        .modal-body textarea:focus {
            border-color: var(--brand-primary);
            outline: none;
        }
        .modal-footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .modal-btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            border: none;
        }
        .modal-btn-primary {
            background: var(--brand-gradient);
            color: white;
        }
        .modal-btn-secondary {
            background: var(--bg-light);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        /* Responsive */
        @media (max-width: 782px) {
            .templates-header {
                padding: 20px;
            }
            .templates-header h2 {
                font-size: 20px;
            }
            .connection-status {
                flex-direction: column;
                align-items: flex-start;
            }
            .status-card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            .variable-grid {
                grid-template-columns: 1fr;
            }
            .save-section {
                flex-direction: column;
            }
        }
        </style>

        <div class="templates-container">
            <!-- Header -->
            <div class="templates-header">
                <h2>
                    <span class="dashicons dashicons-whatsapp"></span>
                    <?php esc_html_e( 'WhatsApp Template Configuration', 'broodle-engage-connector' ); ?>
                </h2>
                <p><?php esc_html_e( 'Configure which WhatsApp templates to send for each order status. Templates are automatically fetched from your Broodle Engage inbox.', 'broodle-engage-connector' ); ?></p>
            </div>

            <!-- Loading Overlay -->
            <div class="loading-overlay" id="loading-overlay">
                <div class="loading-content">
                    <div class="loading-spinner"></div>
                    <div class="loading-text"><?php esc_html_e( 'Loading templates...', 'broodle-engage-connector' ); ?></div>
                </div>
            </div>

            <!-- Connection Status -->
            <div class="connection-status">
                <div class="connection-info">
                    <div class="status-indicator" id="connection-indicator"></div>
                    <div class="connection-text">
                        <h4 id="connection-title"><?php esc_html_e( 'Checking connection...', 'broodle-engage-connector' ); ?></h4>
                        <p id="connection-subtitle"><?php esc_html_e( 'Fetching templates from Broodle Engage', 'broodle-engage-connector' ); ?></p>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span class="templates-count" id="templates-count">0 templates</span>
                    <button type="button" class="button refresh-btn" id="refresh-templates">
                        <span class="dashicons dashicons-update" style="vertical-align: middle;"></span>
                        <?php esc_html_e( 'Refresh', 'broodle-engage-connector' ); ?>
                    </button>
                </div>
            </div>

            <!-- No Templates Notice -->
            <div class="no-templates-notice" id="no-templates-notice" style="display: none;">
                <h4><span class="dashicons dashicons-warning"></span> <?php esc_html_e( 'No Templates Found', 'broodle-engage-connector' ); ?></h4>
                <p><?php esc_html_e( 'Make sure your API settings are configured correctly and your WhatsApp inbox has approved templates.', 'broodle-engage-connector' ); ?></p>
            </div>

            <!-- Status Cards -->
            <div class="status-cards" id="status-cards">
                <?php
                $status_icons = array(
                    'order_received'   => '<span class="dashicons dashicons-cart"></span>',
                    'order_processing' => '<span class="dashicons dashicons-admin-generic"></span>',
                    'order_shipped'    => '<span class="dashicons dashicons-migrate"></span>',
                    'order_delivered'  => '<span class="dashicons dashicons-yes-alt"></span>',
                    'order_completed'  => '<span class="dashicons dashicons-flag"></span>',
                    'order_cancelled'  => '<span class="dashicons dashicons-dismiss"></span>',
                    'order_failed'     => '<span class="dashicons dashicons-warning"></span>',
                    'order_refunded'   => '<span class="dashicons dashicons-money-alt"></span>',
                );

                // Map notification types to actual WooCommerce statuses
                $wc_status_map = array(
                    'order_received'   => 'wc-pending',
                    'order_processing' => 'wc-processing',
                    'order_shipped'    => 'wc-shipped',
                    'order_delivered'  => 'wc-delivered',
                    'order_completed'  => 'wc-completed',
                    'order_cancelled'  => 'wc-cancelled',
                    'order_failed'     => 'wc-failed',
                    'order_refunded'   => 'wc-refunded',
                );

                $status_descriptions = array(
                    'order_received'   => __( 'Sends when a new order is placed', 'broodle-engage-connector' ),
                    'order_processing' => __( 'Sends when order is being prepared', 'broodle-engage-connector' ),
                    'order_shipped'    => __( 'Sends when order is dispatched', 'broodle-engage-connector' ),
                    'order_delivered'  => __( 'Sends when order is delivered', 'broodle-engage-connector' ),
                    'order_completed'  => __( 'Sends when order is completed', 'broodle-engage-connector' ),
                    'order_cancelled'  => __( 'Sends when order is cancelled', 'broodle-engage-connector' ),
                    'order_failed'     => __( 'Sends when payment fails', 'broodle-engage-connector' ),
                    'order_refunded'   => __( 'Sends when order is refunded', 'broodle-engage-connector' ),
                );

                // Render default status cards
                foreach ( Broodle_Engage_Settings::get_order_status_options() as $status => $label ) :
                    $config = $template_config[ $status ] ?? array();
                    $is_enabled = ! empty( $config['enabled'] );
                ?>
                <div class="status-card <?php echo esc_attr( $is_enabled ? 'enabled' : '' ); ?>" data-status="<?php echo esc_attr( $status ); ?>" data-is-custom="false">
                    <div class="status-card-header">
                        <div class="status-info">
                            <div class="status-icon"><?php echo wp_kses( $status_icons[ $status ] ?? '<span class="dashicons dashicons-clipboard"></span>', array( 'span' => array( 'class' => array() ) ) ); ?></div>
                            <div class="status-details">
                                <h3><?php echo esc_html( $label ); ?></h3>
                                <p><?php echo esc_html( $status_descriptions[ $status ] ?? '' ); ?></p>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center;">
                            <button type="button" class="expand-btn" title="<?php esc_attr_e( 'Expand/Collapse', 'broodle-engage-connector' ); ?>">
                                <span class="expand-text"><?php esc_html_e( 'Expand', 'broodle-engage-connector' ); ?></span>
                                <span class="collapse-text"><?php esc_html_e( 'Collapse', 'broodle-engage-connector' ); ?></span>
                                <span class="dashicons dashicons-arrow-down-alt2"></span>
                            </button>
                            <label class="toggle-switch">
                                <input type="checkbox" class="status-toggle" data-status="<?php echo esc_attr( $status ); ?>" <?php checked( $is_enabled ); ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                    <div class="status-card-body">
                        <!-- Template Selection Section -->
                        <div class="config-section">
                            <h4 class="config-section-title">
                                <span class="dashicons dashicons-format-aside"></span>
                                <?php esc_html_e( 'Select Template', 'broodle-engage-connector' ); ?>
                            </h4>
                            <select class="template-select" data-status="<?php echo esc_attr( $status ); ?>">
                                <option value=""><?php esc_html_e( '-- Select a template --', 'broodle-engage-connector' ); ?></option>
                            </select>
                            
                            <!-- Template Preview -->
                            <div class="template-preview" data-status="<?php echo esc_attr( $status ); ?>">
                                <div class="preview-header">
                                    <span class="dashicons dashicons-visibility"></span>
                                    <h4><?php esc_html_e( 'Template Preview', 'broodle-engage-connector' ); ?></h4>
                                    <span class="preview-badge template-language">EN</span>
                                    <span class="preview-badge template-category">UTILITY</span>
                                </div>
                                <div class="preview-content">
                                    <div class="preview-image" style="display: none;">
                                        <span class="dashicons dashicons-format-image"></span>
                                        <div class="preview-image-info">
                                            <h5><?php esc_html_e( 'Header Image', 'broodle-engage-connector' ); ?></h5>
                                            <p><?php esc_html_e( 'This template includes an image header', 'broodle-engage-connector' ); ?></p>
                                        </div>
                                    </div>
                                    <div class="preview-message"></div>
                                    <div class="preview-footer" style="display: none;"></div>
                                    <div class="preview-buttons"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Image Selection (shown for templates with image headers) -->
                        <div class="image-selection" data-status="<?php echo esc_attr( $status ); ?>">
                            <div class="image-selection-header">
                                <span class="dashicons dashicons-format-image"></span>
                                <h5><?php esc_html_e( 'Header Image (Optional)', 'broodle-engage-connector' ); ?></h5>
                            </div>
                            <div class="image-selection-row">
                                <div class="image-preview-box">
                                    <div class="image-thumb">
                                        <span class="dashicons dashicons-format-image"></span>
                                    </div>
                                    <div>
                                        <button type="button" class="button select-image-btn" data-status="<?php echo esc_attr( $status ); ?>">
                                            <?php esc_html_e( 'Select Image', 'broodle-engage-connector' ); ?>
                                        </button>
                                        <button type="button" class="button remove-image-btn" data-status="<?php echo esc_attr( $status ); ?>" style="display: none;">
                                            <?php esc_html_e( 'Remove', 'broodle-engage-connector' ); ?>
                                        </button>
                                        <input type="hidden" class="image-id-input" data-status="<?php echo esc_attr( $status ); ?>" value="">
                                    </div>
                                </div>
                                <label class="use-product-image-label">
                                    <input type="checkbox" class="use-product-image-check" data-status="<?php echo esc_attr( $status ); ?>">
                                    <span class="dashicons dashicons-products"></span>
                                    <?php esc_html_e( 'Use product image', 'broodle-engage-connector' ); ?>
                                </label>
                            </div>
                        </div>

                        <!-- Variable Mapping Section -->
                        <div class="config-section variable-mapping" data-status="<?php echo esc_attr( $status ); ?>" style="display: none;">
                            <h4 class="config-section-title">
                                <span class="dashicons dashicons-admin-generic"></span>
                                <?php esc_html_e( 'Map Template Variables', 'broodle-engage-connector' ); ?>
                            </h4>
                            <div class="variable-grid"></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Custom Status Cards -->
                <?php foreach ( $custom_statuses as $custom_status ) : 
                    $config = $template_config[ $custom_status['id'] ] ?? array();
                    $is_enabled = ! empty( $config['enabled'] );
                    $wc_status = $custom_status['wc_status'] ?? '';
                    $event_type = $custom_status['event_type'] ?? '';
                ?>
                <div class="status-card <?php echo esc_attr( $is_enabled ? 'enabled' : '' ); ?>" data-status="<?php echo esc_attr( $custom_status['id'] ); ?>" data-is-custom="true" <?php if ( $wc_status ) { echo 'data-wc-status="' . esc_attr( $wc_status ) . '"'; } ?> <?php if ( $event_type ) { echo 'data-event-type="' . esc_attr( $event_type ) . '"'; } ?>>
                    <div class="status-card-header">
                        <div class="status-info">
                            <div class="status-icon"><span class="dashicons dashicons-bell"></span></div>
                            <div class="status-details">
                                <h3><?php echo esc_html( $custom_status['name'] ); ?></h3>
                                <p><?php echo esc_html( $custom_status['description'] ?? '' ); ?></p>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center;">
                            <button type="button" class="expand-btn" title="<?php esc_attr_e( 'Expand/Collapse', 'broodle-engage-connector' ); ?>">
                                <span class="expand-text"><?php esc_html_e( 'Expand', 'broodle-engage-connector' ); ?></span>
                                <span class="collapse-text"><?php esc_html_e( 'Collapse', 'broodle-engage-connector' ); ?></span>
                                <span class="dashicons dashicons-arrow-down-alt2"></span>
                            </button>
                            <label class="toggle-switch">
                                <input type="checkbox" class="status-toggle" data-status="<?php echo esc_attr( $custom_status['id'] ); ?>" <?php checked( $is_enabled ); ?>>
                                <span class="toggle-slider"></span>
                            </label>
                            <button type="button" class="delete-status-btn" data-status="<?php echo esc_attr( $custom_status['id'] ); ?>" title="<?php esc_attr_e( 'Delete', 'broodle-engage-connector' ); ?>">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                    </div>
                    <div class="status-card-body">
                        <div class="config-section">
                            <h4 class="config-section-title">
                                <span class="dashicons dashicons-format-aside"></span>
                                <?php esc_html_e( 'Select Template', 'broodle-engage-connector' ); ?>
                            </h4>
                            <select class="template-select" data-status="<?php echo esc_attr( $custom_status['id'] ); ?>">
                                <option value=""><?php esc_html_e( '-- Select a template --', 'broodle-engage-connector' ); ?></option>
                            </select>
                            <div class="template-preview" data-status="<?php echo esc_attr( $custom_status['id'] ); ?>">
                                <div class="preview-header">
                                    <span class="dashicons dashicons-visibility"></span>
                                    <h4><?php esc_html_e( 'Template Preview', 'broodle-engage-connector' ); ?></h4>
                                    <span class="preview-badge template-language">EN</span>
                                    <span class="preview-badge template-category">UTILITY</span>
                                </div>
                                <div class="preview-content">
                                    <div class="preview-image" style="display: none;">
                                        <span class="dashicons dashicons-format-image"></span>
                                        <div class="preview-image-info">
                                            <h5><?php esc_html_e( 'Header Image', 'broodle-engage-connector' ); ?></h5>
                                            <p><?php esc_html_e( 'This template includes an image header', 'broodle-engage-connector' ); ?></p>
                                        </div>
                                    </div>
                                    <div class="preview-message"></div>
                                    <div class="preview-footer" style="display: none;"></div>
                                    <div class="preview-buttons"></div>
                                </div>
                            </div>
                        </div>
                        <div class="image-selection" data-status="<?php echo esc_attr( $custom_status['id'] ); ?>">
                            <div class="image-selection-header">
                                <span class="dashicons dashicons-format-image"></span>
                                <h5><?php esc_html_e( 'Header Image (Optional)', 'broodle-engage-connector' ); ?></h5>
                            </div>
                            <div class="image-selection-row">
                                <div class="image-preview-box">
                                    <div class="image-thumb">
                                        <span class="dashicons dashicons-format-image"></span>
                                    </div>
                                    <div>
                                        <button type="button" class="button select-image-btn" data-status="<?php echo esc_attr( $custom_status['id'] ); ?>">
                                            <?php esc_html_e( 'Select Image', 'broodle-engage-connector' ); ?>
                                        </button>
                                        <button type="button" class="button remove-image-btn" data-status="<?php echo esc_attr( $custom_status['id'] ); ?>" style="display: none;">
                                            <?php esc_html_e( 'Remove', 'broodle-engage-connector' ); ?>
                                        </button>
                                        <input type="hidden" class="image-id-input" data-status="<?php echo esc_attr( $custom_status['id'] ); ?>" value="">
                                    </div>
                                </div>
                                <label class="use-product-image-label">
                                    <input type="checkbox" class="use-product-image-check" data-status="<?php echo esc_attr( $custom_status['id'] ); ?>">
                                    <span class="dashicons dashicons-products"></span>
                                    <?php esc_html_e( 'Use product image', 'broodle-engage-connector' ); ?>
                                </label>
                            </div>
                        </div>
                        <div class="config-section variable-mapping" data-status="<?php echo esc_attr( $custom_status['id'] ); ?>" style="display: none;">
                            <h4 class="config-section-title">
                                <span class="dashicons dashicons-admin-generic"></span>
                                <?php esc_html_e( 'Map Template Variables', 'broodle-engage-connector' ); ?>
                            </h4>
                            <div class="variable-grid"></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Add Notification Type Button -->
            <div class="add-status-section">
                <button type="button" class="add-status-btn" id="add-status-btn">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <?php esc_html_e( 'Add Custom Notification Type', 'broodle-engage-connector' ); ?>
                </button>
            </div>

            <!-- Save Section -->
            <div class="save-section">
                <div class="save-status" id="save-status">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <?php esc_html_e( 'Configuration saved successfully!', 'broodle-engage-connector' ); ?>
                </div>
                <button type="button" class="save-btn" id="save-templates">
                    <span class="dashicons dashicons-cloud-saved" style="vertical-align: middle; margin-right: 8px;"></span>
                    <?php esc_html_e( 'Save Configuration', 'broodle-engage-connector' ); ?>
                </button>
            </div>
        </div>

        <!-- Add Status Modal -->
        <div class="modal-overlay" id="add-status-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3><span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e( 'Add Custom Notification', 'broodle-engage-connector' ); ?></h3>
                    <button type="button" class="modal-close" id="close-modal">&times;</button>
                </div>
                <div class="modal-body">
                    <label for="custom-notification-type"><?php esc_html_e( 'Notification Type', 'broodle-engage-connector' ); ?></label>
                    <select id="custom-notification-type" style="width: 100%; padding: 12px; margin-bottom: 15px; border: 2px solid var(--border-color); border-radius: 8px;">
                        <option value="order_event"><?php esc_html_e( 'Order Status Event', 'broodle-engage-connector' ); ?></option>
                        <option value="user_event"><?php esc_html_e( 'User Event', 'broodle-engage-connector' ); ?></option>
                    </select>
                    
                    <div id="order-event-fields">
                        <label for="custom-wc-status"><?php esc_html_e( 'WooCommerce Order Status', 'broodle-engage-connector' ); ?></label>
                        <select id="custom-wc-status" style="width: 100%; padding: 12px; margin-bottom: 15px; border: 2px solid var(--border-color); border-radius: 8px;">
                            <?php
                            $wc_statuses = $this->get_woocommerce_order_statuses();
                            foreach ( $wc_statuses as $status_key => $status_label ) :
                            ?>
                            <option value="<?php echo esc_attr( $status_key ); ?>"><?php echo esc_html( $status_label ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div id="user-event-fields" style="display: none;">
                        <label for="custom-user-event"><?php esc_html_e( 'User Event Type', 'broodle-engage-connector' ); ?></label>
                        <select id="custom-user-event" style="width: 100%; padding: 12px; margin-bottom: 15px; border: 2px solid var(--border-color); border-radius: 8px;">
                            <option value="user_registered"><?php esc_html_e( 'User Account Registered', 'broodle-engage-connector' ); ?></option>
                            <option value="password_reset"><?php esc_html_e( 'Password Reset Request', 'broodle-engage-connector' ); ?></option>
                            <option value="user_login"><?php esc_html_e( 'User Login', 'broodle-engage-connector' ); ?></option>
                        </select>
                    </div>
                    
                    <label for="custom-status-name"><?php esc_html_e( 'Display Name', 'broodle-engage-connector' ); ?></label>
                    <input type="text" id="custom-status-name" placeholder="<?php esc_attr_e( 'e.g., Abandoned Cart Recovery', 'broodle-engage-connector' ); ?>">
                    
                    <label for="custom-status-desc"><?php esc_html_e( 'Description (Optional)', 'broodle-engage-connector' ); ?></label>
                    <textarea id="custom-status-desc" rows="2" placeholder="<?php esc_attr_e( 'Brief description of this notification...', 'broodle-engage-connector' ); ?>"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="modal-btn modal-btn-secondary" id="cancel-add-status"><?php esc_html_e( 'Cancel', 'broodle-engage-connector' ); ?></button>
                    <button type="button" class="modal-btn modal-btn-primary" id="confirm-add-status"><?php esc_html_e( 'Add Notification', 'broodle-engage-connector' ); ?></button>
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            // Store data
            var templatesData = [];
            var savedConfig = <?php echo wp_json_encode( $template_config ); ?>;
            var customStatuses = <?php echo wp_json_encode( $custom_statuses ); ?>;
            var variableOptions = <?php echo wp_json_encode( $variable_options ); ?>;
            var mediaFrame;

            // Initialize
            fetchTemplates();

            // Refresh button
            $('#refresh-templates').on('click', fetchTemplates);

            // Toggle status card
            $(document).on('change', '.status-toggle', function() {
                var card = $(this).closest('.status-card');
                if ($(this).is(':checked')) {
                    card.addClass('enabled');
                } else {
                    card.removeClass('enabled');
                }
            });

            // Expand/Collapse status card
            $(document).on('click', '.expand-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var card = $(this).closest('.status-card');
                card.toggleClass('expanded');
            });

            // Template selection change
            $(document).on('change', '.template-select', function() {
                var status = $(this).data('status');
                var templateName = $(this).val();
                updateTemplatePreview(status, templateName);
            });

            // Variable mapping change - handle custom text
            $(document).on('change', '.variable-map-select', function() {
                var item = $(this).closest('.variable-item');
                var customInput = item.find('.custom-text-input');
                if ($(this).val() === 'custom_text') {
                    customInput.addClass('visible');
                } else {
                    customInput.removeClass('visible');
                }
            });

            // Toggle upload disabled when use-product-image checkbox changes
            $(document).on('change', '.use-product-image-check', function() {
                var card = $(this).closest('.status-card');
                var isChecked = $(this).is(':checked');
                card.find('.image-preview-box').toggleClass('disabled', isChecked);
                $(this).closest('.use-product-image-label').toggleClass('active', isChecked);
            });

            // Image selection
            var currentImageCard = null;
            $(document).on('click', '.select-image-btn', function() {
                var button = $(this);
                currentImageCard = button.closest('.status-card');
                
                if (mediaFrame) {
                    mediaFrame.open();
                    return;
                }
                
                mediaFrame = wp.media({
                    title: '<?php esc_html_e( 'Select Header Image', 'broodle-engage-connector' ); ?>',
                    button: { text: '<?php esc_html_e( 'Use this image', 'broodle-engage-connector' ); ?>' },
                    multiple: false
                });
                
                mediaFrame.on('select', function() {
                    var attachment = mediaFrame.state().get('selection').first().toJSON();
                    var card = currentImageCard;
                    var thumb = card.find('.image-thumb');
                    
                    thumb.html('<img src="' + attachment.url + '" alt="">');
                    card.find('.image-id-input').val(attachment.id);
                    card.find('.remove-image-btn').show();
                });
                
                mediaFrame.open();
            });

            // Remove image
            $(document).on('click', '.remove-image-btn', function() {
                var card = $(this).closest('.status-card');
                card.find('.image-thumb').html('<span class="dashicons dashicons-format-image"></span>');
                card.find('.image-id-input').val('');
                $(this).hide();
            });

            // Save configuration
            $('#save-templates').on('click', saveConfiguration);

            // Add status modal
            $('#add-status-btn').on('click', function() {
                $('#add-status-modal').addClass('visible');
            });

            // Toggle notification type fields
            $('#custom-notification-type').on('change', function() {
                if ($(this).val() === 'order_event') {
                    $('#order-event-fields').show();
                    $('#user-event-fields').hide();
                } else {
                    $('#order-event-fields').hide();
                    $('#user-event-fields').show();
                }
            });

            $('#close-modal, #cancel-add-status').on('click', function() {
                $('#add-status-modal').removeClass('visible');
                clearModalFields();
            });

            $('#confirm-add-status').on('click', function() {
                var notificationType = $('#custom-notification-type').val();
                var name = $('#custom-status-name').val().trim();
                var desc = $('#custom-status-desc').val().trim();
                var wcStatus = '';
                var eventType = '';
                var icon = '<span class="dashicons dashicons-bell"></span>'; // Default icon for custom notifications
                
                if (notificationType === 'order_event') {
                    wcStatus = $('#custom-wc-status').val();
                    if (!name) {
                        // Auto-generate name from WC status
                        name = $('#custom-wc-status option:selected').text().split('(')[0].trim();
                    }
                    eventType = 'order';
                    icon = '<span class="dashicons dashicons-products"></span>';
                    if (!desc) {
                        desc = 'Triggers on: wc-' + wcStatus;
                    }
                } else {
                    eventType = $('#custom-user-event').val();
                    if (!name) {
                        name = $('#custom-user-event option:selected').text();
                    }
                    icon = '<span class="dashicons dashicons-admin-users"></span>';
                    if (!desc) {
                        desc = 'User event: ' + eventType;
                    }
                }
                
                if (!name) {
                    alert('<?php esc_html_e( 'Please enter a notification name.', 'broodle-engage-connector' ); ?>');
                    return;
                }
                
                var id = 'custom_' + (wcStatus || eventType || name.toLowerCase().replace(/[^a-z0-9]/g, '_'));
                addCustomStatusCard(id, name, desc, icon, wcStatus, eventType);
                
                $('#add-status-modal').removeClass('visible');
                clearModalFields();
            });

            // Delete custom status
            $(document).on('click', '.delete-status-btn', function() {
                if (confirm('<?php esc_html_e( 'Are you sure you want to delete this notification type?', 'broodle-engage-connector' ); ?>')) {
                    $(this).closest('.status-card').remove();
                }
            });

            // Fetch templates
            function fetchTemplates() {
                $('#loading-overlay').addClass('visible');
                $('#connection-indicator').removeClass('connected');
                $('#connection-title').text('<?php esc_html_e( 'Fetching templates...', 'broodle-engage-connector' ); ?>');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'broodle_engage_fetch_templates',
                        nonce: '<?php echo wp_create_nonce( 'broodle_engage_admin_nonce' ); ?>'
                    },
                    success: function(response) {
                        $('#loading-overlay').removeClass('visible');
                        
                        if (response.success) {
                            templatesData = response.data.templates;
                            
                            $('#connection-indicator').addClass('connected');
                            $('#connection-title').text('<?php esc_html_e( 'Connected to Broodle Engage', 'broodle-engage-connector' ); ?>');
                            $('#connection-subtitle').text('<?php esc_html_e( 'Templates synced successfully', 'broodle-engage-connector' ); ?>');
                            $('#templates-count').text(response.data.count + ' templates');
                            $('#no-templates-notice').hide();

                            populateTemplateDropdowns();
                        } else {
                            $('#connection-title').text('<?php esc_html_e( 'Connection Failed', 'broodle-engage-connector' ); ?>');
                            $('#connection-subtitle').text(response.data.message || '<?php esc_html_e( 'Check your API settings', 'broodle-engage-connector' ); ?>');
                            $('#no-templates-notice').show();
                        }
                    },
                    error: function() {
                        $('#loading-overlay').removeClass('visible');
                        $('#connection-title').text('<?php esc_html_e( 'Connection Error', 'broodle-engage-connector' ); ?>');
                        $('#connection-subtitle').text('<?php esc_html_e( 'Unable to reach server', 'broodle-engage-connector' ); ?>');
                    }
                });
            }

            // Populate dropdowns
            function populateTemplateDropdowns() {
                $('.template-select').each(function() {
                    var select = $(this);
                    var status = select.data('status');
                    var savedTemplate = savedConfig[status] ? savedConfig[status].template_name : '';
                    
                    select.find('option:not(:first)').remove();
                    
                    templatesData.forEach(function(template) {
                        var option = $('<option></option>')
                            .val(template.name)
                            .text(template.name + ' (' + template.category + ')')
                            .data('template', template);
                        
                        if (template.name === savedTemplate) {
                            option.prop('selected', true);
                        }
                        
                        select.append(option);
                    });

                    if (savedTemplate) {
                        updateTemplatePreview(status, savedTemplate);
                    }
                });
            }

            // Update template preview
            function updateTemplatePreview(status, templateName) {
                var card = $('.status-card[data-status="' + status + '"]');
                var preview = card.find('.template-preview');
                var mapping = card.find('.variable-mapping');
                var imageSelection = card.find('.image-selection');
                
                if (!templateName) {
                    preview.removeClass('visible');
                    mapping.hide();
                    imageSelection.removeClass('visible');
                    return;
                }

                var template = templatesData.find(function(t) {
                    return t.name === templateName;
                });

                if (!template) {
                    preview.removeClass('visible');
                    mapping.hide();
                    imageSelection.removeClass('visible');
                    return;
                }

                // Update badges
                preview.find('.template-language').text(template.language.toUpperCase());
                preview.find('.template-category').text(template.category);

                // Check for image header
                var hasImageHeader = false;
                template.components.forEach(function(comp) {
                    if (comp.type === 'HEADER' && comp.format === 'IMAGE') {
                        hasImageHeader = true;
                    }
                });

                if (hasImageHeader) {
                    preview.find('.preview-image').show();
                    imageSelection.addClass('visible');

                    // Restore saved image_id and thumbnail
                    var savedImageId = savedConfig[status] ? savedConfig[status].image_id : '';
                    if (savedImageId && parseInt(savedImageId) > 0) {
                        card.find('.image-id-input').val(savedImageId);
                        card.find('.remove-image-btn').show();
                        // Fetch thumbnail via WP REST API
                        $.ajax({
                            url: '<?php echo esc_url( rest_url( 'wp/v2/media/' ) ); ?>' + savedImageId,
                            method: 'GET',
                            beforeSend: function(xhr) {
                                xhr.setRequestHeader('X-WP-Nonce', '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>');
                            },
                            success: function(media) {
                                var thumbUrl = media.media_details && media.media_details.sizes && media.media_details.sizes.thumbnail
                                    ? media.media_details.sizes.thumbnail.source_url
                                    : media.source_url;
                                card.find('.image-thumb').html('<img src="' + thumbUrl + '" alt="">');
                            }
                        });
                    }

                    // Restore use_product_image checkbox state
                    var useProductImage = savedConfig[status] ? savedConfig[status].use_product_image : false;
                    if (useProductImage) {
                        card.find('.use-product-image-check').prop('checked', true);
                        card.find('.image-preview-box').addClass('disabled');
                        card.find('.use-product-image-label').addClass('active');
                    }
                } else {
                    preview.find('.preview-image').hide();
                    imageSelection.removeClass('visible');
                }

                // Build message preview
                var messageContent = '';
                if (template.header && template.header.indexOf('{{') === -1) {
                    messageContent += '<strong>' + template.header + '</strong>\n\n';
                }
                if (template.body) {
                    messageContent += template.body;
                }
                
                // Highlight variables
                messageContent = messageContent.replace(/\{\{(\d+)\}\}/g, '<span class="variable-highlight">{{$1}}</span>');
                preview.find('.preview-message').html(messageContent);

                // Footer
                if (template.footer) {
                    preview.find('.preview-footer').text(template.footer).show();
                } else {
                    preview.find('.preview-footer').hide();
                }

                // Buttons
                var buttonsHtml = '';
                var buttonVars = [];
                template.components.forEach(function(comp) {
                    if (comp.type === 'BUTTONS' && comp.buttons) {
                        comp.buttons.forEach(function(btn, idx) {
                            var hasVar = btn.url && btn.url.indexOf('{{') > -1;
                            buttonsHtml += '<div class="preview-button ' + (hasVar ? 'has-variable' : '') + '">';
                            if (btn.type === 'URL') {
                                buttonsHtml += '<span class="dashicons dashicons-external"></span>';
                            } else if (btn.type === 'PHONE_NUMBER') {
                                buttonsHtml += '<span class="dashicons dashicons-phone"></span>';
                            }
                            buttonsHtml += btn.text;
                            if (hasVar) {
                                buttonsHtml += ' <small>(has variable)</small>';
                                // Extract button variable
                                var btnVarMatch = btn.url.match(/\{\{(\d+)\}\}/);
                                if (btnVarMatch) {
                                    buttonVars.push({num: btnVarMatch[1], type: 'button_url'});
                                }
                            }
                            buttonsHtml += '</div>';
                        });
                    }
                });
                preview.find('.preview-buttons').html(buttonsHtml);

                preview.addClass('visible');

                // Update variable mapping
                var allVars = (template.variables || []).slice();
                // Add button variables
                buttonVars.forEach(function(bv) {
                    if (allVars.indexOf(bv.num) === -1) {
                        allVars.push(bv.num);
                    }
                });
                allVars.sort(function(a, b) { return parseInt(a) - parseInt(b); });

                if (allVars.length > 0) {
                    var grid = mapping.find('.variable-grid');
                    grid.empty();

                    var savedVarMap = savedConfig[status] ? savedConfig[status].variable_map : {};
                    var savedCustomText = savedConfig[status] ? savedConfig[status].custom_text : {};

                    allVars.forEach(function(varNum) {
                        var savedVal = savedVarMap['var_' + varNum] || '';
                        var savedCustom = savedCustomText['var_' + varNum] || '';
                        var isCustomText = savedVal === 'custom_text';
                        
                        var item = $('<div class="variable-item"></div>');
                        var header = $('<div class="variable-item-header"></div>');
                        var label = $('<label></label>').html(
                            '<span class="var-num">{{' + varNum + '}}</span> ' +
                            '<?php esc_html_e( 'maps to:', 'broodle-engage-connector' ); ?>'
                        );
                        
                        var select = $('<select></select>')
                            .attr('data-var', varNum)
                            .addClass('variable-map-select');
                        
                        for (var key in variableOptions) {
                            var opt = $('<option></option>')
                                .val(key)
                                .text(variableOptions[key]);
                            
                            if (savedVal === key) {
                                opt.prop('selected', true);
                            }
                            
                            select.append(opt);
                        }

                        // Custom text input
                        var customTextDiv = $('<div class="custom-text-input ' + (isCustomText ? 'visible' : '') + '"></div>');
                        customTextDiv.append('<label><?php esc_html_e( 'Enter custom value:', 'broodle-engage-connector' ); ?></label>');
                        var customInput = $('<input type="text" class="custom-text-value" data-var="' + varNum + '" placeholder="<?php esc_attr_e( 'Enter coupon code or custom text...', 'broodle-engage-connector' ); ?>">')
                            .val(savedCustom);
                        customTextDiv.append(customInput);

                        header.append(label);
                        item.append(header).append(select).append(customTextDiv);
                        grid.append(item);
                    });

                    mapping.show();
                } else {
                    mapping.hide();
                }
            }

            // Add custom status card
            function addCustomStatusCard(id, name, desc, icon, wcStatus, eventType) {
                var dataAttrs = 'data-status="' + id + '" data-is-custom="true"';
                if (wcStatus) {
                    dataAttrs += ' data-wc-status="' + wcStatus + '"';
                }
                if (eventType) {
                    dataAttrs += ' data-event-type="' + eventType + '"';
                }
                
                var cardHtml = `
                <div class="status-card" ${dataAttrs}>
                    <div class="status-card-header">
                        <div class="status-info">
                            <div class="status-icon">${icon}</div>
                            <div class="status-details">
                                <h3>${name}</h3>
                                <p>${desc}</p>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center;">
                            <button type="button" class="expand-btn" title="<?php esc_attr_e( 'Expand/Collapse', 'broodle-engage-connector' ); ?>">
                                <span class="expand-text"><?php esc_html_e( 'Expand', 'broodle-engage-connector' ); ?></span>
                                <span class="collapse-text"><?php esc_html_e( 'Collapse', 'broodle-engage-connector' ); ?></span>
                                <span class="dashicons dashicons-arrow-down-alt2"></span>
                            </button>
                            <label class="toggle-switch">
                                <input type="checkbox" class="status-toggle" data-status="${id}">
                                <span class="toggle-slider"></span>
                            </label>
                            <button type="button" class="delete-status-btn" data-status="${id}" title="<?php esc_attr_e( 'Delete', 'broodle-engage-connector' ); ?>">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                    </div>
                    <div class="status-card-body">
                        <div class="config-section">
                            <h4 class="config-section-title">
                                <span class="dashicons dashicons-format-aside"></span>
                                <?php esc_html_e( 'Select Template', 'broodle-engage-connector' ); ?>
                            </h4>
                            <select class="template-select" data-status="${id}">
                                <option value=""><?php esc_html_e( '-- Select a template --', 'broodle-engage-connector' ); ?></option>
                            </select>
                            <div class="template-preview" data-status="${id}">
                                <div class="preview-header">
                                    <span class="dashicons dashicons-visibility"></span>
                                    <h4><?php esc_html_e( 'Template Preview', 'broodle-engage-connector' ); ?></h4>
                                    <span class="preview-badge template-language">EN</span>
                                    <span class="preview-badge template-category">UTILITY</span>
                                </div>
                                <div class="preview-content">
                                    <div class="preview-image" style="display: none;">
                                        <span class="dashicons dashicons-format-image"></span>
                                        <div class="preview-image-info">
                                            <h5><?php esc_html_e( 'Header Image', 'broodle-engage-connector' ); ?></h5>
                                            <p><?php esc_html_e( 'This template includes an image header', 'broodle-engage-connector' ); ?></p>
                                        </div>
                                    </div>
                                    <div class="preview-message"></div>
                                    <div class="preview-footer" style="display: none;"></div>
                                    <div class="preview-buttons"></div>
                                </div>
                            </div>
                        </div>
                        <div class="image-selection" data-status="${id}">
                            <div class="image-selection-header">
                                <span class="dashicons dashicons-format-image"></span>
                                <h5><?php esc_html_e( 'Header Image (Optional)', 'broodle-engage-connector' ); ?></h5>
                            </div>
                            <div class="image-selection-row">
                                <div class="image-preview-box">
                                    <div class="image-thumb">
                                        <span class="dashicons dashicons-format-image"></span>
                                    </div>
                                    <div>
                                        <button type="button" class="button select-image-btn" data-status="${id}">
                                            <?php esc_html_e( 'Select Image', 'broodle-engage-connector' ); ?>
                                        </button>
                                        <button type="button" class="button remove-image-btn" data-status="${id}" style="display: none;">
                                            <?php esc_html_e( 'Remove', 'broodle-engage-connector' ); ?>
                                        </button>
                                        <input type="hidden" class="image-id-input" data-status="${id}" value="">
                                    </div>
                                </div>
                                <label class="use-product-image-label">
                                    <input type="checkbox" class="use-product-image-check" data-status="${id}">
                                    <span class="dashicons dashicons-products"></span>
                                    <?php esc_html_e( 'Use product image', 'broodle-engage-connector' ); ?>
                                </label>
                            </div>
                        </div>
                        <div class="config-section variable-mapping" data-status="${id}" style="display: none;">
                            <h4 class="config-section-title">
                                <span class="dashicons dashicons-admin-generic"></span>
                                <?php esc_html_e( 'Map Template Variables', 'broodle-engage-connector' ); ?>
                            </h4>
                            <div class="variable-grid"></div>
                        </div>
                    </div>
                </div>
                `;
                
                $('#status-cards').append(cardHtml);
                
                // Populate the new dropdown
                var select = $('.template-select[data-status="' + id + '"]');
                templatesData.forEach(function(template) {
                    var option = $('<option></option>')
                        .val(template.name)
                        .text(template.name + ' (' + template.category + ')')
                        .data('template', template);
                    select.append(option);
                });
            }

            // Clear modal
            function clearModalFields() {
                $('#custom-notification-type').val('order_event');
                $('#custom-wc-status').val($('#custom-wc-status option:first').val());
                $('#custom-user-event').val('user_registered');
                $('#custom-status-name').val('');
                $('#custom-status-desc').val('');
                $('#order-event-fields').show();
                $('#user-event-fields').hide();
            }

            // Save configuration
            function saveConfiguration() {
                var btn = $('#save-templates');
                btn.prop('disabled', true).html(
                    '<span class="dashicons dashicons-update spin" style="vertical-align: middle; margin-right: 8px;"></span>' +
                    '<?php esc_html_e( 'Saving...', 'broodle-engage-connector' ); ?>'
                );

                var config = {};
                var customStatusList = [];

                $('.status-card').each(function() {
                    var card = $(this);
                    var status = card.data('status');
                    var isCustom = card.data('is-custom') === true || card.data('is-custom') === 'true';
                    var isEnabled = card.find('.status-toggle').is(':checked');
                    var templateName = card.find('.template-select').val();
                    var templateLang = '';
                    var variableMap = {};
                    var customText = {};
                    var imageId = card.find('.image-id-input').val();
                    var useProductImage = card.find('.use-product-image-check').is(':checked');

                    // Get template info
                    var templateBody = '';
                    var template = templatesData.find(function(t) {
                        return t.name === templateName;
                    });
                    if (template) {
                        templateLang = template.language;

                        // Check for image header
                        var hasImageHeader = false;
                        template.components.forEach(function(comp) {
                            if (comp.type === 'HEADER' && comp.format === 'IMAGE') {
                                hasImageHeader = true;
                            }
                        });

                        // Build full message body
                        if (hasImageHeader) {
                            templateBody = '🖼️ [Image]\n\n';
                        } else if (template.header) {
                            templateBody = template.header + '\n\n';
                        }

                        templateBody += (template.body || '');

                        if (template.footer) {
                            templateBody += '\n\n_' + template.footer + '_';
                        }

                        // Add buttons
                        template.components.forEach(function(comp) {
                            if (comp.type === 'BUTTONS' && comp.buttons) {
                                templateBody += '\n';
                                comp.buttons.forEach(function(btn) {
                                    if (btn.type === 'URL') {
                                        templateBody += '\n🔗 ' + btn.text + (btn.url ? ' — ' + btn.url : '');
                                    } else if (btn.type === 'PHONE_NUMBER') {
                                        templateBody += '\n📞 ' + btn.text + (btn.phone_number ? ' — ' + btn.phone_number : '');
                                    } else if (btn.type === 'QUICK_REPLY') {
                                        templateBody += '\n↩️ ' + btn.text;
                                    }
                                });
                            }
                        });
                    }

                    // Get variable mappings and custom text
                    card.find('.variable-map-select').each(function() {
                        var varNum = $(this).data('var');
                        var value = $(this).val();
                        variableMap['var_' + varNum] = value;
                    });

                    card.find('.custom-text-value').each(function() {
                        var varNum = $(this).data('var');
                        var value = $(this).val();
                        if (value) {
                            customText['var_' + varNum] = value;
                        }
                    });

                    // Collect button variable info
                    var buttonVariablesList = [];
                    var bodyVariableCount = 0;
                    if (template) {
                        bodyVariableCount = (template.variables || []).length;
                        template.components.forEach(function(comp) {
                            if (comp.type === 'BUTTONS' && comp.buttons) {
                                comp.buttons.forEach(function(btn, idx) {
                                    if (btn.type === 'URL' && btn.url && btn.url.indexOf('{{') > -1) {
                                        var match = btn.url.match(/\{\{(\d+)\}\}/);
                                        if (match) {
                                            buttonVariablesList.push({index: idx, type: 'url', var_num: match[1]});
                                        }
                                    }
                                });
                            }
                        });
                    }

                    config[status] = {
                        enabled: isEnabled,
                        template_name: templateName,
                        template_lang: templateLang,
                        template_body: templateBody,
                        variable_map: variableMap,
                        custom_text: customText,
                        image_id: imageId,
                        use_product_image: useProductImage,
                        button_variables: buttonVariablesList,
                        body_variable_count: bodyVariableCount
                    };

                    // Collect custom statuses
                    if (isCustom) {
                        customStatusList.push({
                            id: status,
                            name: card.find('.status-details h3').text(),
                            description: card.find('.status-details p').text(),
                            icon: card.find('.status-icon').text(),
                            wc_status: card.data('wc-status') || '',
                            event_type: card.data('event-type') || ''
                        });
                    }
                });

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'broodle_engage_save_template_config',
                        nonce: '<?php echo wp_create_nonce( 'broodle_engage_admin_nonce' ); ?>',
                        config: config,
                        custom_statuses: customStatusList
                    },
                    success: function(response) {
                        btn.prop('disabled', false).html(
                            '<span class="dashicons dashicons-cloud-saved" style="vertical-align: middle; margin-right: 8px;"></span>' +
                            '<?php esc_html_e( 'Save Configuration', 'broodle-engage-connector' ); ?>'
                        );
                        
                        if (response.success) {
                            savedConfig = config;
                            $('#save-status').addClass('visible');
                            setTimeout(function() {
                                $('#save-status').removeClass('visible');
                            }, 4000);
                        } else {
                            alert(response.data.message || '<?php esc_html_e( 'Error saving configuration', 'broodle-engage-connector' ); ?>');
                        }
                    },
                    error: function() {
                        btn.prop('disabled', false).html(
                            '<span class="dashicons dashicons-cloud-saved" style="vertical-align: middle; margin-right: 8px;"></span>' +
                            '<?php esc_html_e( 'Save Configuration', 'broodle-engage-connector' ); ?>'
                        );
                        alert('<?php esc_html_e( 'Network error. Please try again.', 'broodle-engage-connector' ); ?>');
                    }
                });
            }
        });
        </script>
        <?php
        // Enqueue media scripts for image selection
        wp_enqueue_media();
    }

    private function get_woocommerce_order_statuses() {
        if ( ! function_exists( 'wc_get_order_statuses' ) ) {
            return array();
        }

        $statuses = wc_get_order_statuses();

        // Remove the 'wc-' prefix for cleaner display and add some custom options
        $clean_statuses = array();
        foreach ( $statuses as $key => $label ) {
            $clean_key = str_replace( 'wc-', '', $key );
            $clean_statuses[ $clean_key ] = sprintf( '%s (%s)', $label, $clean_key );
        }

        // Add common shipping plugin statuses that might not be registered yet
        $additional_statuses = array(
            'shipped' => 'Shipped (shipped)',
            'partial-shipped' => 'Partially Shipped (partial-shipped)',
            'delivered' => 'Delivered (delivered)',
            'out-for-delivery' => 'Out for Delivery (out-for-delivery)',
            'dispatched' => 'Dispatched (dispatched)',
            'in-transit' => 'In Transit (in-transit)',
            'ready-for-pickup' => 'Ready for Pickup (ready-for-pickup)',
            'picked-up' => 'Picked Up (picked-up)',
        );

        // Only add if they don't already exist
        foreach ( $additional_statuses as $key => $label ) {
            if ( ! isset( $clean_statuses[ $key ] ) ) {
                $clean_statuses[ $key ] = $label;
            }
        }

        return $clean_statuses;
    }

    /**
     * Render logs tab
     */
    private function render_logs_tab() {
        $page = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
        $per_page = 20;
        $offset = ( $page - 1 ) * $per_page;

        $logs_data = Broodle_Engage_Logger::get_logs(
            array(
                'limit' => $per_page,
                'offset' => $offset,
            )
        );

        $stats = Broodle_Engage_Logger::get_stats( 30 );
        $scheduled_logs = Broodle_Engage_Logger::get_scheduled_logs();
        ?>
        <style>
        /* Brand Colors - Scoped to logs tab */
        .logs-tab-content {
            --logs-brand-primary: #0E5ECE;
            --logs-brand-secondary: #0A4BA6;
            --logs-brand-gradient: linear-gradient(135deg, #0E5ECE, #1a73e8);
            --logs-brand-translucent: rgba(14, 94, 206, 0.1);
            --logs-success: #00a32a;
            --logs-error: #d63638;
            --logs-pending: #dba617;
            --logs-retry: #0073aa;
            --logs-scheduled: #8c5fc3;
        }

        /* Logs Page Layout */
        .logs-page-header {
            background: var(--logs-brand-gradient);
            color: white;
            padding: 32px 40px;
            border-radius: 12px;
            margin-bottom: 32px;
            box-shadow: 0 4px 20px rgba(14, 94, 206, 0.25);
        }
        .logs-page-title {
            margin: 0 0 8px 0;
            font-size: 28px;
            font-weight: 700;
            color: white;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .logs-page-title .dashicons {
            font-size: 32px;
            width: 32px;
            height: 32px;
            color: white;
        }
        .logs-page-subtitle {
            margin: 0;
            font-size: 15px;
            color: rgba(255, 255, 255, 0.9);
        }

        /* Stats Grid */
        .logs-stats-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 20px;
            margin-bottom: 32px;
        }
        .logs-stat-box {
            background: #fff;
            border: 1px solid #e1e5e9;
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }
        .logs-stat-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--logs-brand-primary);
        }
        .logs-stat-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }
        .logs-stat-box h3 {
            font-size: 2.4em;
            margin: 0 0 8px 0;
            font-weight: 700;
            line-height: 1;
            color: var(--logs-brand-primary);
        }
        .logs-stat-box p {
            margin: 0;
            color: #646970;
            font-weight: 500;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .logs-stat-box.success::before { background: var(--logs-success); }
        .logs-stat-box.success h3 { color: var(--logs-success); }
        .logs-stat-box.failed::before { background: var(--logs-error); }
        .logs-stat-box.failed h3 { color: var(--logs-error); }
        .logs-stat-box.pending::before { background: var(--logs-pending); }
        .logs-stat-box.pending h3 { color: var(--logs-pending); }
        .logs-stat-box.retry::before { background: var(--logs-retry); }
        .logs-stat-box.retry h3 { color: var(--logs-retry); }
        .logs-stat-box.scheduled::before { background: var(--logs-scheduled); }
        .logs-stat-box.scheduled h3 { color: var(--logs-scheduled); }

        /* Section Headers */
        .logs-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 32px 0 20px 0;
            padding-bottom: 12px;
            border-bottom: 2px solid #f0f0f1;
        }
        .logs-section-title {
            font-size: 20px;
            font-weight: 600;
            color: #1d2327;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .logs-section-title .dashicons {
            color: var(--logs-brand-primary);
        }
        .logs-section-count {
            background: var(--logs-brand-translucent);
            color: var(--logs-brand-primary);
            padding: 4px 14px;
            border-radius: 16px;
            font-size: 13px;
            font-weight: 600;
        }

        /* Table Container */
        .logs-table-container {
            background: #fff;
            border: 1px solid #e1e5e9;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            margin-bottom: 24px;
        }
        .logs-table {
            margin: 0;
            border: none;
            border-radius: 0;
            width: 100%;
            table-layout: fixed;
        }
        .logs-table thead th {
            background: linear-gradient(to bottom, #f8f9fa, #f3f4f6);
            border-bottom: 2px solid #e1e5e9;
            padding: 16px 14px;
            font-weight: 600;
            color: #1d2327;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .logs-table tbody td {
            padding: 16px 14px;
            border-bottom: 1px solid #f0f0f1;
            vertical-align: top;
            font-size: 13px;
            word-wrap: break-word;
        }
        .logs-table tbody tr:hover {
            background: var(--logs-brand-translucent);
        }
        .logs-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Status Badge */
        .logs-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #fff;
        }
        .logs-status-badge.status-success { background: var(--logs-brand-gradient); }
        .logs-status-badge.status-error { background: linear-gradient(135deg, #d63638, #dc3545); }
        .logs-status-badge.status-pending { background: linear-gradient(135deg, #dba617, #f0b90b); color: #1d2327; }
        .logs-status-badge.status-retry { background: linear-gradient(135deg, #0073aa, #005a87); }
        .logs-status-badge.status-scheduled { background: linear-gradient(135deg, #8c5fc3, #a066d3); }

        /* Order Link */
        .logs-order-link {
            color: var(--logs-brand-primary);
            text-decoration: none;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 6px;
            transition: all 0.2s ease;
            display: inline-block;
        }
        .logs-order-link:hover {
            background: var(--logs-brand-translucent);
            color: var(--logs-brand-secondary);
        }

        /* Log Detail Card */
        .log-detail-card {
            background: #f8f9fa;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            margin-top: 8px;
            overflow: hidden;
        }
        .log-detail-header {
            background: linear-gradient(to right, var(--logs-brand-translucent), transparent);
            padding: 10px 14px;
            border-bottom: 1px solid #e1e5e9;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--logs-brand-primary);
            font-weight: 500;
            font-size: 12px;
            transition: background 0.2s ease;
        }
        .log-detail-header:hover {
            background: var(--logs-brand-translucent);
        }
        .log-detail-header .dashicons {
            font-size: 16px;
            width: 16px;
            height: 16px;
            transition: transform 0.3s ease;
        }
        .log-detail-card.open .log-detail-header .dashicons {
            transform: rotate(180deg);
        }
        .log-detail-content {
            display: none;
            padding: 14px;
        }
        .log-detail-card.open .log-detail-content {
            display: block;
        }
        .log-detail-section {
            margin-bottom: 14px;
        }
        .log-detail-section:last-child {
            margin-bottom: 0;
        }
        .log-detail-section h5 {
            margin: 0 0 8px 0;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #646970;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .log-detail-section pre {
            background: #fff;
            border: 1px solid #e1e5e9;
            padding: 12px;
            margin: 0;
            border-radius: 6px;
            font-size: 11px;
            max-height: 200px;
            overflow: auto;
            line-height: 1.5;
            color: #1d2327;
        }
        .log-message-preview {
            background: #e7f5ff;
            border: 1px solid #b3d9ff;
            border-radius: 8px;
            padding: 14px;
            font-size: 13px;
            line-height: 1.6;
            color: #1d2327;
            white-space: pre-wrap;
        }
        .log-variables-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
        }
        .log-variable-item {
            background: #fff;
            border: 1px solid #e1e5e9;
            border-radius: 6px;
            padding: 10px 12px;
        }
        .log-variable-item .var-name {
            font-size: 10px;
            text-transform: uppercase;
            color: #646970;
            margin-bottom: 4px;
        }
        .log-variable-item .var-value {
            font-size: 13px;
            color: #1d2327;
            font-weight: 500;
            word-break: break-word;
        }

        /* Scheduled Notifications */
        .scheduled-notifications-container {
            background: linear-gradient(to right, rgba(140, 95, 195, 0.08), transparent);
            border: 1px solid rgba(140, 95, 195, 0.3);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
        }
        .scheduled-notifications-description {
            margin: 0 0 16px 0;
            color: #6d4a8c;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .overdue-notification {
            background: #fff2f2;
        }
        .overdue-notification td:first-child {
            border-left: 4px solid #d63638;
        }

        /* Pagination */
        .logs-pagination {
            background: #fff;
            border: 1px solid #e1e5e9;
            border-radius: 10px;
            padding: 16px 20px;
            margin-top: 20px;
            display: flex;
            justify-content: center;
        }
        .logs-pagination .page-numbers {
            padding: 8px 14px;
            margin: 0 4px;
            border-radius: 6px;
            transition: all 0.2s ease;
            color: #1d2327;
            text-decoration: none;
        }
        .logs-pagination .page-numbers:hover {
            background: var(--logs-brand-translucent);
            color: var(--logs-brand-primary);
        }
        .logs-pagination .page-numbers.current {
            background: var(--logs-brand-gradient);
            color: #fff;
        }

        /* Empty State */
        .logs-empty-state {
            text-align: center;
            padding: 60px 20px;
            background: #fff;
            border: 1px solid #e1e5e9;
            border-radius: 12px;
        }
        .logs-empty-state .dashicons {
            font-size: 48px;
            width: 48px;
            height: 48px;
            color: var(--logs-brand-primary);
            background: var(--logs-brand-translucent);
            padding: 20px;
            border-radius: 50%;
            margin-bottom: 20px;
        }
        .logs-empty-state h3 {
            margin: 0 0 8px 0;
            color: #1d2327;
            font-size: 18px;
        }
        .logs-empty-state p {
            margin: 0;
            color: #646970;
        }

        /* Error Message */
        .logs-error-message {
            color: #d63638;
            font-size: 12px;
            background: #fff2f2;
            padding: 6px 10px;
            border-radius: 4px;
            display: inline-block;
            max-width: 100%;
            word-break: break-word;
        }

        /* Responsive */
        @media (max-width: 1400px) {
            .logs-stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        @media (max-width: 1000px) {
            .logs-stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 782px) {
            .logs-page-header {
                padding: 24px;
                border-radius: 8px;
            }
            .logs-page-title {
                font-size: 22px;
            }
            .logs-stats-grid {
                grid-template-columns: 1fr 1fr;
                gap: 12px;
            }
            .logs-stat-box {
                padding: 16px;
            }
            .logs-stat-box h3 {
                font-size: 1.8em;
            }
            .logs-table-container {
                overflow-x: auto;
            }
            .logs-table {
                min-width: 700px;
            }
        }
        @media (max-width: 480px) {
            .logs-stats-grid {
                grid-template-columns: 1fr;
            }
        }
        </style>

        <div class="logs-tab-content">
        <div class="logs-page-header">
            <h2 class="logs-page-title">
                <span class="dashicons dashicons-chart-line"></span>
                <?php esc_html_e( 'Notification Logs', 'broodle-engage-connector' ); ?>
            </h2>
            <p class="logs-page-subtitle"><?php esc_html_e( 'Track all WhatsApp notification activity, API responses, and message delivery status.', 'broodle-engage-connector' ); ?></p>
        </div>

        <div class="logs-stats-grid">
            <div class="logs-stat-box">
                <h3><?php echo esc_html( $stats['total'] ); ?></h3>
                <p><?php esc_html_e( 'Total (30 Days)', 'broodle-engage-connector' ); ?></p>
            </div>
            <div class="logs-stat-box success">
                <h3><?php echo esc_html( $stats['success'] ); ?></h3>
                <p><?php esc_html_e( 'Successful', 'broodle-engage-connector' ); ?></p>
            </div>
            <div class="logs-stat-box failed">
                <h3><?php echo esc_html( $stats['error'] ?? 0 ); ?></h3>
                <p><?php esc_html_e( 'Failed', 'broodle-engage-connector' ); ?></p>
            </div>
            <div class="logs-stat-box pending">
                <h3><?php echo esc_html( $stats['pending'] ); ?></h3>
                <p><?php esc_html_e( 'Pending', 'broodle-engage-connector' ); ?></p>
            </div>
            <div class="logs-stat-box scheduled">
                <h3><?php echo esc_html( count( $scheduled_logs['logs'] ?? array() ) ); ?></h3>
                <p><?php esc_html_e( 'Scheduled', 'broodle-engage-connector' ); ?></p>
            </div>
        </div>

        <?php if ( ! empty( $scheduled_logs['logs'] ) ) : ?>
        <div class="logs-section-header">
            <h2 class="logs-section-title">
                <span class="dashicons dashicons-clock"></span>
                <?php esc_html_e( 'Scheduled Notifications', 'broodle-engage-connector' ); ?>
            </h2>
            <span class="logs-section-count"><?php echo count( $scheduled_logs['logs'] ); ?> <?php esc_html_e( 'pending', 'broodle-engage-connector' ); ?></span>
        </div>
        <div class="scheduled-notifications-container">
            <p class="scheduled-notifications-description">
                <span class="dashicons dashicons-info"></span>
                <?php esc_html_e( 'These notifications are queued and will be sent at the specified times.', 'broodle-engage-connector' ); ?>
            </p>
            <div class="logs-table-container" style="margin-bottom: 0;">
                <table class="wp-list-table widefat fixed striped logs-table">
                <thead>
                    <tr>
                        <th style="width: 18%;"><?php esc_html_e( 'Scheduled Time', 'broodle-engage-connector' ); ?></th>
                        <th style="width: 10%;"><?php esc_html_e( 'Order', 'broodle-engage-connector' ); ?></th>
                        <th style="width: 15%;"><?php esc_html_e( 'Phone', 'broodle-engage-connector' ); ?></th>
                        <th style="width: 20%;"><?php esc_html_e( 'Template', 'broodle-engage-connector' ); ?></th>
                        <th style="width: 12%;"><?php esc_html_e( 'Status', 'broodle-engage-connector' ); ?></th>
                        <th style="width: 25%;"><?php esc_html_e( 'Details', 'broodle-engage-connector' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $scheduled_logs['logs'] as $log ) :
                        $response_data = json_decode( $log->api_response, true );
                        $scheduled_time = $response_data['scheduled_time'] ?? '';
                        $delay_minutes = $response_data['delay_minutes'] ?? '';
                        $is_overdue = ! empty( $scheduled_time ) && strtotime( $scheduled_time ) < time();
                    ?>
                        <tr class="<?php echo esc_attr( $is_overdue ? 'overdue-notification' : '' ); ?>">
                            <td>
                                <strong><?php echo esc_html( $scheduled_time ); ?></strong>
                                <?php if ( $is_overdue ) : ?>
                                    <br><span style="color: #d63638; font-size: 11px;">⚠️ <?php esc_html_e( 'OVERDUE', 'broodle-engage-connector' ); ?></span>
                                <?php elseif ( $scheduled_time ) : ?>
                                    <br><span style="color: #646970; font-size: 11px;">
                                        <?php
                                        $time_diff = strtotime( $scheduled_time ) - time();
                                        if ( $time_diff > 0 ) {
                                            /* translators: %s: human readable time difference */
                                            echo esc_html( sprintf( __( 'in %s', 'broodle-engage-connector' ), human_time_diff( time(), strtotime( $scheduled_time ) ) ) );
                                        }
                                        ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo esc_url( admin_url( 'post.php?post=' . $log->order_id . '&action=edit' ) ); ?>" class="logs-order-link">
                                    #<?php echo esc_html( $log->order_id ); ?>
                                </a>
                            </td>
                            <td><?php echo esc_html( $log->phone_number ); ?></td>
                            <td><strong><?php echo esc_html( $log->template_name ); ?></strong></td>
                            <td>
                                <span class="logs-status-badge status-scheduled">
                                    <?php echo esc_html( ucfirst( $log->status ) ); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ( $delay_minutes ) : ?>
                                    <?php /* translators: %s: number of minutes */ ?>
                                    <?php echo esc_html( sprintf( __( 'Delay: %s minutes', 'broodle-engage-connector' ), $delay_minutes ) ); ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <div class="logs-section-header">
            <h2 class="logs-section-title">
                <span class="dashicons dashicons-list-view"></span>
                <?php esc_html_e( 'Recent Notification Logs', 'broodle-engage-connector' ); ?>
            </h2>
            <span class="logs-section-count"><?php echo esc_html( $logs_data['total'] ); ?> <?php esc_html_e( 'total', 'broodle-engage-connector' ); ?></span>
        </div>

        <?php if ( ! empty( $logs_data['logs'] ) ) : ?>
            <div class="logs-table-container">
                <table class="wp-list-table widefat fixed striped logs-table">
                <thead>
                    <tr>
                        <th style="width: 14%;"><?php esc_html_e( 'Date', 'broodle-engage-connector' ); ?></th>
                        <th style="width: 8%;"><?php esc_html_e( 'Order', 'broodle-engage-connector' ); ?></th>
                        <th style="width: 12%;"><?php esc_html_e( 'Phone', 'broodle-engage-connector' ); ?></th>
                        <th style="width: 14%;"><?php esc_html_e( 'Template', 'broodle-engage-connector' ); ?></th>
                        <th style="width: 10%;"><?php esc_html_e( 'Status', 'broodle-engage-connector' ); ?></th>
                        <th style="width: 42%;"><?php esc_html_e( 'Details', 'broodle-engage-connector' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $logs_data['logs'] as $index => $log ) :
                        $api_response = json_decode( $log->api_response, true );
                        $response_data_decoded = json_decode( $log->response_data, true );
                        $template_vars = $response_data_decoded['template_vars'] ?? array();
                        $sent_message = $api_response['message'] ?? $api_response['sent_message'] ?? '';
                        $notification_type = $response_data_decoded['notification_type'] ?? '';
                    ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html( wp_date( 'M j, Y', strtotime( $log->created_at ) ) ); ?></strong>
                                <br><span style="color: #646970; font-size: 11px;"><?php echo esc_html( wp_date( 'g:i A', strtotime( $log->created_at ) ) ); ?></span>
                            </td>
                            <td>
                                <a href="<?php echo esc_url( admin_url( 'post.php?post=' . $log->order_id . '&action=edit' ) ); ?>" class="logs-order-link">
                                    #<?php echo esc_html( $log->order_id ); ?>
                                </a>
                            </td>
                            <td><?php echo esc_html( $log->phone_number ); ?></td>
                            <td>
                                <strong><?php echo esc_html( $log->template_name ); ?></strong>
                                <?php if ( $notification_type ) : ?>
                                    <br><span style="color: #646970; font-size: 11px;"><?php echo esc_html( $notification_type ); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="logs-status-badge status-<?php echo esc_attr( $log->status ); ?>">
                                    <?php echo esc_html( ucfirst( $log->status ) ); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ( ! empty( $log->error_message ) ) : ?>
                                    <span class="logs-error-message">⚠️ <?php echo esc_html( $log->error_message ); ?></span>
                                <?php endif; ?>

                                <?php if ( $api_response || ! empty( $template_vars ) ) : ?>
                                <div class="log-detail-card" id="log-detail-<?php echo esc_attr( $index ); ?>">
                                    <div class="log-detail-header" onclick="this.parentElement.classList.toggle('open')">
                                        <span class="dashicons dashicons-arrow-down-alt2"></span>
                                        <?php esc_html_e( 'View API Details', 'broodle-engage-connector' ); ?>
                                    </div>
                                    <div class="log-detail-content">
                                        <?php if ( $sent_message ) : ?>
                                        <div class="log-detail-section">
                                            <h5><span class="dashicons dashicons-format-chat"></span> <?php esc_html_e( 'Sent Message', 'broodle-engage-connector' ); ?></h5>
                                            <div class="log-message-preview"><?php echo esc_html( $sent_message ); ?></div>
                                        </div>
                                        <?php endif; ?>

                                        <?php if ( ! empty( $template_vars ) ) : ?>
                                        <div class="log-detail-section">
                                            <h5><span class="dashicons dashicons-editor-code"></span> <?php esc_html_e( 'Template Variables', 'broodle-engage-connector' ); ?></h5>
                                            <div class="log-variables-grid">
                                                <?php foreach ( $template_vars as $var_name => $var_value ) : ?>
                                                <div class="log-variable-item">
                                                    <div class="var-name"><?php echo esc_html( $var_name ); ?></div>
                                                    <div class="var-value"><?php echo esc_html( is_array( $var_value ) ? wp_json_encode( $var_value ) : $var_value ); ?></div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <?php if ( $api_response ) : ?>
                                        <div class="log-detail-section">
                                            <h5><span class="dashicons dashicons-rest-api"></span> <?php esc_html_e( 'API Response', 'broodle-engage-connector' ); ?></h5>
                                            <pre><?php echo esc_html( wp_json_encode( $api_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) ); ?></pre>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php else : ?>
                                    <span style="color: #999;">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                </table>
            </div>

            <?php
            // Pagination
            $total_pages = ceil( $logs_data['total'] / $per_page );
            if ( $total_pages > 1 ) {
                echo '<div class="logs-pagination">';
                echo paginate_links(
                    array(
                        'base' => add_query_arg( 'paged', '%#%' ),
                        'format' => '',
                        'prev_text' => __( '&laquo; Previous', 'broodle-engage-connector' ),
                        'next_text' => __( 'Next &raquo;', 'broodle-engage-connector' ),
                        'total' => $total_pages,
                        'current' => $page,
                    )
                );
                echo '</div>';
            }
            ?>
        <?php else : ?>
            <div class="logs-empty-state">
                <span class="dashicons dashicons-email-alt"></span>
                <h3><?php esc_html_e( 'No notification logs found', 'broodle-engage-connector' ); ?></h3>
                <p><?php esc_html_e( 'WhatsApp notifications will appear here once they are sent.', 'broodle-engage-connector' ); ?></p>
            </div>
        <?php endif; ?>
        </div><!-- .logs-tab-content -->
        <?php
    }

    /**
     * Render help tab
     */
    private function render_help_tab() {
        ?>
        <style>
        /* Help Page Styles */
        .help-page-header {
            background: linear-gradient(135deg, #0E5ECE 0%, #0a4aa3 100%);
            color: white;
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(14, 94, 206, 0.25);
            position: relative;
            overflow: hidden;
        }
        .help-page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 60%;
            height: 200%;
            background: rgba(255,255,255,0.05);
            transform: rotate(15deg);
            pointer-events: none;
        }
        .help-page-header h2 {
            margin: 0 0 10px 0;
            font-size: 26px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
            z-index: 1;
            color: white;
        }
        .help-page-header h2 .dashicons {
            font-size: 32px;
            width: 32px;
            height: 32px;
        }
        .help-page-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 15px;
            position: relative;
            z-index: 1;
        }
        .help-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
            margin-bottom: 30px;
        }
        .help-card {
            background: #fff;
            border: 1px solid #e1e5e9;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            transition: box-shadow 0.2s ease;
        }
        .help-card:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        }
        .help-card-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0 0 16px 0;
            font-size: 17px;
            font-weight: 600;
            color: #1d2327;
        }
        .help-card-title .dashicons {
            color: #0E5ECE;
            font-size: 22px;
            width: 22px;
            height: 22px;
        }
        .help-card-title .step-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            background: linear-gradient(135deg, #0E5ECE 0%, #0a4aa3 100%);
            color: white;
            border-radius: 50%;
            font-size: 13px;
            font-weight: 700;
            flex-shrink: 0;
        }
        .help-card p,
        .help-card li {
            color: #50575e;
            font-size: 14px;
            line-height: 1.6;
            margin: 0 0 8px 0;
        }
        .help-card ul {
            margin: 10px 0 0 0;
            padding-left: 18px;
        }
        .help-card a {
            color: #0E5ECE;
            text-decoration: none;
            font-weight: 500;
        }
        .help-card a:hover {
            text-decoration: underline;
        }
        .help-card-full {
            grid-column: 1 / -1;
        }
        .variable-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            font-size: 13px;
        }
        .variable-table th {
            background: #f0f6fc;
            text-align: left;
            padding: 10px 14px;
            font-weight: 600;
            color: #1d2327;
            border-bottom: 2px solid #e1e5e9;
        }
        .variable-table td {
            padding: 9px 14px;
            border-bottom: 1px solid #f0f0f1;
            color: #50575e;
        }
        .variable-table tr:hover td {
            background: #f8f9fa;
        }
        .variable-table code {
            background: #f0f6fc;
            color: #0E5ECE;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 12px;
        }
        .help-tip {
            background: #f0f6fc;
            border-left: 4px solid #0E5ECE;
            padding: 14px 18px;
            border-radius: 0 8px 8px 0;
            margin-top: 16px;
            font-size: 13px;
            color: #1d2327;
            line-height: 1.5;
        }
        .help-tip strong {
            color: #0E5ECE;
        }
        @media (max-width: 960px) {
            .help-grid { grid-template-columns: 1fr; }
        }
        </style>

        <!-- Header -->
        <div class="help-page-header">
            <h2>
                <span class="dashicons dashicons-editor-help"></span>
                <?php esc_html_e( 'Help & Documentation', 'broodle-engage-connector' ); ?>
            </h2>
            <p><?php esc_html_e( 'Everything you need to set up and use Broodle Engage Connector for WooCommerce WhatsApp notifications.', 'broodle-engage-connector' ); ?></p>
        </div>

        <!-- Getting Started -->
        <h2 style="font-size: 20px; font-weight: 600; margin: 0 0 16px 0; color: #1d2327;">
            <span class="dashicons dashicons-flag" style="color: #0E5ECE; vertical-align: middle;"></span>
            <?php esc_html_e( 'Getting Started', 'broodle-engage-connector' ); ?>
        </h2>
        <div class="help-grid">
            <div class="help-card">
                <h3 class="help-card-title">
                    <span class="step-badge">1</span>
                    <?php esc_html_e( 'Create Your Broodle Account', 'broodle-engage-connector' ); ?>
                </h3>
                <p><?php esc_html_e( 'Sign up at Broodle Engage to get your WhatsApp Business API credentials:', 'broodle-engage-connector' ); ?></p>
                <ul>
                    <li><?php esc_html_e( 'Go to', 'broodle-engage-connector' ); ?> <a href="https://engage.broodle.one" target="_blank">engage.broodle.one</a></li>
                    <li><?php esc_html_e( 'Create your account and connect a WhatsApp Business number', 'broodle-engage-connector' ); ?></li>
                    <li><?php esc_html_e( 'Navigate to Settings → Account → Access Token to get your API key', 'broodle-engage-connector' ); ?></li>
                    <li><?php esc_html_e( 'Note your Account ID and WhatsApp Inbox ID', 'broodle-engage-connector' ); ?></li>
                </ul>
            </div>
            <div class="help-card">
                <h3 class="help-card-title">
                    <span class="step-badge">2</span>
                    <?php esc_html_e( 'Configure Plugin Settings', 'broodle-engage-connector' ); ?>
                </h3>
                <p><?php esc_html_e( 'Go to the Settings tab and fill in the following:', 'broodle-engage-connector' ); ?></p>
                <ul>
                    <li><strong><?php esc_html_e( 'API Access Token', 'broodle-engage-connector' ); ?></strong> — <?php esc_html_e( 'Your Broodle API token for authentication', 'broodle-engage-connector' ); ?></li>
                    <li><strong><?php esc_html_e( 'Account ID', 'broodle-engage-connector' ); ?></strong> — <?php esc_html_e( 'Your Broodle account identifier', 'broodle-engage-connector' ); ?></li>
                    <li><strong><?php esc_html_e( 'WhatsApp Inbox ID', 'broodle-engage-connector' ); ?></strong> — <?php esc_html_e( 'The inbox connected to your WhatsApp number', 'broodle-engage-connector' ); ?></li>
                    <li><strong><?php esc_html_e( 'Phone Number Field', 'broodle-engage-connector' ); ?></strong> — <?php esc_html_e( 'Choose which WooCommerce field to read the customer phone from', 'broodle-engage-connector' ); ?></li>
                    <li><strong><?php esc_html_e( 'Default Country Code', 'broodle-engage-connector' ); ?></strong> — <?php esc_html_e( 'Auto-prepended if the phone number has no country code', 'broodle-engage-connector' ); ?></li>
                </ul>
                <div class="help-tip">
                    <strong><?php esc_html_e( 'Tip:', 'broodle-engage-connector' ); ?></strong>
                    <?php esc_html_e( 'Use the "Test Connection" button to verify your API credentials, and "Send Test Message" to send a hello_world template to any WhatsApp number.', 'broodle-engage-connector' ); ?>
                </div>
            </div>
            <div class="help-card">
                <h3 class="help-card-title">
                    <span class="step-badge">3</span>
                    <?php esc_html_e( 'Create & Approve WhatsApp Templates', 'broodle-engage-connector' ); ?>
                </h3>
                <p><?php esc_html_e( 'WhatsApp requires pre-approved message templates for business-initiated messages:', 'broodle-engage-connector' ); ?></p>
                <ul>
                    <li><?php esc_html_e( 'Create templates in your Broodle Engage dashboard or via Meta Business Manager', 'broodle-engage-connector' ); ?></li>
                    <li><?php esc_html_e( 'Use {{1}}, {{2}}, {{3}}... placeholders for dynamic variables', 'broodle-engage-connector' ); ?></li>
                    <li><?php esc_html_e( 'Templates can include image headers, body text, footer, and buttons', 'broodle-engage-connector' ); ?></li>
                    <li><?php esc_html_e( 'Wait for WhatsApp to approve your templates (usually 24-48 hours)', 'broodle-engage-connector' ); ?></li>
                    <li><?php esc_html_e( 'Only APPROVED templates will appear in the plugin', 'broodle-engage-connector' ); ?></li>
                </ul>
            </div>
            <div class="help-card">
                <h3 class="help-card-title">
                    <span class="step-badge">4</span>
                    <?php esc_html_e( 'Map Templates to Order Events', 'broodle-engage-connector' ); ?>
                </h3>
                <p><?php esc_html_e( 'Go to the Templates tab to configure notifications:', 'broodle-engage-connector' ); ?></p>
                <ul>
                    <li><?php esc_html_e( 'Enable/disable notifications per order status (Processing, Shipped, Completed, etc.)', 'broodle-engage-connector' ); ?></li>
                    <li><?php esc_html_e( 'Select which approved template to send for each status', 'broodle-engage-connector' ); ?></li>
                    <li><?php esc_html_e( 'Map template variables ({{1}}, {{2}}...) to order data fields', 'broodle-engage-connector' ); ?></li>
                    <li><?php esc_html_e( 'Optionally upload a header image or use the product featured image', 'broodle-engage-connector' ); ?></li>
                    <li><?php esc_html_e( 'Add custom notification types for any WooCommerce status', 'broodle-engage-connector' ); ?></li>
                </ul>
                <div class="help-tip">
                    <strong><?php esc_html_e( 'Tip:', 'broodle-engage-connector' ); ?></strong>
                    <?php esc_html_e( 'Click "Refresh" on the Templates page to fetch the latest approved templates from your Broodle account.', 'broodle-engage-connector' ); ?>
                </div>
            </div>
        </div>

        <!-- Template Variables Reference -->
        <h2 style="font-size: 20px; font-weight: 600; margin: 30px 0 16px 0; color: #1d2327;">
            <span class="dashicons dashicons-editor-code" style="color: #0E5ECE; vertical-align: middle;"></span>
            <?php esc_html_e( 'Available Template Variables', 'broodle-engage-connector' ); ?>
        </h2>
        <div class="help-grid">
            <div class="help-card help-card-full">
                <h3 class="help-card-title">
                    <span class="dashicons dashicons-list-view"></span>
                    <?php esc_html_e( 'Variable Mapping Reference', 'broodle-engage-connector' ); ?>
                </h3>
                <p><?php esc_html_e( 'When configuring a template, map each {{N}} placeholder to one of these WooCommerce data fields. You can assign any variable to any placeholder in any order.', 'broodle-engage-connector' ); ?></p>
                <table class="variable-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Variable', 'broodle-engage-connector' ); ?></th>
                            <th><?php esc_html_e( 'Description', 'broodle-engage-connector' ); ?></th>
                            <th><?php esc_html_e( 'Example Value', 'broodle-engage-connector' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td><code>customer_name</code></td><td><?php esc_html_e( 'Full name (first + last)', 'broodle-engage-connector' ); ?></td><td>John Doe</td></tr>
                        <tr><td><code>customer_first_name</code></td><td><?php esc_html_e( 'First name only', 'broodle-engage-connector' ); ?></td><td>John</td></tr>
                        <tr><td><code>customer_last_name</code></td><td><?php esc_html_e( 'Last name only', 'broodle-engage-connector' ); ?></td><td>Doe</td></tr>
                        <tr><td><code>customer_email</code></td><td><?php esc_html_e( 'Customer email address', 'broodle-engage-connector' ); ?></td><td>john@example.com</td></tr>
                        <tr><td><code>order_id</code></td><td><?php esc_html_e( 'WordPress order post ID', 'broodle-engage-connector' ); ?></td><td>1234</td></tr>
                        <tr><td><code>order_number</code></td><td><?php esc_html_e( 'WooCommerce order number', 'broodle-engage-connector' ); ?></td><td>#1234</td></tr>
                        <tr><td><code>order_total</code></td><td><?php esc_html_e( 'Formatted total with currency', 'broodle-engage-connector' ); ?></td><td>₹1,299.00</td></tr>
                        <tr><td><code>order_total_raw</code></td><td><?php esc_html_e( 'Numeric total without currency', 'broodle-engage-connector' ); ?></td><td>1299.00</td></tr>
                        <tr><td><code>order_date</code></td><td><?php esc_html_e( 'Order creation date', 'broodle-engage-connector' ); ?></td><td>February 6, 2026</td></tr>
                        <tr><td><code>order_status</code></td><td><?php esc_html_e( 'Current order status label', 'broodle-engage-connector' ); ?></td><td>Processing</td></tr>
                        <tr><td><code>product_names</code></td><td><?php esc_html_e( 'Comma-separated product names', 'broodle-engage-connector' ); ?></td><td>Blue T-Shirt, Jeans</td></tr>
                        <tr><td><code>product_count</code></td><td><?php esc_html_e( 'Number of items in order', 'broodle-engage-connector' ); ?></td><td>2</td></tr>
                        <tr><td><code>shipping_address</code></td><td><?php esc_html_e( 'Full shipping address', 'broodle-engage-connector' ); ?></td><td>123 Main St, Mumbai</td></tr>
                        <tr><td><code>billing_address</code></td><td><?php esc_html_e( 'Full billing address', 'broodle-engage-connector' ); ?></td><td>123 Main St, Mumbai</td></tr>
                        <tr><td><code>payment_method</code></td><td><?php esc_html_e( 'Payment method title', 'broodle-engage-connector' ); ?></td><td>Cash on delivery</td></tr>
                        <tr><td><code>shipping_method</code></td><td><?php esc_html_e( 'Shipping method title', 'broodle-engage-connector' ); ?></td><td>Flat rate</td></tr>
                        <tr><td><code>tracking_url</code></td><td><?php esc_html_e( 'Shipment tracking URL', 'broodle-engage-connector' ); ?></td><td>https://track.example.com/...</td></tr>
                        <tr><td><code>tracking_number</code></td><td><?php esc_html_e( 'Shipment tracking number', 'broodle-engage-connector' ); ?></td><td>AWB123456789</td></tr>
                        <tr><td><code>coupon_code</code></td><td><?php esc_html_e( 'Applied coupon codes', 'broodle-engage-connector' ); ?></td><td>SAVE10</td></tr>
                        <tr><td><code>product_url</code></td><td><?php esc_html_e( 'URL of the first product in order', 'broodle-engage-connector' ); ?></td><td>https://shop.example.com/blue-tshirt</td></tr>
                        <tr><td><code>cart_url</code></td><td><?php esc_html_e( 'WooCommerce cart page URL', 'broodle-engage-connector' ); ?></td><td>https://shop.example.com/cart</td></tr>
                        <tr><td><code>shop_url</code></td><td><?php esc_html_e( 'WooCommerce shop page URL', 'broodle-engage-connector' ); ?></td><td>https://shop.example.com/shop</td></tr>
                        <tr><td><code>my_account_url</code></td><td><?php esc_html_e( 'Customer account page URL', 'broodle-engage-connector' ); ?></td><td>https://shop.example.com/my-account</td></tr>
                        <tr><td><code>site_name</code></td><td><?php esc_html_e( 'Your WordPress site name', 'broodle-engage-connector' ); ?></td><td>My Store</td></tr>
                        <tr><td><code>custom_text</code></td><td><?php esc_html_e( 'Any static text you type in', 'broodle-engage-connector' ); ?></td><td>Thank you for shopping!</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Template Features -->
        <h2 style="font-size: 20px; font-weight: 600; margin: 30px 0 16px 0; color: #1d2327;">
            <span class="dashicons dashicons-admin-appearance" style="color: #0E5ECE; vertical-align: middle;"></span>
            <?php esc_html_e( 'Template Features', 'broodle-engage-connector' ); ?>
        </h2>
        <div class="help-grid">
            <div class="help-card">
                <h3 class="help-card-title">
                    <span class="dashicons dashicons-format-image"></span>
                    <?php esc_html_e( 'Image Headers', 'broodle-engage-connector' ); ?>
                </h3>
                <p><?php esc_html_e( 'Templates with IMAGE headers support two image options:', 'broodle-engage-connector' ); ?></p>
                <ul>
                    <li><strong><?php esc_html_e( 'Upload Image:', 'broodle-engage-connector' ); ?></strong> <?php esc_html_e( 'Select a static image from your WordPress Media Library to use for all notifications of that type.', 'broodle-engage-connector' ); ?></li>
                    <li><strong><?php esc_html_e( 'Use Product Image:', 'broodle-engage-connector' ); ?></strong> <?php esc_html_e( 'Tick the checkbox to automatically use the featured image of the first product in the order. Falls back to the uploaded image if no product image exists.', 'broodle-engage-connector' ); ?></li>
                </ul>
            </div>
            <div class="help-card">
                <h3 class="help-card-title">
                    <span class="dashicons dashicons-admin-links"></span>
                    <?php esc_html_e( 'Button Variables', 'broodle-engage-connector' ); ?>
                </h3>
                <p><?php esc_html_e( 'Templates with URL buttons containing {{N}} placeholders are automatically detected. The plugin maps button variables using order data:', 'broodle-engage-connector' ); ?></p>
                <ul>
                    <li><?php esc_html_e( 'Dynamic URL buttons (e.g., "Track Order" → {{1}}) are mapped to your chosen variable', 'broodle-engage-connector' ); ?></li>
                    <li><?php esc_html_e( 'Button variables are sent separately from body variables in the correct API format', 'broodle-engage-connector' ); ?></li>
                    <li><?php esc_html_e( 'Common use: Map product_url or tracking_url to a button placeholder', 'broodle-engage-connector' ); ?></li>
                </ul>
            </div>
            <div class="help-card">
                <h3 class="help-card-title">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <?php esc_html_e( 'Custom Notification Types', 'broodle-engage-connector' ); ?>
                </h3>
                <p><?php esc_html_e( 'Beyond the default order statuses, you can add custom notifications:', 'broodle-engage-connector' ); ?></p>
                <ul>
                    <li><?php esc_html_e( 'Click "Add Notification Type" on the Templates page', 'broodle-engage-connector' ); ?></li>
                    <li><?php esc_html_e( 'Map to any WooCommerce order status (including custom ones from plugins)', 'broodle-engage-connector' ); ?></li>
                    <li><?php esc_html_e( 'Each custom notification gets its own template, variable mapping, and image settings', 'broodle-engage-connector' ); ?></li>
                    <li><?php esc_html_e( 'Remove custom notifications any time without affecting default ones', 'broodle-engage-connector' ); ?></li>
                </ul>
            </div>
            <div class="help-card">
                <h3 class="help-card-title">
                    <span class="dashicons dashicons-chart-bar"></span>
                    <?php esc_html_e( 'Notification Logs', 'broodle-engage-connector' ); ?>
                </h3>
                <p><?php esc_html_e( 'Track every notification sent from the Logs tab:', 'broodle-engage-connector' ); ?></p>
                <ul>
                    <li><?php esc_html_e( 'View success and error status for each message', 'broodle-engage-connector' ); ?></li>
                    <li><?php esc_html_e( 'See timestamp, phone number, order ID, and template used', 'broodle-engage-connector' ); ?></li>
                    <li><?php esc_html_e( 'Filter by status (success/error) to quickly find issues', 'broodle-engage-connector' ); ?></li>
                    <li><?php esc_html_e( 'Logs are automatically cleaned up based on your retention setting', 'broodle-engage-connector' ); ?></li>
                </ul>
            </div>
        </div>

        <!-- Troubleshooting -->
        <h2 style="font-size: 20px; font-weight: 600; margin: 30px 0 16px 0; color: #1d2327;">
            <span class="dashicons dashicons-sos" style="color: #0E5ECE; vertical-align: middle;"></span>
            <?php esc_html_e( 'Troubleshooting', 'broodle-engage-connector' ); ?>
        </h2>
        <div class="help-grid">
            <div class="help-card help-card-full">
                <h3 class="help-card-title">
                    <span class="dashicons dashicons-warning"></span>
                    <?php esc_html_e( 'Common Issues & Solutions', 'broodle-engage-connector' ); ?>
                </h3>
                <table class="variable-table">
                    <thead>
                        <tr>
                            <th style="width: 35%;"><?php esc_html_e( 'Issue', 'broodle-engage-connector' ); ?></th>
                            <th><?php esc_html_e( 'Solution', 'broodle-engage-connector' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong><?php esc_html_e( 'API connection test fails', 'broodle-engage-connector' ); ?></strong></td>
                            <td><?php esc_html_e( 'Verify your API Access Token is correct. Go to your Broodle dashboard → Settings → Account → copy the token again.', 'broodle-engage-connector' ); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e( 'No templates appear', 'broodle-engage-connector' ); ?></strong></td>
                            <td><?php esc_html_e( 'Only APPROVED templates are shown. Check your template status in the Broodle dashboard. New templates take 24-48 hours for WhatsApp approval.', 'broodle-engage-connector' ); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e( 'Messages logged as success but not received', 'broodle-engage-connector' ); ?></strong></td>
                            <td><?php esc_html_e( 'Check that the recipient has an active WhatsApp account on the number. Also verify the phone number format includes country code (e.g., +91).', 'broodle-engage-connector' ); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e( 'Error #132012 (parameter mismatch)', 'broodle-engage-connector' ); ?></strong></td>
                            <td><?php esc_html_e( 'The number of mapped variables must match the template placeholders exactly. Count the {{N}} placeholders in your template body and buttons, and map each one.', 'broodle-engage-connector' ); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e( 'Image not showing in WhatsApp', 'broodle-engage-connector' ); ?></strong></td>
                            <td><?php esc_html_e( 'Ensure the image URL is publicly accessible (not behind authentication). Use HTTPS URLs. If using "Use product image", verify the product has a featured image set.', 'broodle-engage-connector' ); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e( 'Notifications not triggering', 'broodle-engage-connector' ); ?></strong></td>
                            <td><?php esc_html_e( 'Make sure the notification toggle is ON for that order status. Also verify the customer order has a phone number in the selected phone field.', 'broodle-engage-connector' ); ?></td>
                        </tr>
                    </tbody>
                </table>
                <div class="help-tip" style="margin-top: 20px;">
                    <strong><?php esc_html_e( 'Debug Logs:', 'broodle-engage-connector' ); ?></strong>
                    <?php esc_html_e( 'Enable WP_DEBUG_LOG in wp-config.php to see detailed API request/response logs at wp-content/debug.log. The plugin logs all API calls including the full request payload and response.', 'broodle-engage-connector' ); ?>
                </div>
            </div>
        </div>

        <!-- Settings Reference -->
        <h2 style="font-size: 20px; font-weight: 600; margin: 30px 0 16px 0; color: #1d2327;">
            <span class="dashicons dashicons-admin-settings" style="color: #0E5ECE; vertical-align: middle;"></span>
            <?php esc_html_e( 'Settings Reference', 'broodle-engage-connector' ); ?>
        </h2>
        <div class="help-grid">
            <div class="help-card">
                <h3 class="help-card-title">
                    <span class="dashicons dashicons-admin-network"></span>
                    <?php esc_html_e( 'API Configuration', 'broodle-engage-connector' ); ?>
                </h3>
                <ul>
                    <li><strong><?php esc_html_e( 'API Access Token:', 'broodle-engage-connector' ); ?></strong> <?php esc_html_e( 'Authentication token for the Broodle Engage API. Found in Settings → Account.', 'broodle-engage-connector' ); ?></li>
                    <li><strong><?php esc_html_e( 'Account ID:', 'broodle-engage-connector' ); ?></strong> <?php esc_html_e( 'Your unique Broodle account number. Visible in your dashboard URL.', 'broodle-engage-connector' ); ?></li>
                    <li><strong><?php esc_html_e( 'WhatsApp Inbox ID:', 'broodle-engage-connector' ); ?></strong> <?php esc_html_e( 'The ID of the WhatsApp inbox to send from. Found in Settings → Inboxes.', 'broodle-engage-connector' ); ?></li>
                    <li><strong><?php esc_html_e( 'Template Language:', 'broodle-engage-connector' ); ?></strong> <?php esc_html_e( 'Default language code used when sending templates (e.g., en_US, hi, es).', 'broodle-engage-connector' ); ?></li>
                    <li><strong><?php esc_html_e( 'Template Category:', 'broodle-engage-connector' ); ?></strong> <?php esc_html_e( 'UTILITY for order notifications, MARKETING for promotions.', 'broodle-engage-connector' ); ?></li>
                </ul>
            </div>
            <div class="help-card">
                <h3 class="help-card-title">
                    <span class="dashicons dashicons-admin-generic"></span>
                    <?php esc_html_e( 'General Settings', 'broodle-engage-connector' ); ?>
                </h3>
                <ul>
                    <li><strong><?php esc_html_e( 'Phone Number Field:', 'broodle-engage-connector' ); ?></strong> <?php esc_html_e( 'Which WooCommerce checkout field to read the customer phone from (billing phone, shipping phone, or custom field).', 'broodle-engage-connector' ); ?></li>
                    <li><strong><?php esc_html_e( 'Default Country Code:', 'broodle-engage-connector' ); ?></strong> <?php esc_html_e( 'Auto-prepended when a phone number lacks a country prefix (e.g., +91 for India).', 'broodle-engage-connector' ); ?></li>
                    <li><strong><?php esc_html_e( 'Retry Attempts:', 'broodle-engage-connector' ); ?></strong> <?php esc_html_e( 'How many times to retry a failed notification (0-10).', 'broodle-engage-connector' ); ?></li>
                    <li><strong><?php esc_html_e( 'Retry Delay:', 'broodle-engage-connector' ); ?></strong> <?php esc_html_e( 'Wait time in seconds between retry attempts (60-3600).', 'broodle-engage-connector' ); ?></li>
                    <li><strong><?php esc_html_e( 'Log Retention:', 'broodle-engage-connector' ); ?></strong> <?php esc_html_e( 'Days to keep notification logs before automatic cleanup (1-365).', 'broodle-engage-connector' ); ?></li>
                </ul>
            </div>
        </div>

        <!-- Support -->
        <h2 style="font-size: 20px; font-weight: 600; margin: 30px 0 16px 0; color: #1d2327;">
            <span class="dashicons dashicons-heart" style="color: #0E5ECE; vertical-align: middle;"></span>
            <?php esc_html_e( 'Support & Resources', 'broodle-engage-connector' ); ?>
        </h2>
        <div class="help-grid">
            <div class="help-card help-card-full">
                <div style="display: flex; gap: 30px; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 200px;">
                        <h4 style="margin: 0 0 10px 0; color: #1d2327;"><?php esc_html_e( 'Broodle Engage Dashboard', 'broodle-engage-connector' ); ?></h4>
                        <p><?php esc_html_e( 'Manage templates, view conversations, and configure your WhatsApp inbox.', 'broodle-engage-connector' ); ?></p>
                        <a href="https://engage.broodle.one" target="_blank" style="display: inline-flex; align-items: center; gap: 4px;">
                            engage.broodle.one <span class="dashicons dashicons-external" style="font-size: 16px; width: 16px; height: 16px;"></span>
                        </a>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <h4 style="margin: 0 0 10px 0; color: #1d2327;"><?php esc_html_e( 'Broodle Website', 'broodle-engage-connector' ); ?></h4>
                        <p><?php esc_html_e( 'Documentation, FAQs, and contact support.', 'broodle-engage-connector' ); ?></p>
                        <a href="https://broodle.host" target="_blank" style="display: inline-flex; align-items: center; gap: 4px;">
                            broodle.host <span class="dashicons dashicons-external" style="font-size: 16px; width: 16px; height: 16px;"></span>
                        </a>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <h4 style="margin: 0 0 10px 0; color: #1d2327;"><?php esc_html_e( 'Plugin Version', 'broodle-engage-connector' ); ?></h4>
                        <p style="margin-bottom: 4px;"><?php echo esc_html( BROODLE_ENGAGE_VERSION ); ?></p>
                        <p style="color: #646970; font-size: 13px; margin: 0;"><?php esc_html_e( 'Broodle Engage Connector for WooCommerce', 'broodle-engage-connector' ); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Sanitize settings
     *
     * @param array $input Input settings.
     * @return array
     */
    public function sanitize_settings( $input ) {
        // This callback runs on EVERY update_option() call for this option
        // (registered via register_setting). Only apply Settings-tab sanitization
        // when the Settings form nonce is present. Otherwise, pass through
        // unchanged — the AJAX handler has its own sanitization.
        if ( ! isset( $_POST['broodle_engage_settings_nonce'] ) || ! wp_verify_nonce( $_POST['broodle_engage_settings_nonce'], 'broodle_engage_settings_nonce' ) ) {
            return $input;
        }

        $current_settings = Broodle_Engage_Settings::get_settings();
        $sanitized = wp_parse_args( $input, $current_settings );

        // Use the settings class sanitization
        return Broodle_Engage_Settings::sanitize_settings( $sanitized );
    }

    /**
     * AJAX handler for testing API connection
     */
    public function ajax_test_api() {
        check_ajax_referer( 'broodle_engage_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'broodle-engage-connector' ) );
        }

        $api_key = sanitize_text_field( $_POST['api_key'] ?? '' );

        // Test the API connection using the API class
        $api = new Broodle_Engage_API( $api_key );
        
        // Perform comprehensive access test
        $access_test = $api->test_full_access();
        
        // Build response message
        $message = implode( "\n", $access_test['messages'] );
        
        if ( $access_test['profile'] && $access_test['account'] && $access_test['inbox'] ) {
            wp_send_json_success( array(
                'message' => __( 'All connections verified!', 'broodle-engage-connector' ) . "\n" . $message,
                'diagnostics' => $access_test,
            ) );
        } elseif ( $access_test['profile'] ) {
            // Profile works but account/inbox doesn't
            wp_send_json_error( array(
                'message' => __( 'API token is valid but account/inbox access failed:', 'broodle-engage-connector' ) . "\n" . $message,
                'diagnostics' => $access_test,
            ) );
        } else {
            wp_send_json_error( array(
                'message' => $message,
                'diagnostics' => $access_test,
            ) );
        }
    }

    /**
     * AJAX handler for sending test message
     */
    public function ajax_send_test_message() {
        check_ajax_referer( 'broodle_engage_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'broodle-engage-connector' ) );
        }

        $status = sanitize_text_field( $_POST['status'] ?? '' );
        $phone = sanitize_text_field( $_POST['phone'] ?? '' );

        if ( empty( $phone ) ) {
            wp_send_json_error( __( 'Phone number is required for test message.', 'broodle-engage-connector' ) );
        }

        // Send test message logic would go here
        wp_send_json_success( __( 'Test message sent successfully!', 'broodle-engage-connector' ) );
    }

    /**
     * AJAX handler for quick test with predefined values
     */
    public function ajax_quick_test() {
        check_ajax_referer( 'broodle_engage_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'broodle-engage-connector' ) );
        }

        // Get phone number from request
        $phone_number = sanitize_text_field( $_POST['phone'] ?? '' );
        
        if ( empty( $phone_number ) ) {
            wp_send_json_error( array(
                'message' => __( 'Please enter a phone number with country code (e.g., +919876543210).', 'broodle-engage-connector' ),
                'error_code' => 'missing_phone',
            ) );
            return;
        }

        $api = new Broodle_Engage_API();

        // First, test full access to diagnose any issues
        $access_test = $api->test_full_access();
        
        if ( ! $access_test['account'] ) {
            wp_send_json_error( array(
                'message' => implode( "\n", $access_test['messages'] ),
                'error_code' => 'account_access_failed',
                'diagnostics' => $access_test,
            ) );
            return;
        }

        if ( ! $access_test['inbox'] ) {
            wp_send_json_error( array(
                'message' => implode( "\n", $access_test['messages'] ),
                'error_code' => 'inbox_access_failed',
                'diagnostics' => $access_test,
            ) );
            return;
        }

        // Test with hello_world template (no variables required)
        $response = $api->send_template_message(
            $phone_number,
            'hello_world',
            array() // hello_world template typically has no variables
        );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( array(
                'message' => $response->get_error_message(),
                'error_code' => $response->get_error_code(),
                'diagnostics' => $access_test,
            ) );
        }

        // Get detailed response information
        $message = sprintf( __( 'Test message sent successfully to %s!', 'broodle-engage-connector' ), $phone_number );
        if ( isset( $response['status_message'] ) && ! empty( $response['status_message'] ) ) {
            $message .= ' ' . $response['status_message'];
        }

        wp_send_json_success( array(
            'message' => $message,
            'response_data' => $response,
            'diagnostics' => $access_test,
        ) );
    }

    /**
     * Test failed/cancelled notification
     */
    public function ajax_test_failed_notification() {
        // Check nonce and permissions
        if ( ! check_ajax_referer( 'broodle_engage_admin_nonce', 'nonce', false ) || ! $this->user_can_access() ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'broodle-engage-connector' ) ) );
        }

        $notification_type = isset( $_POST['notification_type'] ) ? sanitize_text_field( wp_unslash( $_POST['notification_type'] ) ) : 'order_failed';
        $order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;

        if ( ! $order_id ) {
            wp_send_json_error( array( 'message' => __( 'Please provide a valid order ID.', 'broodle-engage-connector' ) ) );
        }

        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            wp_send_json_error( array( 'message' => __( 'Order not found.', 'broodle-engage-connector' ) ) );
        }

        // Test the notification
        $notifications = new Broodle_Engage_Notifications();

        try {
            $notifications->send_notification_safe( $order_id, $notification_type, $order );

            wp_send_json_success( array(
                'message' => "Test {$notification_type} notification sent for order #{$order_id}. Check logs for details.",
            ) );
        } catch ( Exception $e ) {
            wp_send_json_error( array(
                'message' => 'Error: ' . $e->getMessage(),
            ) );
        }
    }

    /**
     * Show diagnostic information about available order statuses
     */
    public function show_status_diagnostic() {
        // Only show on our plugin pages and only if user can manage options
        if ( ! $this->user_can_access() ) {
            return;
        }

        $screen = get_current_screen();
        if ( ! $screen || strpos( $screen->id, 'broodle-engage-connector' ) === false ) {
            return;
        }

        // Only show if diagnostic parameter is present
        if ( ! isset( $_GET['wa_diagnostic'] ) ) {
            return;
        }

        if ( function_exists( 'wc_get_order_statuses' ) ) {
            $statuses = wc_get_order_statuses();

            echo '<div class="notice notice-success is-dismissible">';
            echo '<h3>✅ Order Status Configuration Help</h3>';
            echo '<p><strong>The dropdown now shows both the display name and the actual status key in brackets.</strong></p>';
            echo '<p><strong>Example:</strong> "Completed (completed)" means you select this option to trigger notifications when orders are marked as Completed.</p>';

            echo '<div style="background: #f9f9f9; padding: 15px; border-left: 4px solid #00a32a; margin: 10px 0;">';
            echo '<h4>📋 Available Order Statuses:</h4>';
            echo '<ul style="margin-left: 20px; columns: 2; column-gap: 30px;">';

            foreach ( $statuses as $key => $label ) {
                $clean_key = str_replace( 'wc-', '', $key );
                $icon = '';
                if ( strpos( $clean_key, 'pp-' ) === 0 ) {
                    $icon = '📦 '; // ParcelPanel status
                } elseif ( in_array( $clean_key, array( 'shipped', 'delivered' ) ) ) {
                    $icon = '🚚 '; // Shipping related
                } elseif ( in_array( $clean_key, array( 'completed', 'processing' ) ) ) {
                    $icon = '✅ '; // Standard statuses
                } elseif ( in_array( $clean_key, array( 'cancelled', 'failed' ) ) ) {
                    $icon = '❌ '; // Problem statuses
                }

                echo '<li>' . $icon . '<strong>' . esc_html( $label ) . '</strong> <code>(' . esc_html( $clean_key ) . ')</code></li>';
            }

            echo '</ul>';
            echo '</div>';

            echo '<p><strong>💡 Quick Tips:</strong></p>';
            echo '<ul>';
            echo '<li>For <strong>Order Shipped</strong>: Look for statuses like "Shipped", "Dispatched", or ParcelPanel statuses starting with <code>pp-</code></li>';
            echo '<li>For <strong>Order Delivered</strong>: Look for "Delivered" or "Completed" statuses</li>';
            echo '<li>Test by changing an order to the exact status you selected in the dropdown</li>';
            echo '</ul>';

            echo '<p><a href="' . remove_query_arg( 'wa_diagnostic' ) . '" class="button button-primary">Got it, hide this help</a></p>';
            echo '</div>';
        }
    }

    /**
     * Add dashboard widget
     */
    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'broodle_engage_dashboard_widget',
            __( 'Broodle WhatsApp Notifications', 'broodle-engage-connector' ),
            array( $this, 'render_dashboard_widget' ),
            null,
            null,
            'normal',
            'high'
        );
    }

    /**
     * Render dashboard widget
     */
    public function render_dashboard_widget() {
        // Get message statistics
        $stats = $this->get_message_statistics();

        ?>
        <div class="broodle-dashboard-widget">
            <style>
            .broodle-dashboard-widget { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
            .broodle-widget-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 18px;
                padding: 16px 18px;
                background: linear-gradient(135deg, #0E5ECE 0%, #0a4aa3 100%);
                border-radius: 12px;
                position: relative;
                overflow: hidden;
                box-shadow: 0 4px 16px rgba(14, 94, 206, 0.25);
            }
            .broodle-widget-header::before {
                content: '';
                position: absolute;
                top: -50%;
                right: -20%;
                width: 60%;
                height: 200%;
                background: rgba(255,255,255,0.06);
                transform: rotate(15deg);
                pointer-events: none;
            }
            .broodle-widget-title {
                font-size: 14px;
                font-weight: 600;
                color: white;
                margin: 0;
                display: flex;
                align-items: center;
                gap: 8px;
                position: relative;
                z-index: 1;
            }
            .broodle-widget-title .dashicons {
                font-size: 20px;
                width: 20px;
                height: 20px;
                color: rgba(255,255,255,0.9);
            }
            .broodle-widget-status {
                font-size: 12px;
                color: rgba(255,255,255,0.85);
                display: flex;
                align-items: center;
                position: relative;
                z-index: 1;
                background: rgba(255,255,255,0.15);
                padding: 4px 10px;
                border-radius: 20px;
                backdrop-filter: blur(4px);
            }
            .broodle-status-indicator { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 6px; }
            .broodle-status-success { background: #4ade80; box-shadow: 0 0 6px rgba(74,222,128,0.5); }
            .broodle-status-warning { background: #facc15; box-shadow: 0 0 6px rgba(250,204,21,0.5); }
            .broodle-status-failed { background: #f87171; box-shadow: 0 0 6px rgba(248,113,113,0.5); }
            .broodle-stats-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 12px;
                margin: 0 0 18px 0;
            }
            .broodle-stat-card {
                background: rgba(14, 94, 206, 0.06);
                border: 1px solid rgba(14, 94, 206, 0.12);
                color: #1d2327;
                padding: 16px 12px;
                border-radius: 10px;
                text-align: center;
                transition: all 0.2s ease;
            }
            .broodle-stat-card:hover {
                background: rgba(14, 94, 206, 0.1);
                border-color: rgba(14, 94, 206, 0.2);
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(14, 94, 206, 0.1);
            }
            .broodle-stat-number { font-size: 28px; font-weight: 700; margin-bottom: 4px; color: #0E5ECE; }
            .broodle-stat-label { font-size: 11px; color: #646970; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
            .broodle-actions { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
            .broodle-action-btn {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
                padding: 12px 15px;
                text-decoration: none;
                border-radius: 10px;
                font-weight: 500;
                font-size: 13px;
                transition: all 0.2s ease;
            }
            .broodle-action-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.12); text-decoration: none; }
            .broodle-action-btn .dashicons { font-size: 18px; width: 18px; height: 18px; }
            .broodle-btn-primary {
                background: rgba(14, 94, 206, 0.08);
                color: #0E5ECE;
                border: 1px solid rgba(14, 94, 206, 0.15);
            }
            .broodle-btn-primary:hover { background: rgba(14, 94, 206, 0.14); color: #0E5ECE; }
            .broodle-btn-secondary {
                background: rgba(100, 105, 112, 0.06);
                color: #1d2327;
                border: 1px solid rgba(100, 105, 112, 0.12);
            }
            .broodle-btn-secondary:hover { background: rgba(100, 105, 112, 0.1); color: #1d2327; }
            .broodle-btn-whatsapp {
                background: rgba(37, 211, 102, 0.08);
                color: #128C7E;
                border: 1px solid rgba(37, 211, 102, 0.15);
                grid-column: 1 / -1;
            }
            .broodle-btn-whatsapp:hover { background: rgba(37, 211, 102, 0.14); color: #128C7E; }
            .broodle-error-notice {
                margin-top: 15px;
                padding: 12px 16px;
                background: rgba(220, 50, 50, 0.06);
                border: 1px solid rgba(220, 50, 50, 0.12);
                border-radius: 10px;
                font-size: 13px;
                color: #1d2327;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .broodle-error-notice .dashicons { color: #dc3232; font-size: 18px; width: 18px; height: 18px; flex-shrink: 0; }
            .broodle-error-notice a { color: #0E5ECE; text-decoration: none; font-weight: 500; }
            .broodle-error-notice a:hover { text-decoration: underline; }
            @media (max-width: 782px) {
                .broodle-stats-grid { grid-template-columns: 1fr; }
                .broodle-actions { grid-template-columns: 1fr; }
                .broodle-btn-whatsapp { grid-column: 1; }
            }
            </style>

            <div class="broodle-widget-header">
                <h3 class="broodle-widget-title">
                    <span class="dashicons dashicons-whatsapp"></span>
                    <?php esc_html_e( 'Broodle Engage', 'broodle-engage-connector' ); ?>
                </h3>
                <div class="broodle-widget-status">
                    <span class="broodle-status-indicator broodle-status-<?php echo esc_attr( $stats['status'] ); ?>"></span>
                    <?php echo esc_html( $stats['status_text'] ); ?>
                </div>
            </div>

            <div class="broodle-stats-grid">
                <div class="broodle-stat-card">
                    <div class="broodle-stat-number"><?php echo esc_html( number_format( $stats['today'] ) ); ?></div>
                    <div class="broodle-stat-label"><?php esc_html_e( 'Today', 'broodle-engage-connector' ); ?></div>
                </div>
                <div class="broodle-stat-card">
                    <div class="broodle-stat-number"><?php echo esc_html( number_format( $stats['last_7_days'] ) ); ?></div>
                    <div class="broodle-stat-label"><?php esc_html_e( '7 Days', 'broodle-engage-connector' ); ?></div>
                </div>
                <div class="broodle-stat-card">
                    <div class="broodle-stat-number"><?php echo esc_html( number_format( $stats['last_30_days'] ) ); ?></div>
                    <div class="broodle-stat-label"><?php esc_html_e( '30 Days', 'broodle-engage-connector' ); ?></div>
                </div>
            </div>

            <div class="broodle-actions">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=broodle-engage-connector&tab=logs' ) ); ?>"
                   class="broodle-action-btn broodle-btn-primary">
                    <span class="dashicons dashicons-chart-bar"></span>
                    <?php esc_html_e( 'View Logs', 'broodle-engage-connector' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=broodle-engage-connector&tab=templates' ) ); ?>"
                   class="broodle-action-btn broodle-btn-secondary">
                    <span class="dashicons dashicons-admin-generic"></span>
                    <?php esc_html_e( 'Settings', 'broodle-engage-connector' ); ?>
                </a>
                <a href="https://engage.broodle.one"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="broodle-action-btn broodle-btn-whatsapp">
                    <span class="dashicons dashicons-format-chat"></span>
                    <?php esc_html_e( 'Open WhatsApp Inbox', 'broodle-engage-connector' ); ?>
                </a>
            </div>

            <?php if ( $stats['recent_errors'] > 0 ) : ?>
                <div class="broodle-error-notice">
                    <span class="dashicons dashicons-warning"></span>
                    <span>
                        <?php echo esc_html( $stats['recent_errors'] ); ?> <?php esc_html_e( 'failed messages in the last 24 hours.', 'broodle-engage-connector' ); ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=broodle-engage-connector&tab=logs&status=error' ) ); ?>"><?php esc_html_e( 'View errors', 'broodle-engage-connector' ); ?></a>
                    </span>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Get message statistics
     *
     * @return array
     */
    private function get_message_statistics() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'broodle_engage_logs';

        // Check if table exists
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) !== $table_name ) {
            return array(
                'today' => 0,
                'last_7_days' => 0,
                'last_30_days' => 0,
                'recent_errors' => 0,
                'status' => 'warning',
                'status_text' => 'Not Configured'
            );
        }

        // Get current date/time
        $now = current_time( 'mysql' );
        $today = current_time( 'Y-m-d' );
        $seven_days_ago = date( 'Y-m-d H:i:s', strtotime( '-7 days', current_time( 'timestamp' ) ) );
        $thirty_days_ago = date( 'Y-m-d H:i:s', strtotime( '-30 days', current_time( 'timestamp' ) ) );
        $twenty_four_hours_ago = date( 'Y-m-d H:i:s', strtotime( '-24 hours', current_time( 'timestamp' ) ) );

        // Get statistics
        $today_count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} WHERE DATE(created_at) = %s AND status = 'success'",
            $today
        ) );

        $seven_days_count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} WHERE created_at >= %s AND status = 'success'",
            $seven_days_ago
        ) );

        $thirty_days_count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} WHERE created_at >= %s AND status = 'success'",
            $thirty_days_ago
        ) );

        $recent_errors = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} WHERE created_at >= %s AND status = 'error'",
            $twenty_four_hours_ago
        ) );

        // Determine status and status text
        $status = 'success';
        $status_text = 'Connected';

        if ( $recent_errors > 5 ) {
            $status = 'failed';
            $status_text = 'Connection Issues';
        } elseif ( $recent_errors > 0 ) {
            $status = 'warning';
            $status_text = 'Partial Issues';
        } elseif ( $today_count == 0 && $seven_days_count == 0 ) {
            $status = 'warning';
            $status_text = 'No Recent Activity';
        }

        return array(
            'today' => intval( $today_count ),
            'last_7_days' => intval( $seven_days_count ),
            'last_30_days' => intval( $thirty_days_count ),
            'recent_errors' => intval( $recent_errors ),
            'status' => $status,
            'status_text' => $status_text
        );
    }

    /**
     * AJAX handler to fetch templates from Chatwoot
     */
    public function ajax_fetch_templates() {
        check_ajax_referer( 'broodle_engage_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'broodle-engage-connector' ) ) );
        }

        $api = new Broodle_Engage_API();
        $templates = $api->get_whatsapp_templates();

        if ( is_wp_error( $templates ) ) {
            wp_send_json_error( array( 'message' => $templates->get_error_message() ) );
        }

        // Filter to only approved templates
        $approved = array_filter( $templates, function( $t ) {
            return $t['status'] === 'APPROVED';
        });

        wp_send_json_success( array(
            'templates' => array_values( $approved ),
            'count' => count( $approved ),
        ) );
    }

    /**
     * AJAX handler to save template configuration
     */
    public function ajax_save_template_config() {
        check_ajax_referer( 'broodle_engage_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'broodle-engage-connector' ) ) );
        }

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Data is sanitized below per field.
        $config = isset( $_POST['config'] ) ? wp_unslash( $_POST['config'] ) : array();
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Data is sanitized below per field.
        $custom_statuses = isset( $_POST['custom_statuses'] ) ? wp_unslash( $_POST['custom_statuses'] ) : array();
        
        if ( empty( $config ) ) {
            wp_send_json_error( array( 'message' => __( 'No configuration data provided.', 'broodle-engage-connector' ) ) );
        }

        $settings = Broodle_Engage_Settings::get_settings();
        
        // Update template configurations
        $settings['template_config'] = array();
        
        foreach ( $config as $status => $template_data ) {
            $status = sanitize_key( $status );
            $enabled_raw = $template_data['enabled'] ?? false;
            $settings['template_config'][ $status ] = array(
                'enabled'       => filter_var( $enabled_raw, FILTER_VALIDATE_BOOLEAN ),
                'template_name' => sanitize_text_field( $template_data['template_name'] ?? '' ),
                'template_lang' => sanitize_text_field( $template_data['template_lang'] ?? 'en' ),
                'template_body' => sanitize_textarea_field( $template_data['template_body'] ?? '' ),
                'variable_map'  => array_map( 'sanitize_text_field', $template_data['variable_map'] ?? array() ),
                'custom_text'   => array_map( 'sanitize_text_field', $template_data['custom_text'] ?? array() ),
                'image_id'      => absint( $template_data['image_id'] ?? 0 ),
                'use_product_image' => filter_var( $template_data['use_product_image'] ?? false, FILTER_VALIDATE_BOOLEAN ),
                'button_variables' => self::sanitize_button_variables( $template_data['button_variables'] ?? array() ),
                'body_variable_count' => absint( $template_data['body_variable_count'] ?? 0 ),
            );
            
            // Also update the old templates array for backward compatibility
            $settings['templates'][ $status ] = sanitize_text_field( $template_data['template_name'] ?? '' );
            $settings['enabled_notifications'][ $status ] = ! empty( $template_data['enabled'] ) ? 'yes' : 'no';
        }

        // Save custom notification statuses
        $sanitized_custom_statuses = array();
        if ( ! empty( $custom_statuses ) && is_array( $custom_statuses ) ) {
            foreach ( $custom_statuses as $cs ) {
                $sanitized_custom_statuses[] = array(
                    'id'          => sanitize_key( $cs['id'] ?? '' ),
                    'name'        => sanitize_text_field( $cs['name'] ?? '' ),
                    'description' => sanitize_text_field( $cs['description'] ?? '' ),
                    'icon'        => sanitize_text_field( $cs['icon'] ?? '�' ),
                    'wc_status'   => sanitize_key( $cs['wc_status'] ?? '' ),
                    'event_type'  => sanitize_key( $cs['event_type'] ?? '' ),
                );
            }
        }
        $settings['custom_notification_statuses'] = $sanitized_custom_statuses;

        // Save settings
        update_option( 'broodle_engage_settings', $settings );

        wp_send_json_success( array( 
            'message' => __( 'Template configuration saved successfully!', 'broodle-engage-connector' ),
        ) );
    }

    /**
     * Get available variable options for template mapping
     */
    public static function get_variable_options() {
        return array(
            ''                    => __( '-- Select Variable --', 'broodle-engage-connector' ),
            'customer_name'       => __( 'Customer Name', 'broodle-engage-connector' ),
            'customer_first_name' => __( 'Customer First Name', 'broodle-engage-connector' ),
            'customer_last_name'  => __( 'Customer Last Name', 'broodle-engage-connector' ),
            'customer_email'      => __( 'Customer Email', 'broodle-engage-connector' ),
            'order_id'            => __( 'Order ID', 'broodle-engage-connector' ),
            'order_number'        => __( 'Order Number', 'broodle-engage-connector' ),
            'order_total'         => __( 'Order Total (formatted)', 'broodle-engage-connector' ),
            'order_total_raw'     => __( 'Order Total (number only)', 'broodle-engage-connector' ),
            'order_date'          => __( 'Order Date', 'broodle-engage-connector' ),
            'order_status'        => __( 'Order Status', 'broodle-engage-connector' ),
            'product_names'       => __( 'Product Names', 'broodle-engage-connector' ),
            'product_count'       => __( 'Product Count', 'broodle-engage-connector' ),
            'shipping_address'    => __( 'Shipping Address', 'broodle-engage-connector' ),
            'billing_address'     => __( 'Billing Address', 'broodle-engage-connector' ),
            'payment_method'      => __( 'Payment Method', 'broodle-engage-connector' ),
            'shipping_method'     => __( 'Shipping Method', 'broodle-engage-connector' ),
            'tracking_url'        => __( 'Tracking URL', 'broodle-engage-connector' ),
            'tracking_number'     => __( 'Tracking Number', 'broodle-engage-connector' ),
            'coupon_code'         => __( 'Coupon Code', 'broodle-engage-connector' ),
            'cart_url'            => __( 'Cart URL', 'broodle-engage-connector' ),
            'product_url'         => __( 'Product URL (first item)', 'broodle-engage-connector' ),
            'shop_url'            => __( 'Shop URL', 'broodle-engage-connector' ),
            'my_account_url'      => __( 'My Account URL', 'broodle-engage-connector' ),
            'site_name'           => __( 'Site Name', 'broodle-engage-connector' ),
            'custom_text'         => __( 'Custom Text (enter below)', 'broodle-engage-connector' ),
        );
    }

    /**
     * Sanitize button variables array
     *
     * @param array $button_variables Raw button variables data.
     * @return array Sanitized button variables.
     */
    public static function sanitize_button_variables( $button_variables ) {
        if ( ! is_array( $button_variables ) ) {
            return array();
        }

        $sanitized = array();
        foreach ( $button_variables as $bv ) {
            if ( ! is_array( $bv ) ) {
                continue;
            }
            $sanitized[] = array(
                'index'   => absint( $bv['index'] ?? 0 ),
                'type'    => sanitize_text_field( $bv['type'] ?? 'url' ),
                'var_num' => sanitize_text_field( $bv['var_num'] ?? '1' ),
            );
        }

        return $sanitized;
    }
}
