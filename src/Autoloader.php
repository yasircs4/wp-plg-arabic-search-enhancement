<?php
/**
 * Simple Autoloader for Arabic Search Enhancement
 *
 * @package ArabicSearchEnhancement
 * @since 1.1.0
 * @author yasircs4 <yasircs4@live.com>
 * @copyright 2025 yasircs4
 * @license GPL-2.0-or-later
 * @link https://yasircs4.github.io/wp-plg-arabic-search-enhancement/
 */

namespace ArabicSearchEnhancement;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Autoloader {
    
    /**
     * Autoloader instance
     *
     * @var Autoloader|null
     */
    private static $instance = null;
    
    /**
     * Base directory for classes
     *
     * @var string
     */
    private $base_dir;
    
    /**
     * Namespace prefix
     *
     * @var string
     */
    private $namespace_prefix = 'ArabicSearchEnhancement\\';
    
    /**
     * Constructor
     *
     * @param string $base_dir Base directory for classes
     */
    private function __construct(string $base_dir) {
        $this->base_dir = rtrim($base_dir, '/\\') . '/';
    }
    
    /**
     * Get autoloader instance
     *
     * @param string $base_dir Base directory for classes
     * @return Autoloader
     */
    public static function get_instance(string $base_dir): Autoloader {
        if (null === self::$instance) {
            self::$instance = new self($base_dir);
        }
        
        return self::$instance;
    }
    
    /**
     * Register autoloader
     *
     * @return bool True on success, false on failure
     */
    public function register(): bool {
        return spl_autoload_register([$this, 'load_class']);
    }
    
    /**
     * Unregister autoloader
     *
     * @return bool True on success, false on failure
     */
    public function unregister(): bool {
        return spl_autoload_unregister([$this, 'load_class']);
    }
    
    /**
     * Load class file
     *
     * @param string $class_name Fully qualified class name
     * @return bool True if class was loaded, false otherwise
     */
    public function load_class(string $class_name): bool {
        // Validate input
        if (empty($class_name) || !is_string($class_name)) {
            return false;
        }
        
        // Check if class uses our namespace
        if (strpos($class_name, $this->namespace_prefix) !== 0) {
            return false;
        }
        
        // Check if class is already loaded
        if (class_exists($class_name, false) || interface_exists($class_name, false) || trait_exists($class_name, false)) {
            return true;
        }
        
        // Remove namespace prefix
        $relative_class = substr($class_name, strlen($this->namespace_prefix));
        
        // Sanitize the relative class name
        $relative_class = trim($relative_class, '\\');
        
        // Convert namespace separators to directory separators
        $file_path = $this->base_dir . str_replace('\\', DIRECTORY_SEPARATOR, $relative_class) . '.php';
        
        // Normalize the file path
        $file_path = realpath($file_path);
        
        // Security check: ensure the file is within our base directory
        if ($file_path === false || strpos($file_path, realpath($this->base_dir)) !== 0) {
            return false;
        }
        
        // Load the file if it exists and is readable
        if (is_file($file_path) && is_readable($file_path)) {
            try {
                require_once $file_path;
                return class_exists($class_name, false) || interface_exists($class_name, false) || trait_exists($class_name, false);
            } catch (\Throwable $e) {
                // Log error in debug mode
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('Arabic Search Enhancement Autoloader Error: ' . $e->getMessage());
                }
                return false;
            }
        }
        
        return false;
    }
    
    /**
     * Check if a class exists and can be loaded
     *
     * @param string $class_name Fully qualified class name
     * @return bool True if class can be loaded
     */
    public function can_load_class(string $class_name): bool {
        if (strpos($class_name, $this->namespace_prefix) !== 0) {
            return false;
        }
        
        $relative_class = substr($class_name, strlen($this->namespace_prefix));
        $file_path = $this->base_dir . str_replace('\\', '/', $relative_class) . '.php';
        
        return file_exists($file_path);
    }
}