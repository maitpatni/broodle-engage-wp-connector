<?php
/**
 * GitHub-based plugin updater
 *
 * Checks GitHub releases for new versions and enables one-click updates
 * from the WordPress admin Plugins page.
 *
 * @package BroodleEngageConnector
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Broodle_Engage_Updater {

    /**
     * GitHub repository owner/name
     */
    const GITHUB_REPO = 'maitpatni/broodle-engage-wp-connector';

    /**
     * GitHub API URL for latest release
     */
    const GITHUB_API_URL = 'https://api.github.com/repos/maitpatni/broodle-engage-wp-connector/releases/latest';

    /**
     * Plugin slug (folder name)
     */
    const PLUGIN_SLUG = 'broodle-engage-connector';

    /**
     * Transient key for caching GitHub response
     */
    const CACHE_KEY = 'broodle_engage_github_update';

    /**
     * Cache duration in seconds (6 hours)
     */
    const CACHE_DURATION = 21600;

    /**
     * Plugin basename (e.g. broodle-engage-connector/broodle-engage-connector.php)
     *
     * @var string
     */
    private $plugin_basename;

    /**
     * Current plugin version
     *
     * @var string
     */
    private $current_version;

    /**
     * Constructor
     */
    public function __construct() {
        $this->plugin_basename  = BROODLE_ENGAGE_PLUGIN_BASENAME;
        $this->current_version  = BROODLE_ENGAGE_VERSION;

        // Check for updates
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_update' ) );

        // Provide plugin info for the "View details" popup
        add_filter( 'plugins_api', array( $this, 'plugin_info' ), 20, 3 );

        // Rename extracted folder to match our plugin slug
        add_filter( 'upgrader_source_selection', array( $this, 'fix_directory_name' ), 10, 4 );

        // Clear cache after update
        add_action( 'upgrader_process_complete', array( $this, 'clear_cache' ), 10, 2 );
    }

    /**
     * Fetch release data from GitHub (cached)
     *
     * @return object|false Release data or false on failure.
     */
    private function get_github_release() {
        $cached = get_transient( self::CACHE_KEY );

        if ( false !== $cached ) {
            return $cached;
        }

        $response = wp_remote_get( self::GITHUB_API_URL, array(
            'timeout' => 10,
            'headers' => array(
                'Accept'     => 'application/vnd.github.v3+json',
                'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url(),
            ),
        ) );

        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            // Cache the failure briefly (30 min) to avoid hammering the API
            set_transient( self::CACHE_KEY, 'error', 1800 );
            return false;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ) );

        if ( empty( $body ) || ! isset( $body->tag_name ) ) {
            set_transient( self::CACHE_KEY, 'error', 1800 );
            return false;
        }

        set_transient( self::CACHE_KEY, $body, self::CACHE_DURATION );

        return $body;
    }

    /**
     * Get the clean version number from a GitHub tag
     *
     * Strips leading 'v' if present (e.g. v3.0.2 → 3.0.2)
     *
     * @param string $tag GitHub release tag.
     * @return string Clean version number.
     */
    private function parse_version( $tag ) {
        return ltrim( $tag, 'vV' );
    }

    /**
     * Check GitHub for a newer version
     *
     * @param object $transient The update_plugins transient.
     * @return object Modified transient.
     */
    public function check_for_update( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        $release = $this->get_github_release();

        if ( ! $release || 'error' === $release ) {
            return $transient;
        }

        $remote_version = $this->parse_version( $release->tag_name );

        if ( version_compare( $this->current_version, $remote_version, '<' ) ) {
            $download_url = $release->zipball_url;

            // Prefer an attached ZIP asset if available
            if ( ! empty( $release->assets ) ) {
                foreach ( $release->assets as $asset ) {
                    if ( 'application/zip' === $asset->content_type || substr( $asset->name, -4 ) === '.zip' ) {
                        $download_url = $asset->browser_download_url;
                        break;
                    }
                }
            }

            $plugin_data = (object) array(
                'slug'         => self::PLUGIN_SLUG,
                'plugin'       => $this->plugin_basename,
                'new_version'  => $remote_version,
                'url'          => 'https://github.com/' . self::GITHUB_REPO,
                'package'      => $download_url,
                'icons'        => array(),
                'banners'      => array(),
                'tested'       => '6.7',
                'requires_php' => '7.4',
                'requires'     => '5.0',
            );

            $transient->response[ $this->plugin_basename ] = $plugin_data;
        } else {
            // No update — report as "no_update" so WP doesn't flag it
            $transient->no_update[ $this->plugin_basename ] = (object) array(
                'slug'        => self::PLUGIN_SLUG,
                'plugin'      => $this->plugin_basename,
                'new_version' => $this->current_version,
                'url'         => 'https://github.com/' . self::GITHUB_REPO,
                'package'     => '',
            );
        }

        return $transient;
    }

    /**
     * Supply plugin info for the "View details" popup
     *
     * @param false|object|array $result The result object or array.
     * @param string             $action The API action being performed.
     * @param object             $args   Plugin API arguments.
     * @return false|object
     */
    public function plugin_info( $result, $action, $args ) {
        if ( 'plugin_information' !== $action ) {
            return $result;
        }

        if ( ! isset( $args->slug ) || self::PLUGIN_SLUG !== $args->slug ) {
            return $result;
        }

        $release = $this->get_github_release();

        if ( ! $release || 'error' === $release ) {
            return $result;
        }

        $remote_version = $this->parse_version( $release->tag_name );

        $info = (object) array(
            'name'            => 'Broodle Engage Connector',
            'slug'            => self::PLUGIN_SLUG,
            'version'         => $remote_version,
            'author'          => '<a href="https://broodle.host">Broodle</a>',
            'author_profile'  => 'https://broodle.host',
            'homepage'        => 'https://github.com/' . self::GITHUB_REPO,
            'requires'        => '5.0',
            'tested'          => '6.7',
            'requires_php'    => '7.4',
            'downloaded'      => 0,
            'last_updated'    => $release->published_at,
            'sections'        => array(
                'description'  => 'Send WooCommerce order notifications to customers via WhatsApp using Broodle Engage API (Chatwoot). Supports order received, shipped, delivered, and failed/cancelled notifications.',
                'changelog'    => $this->format_changelog( $release->body ),
            ),
            'download_link'   => $release->zipball_url,
            'banners'         => array(),
        );

        // Prefer attached ZIP
        if ( ! empty( $release->assets ) ) {
            foreach ( $release->assets as $asset ) {
                if ( 'application/zip' === $asset->content_type || substr( $asset->name, -4 ) === '.zip' ) {
                    $info->download_link = $asset->browser_download_url;
                    break;
                }
            }
        }

        return $info;
    }

    /**
     * Fix the extracted directory name after download
     *
     * GitHub ZIPs extract as "repo-name-branch" (e.g. broodle-engage-wp-connector-main).
     * This renames it to the correct plugin folder name.
     *
     * @param string      $source        Path to the extracted source.
     * @param string      $remote_source Path to the remote source (unused).
     * @param WP_Upgrader $upgrader      WP_Upgrader instance.
     * @param array       $hook_extra    Extra data about the upgrade.
     * @return string|WP_Error Corrected source path or WP_Error.
     */
    public function fix_directory_name( $source, $remote_source, $upgrader, $hook_extra ) {
        // Only act on our plugin
        if ( ! isset( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== $this->plugin_basename ) {
            return $source;
        }

        global $wp_filesystem;

        $correct_slug = self::PLUGIN_SLUG;
        $new_source   = trailingslashit( $remote_source ) . trailingslashit( $correct_slug );

        // If source already has the correct name, skip
        if ( trailingslashit( $source ) === $new_source ) {
            return $source;
        }

        // Rename the extracted directory
        if ( $wp_filesystem->move( $source, $new_source, true ) ) {
            return $new_source;
        }

        return new WP_Error(
            'rename_failed',
            sprintf(
                /* translators: 1: Source folder, 2: Destination folder */
                __( 'Could not rename plugin folder from %1$s to %2$s.', 'broodle-engage-connector' ),
                $source,
                $new_source
            )
        );
    }

    /**
     * Convert GitHub markdown release body to HTML for changelog
     *
     * @param string $body Markdown release body.
     * @return string HTML changelog.
     */
    private function format_changelog( $body ) {
        if ( empty( $body ) ) {
            return '<p>No changelog provided for this release.</p>';
        }

        // Basic markdown conversion
        $html = esc_html( $body );
        $html = nl2br( $html );

        // Convert markdown headers
        $html = preg_replace( '/^### (.+)$/m', '<h4>$1</h4>', $html );
        $html = preg_replace( '/^## (.+)$/m', '<h3>$1</h3>', $html );

        // Convert markdown bold
        $html = preg_replace( '/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html );

        // Convert markdown list items
        $html = preg_replace( '/^- (.+)$/m', '<li>$1</li>', $html );
        $html = preg_replace( '/(<li>.*<\/li>)/s', '<ul>$1</ul>', $html );

        return $html;
    }

    /**
     * Clear the cached GitHub release data
     *
     * @param WP_Upgrader $upgrader  Upgrader instance.
     * @param array       $hook_extra Extra data about the upgrade.
     */
    public function clear_cache( $upgrader, $hook_extra ) {
        if (
            isset( $hook_extra['action'], $hook_extra['type'], $hook_extra['plugins'] ) &&
            'update' === $hook_extra['action'] &&
            'plugin' === $hook_extra['type'] &&
            in_array( $this->plugin_basename, $hook_extra['plugins'], true )
        ) {
            delete_transient( self::CACHE_KEY );
        }
    }
}
