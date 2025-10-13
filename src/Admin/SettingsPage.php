<?php
/**
 * Admin Settings Interface
 *
 * @package ArabicSearchEnhancement
 * @since 1.1.0
 * @author Yasser Nageep Maisra <info@maisra.net>
 * @copyright 2025 Yasser Nageep Maisra
 * @license GPL-2.0-or-later
 * @link https://maisra.net
 */

namespace ArabicSearchEnhancement\Admin;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

use ArabicSearchEnhancement\Interfaces\ConfigurationInterface;
use ArabicSearchEnhancement\Core\Configuration;

class SettingsPage {
    
    /**
     * Configuration instance
     *
     * @var ConfigurationInterface
     */
    private $config;
    
    /**
     * Settings page slug
     */
    private const PAGE_SLUG = 'arabic-search-enhancement';
    
    /**
     * Settings group
     */
    private const SETTINGS_GROUP = 'ase_settings_group';
    
    /**
     * Constructor
     *
     * @param ConfigurationInterface $config Configuration instance
     */
    public function __construct(ConfigurationInterface $config) {
        $this->config = $config;
    }
    
    /**
     * Initialize admin hooks
     *
     * @return void
     */
    public function init_hooks(): void {
    add_action('admin_menu', [$this, 'add_settings_page']);
    add_action('admin_init', [$this, 'register_settings']);
    add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        
        // Get the correct plugin file path
        $plugin_file = defined('ARABIC_SEARCH_ENHANCEMENT_PLUGIN_FILE') 
            ? ARABIC_SEARCH_ENHANCEMENT_PLUGIN_FILE 
            : dirname(__DIR__, 2) . '/wp-plg-arabic-search-enhancement.php';
            
        add_filter('plugin_action_links_' . plugin_basename($plugin_file), [$this, 'add_settings_link']);
    }
    
    /**
     * Add settings page to admin menu
     *
     * @return void
     */
    public function add_settings_page(): void {
        add_options_page(
            __('Arabic Search Settings', 'arabic-search-enhancement'),
            __('Arabic Search', 'arabic-search-enhancement'),
            'manage_options',
            self::PAGE_SLUG,
            [$this, 'render_settings_page']
        );
    }
    
    /**
     * Register plugin settings
     *
     * @return void
     */
    public function register_settings(): void {
        $settings = [
            'enable_enhancement' => [
                'type' => 'boolean',
                'sanitize_callback' => [$this, 'sanitize_checkbox'],
                'default' => true
            ],
            'search_post_types' => [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitize_post_types'],
                'default' => ['post', 'page']
            ],
            'search_excerpt' => [
                'type' => 'boolean',
                'sanitize_callback' => [$this, 'sanitize_checkbox'],
                'default' => true
            ],
            'posts_per_page' => [
                'type' => 'integer',
                'sanitize_callback' => 'absint',
                'default' => get_option('posts_per_page')
            ],
            'debug_mode' => [
                'type' => 'boolean',
                'sanitize_callback' => [$this, 'sanitize_checkbox'],
                'default' => false
            ],
            'analytics_enabled' => [
                'type' => 'boolean',
                'sanitize_callback' => [$this, 'sanitize_checkbox'],
                'default' => false
            ],
        ];
        
        foreach ($settings as $key => $args) {
            register_setting(
                self::SETTINGS_GROUP,
                Configuration::OPTION_PREFIX . $key,
                $args
            );
        }
    }
    
    /**
     * Sanitize checkbox value
     *
     * @param mixed $value Input value
     * @return bool Sanitized boolean value
     */
    public function sanitize_checkbox($value): bool {
        return (bool) $value;
    }
    
    /**
     * Sanitize post types array
     *
     * @param mixed $value Input value
     * @return array Sanitized post types array
     */
    public function sanitize_post_types($value): array {
        if (!is_array($value)) {
            return ['post', 'page'];
        }
        
        $allowed_post_types = get_post_types(['public' => true]);
        $sanitized = [];
        
        foreach ($value as $post_type) {
            $post_type = sanitize_key($post_type);
            if (in_array($post_type, $allowed_post_types, true)) {
                $sanitized[] = $post_type;
            }
        }
        
        return empty($sanitized) ? ['post'] : $sanitized;
    }
    
    /**
     * Render settings page
     *
     * @return void
     */
    public function render_settings_page(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'arabic-search-enhancement'));
        }
        
        $rtl_class = $this->config->is_rtl() ? 'rtl' : '';
        
        ?>
        <div class="wrap <?php echo esc_attr($rtl_class); ?>">
            <?php if ($this->config->is_rtl()): ?>
            <style type="text/css">
                .wrap.rtl {
                    direction: rtl;
                    text-align: right;
                }
                .wrap.rtl .form-table th {
                    text-align: right;
                    padding-right: 0;
                    padding-left: 20px;
                }
                .wrap.rtl .form-table td {
                    text-align: right;
                }
                .wrap.rtl ul {
                    margin-right: 20px;
                    margin-left: 0;
                }
                .wrap.rtl .card {
                    text-align: right;
                }
                .wrap.rtl input[type="checkbox"] {
                    margin-left: 10px;
                    margin-right: 0;
                }
                .wrap.rtl label {
                    display: inline-block;
                }
            </style>
            <?php endif; ?>
            
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <?php $this->render_notices(); ?>
            
            <form method="post" action="options.php">
                <?php
                settings_fields(self::SETTINGS_GROUP);
                do_settings_sections(self::SETTINGS_GROUP);
                ?>
                
                <table class="form-table" role="presentation">
                    <?php $this->render_enhancement_setting(); ?>
                    <?php $this->render_excerpt_setting(); ?>
                    <?php $this->render_post_types_setting(); ?>
                    <?php $this->render_posts_per_page_setting(); ?>
                    <?php $this->render_debug_setting(); ?>
                    <?php $this->render_analytics_setting(); ?>
                </table>
                
                <?php $this->render_normalization_info(); ?>
                <?php $this->render_examples(); ?>
                
                <?php submit_button(); ?>
            </form>
            
            <?php $this->render_plugin_info(); ?>
            <?php $this->render_self_test_section(); ?>
        </div>
        <?php
    }
    
    /**
     * Render admin notices
     *
     * @return void
     */
    private function render_notices(): void {
        if (isset($_GET['settings-updated']) && sanitize_text_field(wp_unslash($_GET['settings-updated']))) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e('Settings saved.', 'arabic-search-enhancement'); ?></p>
            </div>
            <?php
        }
    }
    
    /**
     * Render enhancement enable/disable setting
     *
     * @return void
     */
    private function render_enhancement_setting(): void {
        ?>
        <tr>
            <th scope="row">
                <?php esc_html_e('Enable Arabic Enhancement', 'arabic-search-enhancement'); ?>
            </th>
            <td>
                <label>
                    <input type="checkbox" 
                           name="<?php echo esc_attr(Configuration::OPTION_PREFIX . 'enable_enhancement'); ?>" 
                           value="1" 
                           <?php checked($this->config->get('enable_enhancement', true), true); ?>>
                    <?php esc_html_e('Enable Arabic text normalization in search', 'arabic-search-enhancement'); ?>
                </label>
                <p class="description">
                    <?php esc_html_e('When enabled, searches will find content with different Arabic letter forms (e.g., searching "قران" will find "قرآن")', 'arabic-search-enhancement'); ?>
                </p>
            </td>
        </tr>
        <?php
    }
    
    /**
     * Render excerpt search setting
     *
     * @return void
     */
    private function render_excerpt_setting(): void {
        ?>
        <tr>
            <th scope="row">
                <?php esc_html_e('Search in Excerpt', 'arabic-search-enhancement'); ?>
            </th>
            <td>
                <label>
                    <input type="checkbox" 
                           name="<?php echo esc_attr(Configuration::OPTION_PREFIX . 'search_excerpt'); ?>" 
                           value="1" 
                           <?php checked($this->config->get('search_excerpt', true), true); ?>>
                    <?php esc_html_e('Include post excerpts in search', 'arabic-search-enhancement'); ?>
                </label>
            </td>
        </tr>
        <?php
    }
    
    /**
     * Render post types setting
     *
     * @return void
     */
    private function render_post_types_setting(): void {
        $selected_post_types = $this->config->get('search_post_types', ['post', 'page']);
        
        // Ensure selected_post_types is an array
        if (!is_array($selected_post_types)) {
            $selected_post_types = ['post', 'page'];
        }
        
        $post_types = get_post_types(['public' => true], 'objects');
        
        // Ensure we have post types
        if (empty($post_types) || !is_array($post_types)) {
            $post_types = [];
        }
        
        ?>
        <tr>
            <th scope="row">
                <?php esc_html_e('Post Types to Search', 'arabic-search-enhancement'); ?>
            </th>
            <td>
                <?php if (!empty($post_types)): ?>
                    <?php foreach ($post_types as $post_type): ?>
                        <?php if (is_object($post_type) && isset($post_type->name, $post_type->label)): ?>
                            <label style="display: block; margin-bottom: 5px;">
                                <input type="checkbox" 
                                       name="<?php echo esc_attr(Configuration::OPTION_PREFIX . 'search_post_types'); ?>[]" 
                                       value="<?php echo esc_attr($post_type->name); ?>" 
                                       <?php checked(in_array($post_type->name, $selected_post_types, true)); ?>>
                                <?php echo esc_html($post_type->label); ?>
                            </label>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p><?php esc_html_e('No public post types available.', 'arabic-search-enhancement'); ?></p>
                <?php endif; ?>
                <p class="description">
                    <?php esc_html_e('Select which post types to include in search results', 'arabic-search-enhancement'); ?>
                </p>
            </td>
        </tr>
        <?php
    }
    
    /**
     * Render posts per page setting
     *
     * @return void
     */
    private function render_posts_per_page_setting(): void {
        ?>
        <tr>
            <th scope="row">
                <label for="<?php echo esc_attr(Configuration::OPTION_PREFIX . 'posts_per_page'); ?>">
                    <?php esc_html_e('Results Per Page', 'arabic-search-enhancement'); ?>
                </label>
            </th>
            <td>
                <input type="number" 
                       id="<?php echo esc_attr(Configuration::OPTION_PREFIX . 'posts_per_page'); ?>"
                       name="<?php echo esc_attr(Configuration::OPTION_PREFIX . 'posts_per_page'); ?>" 
                       value="<?php echo esc_attr($this->config->get('posts_per_page', get_option('posts_per_page'))); ?>" 
                       min="1" 
                       max="100" 
                       class="small-text">
                <p class="description">
                    <?php esc_html_e('Number of search results to show per page', 'arabic-search-enhancement'); ?>
                </p>
            </td>
        </tr>
        <?php
    }
    
    /**
     * Render debug mode setting
     *
     * @return void
     */
    private function render_debug_setting(): void {
        ?>
        <tr>
            <th scope="row">
                <?php esc_html_e('Debug Mode', 'arabic-search-enhancement'); ?>
            </th>
            <td>
                <label>
                    <input type="checkbox" 
                           name="<?php echo esc_attr(Configuration::OPTION_PREFIX . 'debug_mode'); ?>" 
                           value="1" 
                           <?php checked($this->config->get('debug_mode', false), true); ?>>
                    <?php esc_html_e('Enable debug logging', 'arabic-search-enhancement'); ?>
                </label>
                <p class="description">
                    <?php esc_html_e('Log errors and performance information for troubleshooting', 'arabic-search-enhancement'); ?>
                </p>
            </td>
        </tr>
        <?php
    }
    
    /**
     * Render analytics setting
     *
     * @return void
     */
    private function render_analytics_setting(): void {
        ?>
        <tr>
            <th scope="row">
                <?php esc_html_e('Search Analytics', 'arabic-search-enhancement'); ?>
            </th>
            <td>
                <label>
                    <input type="checkbox" 
                           name="<?php echo esc_attr(Configuration::OPTION_PREFIX . 'analytics_enabled'); ?>" 
                           value="1" 
                           <?php checked($this->config->get('analytics_enabled', false), true); ?>>
                    <?php esc_html_e('Enable search analytics collection', 'arabic-search-enhancement'); ?>
                </label>
                <p class="description">
                    <?php esc_html_e('Collect anonymous search statistics to improve functionality. No personal data is collected.', 'arabic-search-enhancement'); ?>
                    <a href="#" onclick="document.getElementById('privacy-details').style.display = document.getElementById('privacy-details').style.display === 'none' ? 'block' : 'none'; return false;">
                        <?php esc_html_e('Privacy Details', 'arabic-search-enhancement'); ?>
                    </a>
                </p>
                <div id="privacy-details" style="display: none; background: #f9f9f9; padding: 10px; border-left: 4px solid #0073aa; margin-top: 10px;">
                    <strong><?php esc_html_e('What data is collected:', 'arabic-search-enhancement'); ?></strong>
                    <ul style="margin: 5px 0 5px 20px;">
                        <li><?php esc_html_e('Search queries (anonymized)', 'arabic-search-enhancement'); ?></li>
                        <li><?php esc_html_e('Search result counts', 'arabic-search-enhancement'); ?></li>
                        <li><?php esc_html_e('Language detection results', 'arabic-search-enhancement'); ?></li>
                        <li><?php esc_html_e('Search timestamps', 'arabic-search-enhancement'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('What is NOT collected:', 'arabic-search-enhancement'); ?></strong>
                    <ul style="margin: 5px 0 5px 20px;">
                        <li><?php esc_html_e('User names, emails, or IP addresses', 'arabic-search-enhancement'); ?></li>
                        <li><?php esc_html_e('Personal identifiable information', 'arabic-search-enhancement'); ?></li>
                        <li><?php esc_html_e('User browsing behavior outside search', 'arabic-search-enhancement'); ?></li>
                    </ul>
                    <p><?php esc_html_e('All data is stored locally in your WordPress database and never transmitted to external servers.', 'arabic-search-enhancement'); ?></p>
                </div>
            </td>
        </tr>
        <?php
    }
    
    /**
     * Render normalization rules information
     *
     * @return void
     */
    private function render_normalization_info(): void {
        ?>
        <hr>
        <h2><?php esc_html_e('Normalization Rules', 'arabic-search-enhancement'); ?></h2>
        <p><?php esc_html_e('This plugin automatically normalizes the following Arabic text variations:', 'arabic-search-enhancement'); ?></p>
        <ul style="list-style: disc; margin-left: 20px;">
            <li><?php esc_html_e('Removes all diacritics (Tashkeel): َ ُ ِ ّ ْ ً ٌ ٍ and more', 'arabic-search-enhancement'); ?></li>
            <li><?php esc_html_e('Normalizes Alef: أ إ آ ٱ → ا', 'arabic-search-enhancement'); ?></li>
            <li><?php esc_html_e('Normalizes Taa Marbuta: ة → ه', 'arabic-search-enhancement'); ?></li>
            <li><?php esc_html_e('Normalizes Yaa: ى → ي', 'arabic-search-enhancement'); ?></li>
            <li><?php esc_html_e('Normalizes Hamza: ؤ → و, ئ → ي', 'arabic-search-enhancement'); ?></li>
            <li><?php esc_html_e('Removes Tatweel (Kashida): ـ', 'arabic-search-enhancement'); ?></li>
        </ul>
        <?php
    }
    
    /**
     * Render usage examples
     *
     * @return void
     */
    private function render_examples(): void {
        ?>
        <h2><?php esc_html_e('Test Your Search', 'arabic-search-enhancement'); ?></h2>
        <p><?php esc_html_e('Try searching for these examples to see the improvement:', 'arabic-search-enhancement'); ?></p>
        <ul style="list-style: disc; margin-left: 20px;">
            <li><?php esc_html_e('Search "قران" to find "قرآن"', 'arabic-search-enhancement'); ?></li>
            <li><?php esc_html_e('Search "اسلام" to find "إسلام" or "الإسلام"', 'arabic-search-enhancement'); ?></li>
            <li><?php esc_html_e('Search "محمد" to find "مُحَمَّد" (with diacritics)', 'arabic-search-enhancement'); ?></li>
            <li><?php esc_html_e('Search "سنه" to find "سنة"', 'arabic-search-enhancement'); ?></li>
        </ul>
        <?php
    }
    
    /**
     * Render plugin information
     *
     * @return void
     */
    private function render_plugin_info(): void {
        global $wpdb;
        ?>
        <hr>
        <div class="card" style="max-width: 100%; padding: 20px;">
            <h2><?php esc_html_e('Plugin Information', 'arabic-search-enhancement'); ?></h2>
            <p><strong><?php esc_html_e('Version:', 'arabic-search-enhancement'); ?></strong> <?php echo esc_html(Configuration::VERSION); ?></p>
            <p><strong><?php esc_html_e('Website:', 'arabic-search-enhancement'); ?></strong> <a href="https://maisra.net" target="_blank" rel="noopener">maisra.net</a></p>
            <p><strong><?php esc_html_e('Database Charset:', 'arabic-search-enhancement'); ?></strong> <?php echo esc_html($wpdb->charset); ?></p>
            <p><strong><?php esc_html_e('Database Collation:', 'arabic-search-enhancement'); ?></strong> <?php echo esc_html($wpdb->collate); ?></p>
        </div>
        <?php
    }
    
    /**
     * Add settings link to plugins page
     *
     * @param array $links Existing plugin action links
     * @return array Modified plugin action links
     */
    public function add_settings_link(array $links): array {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url(admin_url('options-general.php?page=' . self::PAGE_SLUG)),
            esc_html__('Settings', 'arabic-search-enhancement')
        );
        
        array_unshift($links, $settings_link);
        return $links;
    }
    
    /**
     * Render self-test section
     *
     * @return void
     */
    private function render_self_test_section(): void {
        ?>
        <hr>
        <div class="card" style="max-width: 100%; padding: 20px;">
            <h2><?php esc_html_e('Plugin Self-Test', 'arabic-search-enhancement'); ?></h2>
            <p><?php esc_html_e('Click the button below to run a comprehensive test of the plugin functionality.', 'arabic-search-enhancement'); ?></p>
            
            <button type="button" id="ase-run-test" class="button button-secondary">
                <?php esc_html_e('Run Self-Test', 'arabic-search-enhancement'); ?>
            </button>
            
            <div id="ase-test-results" style="margin-top: 15px; display: none;">
                <h3><?php esc_html_e('Test Results:', 'arabic-search-enhancement'); ?></h3>
                <div id="ase-test-output"></div>
            </div>
        </div>
        
        <script type="text/javascript">
        document.getElementById('ase-run-test').addEventListener('click', function() {
            var button = this;
            var resultsDiv = document.getElementById('ase-test-results');
            var outputDiv = document.getElementById('ase-test-output');
            
            button.disabled = true;
            button.textContent = '<?php esc_js(__('Running Tests...', 'arabic-search-enhancement')); ?>';
            
            // Simple client-side tests
            var testResults = [];
            
            // Test 1: Check if essential functions exist
            try {
                if (typeof jQuery !== 'undefined') {
                    testResults.push({name: 'jQuery Available', status: 'passed', message: 'jQuery is loaded'});
                } else {
                    testResults.push({name: 'jQuery Available', status: 'warning', message: 'jQuery not detected'});
                }
            } catch(e) {
                testResults.push({name: 'jQuery Test', status: 'failed', message: e.message});
            }
            
            // Test 2: Check Arabic text rendering
            try {
                var testDiv = document.createElement('div');
                testDiv.innerHTML = 'قرآن كريم';
                testDiv.style.visibility = 'hidden';
                document.body.appendChild(testDiv);
                
                if (testDiv.offsetWidth > 0) {
                    testResults.push({name: 'Arabic Text Rendering', status: 'passed', message: 'Arabic text can be rendered'});
                } else {
                    testResults.push({name: 'Arabic Text Rendering', status: 'warning', message: 'Arabic text rendering may have issues'});
                }
                
                document.body.removeChild(testDiv);
            } catch(e) {
                testResults.push({name: 'Arabic Text Rendering', status: 'failed', message: e.message});
            }
            
            // Display results
            var html = '<div class="notice notice-info"><p><strong>Client-side tests completed.</strong></p></div>';
            html += '<ul>';
            
            testResults.forEach(function(result) {
                var statusClass = result.status === 'passed' ? 'green' : (result.status === 'warning' ? 'orange' : 'red');
                html += '<li><span style="color: ' + statusClass + ';">●</span> <strong>' + result.name + ':</strong> ' + result.message + '</li>';
            });
            
            html += '</ul>';
            html += '<p><em><?php esc_js(__('Note: Server-side tests require the plugin to be fully activated and functional.', 'arabic-search-enhancement')); ?></em></p>';
            
            outputDiv.innerHTML = html;
            resultsDiv.style.display = 'block';
            
            button.disabled = false;
            button.textContent = '<?php esc_js(__('Run Self-Test', 'arabic-search-enhancement')); ?>';
        });
        </script>
        <?php
    }
    
    /**
     * Enqueue admin scripts and styles
     *
     * @param string $hook_suffix Current admin page
     * @return void
     */
    public function enqueue_admin_scripts(string $hook_suffix): void {
        // Only load on our settings page
        if ($hook_suffix !== 'settings_page_' . self::PAGE_SLUG) {
            return;
        }
        
        // Load JavaScript translations if available
        $locale = get_locale();
        $json_file = ARABIC_SEARCH_ENHANCEMENT_PLUGIN_DIR . 'languages/arabic-search-enhancement-' . $locale . '-json.json';
        
        if (!file_exists($json_file)) {
            // Try with just language code
            $language = substr($locale, 0, 2);
            $json_file = ARABIC_SEARCH_ENHANCEMENT_PLUGIN_DIR . 'languages/arabic-search-enhancement-' . $language . '-json.json';
        }
        
        if (file_exists($json_file)) {
            wp_add_inline_script('jquery', '
                window.wp = window.wp || {};
                window.wp.i18n = window.wp.i18n || {};
                window.wp.i18n.setLocaleData(' . file_get_contents($json_file) . ');
            ');
        }
        
        // Add RTL styles for Arabic
        if ($this->config->is_rtl()) {
            wp_add_inline_style('wp-admin', '
                .arabic-search-enhancement .wrap {
                    direction: rtl;
                    text-align: right;
                }
                .arabic-search-enhancement .form-table th {
                    text-align: right;
                    padding-right: 0;
                    padding-left: 20px;
                }
                .arabic-search-enhancement .form-table td {
                    text-align: right;
                }
                .arabic-search-enhancement input[type="checkbox"] {
                    margin-left: 10px;
                    margin-right: 0;
                }
            ');
        }
    }
}