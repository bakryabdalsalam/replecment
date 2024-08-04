<?php
/*
Plugin Name: YITH Refund and Replace Extend
Description: Extends the YITH Refund System plugin to add options for refund and replacement.
Version: 1.0
Author: Bakry Abdelsalam
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include necessary files
include_once plugin_dir_path(__FILE__) . 'includes/class-yith-refund-replace-extend.php';

// Initialize the plugin
add_action('plugins_loaded', 'initialize_yith_refund_replace_extend');
function initialize_yith_refund_replace_extend() {
    if (class_exists('YITH_Refund_Replace_Extend')) {
        YITH_Refund_Replace_Extend::get_instance();
    }
}
