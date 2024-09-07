<?php
add_action('rest_api_init', function() {
    register_rest_route('zls/v1', '/submit-listing', [
        'methods' => 'POST',
        'callback' => 'zsp_submit_listing',
        'permission_callback' => '__return_true',
    ]);
});

function zsp_submit_listing(WP_REST_Request $request) {
    $data = $request->get_json_params();

    // Create a new post for the listing
    $post_id = wp_insert_post([
        'post_title' => sanitize_text_field($data['address']),
        'post_type' => 'zillow_listing',
        'post_status' => 'pending',
    ]);

    if ($post_id) {
        // Save images and other metadata
        update_post_meta($post_id, 'listing_images', maybe_serialize($data['images']));
        return new WP_REST_Response(['success' => true, 'post_id' => $post_id], 200);
    }

    return new WP_REST_Response(['success' => false], 500);
}
