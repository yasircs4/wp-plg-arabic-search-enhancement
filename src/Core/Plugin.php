<?php
/**
 * Main Plugin Class
 *
 * @package ArabicSearchEnhancement
 * @since 1.1.0
 * @author yasircs4 <yasircs4@live.com>
 * @copyright 2025 yasircs4
 * @license GPL-2.0-or-later
 * @link https://yasircs4.github.io/wp-plg-arabic-search-enhancement/
 */

namespace ArabicSearchEnhancement\Core;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

use ArabicSearchEnhancement\Interfaces\ConfigurationInterface;
use ArabicSearchEnhancement\Interfaces\SearchQueryModifierInterface;
use ArabicSearchEnhancement\Admin\SettingsPage;
use ArabicSearchEnhancement\Admin\SearchAnalyticsDashboard;
use ArabicSearchEnhancement\API\RestApiController;
use WP_Query;

class Plugin {
    
    /**
     * Plugin instance
     *
        private const TABLE_SCHEMA_VERSION = '2';
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
     * Analytics dashboard instance
     *
     * @var SearchAnalyticsDashboard
     */
    private $analytics_dashboard;
    
    /**
     * REST API controller instance
     *
     * @var RestApiController
     */
    private $rest_api_controller;

    /**
     * Performance optimizer instance.
     */
    private ?PerformanceOptimizer $performance_optimizer;
    
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
     * @param SearchAnalyticsDashboard|null $analytics_dashboard Analytics dashboard instance
     * @param RestApiController|null $rest_api_controller REST API controller instance
     */
    public function __construct(
        ConfigurationInterface $config,
        SearchQueryModifierInterface $search_modifier,
        SettingsPage $settings_page,
        ?SearchAnalyticsDashboard $analytics_dashboard = null,
        ?RestApiController $rest_api_controller = null,
        ?PerformanceOptimizer $performance_optimizer = null
    ) {
        $this->config = $config;
        $this->search_modifier = $search_modifier;
        $this->settings_page = $settings_page;
        $this->analytics_dashboard = $analytics_dashboard;
        $this->rest_api_controller = $rest_api_controller;
        $this->performance_optimizer = $performance_optimizer;
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
        
        $this->ensure_database_tables();
        $this->init_hooks();
        $this->init_admin();
        
        $this->initialized = true;
        
        // Log initialization if debug mode is enabled
        if ($this->config->get('debug_mode', false)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Arabic Search Enhancement: Plugin initialized successfully');
        }
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
    add_filter('posts_results', [$this, 'track_search_results'], 10, 2);
        
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
            
            // Initialize analytics dashboard if available
            if ($this->analytics_dashboard) {
                $this->analytics_dashboard->init_hooks();
            }
        }
        
        // Initialize REST API controller if available
        if ($this->rest_api_controller) {
            // REST API is initialized automatically via rest_api_init hook in the controller
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
            
            // Ensure required database tables are present
            $this->ensure_database_tables(true);

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
                if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Arabic Search Enhancement: Plugin activated successfully');
            }
            }
            
        } catch (\Throwable $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Arabic Search Enhancement Activation Error: ' . $e->getMessage());
            }
            
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
                if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Arabic Search Enhancement: Plugin deactivated');
            }
            }
            
        } catch (\Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Arabic Search Enhancement Deactivation Error: ' . $e->getMessage());
            }
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
     * Ensure performance-related tables are available.
     *
     * @param bool $force When true, always attempt to (re)create the tables
     */
    private function ensure_database_tables(bool $force = false): void {
        if (!$this->performance_optimizer) {
            return;
        }

        $status_option = 'ase_tables_version';
        $current_version = get_option($status_option);

        if ($force || $current_version !== Configuration::VERSION) {
            $this->performance_optimizer->create_optimization_tables();
            update_option($status_option, Configuration::VERSION);
        }
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
            if (defined('WP_DEBUG') && WP_DEBUG) {
                echo esc_html(sprintf("%s: %s\n", $metric, print_r($value, true)));
            }
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
     * Get configuration instance (alias for compatibility)
     *
     * @return ConfigurationInterface
     */
    public function get_configuration(): ConfigurationInterface {
        return $this->config;
    }
    
    /**
     * Get search query modifier instance
     *
     * @return SearchQueryModifierInterface
     */
    public function get_search_modifier(): SearchQueryModifierInterface {
        return $this->search_modifier;
    }
    
    /**
     * Get settings page instance
     *
     * @return SettingsPage
     */
    public function get_settings_page(): SettingsPage {
        return $this->settings_page;
    }
    
    /**
     * Get analytics dashboard instance
     *
     * @return SearchAnalyticsDashboard|null
     */
    public function get_analytics_dashboard(): ?SearchAnalyticsDashboard {
        return $this->analytics_dashboard;
    }
    
    /**
     * Get REST API controller instance
     *
     * @return RestApiController|null
     */
    public function get_rest_api_controller(): ?RestApiController {
        return $this->rest_api_controller;
    }

    /**
     * Capture search results to feed analytics when enabled.
     *
     * @param array $posts Query results
     * @param WP_Query $query WordPress query object
     * @return array Original posts array
     */
    public function track_search_results(array $posts, WP_Query $query): array {
        if (!$this->performance_optimizer || is_admin() || !$query->is_main_query() || !$query->is_search()) {
            return $posts;
        }

        $search_term = $query->get('s');

        if (!is_string($search_term) || $search_term === '') {
            return $posts;
        }

        try {
            $this->performance_optimizer->track_search_event($search_term, count($posts));
        } catch (\Throwable $exception) {
            if ($this->config->get('debug_mode', false)) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('Arabic Search Enhancement Analytics Error: ' . $exception->getMessage());
                }
            }
        }

        return $posts;
    }
    
    /**
     * Get normalizer instance from search modifier
     *
     * @return mixed
     */
    public function get_normalizer() {
        // Check if we can get the normalizer from the search modifier
        if (method_exists($this->search_modifier, 'get_normalizer')) {
            return $this->search_modifier->get_normalizer();
        }
        
        // Fallback: create a simple normalizer for testing
        static $normalizer = null;
        if ($normalizer === null) {
            $cache = PluginFactory::create_cache();
            $normalizer = new ArabicTextNormalizer($cache);
        }
        return $normalizer;
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
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log($error_message);
        }
        
        if ($this->config->get('debug_mode', false)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Stack trace: ' . $exception->getTraceAsString());
            }
        }
    }
}