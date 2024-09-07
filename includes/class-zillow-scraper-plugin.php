<?php
class Zillow_Scraper_Plugin {

    public function run() {
        add_action('admin_menu', [$this, 'register_admin_page']);
        $this->register_post_type();
    }

    public function register_admin_page() {
        add_menu_page(
            'Zillow Listings',
            'Zillow Listings',
            'manage_options',
            'zillow-listings',
            [$this, 'render_admin_page'],
            'dashicons-admin-home',
            20
        );
    }

    public function render_admin_page() {
        include ZSP_PATH . 'admin/admin-page.php';
    }

    public function register_post_type() {
        register_post_type('zillow_listing', [
            'labels' => [
                'name' => 'Zillow Listings',
                'singular_name' => 'Zillow Listing',
            ],
            'public' => true,
            'has_archive' => true,
            'supports' => ['title', 'editor', 'thumbnail'],
        ]);
    }
}
