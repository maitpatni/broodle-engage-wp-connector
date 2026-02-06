<?php
/**
 * Uninstall script for Broodle Engage Connector
 *
 * @package BroodleEngageConnector
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Delete plugin options
delete_option( 'broodle_engage_settings' );

// Delete transients
delete_transient( 'broodle_engage_api_test' );

// Remove scheduled cron jobs
wp_clear_scheduled_hook( 'broodle_engage_cleanup_logs' );

// Drop custom tables
global $wpdb;

$table_name = esc_sql( $wpdb->prefix . 'broodle_engage_logs' );
$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared

// Clear any cached data
wp_cache_flush();
