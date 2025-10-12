<?php
/**
 * PHPUnit bootstrap file for Arabic Search Enhancement Plugin
 *
 * @copyright 2024 Yasir Najeep
 * @license   GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

// Define plugin constants for testing
define('ARABIC_SEARCH_ENHANCEMENT_PLUGIN_DIR', dirname(__DIR__) . '/');
define('ARABIC_SEARCH_ENHANCEMENT_PLUGIN_FILE', dirname(__DIR__) . '/wp-plg-arabic-search-enhancement.php');

// WordPress test environment (if available)
if (file_exists(dirname(__FILE__) . '/wordpress-tests-lib/includes/functions.php')) {
    require_once dirname(__FILE__) . '/wordpress-tests-lib/includes/functions.php';
    
    function _manually_load_plugin() {
        require ARABIC_SEARCH_ENHANCEMENT_PLUGIN_FILE;
    }
    tests_add_filter('muplugins_loaded', '_manually_load_plugin');
    
    require dirname(__FILE__) . '/wordpress-tests-lib/includes/bootstrap.php';
} else {
    // Mock WordPress functions for unit testing
    require_once __DIR__ . '/mocks/wordpress-mocks.php';
}

// Use Composer autoloader for testing
require_once dirname(__DIR__) . '/vendor/autoload.php';