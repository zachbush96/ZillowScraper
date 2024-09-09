<?php
/**
 * Plugin Name: Zillow Listing Importer_claude
 * Description: Import Zillow listings from Chrome extension via Claude
 * Version: 1.1
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

        // Handle remove listing request
        add_action('admin_post_zls_remove_listing', array($this, 'remove_listing'));
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
            bedrooms int NOT NULL,
            bathrooms int NOT NULL,
            sqft int NOT NULL,
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
                        <th>Bedrooms</th>
                        <th>Bathrooms</th>
                        <th>Square Feet</th>
                        <th>Date Imported</th>
                        <th>Action</th>
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
                            <td><?php echo esc_html($listing->bedrooms); ?></td>
                            <td><?php echo esc_html($listing->bathrooms); ?></td>
                            <td><?php echo esc_html($listing->sqft); ?></td>
                            <td><?php echo esc_html($listing->date); ?></td>
                            <td>
                                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display:inline;">
                                    <?php wp_nonce_field('zls_remove_listing_' . $listing->id, 'zls_remove_listing_nonce'); ?>
                                    <input type="hidden" name="action" value="zls_remove_listing">
                                    <input type="hidden" name="listing_id" value="<?php echo esc_attr($listing->id); ?>">
                                    <button type="submit" class="button action" onclick="return confirm('Are you sure you want to remove this listing?');">Remove</button>
                                </form>
                                <button class="button action">Export to WPDK</button>                             
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function remove_listing() {
        // Check nonce for security
        if (!isset($_POST['zls_remove_listing_nonce']) || !wp_verify_nonce($_POST['zls_remove_listing_nonce'], 'zls_remove_listing_' . $_POST['listing_id'])) {
            wp_die('Security check failed');
        }

        if (!current_user_can('manage_options')) {
            wp_die('You do not have sufficient permissions to perform this action.');
        }

        if (!isset($_POST['listing_id'])) {
            wp_die('Invalid request');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'zillow_listings';

        $listing_id = intval($_POST['listing_id']);
        $result = $wpdb->delete($table_name, array('id' => $listing_id), array('%d'));

        if ($result === false) {
            wp_die('Failed to remove listing');
        }

        wp_redirect(admin_url('admin.php?page=zillow-listings'));
        exit;
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

        //Debug: Log the received params
        error_log('Received params: ' . print_r($params, true));

        if (empty($params)) {
            $json = file_get_contents('php://input');
            $params = json_decode($json, true);
            // Debug: Log the parsed JSON
            error_log('Parsed JSON: ' . print_r($params, true));
        }

        if (!isset($params['address']) || !isset($params['images']) || !isset($params['details'])) {
            return new WP_Error('missing_data', 'Address, images, and details are required', array('status' => 400));
        }

        // Parse details
        $bedrooms = isset($params['details'][0][0]) ? intval(str_replace(',', '', $params['details'][0][0])) : 0;
        $bathrooms = isset($params['details'][1][0]) ? intval(str_replace(',', '', $params['details'][1][0])) : 0;
        $sqft = isset($params['details'][2][0]) ? intval(str_replace(',', '', $params['details'][2][0])) : 0;

        global $wpdb;
        $table_name = $wpdb->prefix . 'zillow_listings';

        $result = $wpdb->insert(
            $table_name,
            array(
                'address' => sanitize_text_field($params['address']),
                'images' => json_encode(array_map('esc_url_raw', $params['images'])),
                'bedrooms' => $bedrooms,
                'bathrooms' => $bathrooms,
                'sqft' => $sqft,
            ),
            array('%s', '%s', '%d', '%d', '%d')
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
