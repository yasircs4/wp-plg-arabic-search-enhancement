<?php
/**
 * REST API Integration for Arabic Search Enhancement
 *
 * Provides REST API endpoints for external search integration and headless WordPress
 *
 * @copyright 2025 yasircs4
 * @license   GPL v2 or later
 */

namespace ArabicSearchEnhancement\API;

use ArabicSearchEnhancement\Core\ArabicTextNormalizer;
use ArabicSearchEnhancement\Core\AdvancedSearchFeatures;
use ArabicSearchEnhancement\Core\PerformanceOptimizer;
use ArabicSearchEnhancement\Core\MultiLanguageNormalizer;
use ArabicSearchEnhancement\Interfaces\ConfigurationInterface;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class RestApiController {
    
    private ConfigurationInterface $config;
    private ArabicTextNormalizer $normalizer;
    private AdvancedSearchFeatures $advanced_search;
    private PerformanceOptimizer $optimizer;
    private MultiLanguageNormalizer $multilang;
    private string $namespace = 'arabic-search/v1';
    
    public function __construct(
        ConfigurationInterface $config,
        ArabicTextNormalizer $normalizer,
        AdvancedSearchFeatures $advanced_search,
        PerformanceOptimizer $optimizer,
        MultiLanguageNormalizer $multilang
    ) {
        $this->config = $config;
        $this->normalizer = $normalizer;
        $this->advanced_search = $advanced_search;
        $this->optimizer = $optimizer;
        $this->multilang = $multilang;
        
        add_action('rest_api_init', [$this, 'register_routes']);
    }
    
    /**
     * Register REST API routes
     */
    public function register_routes(): void {
        // Search endpoints
        register_rest_route($this->namespace, '/search', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'search_posts'],
            'permission_callback' => [$this, 'check_search_permissions'],
            'args' => $this->get_search_args()
        ]);
        
        register_rest_route($this->namespace, '/search/suggestions', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_search_suggestions'],
            'permission_callback' => [$this, 'check_search_permissions'],
            'args' => [
                'query' => [
                    'required' => true,
                    'type' => 'string',
                    'description' => 'Search query for suggestions',
                    'sanitize_callback' => 'sanitize_text_field'
                ]
            ]
        ]);
        
        // Normalization endpoints
        register_rest_route($this->namespace, '/normalize', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'normalize_text'],
            'permission_callback' => [$this, 'check_normalize_permissions'],
            'args' => [
                'text' => [
                    'required' => true,
                    'type' => 'string',
                    'description' => 'Text to normalize'
                ],
                'language' => [
                    'required' => false,
                    'type' => 'string',
                    'default' => 'ar',
                    'enum' => ['ar', 'ur', 'fa', 'ps', 'sd'],
                    'description' => 'Language code for normalization'
                ]
            ]
        ]);
        
        // Analytics endpoints (admin only)
        register_rest_route($this->namespace, '/analytics/stats', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_analytics_stats'],
            'permission_callback' => [$this, 'check_admin_permissions'],
            'args' => [
                'period' => [
                    'required' => false,
                    'type' => 'integer',
                    'default' => 30,
                    'minimum' => 1,
                    'maximum' => 365,
                    'description' => 'Number of days to analyze'
                ]
            ]
        ]);
        
        register_rest_route($this->namespace, '/analytics/top-queries', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_top_queries'],
            'permission_callback' => [$this, 'check_admin_permissions'],
            'args' => [
                'period' => [
                    'required' => false,
                    'type' => 'integer',
                    'default' => 30,
                    'minimum' => 1,
                    'maximum' => 365
                ],
                'limit' => [
                    'required' => false,
                    'type' => 'integer',
                    'default' => 10,
                    'minimum' => 1,
                    'maximum' => 100
                ]
            ]
        ]);
        
        // Language detection endpoint
        register_rest_route($this->namespace, '/detect-language', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'detect_language'],
            'permission_callback' => [$this, 'check_search_permissions'],
            'args' => [
                'text' => [
                    'required' => true,
                    'type' => 'string',
                    'description' => 'Text to analyze for language detection'
                ]
            ]
        ]);
        
        // Index management endpoints (admin only)
        register_rest_route($this->namespace, '/index/rebuild', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'rebuild_search_index'],
            'permission_callback' => [$this, 'check_admin_permissions'],
            'args' => [
                'batch_size' => [
                    'required' => false,
                    'type' => 'integer',
                    'default' => 100,
                    'minimum' => 10,
                    'maximum' => 500
                ]
            ]
        ]);
        
        register_rest_route($this->namespace, '/index/status', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_index_status'],
            'permission_callback' => [$this, 'check_admin_permissions']
        ]);
    }
    
    /**
     * Search posts using Arabic search enhancement
     *
     * @param WP_REST_Request $request Full details about the request
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure
     */
    public function search_posts(WP_REST_Request $request): WP_REST_Response {
        $query = $request->get_param('query');
        $post_types = $request->get_param('post_type') ?: ['post', 'page'];
        $posts_per_page = $request->get_param('per_page') ?: 10;
        $page = $request->get_param('page') ?: 1;
        $language = $request->get_param('language') ?: 'ar';
        
        // Set language for multi-language normalizer
        try {
            $this->multilang->set_language($language);
        } catch (Exception $e) {
            // Invalid language, use default
        }
        
        // Perform optimized search
        $search_args = [
            'posts_per_page' => $posts_per_page,
            'paged' => $page,
            'post_type' => $post_types
        ];
        
        $results = $this->optimizer->optimized_search($query, $search_args);
        
        // Format results for API response
        $formatted_results = array_map(function($result) {
            return [
                'id' => intval($result->post_id),
                'title' => [
                    'rendered' => $result->post_title
                ],
                'excerpt' => [
                    'rendered' => $result->post_excerpt
                ],
                'date' => $result->post_date,
                'relevance_score' => floatval($result->relevance_score),
                'link' => get_permalink($result->post_id)
            ];
        }, $results);
        
        // Get suggestions for the query
        $suggestions = $this->advanced_search->get_search_suggestions($query);
        
        $response_data = [
            'results' => $formatted_results,
            'found_posts' => count($formatted_results),
            'max_pages' => 1, // This would need proper pagination calculation
            'suggestions' => $suggestions,
            'query_info' => [
                'original_query' => $query,
                'normalized_query' => $this->multilang->normalize($query),
                'detected_language' => $this->multilang->detect_text_language($query),
                'expanded_terms' => $this->advanced_search->expand_query($query)
            ]
        ];
        
        return new WP_REST_Response($response_data, 200);
    }
    
    /**
     * Get search suggestions
     *
     * @param WP_REST_Request $request Full details about the request
     * @return WP_REST_Response Response object
     */
    public function get_search_suggestions(WP_REST_Request $request): WP_REST_Response {
        $query = $request->get_param('query');
        $language = $request->get_param('language') ?: 'ar';
        
        try {
            $this->multilang->set_language($language);
        } catch (Exception $e) {
            // Invalid language, use default
        }
        
        $suggestions = $this->advanced_search->get_search_suggestions($query);
        $language_suggestions = $this->multilang->get_language_suggestions($query);
        
        // Combine and deduplicate suggestions
        $all_suggestions = array_unique(array_merge($suggestions, $language_suggestions));
        
        return new WP_REST_Response([
            'suggestions' => array_values($all_suggestions),
            'query' => $query,
            'language' => $language
        ], 200);
    }
    
    /**
     * Normalize text using Arabic text normalizer
     *
     * @param WP_REST_Request $request Full details about the request
     * @return WP_REST_Response Response object
     */
    public function normalize_text(WP_REST_Request $request): WP_REST_Response {
        $text = $request->get_param('text');
        $language = $request->get_param('language');
        
        try {
            $this->multilang->set_language($language);
            $normalized = $this->multilang->normalize($text);
        } catch (Exception $e) {
            return new WP_Error(
                'invalid_language',
                __('Invalid language code provided.', 'arabic-search-enhancement'),
                ['status' => 400]
            );
        }
        
        return new WP_REST_Response([
            'original_text' => $text,
            'normalized_text' => $normalized,
            'language' => $language,
            'detected_language' => $this->multilang->detect_text_language($text),
            'contains_supported_language' => $this->multilang->contains_supported_language($text)
        ], 200);
    }
    
    /**
     * Get analytics statistics
     *
     * @param WP_REST_Request $request Full details about the request
     * @return WP_REST_Response Response object
     */
    public function get_analytics_stats(WP_REST_Request $request): WP_REST_Response {
        $period = $request->get_param('period');
        $stats = $this->optimizer->get_performance_stats($period);
        
        return new WP_REST_Response($stats, 200);
    }
    
    /**
     * Get top search queries
     *
     * @param WP_REST_Request $request Full details about the request
     * @return WP_REST_Response Response object
     */
    public function get_top_queries(WP_REST_Request $request): WP_REST_Response {
        $period = $request->get_param('period');
        $limit = $request->get_param('limit');
        
        global $wpdb;
        $date_limit = gmdate('Y-m-d H:i:s', strtotime("-{$period} days"));
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
        $queries = $wpdb->get_results($wpdb->prepare("
            SELECT 
                original_query,
                normalized_query,
                search_count,
                result_count,
                last_searched
            FROM {$wpdb->prefix}arabic_search_stats
            WHERE last_searched >= %s
            ORDER BY search_count DESC
            LIMIT %d
        ", $date_limit, $limit));
        
        $formatted_queries = array_map(function($query) {
            return [
                'query' => $query->original_query,
                'normalized_query' => $query->normalized_query,
                'search_count' => intval($query->search_count),
                'result_count' => intval($query->result_count),
                'success_rate' => $query->result_count > 0 ? 100 : 0,
                'last_searched' => $query->last_searched
            ];
        }, $queries);
        
        return new WP_REST_Response([
            'queries' => $formatted_queries,
            'period_days' => $period,
            'total_queries' => count($formatted_queries)
        ], 200);
    }
    
    /**
     * Detect language of given text
     *
     * @param WP_REST_Request $request Full details about the request
     * @return WP_REST_Response Response object
     */
    public function detect_language(WP_REST_Request $request): WP_REST_Response {
        $text = $request->get_param('text');
        
        $detected_language = $this->multilang->detect_text_language($text);
        $contains_supported = $this->multilang->contains_supported_language($text);
        $supported_languages = $this->multilang->get_supported_languages();
        
        return new WP_REST_Response([
            'text' => $text,
            'detected_language' => $detected_language,
            'language_name' => $supported_languages[$detected_language] ?? 'Unknown',
            'contains_supported_language' => $contains_supported,
            'supported_languages' => $supported_languages,
            'confidence' => $contains_supported ? 0.9 : 0.1 // Simplified confidence score
        ], 200);
    }
    
    /**
     * Rebuild search index
     *
     * @param WP_REST_Request $request Full details about the request
     * @return WP_REST_Response Response object
     */
    public function rebuild_search_index(WP_REST_Request $request): WP_REST_Response {
        $batch_size = $request->get_param('batch_size');
        
        // This is a time-intensive operation, so we might want to queue it
        $stats = $this->optimizer->rebuild_search_index($batch_size);
        
        return new WP_REST_Response([
            'message' => __('Search index rebuild completed.', 'arabic-search-enhancement'),
            'statistics' => $stats
        ], 200);
    }
    
    /**
     * Get index status
     *
     * @param WP_REST_Request $request Full details about the request
     * @return WP_REST_Response Response object
     */
    public function get_index_status(WP_REST_Request $request): WP_REST_Response {
        global $wpdb;
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
        $index_stats = $wpdb->get_row("
            SELECT 
                COUNT(*) as indexed_posts,
                AVG(word_count) as avg_word_count,
                MAX(last_updated) as last_update
            FROM {$wpdb->prefix}arabic_search_index
        ");
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
        $total_posts = $wpdb->get_var("
            SELECT COUNT(*) 
            FROM {$wpdb->posts} 
            WHERE post_status = 'publish' 
            AND post_type IN ('post', 'page')
        ");
        
        $coverage_percentage = $total_posts > 0 
            ? round(($index_stats->indexed_posts / $total_posts) * 100, 2)
            : 0;
        
        return new WP_REST_Response([
            'indexed_posts' => intval($index_stats->indexed_posts),
            'total_posts' => intval($total_posts),
            'coverage_percentage' => $coverage_percentage,
            'avg_word_count' => round($index_stats->avg_word_count, 2),
            'last_update' => $index_stats->last_update,
            'status' => $coverage_percentage > 80 ? 'good' : ($coverage_percentage > 50 ? 'warning' : 'needs_rebuild')
        ], 200);
    }
    
    /**
     * Check permissions for search endpoints
     *
     * @param WP_REST_Request $request Full details about the request
     * @return bool|WP_Error True if the request has search access, WP_Error object otherwise
     */
    public function check_search_permissions(WP_REST_Request $request) {
        // Allow public access to search endpoints
        return true;
    }
    
    /**
     * Check permissions for normalization endpoints
     *
     * @param WP_REST_Request $request Full details about the request
     * @return bool|WP_Error True if the request has access, WP_Error object otherwise
     */
    public function check_normalize_permissions(WP_REST_Request $request) {
        // Allow authenticated users to use normalization
        return is_user_logged_in();
    }
    
    /**
     * Check permissions for admin endpoints
     *
     * @param WP_REST_Request $request Full details about the request
     * @return bool|WP_Error True if the request has admin access, WP_Error object otherwise
     */
    public function check_admin_permissions(WP_REST_Request $request) {
        return current_user_can('manage_options');
    }
    
    /**
     * Get search endpoint arguments
     *
     * @return array Search arguments schema
     */
    private function get_search_args(): array {
        return [
            'query' => [
                'required' => true,
                'type' => 'string',
                'description' => 'Search query',
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'post_type' => [
                'required' => false,
                'type' => 'array',
                'default' => ['post', 'page'],
                'items' => ['type' => 'string'],
                'description' => 'Post types to search'
            ],
            'per_page' => [
                'required' => false,
                'type' => 'integer',
                'default' => 10,
                'minimum' => 1,
                'maximum' => 100,
                'description' => 'Number of results per page'
            ],
            'page' => [
                'required' => false,
                'type' => 'integer',
                'default' => 1,
                'minimum' => 1,
                'description' => 'Page number'
            ],
            'language' => [
                'required' => false,
                'type' => 'string',
                'default' => 'ar',
                'enum' => ['ar', 'ur', 'fa', 'ps', 'sd'],
                'description' => 'Language for search processing'
            ]
        ];
    }
}