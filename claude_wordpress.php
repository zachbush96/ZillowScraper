<?php
/**
 * Plugin Name: Zillow Listing Importer_claude
 * Description: Import Zillow listings from Chrome extension via Claude
 * Version: 1.0
 * Author: ZachB.
 */

// Prevent direct access to the plugin file
if (!defined('ABSPATH')) {
    exit;
}

class Zillow_Listing_Importer {
    public function __construct() {
        // Register activation hook
        register_activation_hook(__FILE__, array($this, 'activate'));

        // Register deactivation hook
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Add menu item
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Register REST API endpoint
        add_action('rest_api_init', array($this, 'register_api_endpoint'));
    }

    public function activate() {
        // Create custom table for storing listings
        global $wpdb;
        $table_name = $wpdb->prefix . 'zillow_listings';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            address text NOT NULL,
            images longtext NOT NULL,
            date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function deactivate() {
        // Cleanup tasks if needed
    }

    public function add_admin_menu() {
        add_menu_page(
            'Zillow Listings',
            'Zillow Listings',
            'manage_options',
            'zillow-listings',
            array($this, 'admin_page'),
            'dashicons-admin-home',
            6
        );
    }

    public function admin_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'zillow_listings';
        $listings = $wpdb->get_results("SELECT * FROM $table_name ORDER BY date DESC");

        ?>
        <div class="wrap">
            <h1>Imported Zillow Listings</h1>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Address</th>
                        <th>Images</th>
                        <th>Date Imported</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listings as $listing): ?>
                        <tr>
                            <td><?php echo esc_html($listing->address); ?></td>
                            <td>
                                <?php
                                $images = json_decode($listing->images);
                                foreach ($images as $image) {
                                    echo '<img src="' . esc_url($image) . '" style="width: 50px; height: 50px; margin: 2px;" />';
                                }
                                ?>
                            </td>
                            <td><?php echo esc_html($listing->date); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function register_api_endpoint() {
        register_rest_route('zls/v1', '/submit-listing', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_listing_submission'),
            'permission_callback' => '__return_true'
        ));
    }

    public function handle_listing_submission($request) {
        
        header("Access-Control-Allow-Origin: chrome-extension://cieaidolioenipcoipaiknkohhhcdpko");

        header("Access-Control-Allow-Methods: POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");
        
        $params = $request->get_params();

        if (!isset($params['address']) || !isset($params['images'])) {
            return new WP_Error('missing_data', 'Address and images are required', array('status' => 400));
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'zillow_listings';

        $result = $wpdb->insert(
            $table_name,
            array(
                'address' => sanitize_text_field($params['address']),
                'images' => json_encode(array_map('esc_url_raw', $params['images']))
            ),
            array('%s', '%s')
        );

        if ($result === false) {
            return new WP_Error('database_error', 'Failed to insert listing', array('status' => 500));
        }

        return array(
            'success' => true,
            'message' => 'Listing imported successfully'
        );
    }
}

// Initialize the plugin
new Zillow_Listing_Importer();
