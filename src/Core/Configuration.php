<?php
/**
 * Plugin Configuration Manager
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

class Configuration implements ConfigurationInterface {
    
    /**
     * Plugin version
     */
    public const VERSION = '1.1.0';
    
    /**
     * Plugin text domain
     */
    public const TEXT_DOMAIN = 'arabic-search-enhancement';
    
    /**
     * Option prefix
     */
    public const OPTION_PREFIX = 'ase_';
    
    /**
     * Supported languages
     */
    public const SUPPORTED_LANGUAGES = [
        'ar' => 'العربية',
        'en_US' => 'English',
        'en_GB' => 'English (UK)',
    ];
    
    /**
     * Cache group
     */
    public const CACHE_GROUP = 'arabic_search_enhancement';
    
    /**
     * Default configuration values
     */
    private const DEFAULTS = [
        'enable_enhancement' => true,
        'search_post_types' => ['post', 'page'],
        'search_excerpt' => true,
        'posts_per_page' => null, // Will use WordPress default
        'cache_expiration' => 3600,
        'debug_mode' => false,
        'performance_monitoring' => false,
        'analytics_enabled' => false, // Privacy-first: Default to disabled
    ];
    
    /**
     * Configuration cache
     *
     * @var array
     */
    private $config_cache = [];
    
    /**
     * Whether configuration has been loaded
     *
     * @var bool
     */
    private $loaded = false;
    
    /**
     * Get configuration value
     *
     * @param string $key Configuration key
     * @param mixed $default Default value
     * @return mixed Configuration value
     */
    public function get(string $key, $default = null) {
        $this->load_if_needed();
        
        $option_key = $this->get_option_key($key);
        
        if (array_key_exists($option_key, $this->config_cache)) {
            return $this->config_cache[$option_key];
        }
        
        // Get default value
        $default_value = $default ?? (self::DEFAULTS[$key] ?? null);
        
        // Special case for posts_per_page
        if ($key === 'posts_per_page' && $default_value === null) {
            $wp_posts_per_page = get_option('posts_per_page', 10);
            $default_value = is_numeric($wp_posts_per_page) ? (int) $wp_posts_per_page : 10;
        }
        
        $value = get_option($option_key, $default_value);
        $this->config_cache[$option_key] = $value;
        
        return $value;
    }
    
    /**
     * Set configuration value
     *
     * @param string $key Configuration key
     * @param mixed $value Configuration value
     * @return bool Success status
     */
    public function set(string $key, $value): bool {
        $option_key = $this->get_option_key($key);
        $sanitized_value = $this->sanitize_value($key, $value);
        
        $success = update_option($option_key, $sanitized_value);
        
        if ($success) {
            $this->config_cache[$option_key] = $sanitized_value;
        }
        
        return $success;
    }
    
    /**
     * Get all configuration values
     *
     * @return array All configuration values
     */
    public function get_all(): array {
        $config = [];
        
        foreach (array_keys(self::DEFAULTS) as $key) {
            $config[$key] = $this->get($key);
        }
        
        return $config;
    }
    
    /**
     * Check if configuration key exists
     *
     * @param string $key Configuration key
     * @return bool True if key exists
     */
    public function has(string $key): bool {
        return array_key_exists($key, self::DEFAULTS);
    }
    
    /**
     * Get WordPress option key
     *
     * @param string $key Configuration key
     * @return string WordPress option key
     */
    private function get_option_key(string $key): string {
        return self::OPTION_PREFIX . $key;
    }
    
    /**
     * Load configuration if not already loaded
     *
     * @return void
     */
    private function load_if_needed(): void {
        if ($this->loaded) {
            return;
        }
        
        // Mark as loaded first to prevent circular calls
        $this->loaded = true;
        
        // Pre-load commonly used options
        $common_keys = ['enable_enhancement', 'search_post_types', 'search_excerpt'];
        
        foreach ($common_keys as $key) {
            $this->get($key);
        }
    }
    
    /**
     * Sanitize configuration value
     *
     * @param string $key Configuration key
     * @param mixed $value Value to sanitize
     * @return mixed Sanitized value
     */
    private function sanitize_value(string $key, $value) {
        switch ($key) {
            case 'enable_enhancement':
            case 'search_excerpt':
            case 'debug_mode':
            case 'performance_monitoring':
                return (bool) $value;
                
            case 'search_post_types':
                return $this->sanitize_post_types($value);
                
            case 'posts_per_page':
            case 'cache_expiration':
                return absint($value);
                
            default:
                return sanitize_text_field($value);
        }
    }
    
    /**
     * Sanitize post types array
     *
     * @param mixed $value Post types value
     * @return array Sanitized post types array
     */
    private function sanitize_post_types($value): array {
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
     * Initialize default options
     *
     * @return void
     */
    public function initialize_defaults(): void {
        foreach (self::DEFAULTS as $key => $default_value) {
            $option_key = $this->get_option_key($key);
            
            if (get_option($option_key) === false) {
                $value = $default_value;
                
                // Special case for posts_per_page
                if ($key === 'posts_per_page' && $value === null) {
                    $wp_posts_per_page = get_option('posts_per_page', 10);
                    $value = is_numeric($wp_posts_per_page) ? (int) $wp_posts_per_page : 10;
                }
                
                add_option($option_key, $value);
            }
        }
    }
    
    /**
     * Clear configuration cache
     *
     * @return void
     */
    public function clear_cache(): void {
        $this->config_cache = [];
        $this->loaded = false;
    }
    
    /**
     * Check if current language is RTL
     *
     * @return bool True if RTL language
     */
    public function is_rtl(): bool {
        $locale = get_locale();
        $rtl_languages = ['ar', 'he', 'fa', 'ur'];
        
        foreach ($rtl_languages as $rtl_lang) {
            if (strpos($locale, $rtl_lang) === 0) {
                return true;
            }
        }
        
        return is_rtl();
    }
    
    /**
     * Get current language code
     *
     * @return string Language code
     */
    public function get_language(): string {
        $locale = get_locale();
        return substr($locale, 0, 2);
    }
    
    /**
     * Check if plugin supports current language
     *
     * @return bool True if supported
     */
    public function is_language_supported(): bool {
        $locale = get_locale();
        return array_key_exists($locale, self::SUPPORTED_LANGUAGES) || 
               array_key_exists(substr($locale, 0, 2), self::SUPPORTED_LANGUAGES);
    }
}