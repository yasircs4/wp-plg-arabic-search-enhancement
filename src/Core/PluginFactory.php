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
     * Create main plugin instance
     *
     * @return Plugin
     */
    public static function create_plugin(): Plugin {
        if (!isset(self::$instances['plugin'])) {
            $config = self::create_configuration();
            $search_modifier = self::create_search_query_modifier();
            $settings_page = self::create_settings_page($config);
            
            self::$instances['plugin'] = new Plugin($config, $search_modifier, $settings_page);
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