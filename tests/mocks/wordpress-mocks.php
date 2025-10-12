<?php
/**
 * WordPress function mocks for unit testing
 *
 * @copyright 2024 Yasir Najeep
 * @license   GPL v2 or later
 */

// Prevent loading if WordPress is already loaded
if (function_exists('add_action')) {
    return;
}

// Simple in-memory option storage for tests
global $ase_mock_wp_options;
$ase_mock_wp_options = [];

if (!function_exists('ase_mock_reset_options')) {
    function ase_mock_reset_options(): void {
        global $ase_mock_wp_options;
        $ase_mock_wp_options = [];
    }
}

// Define WordPress constants
define('OBJECT', 'OBJECT');
define('OBJECT_K', 'OBJECT_K');
define('ARRAY_A', 'ARRAY_A');
define('ARRAY_N', 'ARRAY_N');

// Mock wpdb class
class wpdb {
    public $posts = 'wp_posts';
    public $prefix = 'wp_';
    public $last_query = '';
    public $last_error = '';
    public $last_result = [];
    public $insert_id = 0;
    public $num_rows = 0;
    public $charset = 'utf8';
    public $collate = 'utf8_general_ci';
    
    public function prepare($query, ...$args) {
        if (empty($args)) {
            return $query;
        }

        if (count($args) === 1 && is_array($args[0])) {
            $args = $args[0];
        }

        $query = str_replace('%s', "'%s'", $query);
        return vsprintf($query, $args);
    }
    
    public function get_results($query, $output = OBJECT) {
        $this->last_query = $query;
        return [];
    }
    
    public function get_var($query, $x = 0, $y = 0) {
        $this->last_query = $query;
        return null;
    }
    
    public function get_row($query, $output = OBJECT, $y = 0) {
        $this->last_query = $query;
        return null;
    }
    
    public function query($query) {
        $this->last_query = $query;
        return false;
    }
    
    public function insert($table, $data, $format = null) {
        return false;
    }
    
    public function update($table, $data, $where, $format = null, $where_format = null) {
        return false;
    }
    
    public function delete($table, $where, $where_format = null) {
        return false;
    }

    public function esc_like($text) {
        return addslashes($text);
    }

    public function get_charset_collate() {
        return sprintf('CHARACTER SET %s COLLATE %s', $this->charset, $this->collate);
    }
}

// Mock WordPress globals
global $wpdb;
$wpdb = new wpdb();

// Mock WordPress functions
if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
        return true;
    }
}

if (!function_exists('add_filter')) {
    function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) {
        return true;
    }
}

if (!function_exists('get_option')) {
    function get_option($option, $default = false) {
        global $ase_mock_wp_options;
        return array_key_exists($option, $ase_mock_wp_options) ? $ase_mock_wp_options[$option] : $default;
    }
}

if (!function_exists('update_option')) {
    function update_option($option, $value, $autoload = null) {
        global $ase_mock_wp_options;
        $ase_mock_wp_options[$option] = $value;
        return true;
    }
}

if (!function_exists('add_option')) {
    function add_option($option, $value = '', $deprecated = '', $autoload = null) {
        global $ase_mock_wp_options;
        if (!array_key_exists($option, $ase_mock_wp_options)) {
            $ase_mock_wp_options[$option] = $value;
            return true;
        }
        return false;
    }
}

if (!function_exists('delete_option')) {
    function delete_option($option) {
        global $ase_mock_wp_options;
        unset($ase_mock_wp_options[$option]);
        return true;
    }
}

if (!function_exists('wp_cache_get')) {
    function wp_cache_get($key, $group = '') {
        return false;
    }
}

if (!function_exists('wp_cache_set')) {
    function wp_cache_set($key, $data, $group = '', $expire = 0) {
        return true;
    }
}

if (!function_exists('get_locale')) {
    function get_locale() {
        return 'en_US';
    }
}

if (!function_exists('wp_cache_delete')) {
    function wp_cache_delete($key, $group = '') {
        return true;
    }
}

if (!function_exists('wp_cache_flush')) {
    function wp_cache_flush() {
        return true;
    }
}

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) {
        return trim(strip_tags($str));
    }
}

if (!function_exists('wp_strip_all_tags')) {
    function wp_strip_all_tags($string) {
        return strip_tags($string);
    }
}

if (!function_exists('esc_html')) {
    function esc_html($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_html__')) {
    function esc_html__($text, $domain = 'default') {
        return esc_html($text);
    }
}

if (!function_exists('esc_attr__')) {
    function esc_attr__($text, $domain = 'default') {
        return esc_attr($text);
    }
}

if (!function_exists('esc_html_e')) {
    function esc_html_e($text, $domain = 'default') {
        echo esc_html($text);
    }
}

if (!function_exists('esc_attr_e')) {
    function esc_attr_e($text, $domain = 'default') {
        echo esc_attr($text);
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('__')) {
    function __($text, $domain = 'default') {
        return $text;
    }
}

if (!function_exists('_e')) {
    function _e($text, $domain = 'default') {
        echo $text;
    }
}

if (!function_exists('is_admin')) {
    function is_admin() {
        return false;
    }
}

if (!function_exists('current_user_can')) {
    function current_user_can($capability) {
        return true;
    }
}

if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action = -1) {
        return true;
    }
}

if (!function_exists('wp_create_nonce')) {
    function wp_create_nonce($action = -1) {
        return 'test_nonce';
    }
}

if (!function_exists('sanitize_key')) {
    function sanitize_key($key) {
        return preg_replace('/[^a-z0-9_\-]/', '', strtolower($key));
    }
}

if (!function_exists('absint')) {
    function absint($maybeint) {
        return abs(intval($maybeint));
    }
}

if (!function_exists('is_rtl')) {
    function is_rtl() {
        return false;
    }
}

if (!function_exists('apply_filters')) {
    function apply_filters($tag, $value) {
        return $value;
    }
}

if (!function_exists('do_action')) {
    function do_action($tag, ...$args) {
        return true;
    }
}

if (!function_exists('get_post_types')) {
    function get_post_types($args = [], $output = 'names') {
        $types = [
            'post' => (object) ['name' => 'post', 'label' => 'Posts'],
            'page' => (object) ['name' => 'page', 'label' => 'Pages'],
        ];
        return $output === 'objects' ? $types : array_keys($types);
    }
}

if (!function_exists('checked')) {
    function checked($checked, $current = true, $echo = true) {
        $result = ($checked == $current) ? 'checked="checked"' : '';
        if ($echo) {
            echo $result;
        }
        return $result;
    }
}

if (!function_exists('settings_fields')) {
    function settings_fields($option_group) {
        return true;
    }
}

if (!function_exists('do_settings_sections')) {
    function do_settings_sections($page) {
        return true;
    }
}

if (!function_exists('submit_button')) {
    function submit_button($text = 'Save Changes') {
        return true;
    }
}

if (!function_exists('register_setting')) {
    function register_setting($option_group, $option_name, $args = []) {
        return true;
    }
}

if (!function_exists('add_options_page')) {
    function add_options_page($page_title, $menu_title, $capability, $menu_slug, $callback = '') {
        return true;
    }
}

if (!function_exists('add_submenu_page')) {
    function add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $callback = '') {
        return true;
    }
}

if (!function_exists('plugin_basename')) {
    function plugin_basename($file) {
        return basename($file);
    }
}

if (!function_exists('admin_url')) {
    function admin_url($path = '') {
        return 'http://example.com/wp-admin/' . ltrim($path, '/');
    }
}

if (!function_exists('plugins_url')) {
    function plugins_url($path = '', $plugin = '') {
        return 'http://example.com/wp-content/plugins/' . ltrim($path, '/');
    }
}

if (!function_exists('wp_enqueue_script')) {
    function wp_enqueue_script($handle, $src = '', $deps = [], $ver = false, $in_footer = false) {
        return true;
    }
}

if (!function_exists('wp_enqueue_style')) {
    function wp_enqueue_style($handle, $src = '', $deps = [], $ver = false, $media = 'all') {
        return true;
    }
}

if (!function_exists('wp_localize_script')) {
    function wp_localize_script($handle, $object_name, $l10n) {
        return true;
    }
}

if (!function_exists('wp_die')) {
    function wp_die($message = '') {
        throw new \RuntimeException($message ?: 'wp_die called');
    }
}

// Mock WP_Query class
if (!class_exists('WP_Query')) {
    class WP_Query {
        public $query_vars = [];
        private $is_search = false;
        private $is_main = true;
        
        public function __construct($query = '') {
            if (is_array($query)) {
                $this->query_vars = $query;
                if (!empty($this->query_vars['s'])) {
                    $this->is_search = true;
                }
            }
        }

        public function get($key, $default = null) {
            return $this->query_vars[$key] ?? $default;
        }

        public function set($key, $value) {
            $this->query_vars[$key] = $value;
        }

        public function is_search() {
            return $this->is_search;
        }

        public function set_is_search($value) {
            $this->is_search = (bool) $value;
        }

        public function is_main_query() {
            return $this->is_main;
        }

        public function set_main_query($value) {
            $this->is_main = (bool) $value;
        }
    }
}