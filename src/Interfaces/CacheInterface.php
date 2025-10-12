<?php
/**
 * Cache Interface
 *
 * @package ArabicSearchEnhancement
 * @since 1.1.0
 * @author Yasser Nageep Maisra <info@maisra.net>
 * @copyright 2025 Yasser Nageep Maisra
 * @license GPL-2.0-or-later
 * @link https://maisra.net
 */

namespace ArabicSearchEnhancement\Interfaces;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

interface CacheInterface {
    /**
     * Get cached value
     *
     * @param string $key Cache key
     * @param mixed $default Default value if not found
     * @return mixed Cached value or default
     */
    public function get(string $key, $default = null);

    /**
     * Set cached value
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int $expiration Expiration time in seconds
     * @return bool Success status
     */
    public function set(string $key, $value, int $expiration = 3600): bool;

    /**
     * Delete cached value
     *
     * @param string $key Cache key
     * @return bool Success status
     */
    public function delete(string $key): bool;

    /**
     * Clear all cached values for this plugin
     *
     * @return bool Success status
     */
    public function flush(): bool;
}