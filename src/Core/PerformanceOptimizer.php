<?php
/**
 * Performance Optimization for Arabic Search
 *
 * Provides search indexing, query optimization, and advanced caching
 *
 * @copyright 2025 yasircs4
 * @license   GPL v2 or later
 */

namespace ArabicSearchEnhancement\Core;

use ArabicSearchEnhancement\Interfaces\CacheInterface;
use ArabicSearchEnhancement\Interfaces\ConfigurationInterface;

class PerformanceOptimizer {
    
    private CacheInterface $cache;
    private ConfigurationInterface $config;
    private ?MultiLanguageNormalizer $language_normalizer;
    private string $index_table;
    private string $stats_table;
    
    public function __construct(
        CacheInterface $cache,
        ConfigurationInterface $config,
        ?MultiLanguageNormalizer $language_normalizer = null
    ) {
        $this->cache = $cache;
        $this->config = $config;
        $this->language_normalizer = $language_normalizer;
        
        global $wpdb;
        $this->index_table = $wpdb->prefix . 'arabic_search_index';
        $this->stats_table = $wpdb->prefix . 'arabic_search_stats';
    }
    
    /**
     * Create database tables for search optimization
     */
    public function create_optimization_tables(): void {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Search index table
        $index_sql = "CREATE TABLE {$this->index_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            post_id bigint(20) unsigned NOT NULL,
            content_hash varchar(32) NOT NULL,
            normalized_content longtext NOT NULL,
            word_count int(11) DEFAULT 0,
            last_updated datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY post_id (post_id),
            KEY content_hash (content_hash),
            KEY last_updated (last_updated),
            FULLTEXT KEY normalized_content (normalized_content)
        ) $charset_collate;";
        
        // Search statistics table
        $stats_sql = "CREATE TABLE {$this->stats_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            query_hash varchar(32) NOT NULL,
            original_query varchar(500) NOT NULL,
            normalized_query varchar(500) NOT NULL,
            result_count int(11) DEFAULT 0,
            avg_relevance_score decimal(5,2) DEFAULT 0.00,
            search_count int(11) DEFAULT 1,
            detected_language varchar(10) DEFAULT NULL,
            last_searched datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY query_hash (query_hash),
            KEY last_searched (last_searched),
            KEY search_count (search_count)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.NoCaching
        dbDelta($index_sql);
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.NoCaching
        dbDelta($stats_sql);
    }
    
    /**
     * Build or rebuild search index for all posts
     *
     * @param int $batch_size Number of posts to process per batch
     * @return array Processing statistics
     */
    public function rebuild_search_index(int $batch_size = 100): array {
        global $wpdb;
        
        $stats = [
            'processed' => 0,
            'updated' => 0,
            'errors' => 0,
            'start_time' => microtime(true)
        ];
        
        // Get total post count
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $total_posts = $wpdb->get_var("
            SELECT COUNT(*) 
            FROM {$wpdb->posts} 
            WHERE post_status = 'publish' 
            AND post_type IN ('post', 'page')
        ");
        
        $offset = 0;
        
        while ($offset < $total_posts) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
            $posts = $wpdb->get_results($wpdb->prepare("
                SELECT ID, post_title, post_content, post_excerpt, post_modified
                FROM {$wpdb->posts}
                WHERE post_status = 'publish'
                AND post_type IN ('post', 'page')
                ORDER BY ID
                LIMIT %d OFFSET %d
            ", $batch_size, $offset));
            
            foreach ($posts as $post) {
                try {
                    $this->index_post($post);
                    $stats['updated']++;
                } catch (Exception $e) {
                    $stats['errors']++;
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log("Arabic Search Index Error for Post {$post->ID}: " . $e->getMessage());
                    }
                }
                $stats['processed']++;
            }
            
            $offset += $batch_size;
            
            // Prevent memory exhaustion
            wp_cache_flush();
        }
        
        $stats['end_time'] = microtime(true);
        $stats['duration'] = $stats['end_time'] - $stats['start_time'];
        
        return $stats;
    }
    
    /**
     * Index a single post
     *
     * @param object $post WordPress post object
     */
    public function index_post($post): void {
        global $wpdb;
        
        // Combine post content
        $content = $post->post_title . ' ' . $post->post_content . ' ' . $post->post_excerpt;
        
        // Create content hash for change detection
        $content_hash = md5($content . $post->post_modified);
        
        // Check if already indexed and unchanged
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
        $existing = $wpdb->get_var($wpdb->prepare("
            SELECT content_hash 
            FROM {$this->index_table} 
            WHERE post_id = %d
        ", $post->ID));
        
        if ($existing === $content_hash) {
            return; // No changes, skip indexing
        }
        
        // Normalize content for indexing
        $normalized_content = $this->normalize_content_for_index($content);
        $word_count = str_word_count($normalized_content, 0, 'أابتثجحخدذرزسشصضطظعغفقكلمنهويى');
        
        // Insert or update index
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->replace($this->index_table, [
            'post_id' => $post->ID,
            'content_hash' => $content_hash,
            'normalized_content' => $normalized_content,
            'word_count' => $word_count
        ]);
    }
    
    /**
     * Normalize content for search indexing
     *
     * @param string $content Raw content
     * @return string Normalized content
     */
    private function normalize_content_for_index(string $content): string {
        // Remove HTML tags
        $content = wp_strip_all_tags($content);
        
        // Remove diacritics
        $content = preg_replace('/[\x{064B}-\x{065F}]/u', '', $content);
        $content = preg_replace('/[\x{0670}]/u', '', $content);
        $content = preg_replace('/[\x{06D6}-\x{06ED}]/u', '', $content);
        
        // Normalize letters
        $replacements = [
            'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ٱ' => 'ا',
            'ة' => 'ه',
            'ى' => 'ي',
            'ؤ' => 'و',
            'ئ' => 'ي'
        ];
        
        $content = str_replace(array_keys($replacements), array_values($replacements), $content);
        
        // Remove extra whitespace
        $content = preg_replace('/\s+/', ' ', $content);
        
        return trim($content);
    }
    
    /**
     * Perform optimized search using index
     *
     * @param string $query Search query
     * @param array $args Search arguments
     * @return array Search results with relevance scores
     */
    public function optimized_search(string $query, array $args = []): array {
        global $wpdb;
        
        $cache_key = 'arabic_optimized_search_' . md5($query . serialize($args));
        $cached_results = $this->cache->get($cache_key);
        
        if ($cached_results !== false) {
            return $cached_results;
        }
        
        $start_time = microtime(true);
        
        // Normalize query
        $normalized_query = $this->normalize_content_for_index($query);
        $query_terms = array_filter(explode(' ', $normalized_query));
        
        if (empty($query_terms)) {
            return [];
        }
        
        // Build FULLTEXT search
        $search_terms = array_map(function($term) {
            return '+' . $term . '*'; // Require term with wildcard
        }, $query_terms);
        
        $search_string = implode(' ', $search_terms);
        
        // Execute optimized search
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT 
                i.post_id,
                p.post_title,
                p.post_excerpt,
                p.post_date,
                MATCH(i.normalized_content) AGAINST(%s IN BOOLEAN MODE) as relevance_score
            FROM {$this->index_table} i
            INNER JOIN {$wpdb->posts} p ON i.post_id = p.ID
            WHERE MATCH(i.normalized_content) AGAINST(%s IN BOOLEAN MODE)
            AND p.post_status = 'publish'
            ORDER BY relevance_score DESC, p.post_date DESC
            LIMIT %d
        ", 
            $search_string, 
            $search_string, 
            $args['posts_per_page'] ?? 20
        ));
        
        $search_time = microtime(true) - $start_time;
        
        // Record search statistics
        $this->record_search_stats($query, $normalized_query, count($results), $search_time);
        
        // Cache results
        $this->cache->set($cache_key, $results, 1800); // 30 minutes
        
        return $results;
    }

    /**
     * Track a search event coming from WordPress core queries.
     *
     * @param string $original_query Raw search query entered by the user
     * @param int $result_count Number of posts returned for the query
     * @param float $search_time Optional timing information
     */
    public function track_search_event(string $original_query, int $result_count, float $search_time = 0.0): void {
        $normalized_query = $this->normalize_content_for_index($original_query);

        if ($normalized_query === '') {
            return;
        }

        $this->record_search_stats($original_query, $normalized_query, $result_count, $search_time);
    }
    
    /**
     * Record search statistics
     *
     * @param string $original_query Original search query
     * @param string $normalized_query Normalized query
     * @param int $result_count Number of results
     * @param float $search_time Search execution time
     */
    private function record_search_stats(string $original_query, string $normalized_query, int $result_count, float $search_time): void {
        // Check if analytics are enabled (privacy compliance)
        if (!$this->config->get('analytics_enabled', false)) {
            return;
        }
        
        global $wpdb;
        
        $query_hash = md5($normalized_query);

        $detected_language = null;
        if ($this->language_normalizer) {
            try {
                $detected_language = $this->language_normalizer->detect_text_language($original_query);
            } catch (\Throwable $exception) {
                $detected_language = null;
            }
        } elseif (preg_match('/[\x{0600}-\x{06FF}]/u', $original_query)) {
            $detected_language = 'ar';
        }
        
        // Update or insert search statistics
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
        $wpdb->query($wpdb->prepare("
            INSERT INTO {$this->stats_table} 
            (query_hash, original_query, normalized_query, detected_language, result_count, search_count)
            VALUES (%s, %s, %s, %s, %d, 1)
            ON DUPLICATE KEY UPDATE
            result_count = %d,
            search_count = search_count + 1,
            detected_language = VALUES(detected_language)
        ", 
            $query_hash, 
            $original_query, 
            $normalized_query, 
            $detected_language, 
            $result_count, 
            $result_count
        ));
    }
    
    /**
     * Get search performance statistics
     *
     * @param int $days Number of days to analyze
     * @return array Performance statistics
     */
    public function get_performance_stats(int $days = 30): array {
        global $wpdb;
        
        $cache_key = 'arabic_performance_stats_' . $days;
        $cached_stats = $this->cache->get($cache_key);
        
        if ($cached_stats !== false) {
            return $cached_stats;
        }
        
        $date_limit = gmdate('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $stats = [
            'total_searches' => 0,
            'unique_queries' => 0,
            'avg_results_per_search' => 0,
            'top_queries' => [],
            'search_trends' => [],
            'index_stats' => []
        ];
        
        // Total and unique searches
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
        $search_stats = $wpdb->get_row($wpdb->prepare("
            SELECT 
                SUM(search_count) as total_searches,
                COUNT(*) as unique_queries,
                AVG(result_count) as avg_results
            FROM {$this->stats_table}
            WHERE last_searched >= %s
        ", $date_limit));
        
        if ($search_stats) {
            $stats['total_searches'] = (int) $search_stats->total_searches;
            $stats['unique_queries'] = (int) $search_stats->unique_queries;
            $stats['avg_results_per_search'] = round($search_stats->avg_results, 2);
        }
        
        // Top queries
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
        $stats['top_queries'] = $wpdb->get_results($wpdb->prepare("
            SELECT original_query, search_count, result_count
            FROM {$this->stats_table}
            WHERE last_searched >= %s
            ORDER BY search_count DESC
            LIMIT 10
        ", $date_limit));
        
        // Index statistics
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
        $index_stats = $wpdb->get_row("
            SELECT 
                COUNT(*) as indexed_posts,
                AVG(word_count) as avg_word_count,
                MAX(last_updated) as last_index_update
            FROM {$this->index_table}
        ");
        
        if ($index_stats) {
            $stats['index_stats'] = [
                'indexed_posts' => (int) $index_stats->indexed_posts,
                'avg_word_count' => round($index_stats->avg_word_count, 2),
                'last_update' => $index_stats->last_index_update
            ];
        }
        
        // Cache statistics
        $this->cache->set($cache_key, $stats, 3600); // 1 hour
        
        return $stats;
    }
    
    /**
     * Clean up old search statistics
     *
     * @param int $days Keep statistics for this many days
     * @return int Number of cleaned records
     */
    public function cleanup_old_stats(int $days = 90): int {
        global $wpdb;
        
        $date_limit = gmdate('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
        $deleted = $wpdb->query($wpdb->prepare("
            DELETE FROM {$this->stats_table}
            WHERE last_searched < %s
            AND search_count < 2
        ", $date_limit));
        
        return $deleted;
    }
    
    /**
     * Optimize database tables
     */
    public function optimize_tables(): void {
        global $wpdb;
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query("OPTIMIZE TABLE {$this->index_table}");
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query("OPTIMIZE TABLE {$this->stats_table}");
    }
}