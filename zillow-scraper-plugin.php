<?php
/*
Plugin Name: Zillow Scraper Plugin
Description: A plugin to import Zillow listings from a Chrome extension and display them in the WordPress admin panel.
Version: 1.0
Author: Zach
*/

// Define constants
define('ZSP_PATH', plugin_dir_path(__FILE__));
define('ZSP_URL', plugin_dir_url(__FILE__));

// Include necessary files
require_once ZSP_PATH . 'includes/class-zillow-scraper-plugin.php';
require_once ZSP_PATH . 'includes/api-endpoints.php';

// Initialize the plugin
function zsp_initialize_plugin() {
    $plugin = new Zillow_Scraper_Plugin();
    $plugin->run();
}
add_action('plugins_loaded', 'zsp_initialize_plugin');
