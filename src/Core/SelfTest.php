<?php
/**
 * Arabic Search Enhancement Self-Test
 * 
 * This file provides a simple self-test functionality to verify
 * that the plugin is working correctly.
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

class SelfTest {
    
    /**
     * Run all tests and return results
     *
     * @return array Test results
     */
    public static function run_all_tests(): array {
        $results = [];
        
        $results['autoloader'] = self::test_autoloader();
        $results['configuration'] = self::test_configuration();
        $results['text_normalizer'] = self::test_text_normalizer();
        $results['search_modifier'] = self::test_search_modifier();
        $results['admin_interface'] = self::test_admin_interface();
        
        return $results;
    }
    
    /**
     * Test autoloader functionality
     *
     * @return array Test result
     */
    private static function test_autoloader(): array {
        try {
            // Test that all essential classes can be loaded
            $classes = [
                'ArabicSearchEnhancement\\Core\\Configuration',
                'ArabicSearchEnhancement\\Core\\Cache',
                'ArabicSearchEnhancement\\Core\\ArabicTextNormalizer',
                'ArabicSearchEnhancement\\Core\\SearchQueryModifier',
                'ArabicSearchEnhancement\\Core\\Plugin',
                'ArabicSearchEnhancement\\Admin\\SettingsPage',
            ];
            
            foreach ($classes as $class) {
                if (!class_exists($class)) {
                    return ['status' => 'failed', 'message' => "Class {$class} could not be loaded"];
                }
            }
            
            return ['status' => 'passed', 'message' => 'All classes loaded successfully'];
            
        } catch (\Throwable $e) {
            return ['status' => 'failed', 'message' => 'Autoloader error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Test configuration functionality
     *
     * @return array Test result
     */
    private static function test_configuration(): array {
        try {
            $config = PluginFactory::create_configuration();
            
            // Test getting default values
            $enhancement_enabled = $config->get('enable_enhancement', true);
            if (!is_bool($enhancement_enabled)) {
                return ['status' => 'failed', 'message' => 'Configuration not returning correct type for boolean'];
            }
            
            $post_types = $config->get('search_post_types', ['post', 'page']);
            if (!is_array($post_types)) {
                return ['status' => 'failed', 'message' => 'Configuration not returning correct type for array'];
            }
            
            // Test setting and getting values
            $test_key = 'debug_mode';
            $original_value = $config->get($test_key);
            $test_value = !$original_value;
            
            if (!$config->set($test_key, $test_value)) {
                return ['status' => 'failed', 'message' => 'Configuration set method failed'];
            }
            
            if ($config->get($test_key) !== $test_value) {
                return ['status' => 'failed', 'message' => 'Configuration get after set failed'];
            }
            
            // Restore original value
            $config->set($test_key, $original_value);
            
            return ['status' => 'passed', 'message' => 'Configuration working correctly'];
            
        } catch (\Throwable $e) {
            return ['status' => 'failed', 'message' => 'Configuration error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Test text normalizer functionality
     *
     * @return array Test result
     */
    private static function test_text_normalizer(): array {
        try {
            $normalizer = PluginFactory::create_text_normalizer();
            
            // Test Arabic text normalization
            $test_cases = [
                ['input' => 'قرآن', 'expected_contains' => 'قران'],
                ['input' => 'مُحَمَّد', 'expected_different' => true],
                ['input' => 'الإسلام', 'expected_contains' => 'الاسلام'],
                ['input' => 'سنة', 'expected_contains' => 'سنه'],
            ];
            
            foreach ($test_cases as $test) {
                $normalized = $normalizer->normalize_text($test['input']);
                
                if (isset($test['expected_contains'])) {
                    if (strpos($normalized, $test['expected_contains']) === false && 
                        strpos($test['expected_contains'], $normalized) === false) {
                        return [
                            'status' => 'failed', 
                            'message' => "Normalization failed for '{$test['input']}'. Expected to contain '{$test['expected_contains']}', got '{$normalized}'"
                        ];
                    }
                }
                
                if (isset($test['expected_different']) && $test['expected_different']) {
                    if ($normalized === $test['input']) {
                        return [
                            'status' => 'failed',
                            'message' => "Normalization should have changed '{$test['input']}' but it remained the same"
                        ];
                    }
                }
            }
            
            // Test SQL normalization
            $sql = $normalizer->get_normalization_sql('test_column');
            if (empty($sql) || !is_string($sql) || !str_contains($sql, 'test_column')) {
                return ['status' => 'failed', 'message' => 'SQL normalization failed'];
            }
            
            // Test Arabic detection
            if (!$normalizer->contains_arabic('قرآن')) {
                return ['status' => 'failed', 'message' => 'Arabic detection failed for Arabic text'];
            }
            
            if ($normalizer->contains_arabic('English text')) {
                return ['status' => 'failed', 'message' => 'Arabic detection incorrectly detected English as Arabic'];
            }
            
            return ['status' => 'passed', 'message' => 'Text normalizer working correctly'];
            
        } catch (\Throwable $e) {
            return ['status' => 'failed', 'message' => 'Text normalizer error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Test search query modifier functionality
     *
     * @return array Test result
     */
    private static function test_search_modifier(): array {
        try {
            $modifier = PluginFactory::create_search_query_modifier();
            
            // Create a mock WP_Query for testing
            $wp_query = new \WP_Query();
            $wp_query->set('s', 'قرآن');
            $wp_query->set('search_terms', ['قرآن']);
            
            // Test search SQL modification
            $original_search = ' AND (test_condition)';
            $modified_search = $modifier->modify_search_sql($original_search, $wp_query);
            
            if ($modified_search === $original_search) {
                return ['status' => 'warning', 'message' => 'Search SQL was not modified (may be intentional)'];
            }
            
            if (!is_string($modified_search)) {
                return ['status' => 'failed', 'message' => 'Search modifier returned non-string result'];
            }
            
            return ['status' => 'passed', 'message' => 'Search modifier working correctly'];
            
        } catch (\Throwable $e) {
            return ['status' => 'failed', 'message' => 'Search modifier error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Test admin interface functionality
     *
     * @return array Test result
     */
    private static function test_admin_interface(): array {
        try {
            $config = PluginFactory::create_configuration();
            $settings_page = PluginFactory::create_settings_page($config);
            
            // Test that settings page can be instantiated
            if (!is_object($settings_page)) {
                return ['status' => 'failed', 'message' => 'Settings page could not be created'];
            }
            
            // Test sanitization methods
            $checkbox_result = $settings_page->sanitize_checkbox('1');
            if ($checkbox_result !== true) {
                return ['status' => 'failed', 'message' => 'Checkbox sanitization failed'];
            }
            
            $post_types_result = $settings_page->sanitize_post_types(['post', 'page', 'invalid_type']);
            if (!is_array($post_types_result) || !in_array('post', $post_types_result)) {
                return ['status' => 'failed', 'message' => 'Post types sanitization failed'];
            }
            
            return ['status' => 'passed', 'message' => 'Admin interface working correctly'];
            
        } catch (\Throwable $e) {
            return ['status' => 'failed', 'message' => 'Admin interface error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get test results summary
     *
     * @param array $results Test results
     * @return array Summary
     */
    public static function get_summary(array $results): array {
        $passed = 0;
        $failed = 0;
        $warnings = 0;
        
        foreach ($results as $result) {
            switch ($result['status']) {
                case 'passed':
                    $passed++;
                    break;
                case 'failed':
                    $failed++;
                    break;
                case 'warning':
                    $warnings++;
                    break;
            }
        }
        
        $total = count($results);
        $overall_status = $failed > 0 ? 'failed' : ($warnings > 0 ? 'warning' : 'passed');
        
        return [
            'overall_status' => $overall_status,
            'total_tests' => $total,
            'passed' => $passed,
            'failed' => $failed,
            'warnings' => $warnings,
            'success_rate' => $total > 0 ? round(($passed / $total) * 100, 1) : 0
        ];
    }
}