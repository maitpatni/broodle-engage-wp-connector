<?php
/**
 * Logger class for WhatsApp notifications
 *
 * @package BroodleEngageConnector
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Logger class
 */
class Broodle_Engage_Logger {

    /**
     * Log table name
     */
    const TABLE_NAME = 'broodle_engage_logs';

    /**
     * Log levels
     */
    const LOG_SUCCESS = 'success';
    const LOG_ERROR = 'error';
    const LOG_PENDING = 'pending';
    const LOG_RETRY = 'retry';
    const LOG_SCHEDULED = 'scheduled';

    /**
     * Log a notification attempt
     *
     * @param int    $order_id Order ID.
     * @param string $phone_number Phone number.
     * @param string $template_name Template name.
     * @param string $status Log status.
     * @param array  $response_data Response data.
     * @param array  $api_response Raw API response.
     * @param string $error_message Error message.
     * @return int|false Log ID on success, false on failure.
     */
    public static function log( $order_id, $phone_number, $template_name, $status, $response_data = array(), $api_response = array(), $error_message = '' ) {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME;

        $data = array(
            'order_id' => absint( $order_id ),
            'phone_number' => sanitize_text_field( $phone_number ),
            'template_name' => sanitize_text_field( $template_name ),
            'status' => sanitize_text_field( $status ),
            'response_data' => wp_json_encode( $response_data ),
            'api_response' => wp_json_encode( $api_response ),
            'error_message' => sanitize_textarea_field( $error_message ),
            'created_at' => current_time( 'mysql' ),
        );

        $formats = array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' );

        // STABILITY: Safely handle database operations
        try {
            $result = $wpdb->insert( $table_name, $data, $formats );

            if ( false === $result ) {
                error_log( 'Broodle Engage Connector: Failed to insert log entry - ' . $wpdb->last_error );
                return false;
            }

            return $wpdb->insert_id;
        } catch ( Exception $e ) {
            // STABILITY: Database error shouldn't break anything
            error_log( 'Broodle Engage Connector: Database exception - ' . $e->getMessage() );
            return false;
        }
    }

    /**
     * Get logs with pagination
     *
     * @param array $args Query arguments.
     * @return array
     */
    public static function get_logs( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'order_id' => 0,
            'status' => '',
            'limit' => 20,
            'offset' => 0,
            'orderby' => 'created_at',
            'order' => 'DESC',
        );

        $args = wp_parse_args( $args, $defaults );
        $table_name = $wpdb->prefix . self::TABLE_NAME;

        // Build WHERE clause
        $where_conditions = array( '1=1' );
        $where_values = array();

        if ( ! empty( $args['order_id'] ) ) {
            $where_conditions[] = 'order_id = %d';
            $where_values[] = absint( $args['order_id'] );
        }

        if ( ! empty( $args['status'] ) ) {
            $where_conditions[] = 'status = %s';
            $where_values[] = sanitize_text_field( $args['status'] );
        }

        $where_clause = implode( ' AND ', $where_conditions );

        // Build ORDER BY clause
        $allowed_orderby = array( 'id', 'order_id', 'status', 'created_at' );
        $orderby = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'created_at';
        $order = 'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC';

        // Build LIMIT clause
        $limit = absint( $args['limit'] );
        $offset = absint( $args['offset'] );

        // Build main query
        if ( ! empty( $where_values ) ) {
            $query = $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE {$where_clause} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d",
                array_merge( $where_values, array( $limit, $offset ) )
            );
        } else {
            $query = $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE {$where_clause} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d",
                $limit,
                $offset
            );
        }

        $results = $wpdb->get_results( $query );

        // Get total count
        if ( ! empty( $where_values ) ) {
            $count_query = $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_name} WHERE {$where_clause}",
                $where_values
            );
        } else {
            $count_query = "SELECT COUNT(*) FROM {$table_name} WHERE {$where_clause}";
        }
        $total = $wpdb->get_var( $count_query );

        return array(
            'logs' => $results,
            'total' => $total,
        );
    }

    /**
     * Get log by ID
     *
     * @param int $log_id Log ID.
     * @return object|null
     */
    public static function get_log( $log_id ) {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME;

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM `" . esc_sql( $table_name ) . "` WHERE id = %d",
                absint( $log_id )
            )
        );
    }

    /**
     * Delete old logs based on retention settings
     *
     * @return int Number of deleted logs.
     */
    public static function cleanup_old_logs() {
        global $wpdb;

        $retention_days = Broodle_Engage_Settings::get_setting( 'log_retention_days', 30 );
        $table_name = $wpdb->prefix . self::TABLE_NAME;

        $cutoff_date = gmdate( 'Y-m-d H:i:s', strtotime( "-{$retention_days} days" ) );

        $deleted = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM `" . esc_sql( $table_name ) . "` WHERE created_at < %s",
                $cutoff_date
            )
        );

        if ( false === $deleted ) {
            error_log( 'Broodle Engage Connector: Failed to cleanup old logs - ' . $wpdb->last_error );
            return 0;
        }

        return $deleted;
    }

    /**
     * Get log statistics
     *
     * @param int $days Number of days to look back.
     * @return array
     */
    public static function get_stats( $days = 30 ) {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME;
        $cutoff_date = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

        $stats = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT status, COUNT(*) as count FROM `" . esc_sql( $table_name ) . "` WHERE created_at >= %s GROUP BY status",
                $cutoff_date
            ),
            ARRAY_A
        );

        $formatted_stats = array(
            'success' => 0,
            'error' => 0,
            'pending' => 0,
            'retry' => 0,
            'total' => 0,
        );

        foreach ( $stats as $stat ) {
            $formatted_stats[ $stat['status'] ] = absint( $stat['count'] );
            $formatted_stats['total'] += absint( $stat['count'] );
        }

        return $formatted_stats;
    }

    /**
     * Get recent error logs
     *
     * @param int $limit Number of logs to retrieve.
     * @return array
     */
    public static function get_recent_errors( $limit = 10 ) {
        return self::get_logs(
            array(
                'status' => self::LOG_ERROR,
                'limit' => $limit,
                'orderby' => 'created_at',
                'order' => 'DESC',
            )
        );
    }

    /**
     * Check if notification was already sent for order
     *
     * @param int    $order_id Order ID.
     * @param string $template_name Template name.
     * @return bool
     */
    public static function is_notification_sent( $order_id, $template_name ) {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME;

        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM `" . esc_sql( $table_name ) . "` WHERE order_id = %d AND template_name = %s AND status = %s",
                absint( $order_id ),
                sanitize_text_field( $template_name ),
                self::LOG_SUCCESS
            )
        );

        return $count > 0;
    }

    /**
     * Update log status
     *
     * @param int    $log_id Log ID.
     * @param string $status New status.
     * @param array  $response_data Response data.
     * @param array  $api_response Raw API response.
     * @param string $error_message Error message.
     * @return bool
     */
    public static function update_log_status( $log_id, $status, $response_data = array(), $api_response = array(), $error_message = '' ) {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME;

        $data = array(
            'status' => sanitize_text_field( $status ),
        );

        if ( ! empty( $response_data ) ) {
            $data['response_data'] = wp_json_encode( $response_data );
        }

        if ( ! empty( $api_response ) ) {
            $data['api_response'] = wp_json_encode( $api_response );
        }

        if ( ! empty( $error_message ) ) {
            $data['error_message'] = sanitize_textarea_field( $error_message );
        }

        // Dynamic format array based on data
        $formats = array();
        foreach ( $data as $key => $value ) {
            $formats[] = '%s';
        }

        $result = $wpdb->update(
            $table_name,
            $data,
            array( 'id' => absint( $log_id ) ),
            $formats,
            array( '%d' )
        );

        return false !== $result;
    }

    /**
     * Log a scheduled notification
     *
     * @param int    $order_id Order ID.
     * @param string $phone Phone number.
     * @param string $notification_type Notification type.
     * @param string $scheduled_time Scheduled execution time.
     * @param int    $delay_minutes Delay in minutes.
     * @return int|false Log ID on success, false on failure.
     */
    public static function log_scheduled( $order_id, $phone, $notification_type, $scheduled_time, $delay_minutes ) {
        $message = sprintf(
            /* translators: %1$s: delay minutes, %2$s: scheduled time */
            __( 'Notification scheduled to send in %1$s minutes at %2$s', 'broodle-engage-connector' ),
            $delay_minutes,
            $scheduled_time
        );

        $response_data = array(
            'scheduled_time' => $scheduled_time,
            'delay_minutes' => $delay_minutes,
            'status' => 'scheduled',
        );

        return self::log( $order_id, $phone, $notification_type, self::LOG_SCHEDULED, $response_data, array(), $message );
    }

    /**
     * Find scheduled log entry by order and notification type
     *
     * @param int    $order_id Order ID.
     * @param string $notification_type Notification type.
     * @return object|null Log entry or null if not found.
     */
    public static function find_scheduled_log( $order_id, $notification_type ) {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME;

        $result = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$table_name}
             WHERE order_id = %d
             AND template_name = %s
             AND status = %s
             ORDER BY created_at DESC
             LIMIT 1",
            $order_id,
            $notification_type,
            self::LOG_SCHEDULED
        ) );

        return $result;
    }

    /**
     * Get all scheduled (pending) notifications
     *
     * @return array
     */
    public static function get_scheduled_logs() {
        return self::get_logs( array(
            'status' => self::LOG_SCHEDULED,
            'limit' => 100,
            'orderby' => 'created_at',
            'order' => 'ASC',
        ) );
    }
}
