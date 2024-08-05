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
        add_action('wp_footer', array($this, 'add_replace_option_to_form'));
        add_action('wp_ajax_yith_custom_refund_replace', array($this, 'handle_form_submission'));
        add_action('wp_ajax_nopriv_yith_custom_refund_replace', array($this, 'handle_form_submission'));
        add_filter('manage_edit-shop_order_columns', array($this, 'add_custom_order_column'));
        add_action('manage_shop_order_posts_custom_column', array($this, 'custom_orders_column_content'));
        add_filter('manage_yith_refund_request_posts_columns', array($this, 'add_custom_refund_request_column'));
        add_action('manage_yith_refund_request_posts_custom_column', array($this, 'custom_refund_request_column_content'), 10, 2);
    }

    public function enqueue_scripts() {
        wp_enqueue_script('yith-refund-custom', plugins_url('/assets/js/yith-refund-custom.js', __FILE__), array('jquery'), '1.0', true);
        wp_localize_script('yith-refund-custom', 'ywcars_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('yith_ars_nonce'),
        ));
    }

    public function add_replace_option_to_form() {
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $(document.body).on('ywcars_request_window_created', function() {
                if ($('#ywcars_refund_type').length === 0) {
                    var refundTypeHtml = '<div class="ywcars_block" id="ywcars_refund_type">' +
                                         '<label for="ywcars_refund_type">Choose Request Type:</label><br>' +
                                         '<label><input type="radio" name="ywcars_refund_type_choice" value="refund" checked> Refund</label><br>' +
                                         '<label><input type="radio" name="ywcars_refund_type_choice" value="replacement"> Replacement</label>' +
                                         '</div>';
                    $('#ywcars_form').prepend(refundTypeHtml);
                }

                if ($('#ywcars_hidden_refund_type').length === 0) {
                    $('#ywcars_form').append('<input type="hidden" id="ywcars_hidden_refund_type" name="ywcars_hidden_refund_type" value="refund">');
                }

                $('input[name="ywcars_refund_type_choice"]').on('change', function() {
                    var selectedValue = $(this).val();
                    $('#ywcars_hidden_refund_type').val(selectedValue);
                });

                $('#ywcars_form').on('submit', function(e) {
                    e.preventDefault();

                    var refundReplaceChoice = $('#ywcars_hidden_refund_type').val();
                    var orderId = $('input[name="ywcars_form_order_id"]').val();

                    var formData = new FormData(this);
                    formData.append('action', 'yith_custom_refund_replace'); // Add this line to specify the action
                    formData.append('request_type', refundReplaceChoice);
                    formData.append('nonce', ywcars_params.nonce);

                    $.ajax({
                        url: ywcars_params.ajax_url,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                alert(response.data.message);
                            } else {
                                alert('There was an error processing your request.');
                            }
                        },
                        error: function(xhr, status, error) {
                            alert('An error occurred: ' + error);
                        }
                    });
                });
            });
        });
        </script>
        <?php
    }

    public function handle_form_submission() {
        check_ajax_referer('yith_ars_nonce', 'nonce');

        $order_id = isset($_POST['ywcars_form_order_id']) ? absint($_POST['ywcars_form_order_id']) : 0;
        $refund_replace_choice = isset($_POST['request_type']) ? sanitize_text_field($_POST['request_type']) : 'refund';

        if (!$order_id) {
            wp_send_json_error(array('message' => __('Missing required fields', 'yith-ars')));
        }

        error_log("Order ID: $order_id, Choice: $refund_replace_choice");

        update_post_meta($order_id, '_yith_ars_request_type', $refund_replace_choice);

        wp_send_json_success(array('message' => __('Form submitted successfully', 'yith-ars')));
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
            $refund_replace_choice = get_post_meta($post->ID, '_yith_ars_request_type', true);
            if ($refund_replace_choice) {
                echo esc_html($refund_replace_choice);
            } else {
                echo 'N/A';
            }
        }
    }

    public function add_custom_refund_request_column($columns) {
        $columns['refund_replace_choice'] = 'Refund/Replace';
        return $columns;
    }

    public function custom_refund_request_column_content($column, $post_id) {
        if ('refund_replace_choice' === $column) {
            $refund_replace_choice = get_post_meta($post_id, '_yith_ars_request_type', true);
            if ($refund_replace_choice) {
                echo esc_html($refund_replace_choice);
            } else {
                echo 'N/A';
            }
        }
    }
}

new YITH_Refund_Custom();
