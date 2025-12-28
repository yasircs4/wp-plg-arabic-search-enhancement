<?php
/**
 * Search Query Modifier
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

use ArabicSearchEnhancement\Interfaces\SearchQueryModifierInterface;
use ArabicSearchEnhancement\Interfaces\TextNormalizerInterface;
use ArabicSearchEnhancement\Interfaces\ConfigurationInterface;
use WP_Query;

class SearchQueryModifier implements SearchQueryModifierInterface {
    
    /**
     * Text normalizer instance
     *
     * @var TextNormalizerInterface
     */
    private $normalizer;
    
    /**
     * Configuration instance
     *
     * @var ConfigurationInterface
     */
    private $config;
    
    /**
     * WordPress database instance
     *
     * @var \wpdb
     */
    private $wpdb;
    
    /**
     * Constructor
     *
     * @param TextNormalizerInterface $normalizer Text normalizer
     * @param ConfigurationInterface $config Configuration
     * @param \wpdb $wpdb WordPress database
     */
    public function __construct(
        TextNormalizerInterface $normalizer,
        ConfigurationInterface $config,
        \wpdb $wpdb
    ) {
        $this->normalizer = $normalizer;
        $this->config = $config;
        $this->wpdb = $wpdb;
    }
    
    /**
     * Modify WordPress search query to enhance Arabic text search
     *
     * @param string $search Original search SQL
     * @param WP_Query $wp_query WordPress query object
     * @return string Modified search SQL
     */
    public function modify_search_sql(string $search, WP_Query $wp_query): string {
        // Skip if not enabled or if in admin-only context
        if (!$this->should_modify_search($search, $wp_query)) {
            return $search;
        }
        
        try {
            return $this->build_enhanced_search_sql($search, $wp_query);
        } catch (\Exception $e) {
            // Log error if debug mode is enabled
            if ($this->config->get('debug_mode', false)) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                    error_log('Arabic Search Enhancement Error: ' . $e->getMessage());
                }
            }
            
            // Return original search on error
            return $search;
        }
    }
    
    /**
     * Modify main query parameters for search
     *
     * @param WP_Query $query WordPress query object
     * @return void
     */
    public function modify_query_params(WP_Query $query): void {
        if (!$this->is_main_search_query($query)) {
            return;
        }
        
        $this->set_search_post_types($query);
        $this->set_posts_per_page($query);
    }
    
    /**
     * Check if search should be modified
     *
     * @param string $search Original search SQL
     * @return bool True if should modify
     */
    private function should_modify_search(string $search, WP_Query $query): bool {
        if (!$this->config->get('enable_enhancement', true)) {
            return false;
        }

        $has_search_context = $search !== '' || $this->has_query_search_term($query);

        if (!$has_search_context) {
            return false;
        }

        if (!is_admin()) {
            return true;
        }

        return $this->is_frontend_ajax_search($query);
    }

    /**
     * Determine if the query contains any usable search term even if the SQL fragment is empty.
     *
     * Elementor REST and AJAX requests sometimes set custom flags instead of the regular `s` parameter,
     * so we inspect the query vars directly.
     */
    private function has_query_search_term(WP_Query $query): bool {
        $search_terms = $query->get('search_terms');
        if (is_array($search_terms) && !empty(array_filter($search_terms, 'strlen'))) {
            return true;
        }

        $term = $query->get('s');
        if (is_string($term) && $term !== '') {
            return true;
        }

        $custom_term = $query->get('search_term');
        if (is_string($custom_term) && trim($custom_term) !== '') {
            return true;
        }

        return false;
    }
    
    /**
     * Check if this is the main search query
     *
     * @param WP_Query $query WordPress query object
     * @return bool True if main search query
     */
    private function is_main_search_query(WP_Query $query): bool {
        return !is_admin() 
            && $query->is_search() 
            && $query->is_main_query();
    }

    /**
     * Determine if the current search runs through a frontend AJAX request (e.g., Elementor widgets).
     *
     * @param WP_Query $query WordPress query object
     * @return bool True when we should treat the request as frontend
     */
    private function is_frontend_ajax_search(WP_Query $query): bool {
        if (!function_exists('wp_doing_ajax') || !wp_doing_ajax()) {
            return false;
        }

        if (!$query->is_search()) {
            return false;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $action = isset($_REQUEST['action']) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : '';

        if ($action === '') {
            return false;
        }

        if (function_exists('wp_unslash')) {
            $action = (string) wp_unslash($action);
        }

        $action = trim($action);
        $action_key = $action !== '' && function_exists('sanitize_key')
            ? sanitize_key($action)
            : $action;

        $allowed_actions = apply_filters(
            'arabic_search_enhancement_frontend_ajax_actions',
            [
                'elementor_ajax',
                'elementor_pro_search',
                'elementor_pro_search_form',
                'elementor_search',
            ]
        );

        if (!is_array($allowed_actions)) {
            $allowed_actions = [];
        }

        foreach ($allowed_actions as $allowed_action) {
            $allowed_action = trim((string) $allowed_action);
            if ($allowed_action === '') {
                continue;
            }

            $allowed_action_key = function_exists('sanitize_key')
                ? sanitize_key($allowed_action)
                : $allowed_action;

            if ($allowed_action_key !== '' && strpos($action_key, $allowed_action_key) !== false) {
                return true;
            }

            if (strpos($action, $allowed_action) !== false) {
                return true;
            }
        }

        return false;
    }
    
    /**
     * Build enhanced search SQL
     *
     * @param string $search Original search SQL
     * @param WP_Query $wp_query WordPress query object
     * @return string Enhanced search SQL
     */
    private function build_enhanced_search_sql(string $search, WP_Query $wp_query): string {
        $search_terms = $this->get_search_terms($wp_query);
        
        if (empty($search_terms)) {
            return $search;
        }
        
        $search_fields = $this->get_search_fields();
        $is_exact = (bool) $wp_query->get('exact');
        
        return $this->build_search_conditions($search, $search_terms, $search_fields, $is_exact);
    }
    
    /**
     * Get search terms from query
     *
     * @param WP_Query $wp_query WordPress query object
     * @return array Search terms
     */
    private function get_search_terms(WP_Query $wp_query): array {
        $search_terms = $wp_query->get('search_terms');
        
        if (empty($search_terms)) {
            $search_term = $wp_query->get('s');
            if (!empty($search_term) && is_string($search_term)) {
                $search_terms = [$search_term];
            }
        }

        if (empty($search_terms)) {
            $custom_term = $wp_query->get('search_term');
            if (!empty($custom_term) && is_string($custom_term)) {
                $search_terms = [$custom_term];
            }
        }
        
        return is_array($search_terms) ? $search_terms : [];
    }
    
    /**
     * Get fields to search in
     *
     * @return array Search field templates
     */
    private function get_search_fields(): array {
        $posts_table = $this->wpdb->posts;
        
        $fields = [
            $this->normalizer->get_normalization_sql("{$posts_table}.post_title"),
            $this->normalizer->get_normalization_sql("{$posts_table}.post_content"),
        ];
        
        if ($this->config->get('search_excerpt', true)) {
            $fields[] = $this->normalizer->get_normalization_sql("{$posts_table}.post_excerpt");
        }
        
        return $fields;
    }
    
    /**
     * Build search conditions for all terms and fields
     *
     * @param array $search_terms Search terms
     * @param array $search_fields Search fields
     * @param bool $is_exact Whether to use exact matching
     * @return string Search SQL conditions
     */
    private function build_search_conditions(string $original_search, array $search_terms, array $search_fields, bool $is_exact): string {
        $term_groups = [];
        $prepare_values = [];
        
        $like_prefix = $is_exact ? '' : '%';
        $like_suffix = $like_prefix;
        
        foreach ($search_terms as $term) {
            if (!is_string($term) || $term === '') {
                continue;
            }
            
            $normalized_term = $this->normalizer->normalize_text($term);
            if ($normalized_term === '') {
                continue;
            }
            
            $like_value = $like_prefix . $this->wpdb->esc_like($normalized_term) . $like_suffix;
            $term_conditions = [];
            
            foreach ($search_fields as $field) {
                $term_conditions[] = "({$field} LIKE %s)";
                $prepare_values[] = $like_value;
            }
            
            if (!empty($term_conditions)) {
                $term_groups[] = '(' . implode(' OR ', $term_conditions) . ')';
            }
        }
        
        if (empty($term_groups)) {
            return $original_search;
        }
        
        $search_sql = ' AND ' . implode(' AND ', $term_groups);
        
        $search_sql = ' AND ' . implode(' AND ', $term_groups);
        
        // Prepare the SQL with the collected values
        return $this->wpdb->prepare($search_sql, $prepare_values);
    }
    
    /**
     * Set post types for search query
     *
     * @param WP_Query $query WordPress query object
     * @return void
     */
    private function set_search_post_types(WP_Query $query): void {
        $search_post_types = $this->config->get('search_post_types', ['post', 'page']);
        
        if (!empty($search_post_types) && is_array($search_post_types)) {
            $query->set('post_type', $search_post_types);
        }
    }
    
    /**
     * Set posts per page for search results
     *
     * @param WP_Query $query WordPress query object
     * @return void
     */
    private function set_posts_per_page(WP_Query $query): void {
        $posts_per_page = $this->config->get('posts_per_page');
        
        // Ensure we have a valid positive integer
        if (is_numeric($posts_per_page)) {
            $posts_per_page = (int) $posts_per_page;
            if ($posts_per_page > 0 && $posts_per_page <= 100) {
                $query->set('posts_per_page', $posts_per_page);
            }
        }
    }
    
    /**
     * Get search performance metrics
     *
     * @param array $search_terms Search terms used
     * @param int $results_count Number of results found
     * @param float $execution_time Query execution time
     * @return array Performance metrics
     */
    public function get_performance_metrics(array $search_terms, int $results_count, float $execution_time): array {
        return [
            'terms_count' => count($search_terms),
            'results_count' => $results_count,
            'execution_time' => $execution_time,
            'average_time_per_term' => count($search_terms) > 0 ? $execution_time / count($search_terms) : 0,
            'normalized_terms' => array_map([$this->normalizer, 'normalize_text'], $search_terms),
        ];
    }
}