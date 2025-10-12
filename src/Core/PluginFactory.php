<?php
/**
 * Plugin Factory
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
use ArabicSearchEnhancement\Interfaces\CacheInterface;
use ArabicSearchEnhancement\Interfaces\TextNormalizerInterface;
use ArabicSearchEnhancement\Interfaces\SearchQueryModifierInterface;
use ArabicSearchEnhancement\Admin\SettingsPage;
use ArabicSearchEnhancement\Admin\SearchAnalyticsDashboard;
use ArabicSearchEnhancement\API\RestApiController;
use ArabicSearchEnhancement\Utils\RepositorySubmissionHelper;

class PluginFactory {
    
    /**
     * Singleton instances
     *
     * @var array
     */
    private static $instances = [];
    
    /**
     * Create or get configuration instance
     *
     * @return ConfigurationInterface
     */
    public static function create_configuration(): ConfigurationInterface {
        if (!isset(self::$instances['configuration'])) {
            self::$instances['configuration'] = new Configuration();
        }
        
        return self::$instances['configuration'];
    }
    
    /**
     * Create or get cache instance
     *
     * @param ConfigurationInterface|null $config Configuration instance
     * @return CacheInterface
     */
    public static function create_cache(?ConfigurationInterface $config = null): CacheInterface {
        if (!isset(self::$instances['cache'])) {
            $config = $config ?? self::create_configuration();
            self::$instances['cache'] = new Cache($config);
        }
        
        return self::$instances['cache'];
    }
    
    /**
     * Create or get text normalizer instance
     *
     * @param CacheInterface|null $cache Cache instance
     * @return TextNormalizerInterface
     */
    public static function create_text_normalizer(?CacheInterface $cache = null): TextNormalizerInterface {
        if (!isset(self::$instances['text_normalizer'])) {
            $cache = $cache ?? self::create_cache();
            self::$instances['text_normalizer'] = new ArabicTextNormalizer($cache);
        }
        
        return self::$instances['text_normalizer'];
    }
    
    /**
     * Create or get search query modifier instance
     *
     * @param TextNormalizerInterface|null $normalizer Text normalizer instance
     * @param ConfigurationInterface|null $config Configuration instance
     * @param \wpdb|null $wpdb WordPress database instance
     * @return SearchQueryModifierInterface
     */
    public static function create_search_query_modifier(
        ?TextNormalizerInterface $normalizer = null,
        ?ConfigurationInterface $config = null,
        ?\wpdb $wpdb = null
    ): SearchQueryModifierInterface {
        if (!isset(self::$instances['search_query_modifier'])) {
            $normalizer = $normalizer ?? self::create_text_normalizer();
            $config = $config ?? self::create_configuration();
            $wpdb = $wpdb ?? $GLOBALS['wpdb'];
            
            self::$instances['search_query_modifier'] = new SearchQueryModifier($normalizer, $config, $wpdb);
        }
        
        return self::$instances['search_query_modifier'];
    }
    
    /**
     * Create settings page instance
     *
     * @param ConfigurationInterface|null $config Configuration instance
     * @return SettingsPage
     */
    public static function create_settings_page(?ConfigurationInterface $config = null): SettingsPage {
        $config = $config ?? self::create_configuration();
        return new SettingsPage($config);
    }
    
    /**
     * Create advanced search features instance
     *
     * @param CacheInterface|null $cache Cache instance
     * @return AdvancedSearchFeatures
     */
    public static function create_advanced_search_features(
        ?CacheInterface $cache = null
    ): AdvancedSearchFeatures {
        if (!isset(self::$instances['advanced_search'])) {
            $cache = $cache ?? self::create_cache();
            
            self::$instances['advanced_search'] = new AdvancedSearchFeatures($cache);
        }
        
        return self::$instances['advanced_search'];
    }
    
    /**
     * Create performance optimizer instance
     *
     * @param CacheInterface|null $cache Cache instance
     * @param ConfigurationInterface|null $config Configuration instance
     * @return PerformanceOptimizer
     */
    public static function create_performance_optimizer(
        ?CacheInterface $cache = null,
        ?ConfigurationInterface $config = null
    ): PerformanceOptimizer {
        if (!isset(self::$instances['performance_optimizer'])) {
            $cache = $cache ?? self::create_cache();
            $config = $config ?? self::create_configuration();
            
            self::$instances['performance_optimizer'] = new PerformanceOptimizer($cache, $config);
        }
        
        return self::$instances['performance_optimizer'];
    }
    
    /**
     * Create multi-language normalizer instance
     *
     * @param CacheInterface|null $cache Cache instance
     * @return MultiLanguageNormalizer
     */
    public static function create_multi_language_normalizer(
        ?CacheInterface $cache = null
    ): MultiLanguageNormalizer {
        if (!isset(self::$instances['multi_language_normalizer'])) {
            $cache = $cache ?? self::create_cache();
            
            self::$instances['multi_language_normalizer'] = new MultiLanguageNormalizer($cache);
        }
        
        return self::$instances['multi_language_normalizer'];
    }
    
    /**
     * Create analytics dashboard instance
     *
     * @param ConfigurationInterface|null $config Configuration instance
     * @param PerformanceOptimizer|null $optimizer Performance optimizer instance
     * @return SearchAnalyticsDashboard
     */
    public static function create_analytics_dashboard(
        ?ConfigurationInterface $config = null,
        ?PerformanceOptimizer $optimizer = null
    ): SearchAnalyticsDashboard {
        if (!isset(self::$instances['analytics_dashboard'])) {
            $config = $config ?? self::create_configuration();
            $optimizer = $optimizer ?? self::create_performance_optimizer();
            
            self::$instances['analytics_dashboard'] = new SearchAnalyticsDashboard($config, $optimizer);
        }
        
        return self::$instances['analytics_dashboard'];
    }
    
    /**
     * Create REST API controller instance
     *
     * @param ConfigurationInterface|null $config Configuration instance
     * @param ArabicTextNormalizer|null $normalizer Text normalizer instance
     * @param AdvancedSearchFeatures|null $advanced_search Advanced search instance
     * @param PerformanceOptimizer|null $optimizer Performance optimizer instance
     * @param MultiLanguageNormalizer|null $multilang Multi-language normalizer instance
     * @return RestApiController
     */
    public static function create_rest_api_controller(
        ?ConfigurationInterface $config = null,
        ?ArabicTextNormalizer $normalizer = null,
        ?AdvancedSearchFeatures $advanced_search = null,
        ?PerformanceOptimizer $optimizer = null,
        ?MultiLanguageNormalizer $multilang = null
    ): RestApiController {
        if (!isset(self::$instances['rest_api_controller'])) {
            $config = $config ?? self::create_configuration();
            $normalizer = $normalizer ?? self::create_text_normalizer();
            $advanced_search = $advanced_search ?? self::create_advanced_search_features();
            $optimizer = $optimizer ?? self::create_performance_optimizer();
            $multilang = $multilang ?? self::create_multi_language_normalizer();
            
            self::$instances['rest_api_controller'] = new RestApiController(
                $config,
                $normalizer,
                $advanced_search,
                $optimizer,
                $multilang
            );
        }
        
        return self::$instances['rest_api_controller'];
    }
    
    /**
     * Create repository submission helper instance
     *
     * @param ConfigurationInterface|null $config Configuration instance
     * @param string|null $plugin_dir Plugin directory path
     * @return RepositorySubmissionHelper
     */
    public static function create_submission_helper(
        ?ConfigurationInterface $config = null,
        ?string $plugin_dir = null
    ): RepositorySubmissionHelper {
        if (!isset(self::$instances['submission_helper'])) {
            $config = $config ?? self::create_configuration();
            $plugin_dir = $plugin_dir ?? ARABIC_SEARCH_ENHANCEMENT_PLUGIN_DIR;
            
            self::$instances['submission_helper'] = new RepositorySubmissionHelper($config, $plugin_dir);
        }
        
        return self::$instances['submission_helper'];
    }
    
    /**
     * Create main plugin instance
     *
     * @return Plugin
     */
    public static function create_plugin(): Plugin {
        if (!isset(self::$instances['plugin'])) {
            $config = self::create_configuration();
            $search_modifier = self::create_search_query_modifier();
            $settings_page = self::create_settings_page($config);
            $analytics_dashboard = self::create_analytics_dashboard($config);
            $rest_api_controller = self::create_rest_api_controller();
            $performance_optimizer = self::create_performance_optimizer();
            
            self::$instances['plugin'] = new Plugin(
                $config, 
                $search_modifier, 
                $settings_page,
                $analytics_dashboard,
                $rest_api_controller,
                $performance_optimizer
            );
        }
        
        return self::$instances['plugin'];
    }
    
    /**
     * Clear all instances (useful for testing)
     *
     * @return void
     */
    public static function clear_instances(): void {
        self::$instances = [];
    }
    
    /**
     * Get instance by key (useful for testing)
     *
     * @param string $key Instance key
     * @return mixed|null Instance or null if not found
     */
    public static function get_instance(string $key) {
        return self::$instances[$key] ?? null;
    }
}