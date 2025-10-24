<?php
/**
 * WordPress Cache Wrapper
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

use ArabicSearchEnhancement\Interfaces\CacheInterface;
use ArabicSearchEnhancement\Interfaces\ConfigurationInterface;

class Cache implements CacheInterface {
    
    /**
     * Configuration instance
     *
     * @var ConfigurationInterface
     */
    private $config;
    
    /**
     * Cache group
     *
     * @var string
     */
    private $cache_group;
    
    /**
     * Constructor
     *
     * @param ConfigurationInterface $config Configuration instance
     */
    public function __construct(ConfigurationInterface $config) {
        $this->config = $config;
        $this->cache_group = Configuration::CACHE_GROUP;
    }
    
    /**
     * Get cached value
     *
     * @param string $key Cache key
     * @param mixed $default Default value if not found
     * @return mixed Cached value or default
     */
    public function get(string $key, $default = null) {
        $cache_key = $this->get_cache_key($key);
        $value = wp_cache_get($cache_key, $this->cache_group);
        
        return $value !== false ? $value : $default;
    }
    
    /**
     * Set cached value
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int $expiration Expiration time in seconds
     * @return bool Success status
     */
    public function set(string $key, $value, int $expiration = 3600): bool {
        $cache_key = $this->get_cache_key($key);
        
        if ($expiration <= 0) {
            $expiration = $this->config->get('cache_expiration', 3600);
        }
        
        return wp_cache_set($cache_key, $value, $this->cache_group, $expiration);
    }
    
    /**
     * Delete cached value
     *
     * @param string $key Cache key
     * @return bool Success status
     */
    public function delete(string $key): bool {
        $cache_key = $this->get_cache_key($key);
        return wp_cache_delete($cache_key, $this->cache_group);
    }
    
    /**
     * Clear all cached values for this plugin
     *
     * @return bool Success status
     */
    public function flush(): bool {
        return wp_cache_flush();
    }
    
    /**
     * Get normalized cache key
     *
     * @param string $key Original key
     * @return string Normalized cache key
     */
    private function get_cache_key(string $key): string {
        return sanitize_key($key);
    }
    
    /**
     * Get cache key with context
     *
     * @param string $key Base key
     * @param array $context Additional context for key generation
     * @return string Context-aware cache key
     */
    public function get_contextual_key(string $key, array $context = []): string {
        if (empty($context)) {
            return $key;
        }
        
        ksort($context);
        $context_hash = md5(serialize($context));
        
        return $key . '_' . $context_hash;
    }
}