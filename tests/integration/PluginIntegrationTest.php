<?php
/**
 * Integration Test for Plugin Functionality
 *
 * @copyright 2024 Yasir Najeep
 * @license   GPL v2 or later
 */

namespace ArabicSearchEnhancement\Tests\Integration;

use PHPUnit\Framework\TestCase;
use ArabicSearchEnhancement\Core\PluginFactory;

class PluginIntegrationTest extends TestCase {
    
    private $plugin;
    
    protected function setUp(): void {
        $factory = new PluginFactory();
        $this->plugin = $factory->create_plugin();
    }
    
    public function testPluginInitialization(): void {
        $this->assertNotNull($this->plugin);
        $this->assertInstanceOf('ArabicSearchEnhancement\Core\Plugin', $this->plugin);
    }
    
    public function testComponentsAreLoaded(): void {
        $config = $this->plugin->get_configuration();
        $normalizer = $this->plugin->get_normalizer();
        $modifier = $this->plugin->get_search_modifier();
        
        $this->assertNotNull($config);
        $this->assertNotNull($normalizer);
        $this->assertNotNull($modifier);
    }
    
    public function testEndToEndNormalization(): void {
        $normalizer = $this->plugin->get_normalizer();
        
        $input = 'مَكْتَبَةُ الْجَامِعَةِ الأَمْرِيكِيَّةِ';
        $result = $normalizer->normalize($input);
        
        $this->assertNotEquals($input, $result);
        $this->assertStringNotContainsString('َ', $result); // No diacritics
        $this->assertStringNotContainsString('ْ', $result); // No sukun
        $this->assertStringContainsString('ا', $result); // Normalized alef
    }
    
    public function testConfigurationPersistence(): void {
        $config = $this->plugin->get_configuration();
        
        $config->set('test_setting', 'test_value');
        $retrieved = $config->get('test_setting');
        
        $this->assertEquals('test_value', $retrieved);
    }
    
    public function testSearchQueryProcessing(): void {
        $modifier = $this->plugin->get_search_modifier();
        
        // Create mock query object
        $query = new \stdClass();
        $query->query_vars = ['s' => 'مَكْتُوب كِتَاب'];
        $query->is_search = true;
        
        $modifier->modify_search_query($query);
        
        // Verify normalization occurred
        $this->assertNotEquals('مَكْتُوب كِتَاب', $query->query_vars['s']);
    }
    
    public function testCacheIntegration(): void {
        $normalizer = $this->plugin->get_normalizer();
        
        $input = 'مكتوب طويل للاختبار';
        
        // First call - should cache result
        $result1 = $normalizer->normalize($input);
        
        // Second call - should return cached result
        $result2 = $normalizer->normalize($input);
        
        $this->assertEquals($result1, $result2);
    }
}