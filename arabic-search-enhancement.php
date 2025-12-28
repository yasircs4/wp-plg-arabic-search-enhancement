<?php
/**
 * Plugin Name: Arabic Search Enhancement
 * Plugin Name (Arabic): تحسين البحث العربي
 * Plugin URI: https://maisra.net/arabic-search-enhancement
 * Description: Improves WordPress search for Arabic content by normalizing Arabic text variations, diacritics, and letter forms
 * Description (Arabic): يحسن البحث في ووردبريس للمحتوى العربي من خلال توحيد تنويعات النصوص العربية وعلامات التشكيل وأشكال الحروف
 * Version: 1.4.7
 * Author: yasircs4
 * Author URI: https://maisra.net/
 * License: GPL v2 or later
 * Text Domain: arabic-search-enhancement
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * 
 * Copyright (C) 2025 yasircs4
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ARABIC_SEARCH_ENHANCEMENT_VERSION', '1.4.7');
define('ARABIC_SEARCH_ENHANCEMENT_PLUGIN_FILE', __FILE__);
define('ARABIC_SEARCH_ENHANCEMENT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ARABIC_SEARCH_ENHANCEMENT_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load autoloader
require_once ARABIC_SEARCH_ENHANCEMENT_PLUGIN_DIR . 'src/Autoloader.php';

use ArabicSearchEnhancement\Autoloader;
use ArabicSearchEnhancement\Core\PluginFactory;
use ArabicSearchEnhancement\Core\Plugin;
use ArabicSearchEnhancement\Core\Configuration;

/**
 * Initialize the plugin autoloader
 */
function arabic_search_enhancement_init_autoloader(): bool {
    try {
        $autoloader = Autoloader::get_instance(ARABIC_SEARCH_ENHANCEMENT_PLUGIN_DIR . 'src');
        return $autoloader->register();
    } catch (\Exception $e) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log('Arabic Search Enhancement Autoloader Error: ' . $e->getMessage());
        }
        return false;
    }
}

/**
 * Check plugin requirements
 */
function arabic_search_enhancement_check_requirements(): bool {
    // Check PHP version
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        add_action('admin_notices', function() {
            printf(
                '<div class="notice notice-error"><p>%s %s</p></div>',
                // translators: %s: PHP version
                esc_html__('Arabic Search Enhancement requires PHP 7.4 or higher. Your version:', 'arabic-search-enhancement'),
                esc_html(PHP_VERSION)
            );
        });
        return false;
    }
    
    // Check WordPress version
    if (!isset($GLOBALS['wp_version']) || version_compare($GLOBALS['wp_version'], '5.0', '<')) {
        add_action('admin_notices', function() {
            printf(
                '<div class="notice notice-error"><p>%s</p></div>',
                esc_html__('Arabic Search Enhancement requires WordPress 5.0 or higher.', 'arabic-search-enhancement')
            );
        });
        return false;
    }
    
    // Check for required WordPress functions
    $required_functions = ['add_option', 'get_option', 'update_option', 'delete_option', 'wp_cache_get', 'wp_cache_set'];
    foreach ($required_functions as $function) {
        if (!function_exists($function)) {
            add_action('admin_notices', function() use ($function) {
                // translators: %s: WordPress function name
                printf(
                    '<div class="notice notice-error"><p>%s</p></div>',
                    sprintf(
                        // translators: %s: WordPress function name
                        esc_html__('Arabic Search Enhancement requires WordPress function %s which is not available.', 'arabic-search-enhancement'),
                        esc_html($function)
                    )
                );
            });
            return false;
        }
    }
    
    return true;
}
    
/**
 * Get the main plugin instance
 */
function arabic_search_enhancement_get_plugin(): ?Plugin {
    static $plugin = null;
    static $failed = false;
    
    // Don't try again if we already failed
    if ($failed) {
        return null;
    }
    
    if (null === $plugin) {
        try {
            // Double-check that essential classes are available
            if (!class_exists('ArabicSearchEnhancement\\Core\\PluginFactory')) {
                throw new \Exception('PluginFactory class not available');
            }
            
            $plugin = PluginFactory::create_plugin();
            
            if (!$plugin instanceof Plugin) {
                throw new \Exception('Failed to create valid plugin instance');
            }
            
        } catch (\Throwable $e) {
            $failed = true;
        if (defined('WP_DEBUG') && WP_DEBUG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log('Arabic Search Enhancement Plugin Creation Error: ' . $e->getMessage());
        }
            return null;
        }
    }
    
    return $plugin;
}
    
/**
 * Initialize the plugin
 */
function arabic_search_enhancement_init(): void {
    try {
        // Check requirements first
        if (!arabic_search_enhancement_check_requirements()) {
            return;
        }
        
        // Initialize autoloader
        if (!arabic_search_enhancement_init_autoloader()) {
            add_action('admin_notices', function() {
                printf(
                    '<div class="notice notice-error"><p>%s</p></div>',
                    esc_html__('Arabic Search Enhancement: Failed to initialize autoloader.', 'arabic-search-enhancement')
                );
            });
            return;
        }
        
        // Get and initialize plugin
        $plugin = arabic_search_enhancement_get_plugin();
        
        if ($plugin) {
            $plugin->init();
        } else {
            add_action('admin_notices', function() {
                printf(
                    '<div class="notice notice-error"><p>%s</p></div>',
                    esc_html__('Arabic Search Enhancement: Failed to initialize plugin.', 'arabic-search-enhancement')
                );
            });
        }
        
    } catch (\Throwable $e) {
        add_action('admin_notices', function() use ($e) {
            // translators: %s: error message text
            printf(
                '<div class="notice notice-error"><p>%s</p></div>',
                sprintf(
                    // translators: %s: error message text
                    esc_html__('Arabic Search Enhancement initialization error: %s', 'arabic-search-enhancement'),
                    esc_html($e->getMessage())
                )
            );
        });
        
        // Log the error for debugging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log('Arabic Search Enhancement: ' . $e->getMessage());
        }
    }
}

/**
 * Plugin activation hook
 */
function arabic_search_enhancement_activate(): void {
    try {
        // Check requirements first
        if (!arabic_search_enhancement_check_requirements()) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(
                esc_html__('Arabic Search Enhancement cannot be activated due to unmet requirements.', 'arabic-search-enhancement'),
                esc_html__('Plugin Activation Error', 'arabic-search-enhancement'),
                ['response' => 500, 'back_link' => true]
            );
        }
        
        // Initialize autoloader
        if (!arabic_search_enhancement_init_autoloader()) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(
                esc_html__('Arabic Search Enhancement: Failed to initialize autoloader.', 'arabic-search-enhancement'),
                esc_html__('Plugin Activation Error', 'arabic-search-enhancement'),
                ['response' => 500, 'back_link' => true]
            );
        }
        
        // Test that essential classes can be loaded
        if (!class_exists('ArabicSearchEnhancement\\Core\\Configuration') ||
            !class_exists('ArabicSearchEnhancement\\Core\\Plugin')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(
                esc_html__('Arabic Search Enhancement: Essential plugin classes could not be loaded.', 'arabic-search-enhancement'),
                esc_html__('Plugin Activation Error', 'arabic-search-enhancement'),
                ['response' => 500, 'back_link' => true]
            );
        }
        
        // Create and activate plugin
        $plugin = arabic_search_enhancement_get_plugin();
        if ($plugin) {
            $plugin->activate();
        } else {
            throw new \Exception('Failed to create plugin instance');
        }
        
    } catch (\Throwable $e) {
        deactivate_plugins(plugin_basename(__FILE__));
        // translators: %s: error message text
        wp_die(
            sprintf(
                // translators: %s: error message text
                esc_html__('Arabic Search Enhancement activation failed: %s', 'arabic-search-enhancement'),
                esc_html($e->getMessage())
            ),
            esc_html__('Plugin Activation Error', 'arabic-search-enhancement'),
            ['response' => 500, 'back_link' => true]
        );
    }
}

/**
 * Plugin deactivation hook
 */
function arabic_search_enhancement_deactivate(): void {
    $plugin = arabic_search_enhancement_get_plugin();
    if ($plugin) {
        $plugin->deactivate();
    }
}

// Register hooks
add_action('plugins_loaded', 'arabic_search_enhancement_init');
register_activation_hook(__FILE__, 'arabic_search_enhancement_activate');
register_deactivation_hook(__FILE__, 'arabic_search_enhancement_deactivate');

// Register uninstall hook - pointing to separate file for WordPress.org compliance
register_uninstall_hook(__FILE__, 'arabic_search_enhancement_uninstall');

/**
 * Plugin uninstall cleanup
 */
function arabic_search_enhancement_uninstall(): void {
    // Clean up options on uninstall
    $options = [
        'ase_enable_enhancement',
        'ase_search_post_types', 
        'ase_search_excerpt',
        'ase_posts_per_page',
        'ase_cache_expiration',
        'ase_debug_mode',
        'ase_performance_monitoring',
        'ase_analytics_enabled',
    ];
    
    foreach ($options as $option) {
        delete_option($option);
    }
    
    // Clean up transients
    global $wpdb;
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_ase_%'");
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_ase_%'");
    
    // Clear caches
    wp_cache_flush();
}

/**
 * Backward compatibility functions
 * These functions maintain compatibility with the old API
 */

/**
 * Legacy function for getting plugin instance
 * @deprecated 1.1.0 Use arabic_search_enhancement_get_plugin() instead
 * @return Plugin|null
 */
function Arabic_Search_Enhancement() {
    _deprecated_function(__FUNCTION__, '1.1.0', 'arabic_search_enhancement_get_plugin()');
    return arabic_search_enhancement_get_plugin();
}

/**
 * Legacy static method access
 * @deprecated 1.1.0 Use arabic_search_enhancement_get_plugin() instead
 */
if (!class_exists('Arabic_Search_Enhancement')) {
    class Arabic_Search_Enhancement {
        /**
         * Get plugin instance
         * @deprecated 1.1.0 Use arabic_search_enhancement_get_plugin() instead
         * @return Plugin|null
         */
        public static function get_instance() {
            _deprecated_function(__METHOD__, '1.1.0', 'arabic_search_enhancement_get_plugin()');
            return arabic_search_enhancement_get_plugin();
        }
    }
}