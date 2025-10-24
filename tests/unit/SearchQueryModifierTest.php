<?php
/**
 * Test Search Query Modifier
 *
 * @copyright 2025 yasircs4
 * @license   GPL v2 or later
 */

namespace ArabicSearchEnhancement\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ArabicSearchEnhancement\Core\SearchQueryModifier;
use ArabicSearchEnhancement\Core\ArabicTextNormalizer;
use ArabicSearchEnhancement\Core\Configuration;
use ArabicSearchEnhancement\Core\Cache;

class SearchQueryModifierTest extends TestCase {
    private Configuration $config;
    private Cache $cache;
    private ArabicTextNormalizer $normalizer;
    private SearchQueryModifier $modifier;
    private \wpdb $wpdb;

    protected function setUp(): void {
        ase_mock_reset_options();
        global $wpdb;
        $this->wpdb = $wpdb;

        $this->config = new Configuration();
        $this->cache = new Cache($this->config);
        $this->normalizer = new ArabicTextNormalizer($this->cache);
        $this->modifier = new SearchQueryModifier($this->normalizer, $this->config, $this->wpdb);
    }

    public function testModifySearchSqlNormalizesArabicTerms(): void {
        $wp_query = new \WP_Query(['s' => 'مَكْتُوب كتاب']);
        $wp_query->set_is_search(true);
        $wp_query->set_main_query(true);

    $sql = $this->modifier->modify_search_sql(' original_sql ', $wp_query);

        $this->assertIsString($sql);
        $this->assertStringContainsString('LIKE', $sql);
        $this->assertStringContainsString('مكتوب', $sql);
    }

    public function testModifySearchSqlRespectsDisableFlag(): void {
        $this->config->set('enable_enhancement', false);
        $wp_query = new \WP_Query(['s' => 'مَكْتُوب']);
        $wp_query->set_is_search(true);
        $wp_query->set_main_query(true);

        $original = ' ORIGINAL SQL ';
        $sql = $this->modifier->modify_search_sql($original, $wp_query);

        $this->assertSame($original, $sql);
    }

    public function testModifyQueryParamsAppliesConfiguration(): void {
        $this->config->set('search_post_types', ['post', 'page']);
        $this->config->set('posts_per_page', 12);

        $wp_query = new \WP_Query(['s' => 'test']);
        $wp_query->set_is_search(true);
        $wp_query->set_main_query(true);

        $this->modifier->modify_query_params($wp_query);

        $this->assertEquals(['post', 'page'], $wp_query->get('post_type'));
        $this->assertEquals(12, $wp_query->get('posts_per_page'));
    }

    public function testModifySearchSqlFallsBackWhenNoTerms(): void {
        $wp_query = new \WP_Query(['s' => '   ']);
        $wp_query->set_is_search(true);
        $wp_query->set_main_query(true);

    $original = ' original_sql ';
    $sql = $this->modifier->modify_search_sql($original, $wp_query);

    $this->assertSame($original, $sql);
    }
}