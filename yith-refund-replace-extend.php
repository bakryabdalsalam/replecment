<?php
/*
Plugin Name: YITH Refund Custom
Description: Adds refund or replace options to the YITH refund form and displays this information in the orders and refund requests pages.
Version: 1.0
Author: Bakry Abdelsalam
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class YITH_Refund_Custom {
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_filter('yith_ywraq_after_rfq_order_info', array($this, 'add_refund_replace_options'));
        add_action('wp_ajax_nopriv_yith_custom_refund_replace', array($this, 'handle_form_submission'));
        add_action('wp_ajax_yith_custom_refund_replace', array($this, 'handle_form_submission'));
        add_filter('manage_edit-shop_order_columns', array($this, 'add_custom_order_column'));
        add_action('manage_shop_order_posts_custom_column', array($this, 'custom_orders_column_content'));
        add_filter('manage_yith_refund_request_posts_columns', array($this, 'add_custom_refund_request_column'));
        add_action('manage_yith_refund_request_posts_custom_column', array($this, 'custom_refund_request_column_content'), 10, 2);
    }

    public function enqueue_scripts() {
        wp_enqueue_script('yith-refund-custom', plugins_url('/assets/js/yith-refund-custom.js', __FILE__), array('jquery'), '1.0', true);
        wp_localize_script('yith-refund-custom', 'yithRefundCustom', array(
            'ajax_url' => admin_url('admin-ajax.php'),
        ));
    }

    public function add_refund_replace_options() {
        echo '<div class="yith-refund-replace-options">
                <label for="refund_replace_choice">Choose an option:</label>
                <select id="refund_replace_choice" name="refund_replace_choice">
                    <option value="refund">Refund</option>
                    <option value="replace">Replace</option>
                </select>
              </div>';
    }

    public function handle_form_submission() {
        if (isset($_POST['order_id']) && isset($_POST['refund_replace_choice'])) {
            $order_id = intval($_POST['order_id']);
            $refund_replace_choice = sanitize_text_field($_POST['refund_replace_choice']);

            update_post_meta($order_id, '_refund_replace_choice', $refund_replace_choice);

            wp_send_json_success(array('message' => 'Form submitted successfully'));
        } else {
            wp_send_json_error(array('message' => 'Invalid form submission'));
        }
    }

    public function add_custom_order_column($columns) {
        $new_columns = array();

        foreach ($columns as $key => $column) {
            $new_columns[$key] = $column;
            if ('order_total' === $key) {
                $new_columns['refund_replace_choice'] = 'Refund/Replace';
            }
        }

        return $new_columns;
    }

    public function custom_orders_column_content($column) {
        global $post;

        if ('refund_replace_choice' === $column) {
            $refund_replace_choice = get_post_meta($post->ID, '_refund_replace_choice', true);
            echo esc_html($refund_replace_choice);
        }
    }

    public function add_custom_refund_request_column($columns) {
        $columns['refund_replace_choice'] = 'Refund/Replace';
        return $columns;
    }

    public function custom_refund_request_column_content($column, $post_id) {
        if ('refund_replace_choice' === $column) {
            $refund_replace_choice = get_post_meta($post_id, '_refund_replace_choice', true);
            echo esc_html($refund_replace_choice);
        }
    }
}

new YITH_Refund_Custom();
