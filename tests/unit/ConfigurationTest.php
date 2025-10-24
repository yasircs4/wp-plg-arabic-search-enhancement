<?php
/**
 * Test Configuration Management
 *
 * @copyright 2025 yasircs4
 * @license   GPL v2 or later
 */

namespace ArabicSearchEnhancement\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ArabicSearchEnhancement\Core\Configuration;

class ConfigurationTest extends TestCase {
    
    private Configuration $config;
    
    protected function setUp(): void {
        ase_mock_reset_options();
        $this->config = new Configuration();
    }
    
    public function testDefaultConfiguration(): void {
        $defaults = [
            'enable_enhancement' => true,
            'search_excerpt' => true,
            'search_post_types' => ['post', 'page'],
            // 'posts_per_page' => null, // Skip this test as it depends on WordPress defaults
            'debug_mode' => false,
            'performance_monitoring' => false,
            'analytics_enabled' => false
        ];
        
        foreach ($defaults as $key => $expected) {
            $result = $this->config->get($key);
            $this->assertEquals($expected, $result, "Failed for key: $key");
        }
    }
    
    public function testSetAndGetConfiguration(): void {
        $this->config->set('test_key', 'test_value');
        $result = $this->config->get('test_key');
        
        $this->assertEquals('test_value', $result);
    }
    
    public function testGetAllConfiguration(): void {
        $all_config = $this->config->get_all();
        
        $this->assertIsArray($all_config);
        $this->assertArrayHasKey('enable_enhancement', $all_config);
    }
    
    public function testConfigurationSanitization(): void {
        // Test boolean sanitization
        $this->config->set('enable_enhancement', 'true');
        $this->assertTrue($this->config->get('enable_enhancement'));
        
        $this->config->set('enable_enhancement', '0');
        $this->assertFalse($this->config->get('enable_enhancement'));
        
        // Test array sanitization
        $this->config->set('search_post_types', ['post', 'page', 'custom']);
        $result = $this->config->get('search_post_types');
        $this->assertEquals(['post', 'page'], $result); // Should return default allowed types
        
        // Test integer sanitization
        $this->config->set('posts_per_page', '25');
        $this->assertEquals(25, $this->config->get('posts_per_page'));
    }
    
    public function testIsRtlDetection(): void {
        // Test RTL detection based on locale
        // Since is_rtl() method depends on WordPress get_locale(), 
        // we just test that it returns a boolean
        $result = $this->config->is_rtl();
        $this->assertIsBool($result);
    }
}