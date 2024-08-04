<?php
class YITH_Refund_Replace_Extend {
    private static $instance;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_filter('yith_wcars_request_form', array($this, 'add_replace_option_to_form'));
        add_action('yith_wcars_after_refund_request_created', array($this, 'handle_replacement_request'), 10, 2);
        add_filter('manage_edit-shop_order_columns', array($this, 'add_replacement_column_to_order_page'));
        add_action('manage_shop_order_posts_custom_column', array($this, 'display_replacement_column_content'), 10, 2);
        add_filter('yith_wcars_request_table_columns', array($this, 'add_replacement_column_to_refund_requests_page'));
        add_action('yith_wcars_request_table_column_content', array($this, 'display_replacement_column_in_refund_requests_page'), 10, 3);
    }

    public function enqueue_scripts() {
        wp_enqueue_script('yith-refund-replace-extend', plugin_dir_url(__FILE__) . '../assets/js/yith-refund-replace-extend.js', array('jquery'), '1.0', true);
    }

    public function add_replace_option_to_form($form) {
        ob_start();
        ?>
        <label>
            <input type="radio" name="refund_or_replace" value="refund" checked>
            <?php _e('Refund', 'yith-wcars'); ?>
        </label>
        <label>
            <input type="radio" name="refund_or_replace" value="replace">
            <?php _e('Replace', 'yith-wcars'); ?>
        </label>
        <?php
        $form .= ob_get_clean();
        return $form;
    }

    public function handle_replacement_request($request_id, $request) {
        if (isset($_POST['refund_or_replace']) && $_POST['refund_or_replace'] == 'replace') {
            update_post_meta($request_id, '_is_replacement', 'yes');
        }
    }

    public function add_replacement_column_to_order_page($columns) {
        $columns['replacement_request'] = __('Replacement Request', 'yith-wcars');
        return $columns;
    }

    public function display_replacement_column_content($column, $post_id) {
        if ($column == 'replacement_request') {
            $is_replacement = get_post_meta($post_id, '_is_replacement', true);
            echo $is_replacement ? __('Yes', 'yith-wcars') : __('No', 'yith-wcars');
        }
    }

    public function add_replacement_column_to_refund_requests_page($columns) {
        $columns['replacement_request'] = __('Replacement Request', 'yith-wcars');
        return $columns;
    }

    public function display_replacement_column_in_refund_requests_page($column, $item, $request) {
        if ($column == 'replacement_request') {
            $is_replacement = get_post_meta($request->ID, '_is_replacement', true);
            echo $is_replacement ? __('Yes', 'yith-wcars') : __('No', 'yith-wcars');
        }
    }
}
