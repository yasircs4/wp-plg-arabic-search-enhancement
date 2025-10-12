<?php
/**
 * Test Search Query Modifier
 *
 * @copyright 2024 Yasir Najeep
 * @license   GPL v2 or later
 */

namespace ArabicSearchEnhancement\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ArabicSearchEnhancement\Core\SearchQueryModifier;
use ArabicSearchEnhancement\Core\ArabicTextNormalizer;
use ArabicSearchEnhancement\Core\Configuration;
use ArabicSearchEnhancement\Core\Cache;

class SearchQueryModifierTest extends TestCase {
    
    private SearchQueryModifier $modifier;
    private ArabicTextNormalizer $normalizer;
    private Configuration $config;
    private Cache $cache;
    
    protected function setUp(): void {
        $mockCacheInterface = $this->createMock('ArabicSearchEnhancement\Interfaces\CacheInterface');
        $this->cache = new Cache($mockCacheInterface);
        $this->config = new Configuration($this->cache);
        $mockConfig = $this->createMock('ArabicSearchEnhancement\Interfaces\ConfigurationInterface');
        $this->normalizer = new ArabicTextNormalizer($mockConfig);
        $this->modifier = new SearchQueryModifier($this->normalizer, $this->config, $this->cache);
    }
    
    public function testModifySearchQuery(): void {
        $query = new \stdClass();
        $query->query_vars = [
            's' => 'مكتوب',
            'post_type' => 'post'
        ];
        
        // Mock is_search() function
        $query->is_search = true;
        
        $this->modifier->modify_search_query($query);
        
        // Verify the query was modified
        $this->assertNotEquals('مكتوب', $query->query_vars['s']);
    }
    
    public function testGenerateSearchSql(): void {
        global $wpdb;
        $wpdb = new \stdClass();
        $wpdb->posts = 'wp_posts';
        
        $search_terms = ['مكتوب', 'كتاب'];
        $sql = $this->modifier->generate_search_sql($search_terms);
        
        $this->assertIsString($sql);
        $this->assertStringContains('REPLACE', $sql);
        $this->assertStringContains('wp_posts', $sql);
    }
    
    public function testHandlesEmptySearchTerms(): void {
        $search_terms = [];
        $sql = $this->modifier->generate_search_sql($search_terms);
        
        $this->assertEquals('', $sql);
    }
    
    public function testHandlesNonArabicText(): void {
        $search_terms = ['hello', 'world'];
        $sql = $this->modifier->generate_search_sql($search_terms);
        
        $this->assertIsString($sql);
        $this->assertStringContains('hello', $sql);
        $this->assertStringContains('world', $sql);
    }
    
    public function testCachesSearchSql(): void {
        $search_terms = ['مكتوب'];
        
        // First call should generate and cache
        $sql1 = $this->modifier->generate_search_sql($search_terms);
        
        // Second call should return cached result
        $sql2 = $this->modifier->generate_search_sql($search_terms);
        
        $this->assertEquals($sql1, $sql2);
    }
    
    public function testNormalizesSearchTerms(): void {
        $terms = ['مَكْتُوب', 'كِتَاب'];
        $normalized = $this->modifier->normalize_search_terms($terms);
        
        $this->assertEquals(['مكتوب', 'كتاب'], $normalized);
    }
    
    public function testSplitsSearchString(): void {
        $search_string = 'مكتوب كتاب جميل';
        $terms = $this->modifier->parse_search_terms($search_string);
        
        $this->assertEquals(['مكتوب', 'كتاب', 'جميل'], $terms);
    }
    
    public function testHandlesSpecialCharacters(): void {
        $search_string = 'مكتوب، كتاب! جميل؟';
        $terms = $this->modifier->parse_search_terms($search_string);
        
        $this->assertEquals(['مكتوب', 'كتاب', 'جميل'], $terms);
    }
}