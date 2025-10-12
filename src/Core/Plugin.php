<?php
/**
 * Main Plugin Class
 *
 * @package ArabicSearchEnhancement
 * @since 1.1.0
 * @author Yasser Nageep Maisra <info@maisra.net>
 * @copyright 2025 Yasser Nageep Maisra
 * @license GPL-2.0-or-later
 * @link https://maisra.net
 */

namespace ArabicSearchEnhancement\Core;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

use ArabicSearchEnhancement\Interfaces\ConfigurationInterface;
use ArabicSearchEnhancement\Interfaces\SearchQueryModifierInterface;
use ArabicSearchEnhancement\Admin\SettingsPage;

class Plugin {
    
    /**
     * Plugin instance
     *
     * @var Plugin|null
     */
    private static $instance = null;
    
    /**
     * Configuration instance
     *
     * @var ConfigurationInterface
     */
    private $config;
    
    /**
     * Search query modifier instance
     *
     * @var SearchQueryModifierInterface
     */
    private $search_modifier;
    
    /**
     * Settings page instance
     *
     * @var SettingsPage
     */
    private $settings_page;
    
    /**
     * Whether plugin is initialized
     *
     * @var bool
     */
    private $initialized = false;
    
    /**
     * Performance tracking
     *
     * @var array
     */
    private $performance_data = [];
    
    /**
     * Constructor
     *
     * @param ConfigurationInterface $config Configuration instance
     * @param SearchQueryModifierInterface $search_modifier Search modifier instance
     * @param SettingsPage $settings_page Settings page instance
     */
    public function __construct(
        ConfigurationInterface $config,
        SearchQueryModifierInterface $search_modifier,
        SettingsPage $settings_page
    ) {
        $this->config = $config;
        $this->search_modifier = $search_modifier;
        $this->settings_page = $settings_page;
    }
    
    /**
     * Get plugin instance (singleton pattern)
     *
     * @return Plugin
     */
    public static function get_instance(): Plugin {
        if (null === self::$instance) {
            self::$instance = PluginFactory::create_plugin();
        }
        
        return self::$instance;
    }
    
    /**
     * Initialize the plugin
     *
     * @return void
     */
    public function init(): void {
        if ($this->initialized) {
            return;
        }
        
        $this->init_hooks();
        $this->init_admin();
        
        $this->initialized = true;
        
        // Log initialization if debug mode is enabled
        if ($this->config->get('debug_mode', false)) {
            error_log('Arabic Search Enhancement: Plugin initialized successfully');
        }
    }
    
    /**
     * Initialize WordPress hooks
     *
     * @return void
     */
    private function init_hooks(): void {
        // Search query modification hooks
        add_action('pre_get_posts', [$this->search_modifier, 'modify_query_params']);
        add_filter('posts_search', [$this->search_modifier, 'modify_search_sql'], 10, 2);
        
        // Performance monitoring
        if ($this->config->get('performance_monitoring', false)) {
            add_action('wp_footer', [$this, 'output_performance_data']);
        }
        
        // Plugin lifecycle hooks
        register_activation_hook($this->get_plugin_file(), [$this, 'activate']);
        register_deactivation_hook($this->get_plugin_file(), [$this, 'deactivate']);
    }
    
    /**
     * Initialize admin interface
     *
     * @return void
     */
    private function init_admin(): void {
        if (is_admin()) {
            $this->settings_page->init_hooks();
        }
    }
    
    /**
     * Plugin activation hook
     *
     * @return void
     */
    public function activate(): void {
        try {
            // Initialize default configuration
            $this->config->initialize_defaults();
            
            // Verify that critical options were created
            $critical_options = ['enable_enhancement', 'search_post_types', 'search_excerpt'];
            foreach ($critical_options as $option) {
                if ($this->config->get($option) === null) {
                    throw new \Exception("Failed to initialize critical option: {$option}");
                }
            }
            
            // Clear caches
            $this->clear_caches();
            
            // Test that we can create normalized SQL (this tests the normalizer)
            $normalizer = PluginFactory::create_text_normalizer();
            $test_sql = $normalizer->get_normalization_sql('test_column');
            if (empty($test_sql) || !is_string($test_sql)) {
                throw new \Exception('Text normalizer is not working correctly');
            }
            
            // Log activation
            if ($this->config->get('debug_mode', false)) {
                error_log('Arabic Search Enhancement: Plugin activated successfully');
            }
            
        } catch (\Throwable $e) {
            error_log('Arabic Search Enhancement Activation Error: ' . $e->getMessage());
            
            // Don't deactivate here - let the main activation function handle it
            throw $e;
        }
    }
    
    /**
     * Plugin deactivation hook
     *
     * @return void
     */
    public function deactivate(): void {
        try {
            // Clear caches
            $this->clear_caches();
            
            // Log deactivation
            if ($this->config->get('debug_mode', false)) {
                error_log('Arabic Search Enhancement: Plugin deactivated');
            }
            
        } catch (\Exception $e) {
            error_log('Arabic Search Enhancement Deactivation Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Clear all plugin caches
     *
     * @return void
     */
    private function clear_caches(): void {
        wp_cache_flush();
        
        // Clear configuration cache
        $this->config->clear_cache();
    }
    
    /**
     * Get plugin file path
     *
     * @return string Plugin file path
     */
    private function get_plugin_file(): string {
        return dirname(__DIR__, 2) . '/wp-plg-arabic-search-enhancement.php';
    }
    
    /**
     * Track performance metric
     *
     * @param string $metric Metric name
     * @param mixed $value Metric value
     * @return void
     */
    public function track_performance(string $metric, $value): void {
        if (!$this->config->get('performance_monitoring', false)) {
            return;
        }
        
        $this->performance_data[$metric] = $value;
    }
    
    /**
     * Output performance data in footer (for debugging)
     *
     * @return void
     */
    public function output_performance_data(): void {
        if (empty($this->performance_data) || !current_user_can('manage_options')) {
            return;
        }
        
        echo "<!-- Arabic Search Enhancement Performance Data:\n";
        foreach ($this->performance_data as $metric => $value) {
            echo esc_html(sprintf("%s: %s\n", $metric, print_r($value, true)));
        }
        echo "-->\n";
    }
    
    /**
     * Get plugin version
     *
     * @return string Plugin version
     */
    public function get_version(): string {
        return Configuration::VERSION;
    }
    
    /**
     * Get configuration instance
     *
     * @return ConfigurationInterface
     */
    public function get_config(): ConfigurationInterface {
        return $this->config;
    }
    
    /**
     * Check if plugin is properly initialized
     *
     * @return bool True if initialized
     */
    public function is_initialized(): bool {
        return $this->initialized;
    }
    
    /**
     * Handle plugin errors gracefully
     *
     * @param \Exception $exception Exception to handle
     * @param string $context Error context
     * @return void
     */
    public function handle_error(\Exception $exception, string $context = ''): void {
        $error_message = sprintf(
            'Arabic Search Enhancement Error [%s]: %s',
            $context,
            $exception->getMessage()
        );
        
        error_log($error_message);
        
        if ($this->config->get('debug_mode', false)) {
            error_log('Stack trace: ' . $exception->getTraceAsString());
        }
    }
}