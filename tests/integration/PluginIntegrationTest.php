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
        ase_mock_reset_options();
        PluginFactory::clear_instances();
        $this->plugin = PluginFactory::create_plugin();
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
        $wp_query = new \WP_Query(['s' => 'مَكْتُوب كِتَاب']);
        $wp_query->set_is_search(true);
        $wp_query->set_main_query(true);
    $result = $modifier->modify_search_sql(' original_sql ', $wp_query);
        
        // Verify normalization occurred - result should contain normalized text
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('مكتوب', $result); // Should contain normalized form
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