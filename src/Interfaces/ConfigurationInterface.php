<?php
/**
 * Configuration Interface
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

interface ConfigurationInterface {
    /**
     * Get plugin configuration value
     *
     * @param string $key Configuration key
     * @param mixed $default Default value
     * @return mixed Configuration value
     */
    public function get(string $key, $default = null);

    /**
     * Set plugin configuration value
     *
     * @param string $key Configuration key
     * @param mixed $value Configuration value
     * @return bool Success status
     */
    public function set(string $key, $value): bool;

    /**
     * Get all configuration values
     *
     * @return array All configuration values
     */
    public function get_all(): array;

    /**
     * Check if configuration key exists
     *
     * @param string $key Configuration key
     * @return bool True if key exists
     */
    public function has(string $key): bool;
}