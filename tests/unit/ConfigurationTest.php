<?php
/**
 * Test Configuration Management
 *
 * @copyright 2024 Yasir Najeep
 * @license   GPL v2 or later
 */

namespace ArabicSearchEnhancement\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ArabicSearchEnhancement\Core\Configuration;
use ArabicSearchEnhancement\Core\Cache;

class ConfigurationTest extends TestCase {
    
    private Configuration $config;
    private Cache $cache;
    
    protected function setUp(): void {
        // Create a basic cache mock for Configuration constructor
        $this->cache = $this->createMock('ArabicSearchEnhancement\Interfaces\CacheInterface');
        $this->config = new Configuration($this->cache);
    }
    
    public function testDefaultConfiguration(): void {
        $defaults = [
            'enabled' => true,
            'search_excerpts' => true,
            'post_types' => ['post', 'page'],
            'posts_per_page' => 10,
            'debug_mode' => false,
            'performance_monitoring' => false
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
        $all = $this->config->get_all();
        
        $this->assertIsArray($all);
        $this->assertArrayHasKey('enabled', $all);
        $this->assertArrayHasKey('search_excerpts', $all);
    }
    
    public function testConfigurationSanitization(): void {
        // Test boolean sanitization
        $this->config->set('enabled', 'true');
        $this->assertTrue($this->config->get('enabled'));
        
        $this->config->set('enabled', '0');
        $this->assertFalse($this->config->get('enabled'));
        
        // Test array sanitization
        $this->config->set('post_types', 'post,page,custom');
        $result = $this->config->get('post_types');
        $this->assertEquals(['post', 'page', 'custom'], $result);
        
        // Test integer sanitization
        $this->config->set('posts_per_page', '25');
        $this->assertEquals(25, $this->config->get('posts_per_page'));
    }
    
    public function testIsRtlDetection(): void {
        // Mock WordPress locale functions for testing
        if (!function_exists('get_locale')) {
            function get_locale() {
                return 'ar';
            }
        }
        
        $this->assertTrue($this->config->is_rtl());
    }
    
    public function testSupportsLanguage(): void {
        $this->assertTrue($this->config->supports_language('ar'));
        $this->assertTrue($this->config->supports_language('ar_SA'));
        $this->assertFalse($this->config->supports_language('en_US'));
    }
    
    public function testGetCacheKey(): void {
        $key = $this->config->get_cache_key('test_key');
        $this->assertStringContains('arabic_search_enhancement', $key);
        $this->assertStringContains('test_key', $key);
    }
}