<?php
/**
 * Search Analytics Dashboard
 *
 * Provides comprehensive analytics for Arabic search functionality
 *
 * @copyright 2025 yasircs4
 * @license   GPL v2 or later
 */

namespace ArabicSearchEnhancement\Admin;

use ArabicSearchEnhancement\Interfaces\ConfigurationInterface;
use ArabicSearchEnhancement\Core\PerformanceOptimizer;

class SearchAnalyticsDashboard {
    
    private ConfigurationInterface $config;
    private PerformanceOptimizer $optimizer;
    private string $analytics_table;
    private bool $hooks_initialized = false;
    
    public function __construct(ConfigurationInterface $config, PerformanceOptimizer $optimizer) {
        $this->config = $config;
        $this->optimizer = $optimizer;
        
        global $wpdb;
        $this->analytics_table = $wpdb->prefix . 'arabic_search_analytics';
    }
    
    /**
     * Register admin hooks. Safe to call multiple times.
     */
    public function init_hooks(): void {
        if ($this->hooks_initialized) {
            return;
        }
        
        add_action('admin_menu', [$this, 'add_analytics_menu']);
        add_action('admin_init', [$this, 'ensure_tables_exist']);
        add_action('wp_ajax_arabic_search_analytics_data', [$this, 'get_analytics_data']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_analytics_scripts']);
        
        $this->hooks_initialized = true;
    }

    /**
     * Ensure the analytics tables exist before rendering data queries.
     */
    public function ensure_tables_exist(): void {
        global $wpdb;

        $stats_table = $wpdb->prefix . 'arabic_search_stats';
        $table_like = $wpdb->esc_like($stats_table);
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $table_exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table_like));

        if ($table_exists !== $stats_table) {
            $this->optimizer->create_optimization_tables();
        }
    }
    
    /**
     * Add analytics menu to WordPress admin
     */
    public function add_analytics_menu(): void {
        add_submenu_page(
            'options-general.php',
            __('Arabic Search Analytics', 'arabic-search-enhancement'),
            __('Search Analytics', 'arabic-search-enhancement'),
            'manage_options',
            'arabic-search-analytics',
            [$this, 'render_analytics_page']
        );
    }
    
    /**
     * Enqueue scripts and styles for analytics dashboard
     *
     * @param string $hook_suffix Current admin page
     */
    public function enqueue_analytics_scripts(string $hook_suffix): void {
        if ($hook_suffix !== 'settings_page_arabic-search-analytics') {
            return;
        }
        
        // Use WordPress's built-in chart capabilities or simple HTML/CSS charts
        // Note: WordPress doesn't include Chart.js by default, so we'll use CSS-based charts
        
        // Enqueue our custom analytics script (Chart.js removed for WordPress.org compliance)
        wp_enqueue_script(
            'arabic-search-analytics',
            ARABIC_SEARCH_ENHANCEMENT_PLUGIN_URL . 'assets/admin/analytics.js',
            ['jquery'],
            ARABIC_SEARCH_ENHANCEMENT_VERSION,
            true
        );
        
        wp_localize_script('arabic-search-analytics', 'arabicSearchAnalytics', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('arabic_search_analytics_nonce'),
            'strings' => [
                'loading' => esc_html__('Loading analytics...', 'arabic-search-enhancement'),
                'error' => esc_html__('Error loading data', 'arabic-search-enhancement'),
                'no_data' => esc_html__('No data available', 'arabic-search-enhancement')
            ]
        ]);
        
        wp_enqueue_style(
            'arabic-search-analytics',
            ARABIC_SEARCH_ENHANCEMENT_PLUGIN_URL . 'assets/admin/analytics.css',
            [],
            ARABIC_SEARCH_ENHANCEMENT_VERSION
        );
    }
    
    /**
     * Render the analytics dashboard page
     */
    public function render_analytics_page(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'arabic-search-enhancement'));
        }
        
        if (!$this->config->get('analytics_enabled', false)) {
            echo '<div class="notice notice-warning"><p>' . esc_html__('Analytics are currently disabled. Enable "Search analytics" on the plugin settings page to start collecting data.', 'arabic-search-enhancement') . '</p></div>';
        }

        ?>
        <div class="wrap arabic-search-analytics">
            <h1><?php esc_html_e('Arabic Search Analytics', 'arabic-search-enhancement'); ?></h1>
            
            <div class="analytics-header">
                <div class="period-selector">
                    <label for="analytics-period"><?php esc_html_e('Time Period:', 'arabic-search-enhancement'); ?></label>
                    <select id="analytics-period">
                        <option value="7"><?php esc_html_e('Last 7 days', 'arabic-search-enhancement'); ?></option>
                        <option value="30" selected><?php esc_html_e('Last 30 days', 'arabic-search-enhancement'); ?></option>
                        <option value="90"><?php esc_html_e('Last 90 days', 'arabic-search-enhancement'); ?></option>
                    </select>
                    <button id="refresh-analytics" class="button"><?php esc_html_e('Refresh', 'arabic-search-enhancement'); ?></button>
                </div>
            </div>
            
            <!-- Key Metrics Cards -->
            <div class="analytics-cards">
                <div class="analytics-card">
                    <h3><?php esc_html_e('Total Searches', 'arabic-search-enhancement'); ?></h3>
                    <div class="metric-value" id="total-searches">-</div>
                    <div class="metric-change" id="searches-change"></div>
                </div>
                
                <div class="analytics-card">
                    <h3><?php esc_html_e('Unique Queries', 'arabic-search-enhancement'); ?></h3>
                    <div class="metric-value" id="unique-queries">-</div>
                    <div class="metric-change" id="queries-change"></div>
                </div>
                
                <div class="analytics-card">
                    <h3><?php esc_html_e('Avg Results per Search', 'arabic-search-enhancement'); ?></h3>
                    <div class="metric-value" id="avg-results">-</div>
                    <div class="metric-change" id="results-change"></div>
                </div>
                
                <div class="analytics-card">
                    <h3><?php esc_html_e('Search Success Rate', 'arabic-search-enhancement'); ?></h3>
                    <div class="metric-value" id="success-rate">-</div>
                    <div class="metric-change" id="success-change"></div>
                </div>
            </div>
            
            <!-- Charts Section -->
            <div class="analytics-charts">
                <div class="chart-container">
                    <h3><?php esc_html_e('Search Trends (Last 7 Days)', 'arabic-search-enhancement'); ?></h3>
                    <?php $this->render_css_bar_chart($this->get_search_trends_data()); ?>
                </div>
                
                <div class="chart-container">
                    <h3><?php esc_html_e('Language Distribution', 'arabic-search-enhancement'); ?></h3>
                    <?php $this->render_css_pie_chart($this->get_language_distribution_data()); ?>
                </div>
            </div>
            
            <!-- Data Tables -->
            <div class="analytics-tables">
                <div class="table-container">
                    <h3><?php esc_html_e('Top Search Queries', 'arabic-search-enhancement'); ?></h3>
                    <table class="wp-list-table widefat fixed striped" id="top-queries-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Query', 'arabic-search-enhancement'); ?></th>
                                <th><?php esc_html_e('Searches', 'arabic-search-enhancement'); ?></th>
                                <th><?php esc_html_e('Avg Results', 'arabic-search-enhancement'); ?></th>
                                <th><?php esc_html_e('Success Rate', 'arabic-search-enhancement'); ?></th>
                                <th><?php esc_html_e('Last Searched', 'arabic-search-enhancement'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="5"><?php esc_html_e('Loading...', 'arabic-search-enhancement'); ?></td></tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="table-container">
                    <h3><?php esc_html_e('Failed Searches', 'arabic-search-enhancement'); ?></h3>
                    <table class="wp-list-table widefat fixed striped" id="failed-searches-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Query', 'arabic-search-enhancement'); ?></th>
                                <th><?php esc_html_e('Attempts', 'arabic-search-enhancement'); ?></th>
                                <th><?php esc_html_e('Suggestions', 'arabic-search-enhancement'); ?></th>
                                <th><?php esc_html_e('Last Attempt', 'arabic-search-enhancement'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="4"><?php esc_html_e('Loading...', 'arabic-search-enhancement'); ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Performance Insights -->
            <div class="performance-insights">
                <h3><?php esc_html_e('Performance Insights', 'arabic-search-enhancement'); ?></h3>
                <div id="insights-content">
                    <p><?php esc_html_e('Loading insights...', 'arabic-search-enhancement'); ?></p>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * AJAX handler for analytics data
     */
    public function get_analytics_data(): void {
        check_ajax_referer('arabic_search_analytics_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions.', 'arabic-search-enhancement'));
        }
        
        $period = intval(wp_unslash($_POST['period'] ?? 30));
        $data_type = sanitize_text_field(wp_unslash($_POST['data_type'] ?? 'overview'));
        
        switch ($data_type) {
            case 'overview':
                $data = $this->get_overview_data($period);
                break;
            case 'trends':
                $data = $this->get_trends_data($period);
                break;
            case 'top_queries':
                $data = $this->get_top_queries_data($period);
                break;
            case 'failed_searches':
                $data = $this->get_failed_searches_data($period);
                break;
            case 'languages':
                $data = $this->get_languages_data($period);
                break;
            case 'insights':
                $data = $this->get_performance_insights($period);
                break;
            default:
                $data = ['error' => 'Invalid data type'];
        }
        
        wp_send_json_success($data);
    }
    
    /**
     * Get overview analytics data
     *
     * @param int $period Days to analyze
     * @return array Overview data
     */
    private function get_overview_data(int $period): array {
        $stats = $this->optimizer->get_performance_stats($period);
        
        // Calculate additional metrics
        $success_rate = $stats['total_searches'] > 0 
            ? round(($stats['total_searches'] - $this->get_failed_searches_count($period)) / $stats['total_searches'] * 100, 1)
            : 0;
        
        return [
            'total_searches' => $stats['total_searches'],
            'unique_queries' => $stats['unique_queries'],
            'avg_results' => $stats['avg_results_per_search'],
            'success_rate' => $success_rate,
            'index_stats' => $stats['index_stats']
        ];
    }
    
    /**
     * Get search trends data for charts
     *
     * @param int $period Days to analyze
     * @return array Trends data
     */
    private function get_trends_data(int $period): array {
        global $wpdb;
        
        $date_limit = gmdate('Y-m-d H:i:s', strtotime("-{$period} days"));
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $trends = $wpdb->get_results($wpdb->prepare("
            SELECT 
                DATE(last_searched) as search_date,
                SUM(search_count) as daily_searches,
                COUNT(DISTINCT query_hash) as unique_queries
            FROM {$wpdb->prefix}arabic_search_stats
            WHERE last_searched >= %s
            GROUP BY DATE(last_searched)
            ORDER BY search_date
        ", $date_limit));
        
        $labels = [];
        $searches = [];
        $queries = [];
        
        foreach ($trends as $trend) {
            $labels[] = gmdate('M j', strtotime($trend->search_date));
            $searches[] = intval($trend->daily_searches);
            $queries[] = intval($trend->unique_queries);
        }
        
        return [
            'labels' => $labels,
            'searches' => $searches,
            'unique_queries' => $queries
        ];
    }
    
    /**
     * Get top queries data
     *
     * @param int $period Days to analyze
     * @return array Top queries
     */
    private function get_top_queries_data(int $period): array {
        global $wpdb;
        
        $date_limit = gmdate('Y-m-d H:i:s', strtotime("-{$period} days"));
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $queries = $wpdb->get_results($wpdb->prepare("
            SELECT 
                original_query,
                search_count,
                result_count,
                CASE 
                    WHEN result_count > 0 THEN 100 
                    ELSE 0 
                END as success_rate,
                last_searched
            FROM {$wpdb->prefix}arabic_search_stats
            WHERE last_searched >= %s
            ORDER BY search_count DESC
            LIMIT 20
        ", $date_limit));
        
        return array_map(function($query) {
            return [
                'query' => $query->original_query,
                'searches' => intval($query->search_count),
                'avg_results' => intval($query->result_count),
                'success_rate' => intval($query->success_rate) . '%',
                'last_searched' => gmdate('M j, Y H:i', strtotime($query->last_searched))
            ];
        }, $queries);
    }
    
    /**
     * Get failed searches data
     *
     * @param int $period Days to analyze
     * @return array Failed searches
     */
    private function get_failed_searches_data(int $period): array {
        global $wpdb;
        
        $date_limit = gmdate('Y-m-d H:i:s', strtotime("-{$period} days"));
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $failed = $wpdb->get_results($wpdb->prepare("
            SELECT 
                original_query,
                search_count,
                last_searched
            FROM {$wpdb->prefix}arabic_search_stats
            WHERE last_searched >= %s
            AND result_count = 0
            ORDER BY search_count DESC
            LIMIT 20
        ", $date_limit));
        
        return array_map(function($query) {
            return [
                'query' => $query->original_query,
                'attempts' => intval($query->search_count),
                'suggestions' => $this->generate_suggestions($query->original_query),
                'last_attempt' => gmdate('M j, Y H:i', strtotime($query->last_searched))
            ];
        }, $failed);
    }
    
    private function get_languages_data(int $period): array {
        // This is a simplified implementation
        // In a real scenario, you'd detect language from queries
        return [
            'labels' => ['Arabic', 'Urdu', 'Persian', 'Other'],
            'data' => [70, 15, 10, 5],
            'colors' => ['#2271b1', '#00a32a', '#d63638', '#dba617']
        ];
    }
    
    /**
     * Get performance insights
     *
     * @param int $period Days to analyze
     * @return array Insights
     */
    private function get_performance_insights(int $period): array {
        $stats = $this->get_overview_data($period);
        $insights = [];
        
        // Generate insights based on data
        if ($stats['success_rate'] < 70) {
            $insights[] = [
                'type' => 'warning',
                'title' => __('Low Search Success Rate', 'arabic-search-enhancement'),
                'message' => sprintf(
                    // translators: %s: search success rate percentage without the percent sign
                    __('Your search success rate is %s%%. Consider rebuilding the search index or improving content.', 'arabic-search-enhancement'),
                    esc_html($stats['success_rate'])
                )
            ];
        }
        
        if ($stats['avg_results'] < 2) {
            $insights[] = [
                'type' => 'info',
                'title' => __('Low Average Results', 'arabic-search-enhancement'),
                'message' => __('Users are finding few results per search. Consider expanding your content or improving search algorithms.', 'arabic-search-enhancement')
            ];
        }
        
        if ($stats['unique_queries'] > $stats['total_searches'] * 0.8) {
            $insights[] = [
                'type' => 'success',
                'title' => __('High Query Diversity', 'arabic-search-enhancement'),
                'message' => __('Users are searching for diverse content, indicating good engagement with your site.', 'arabic-search-enhancement')
            ];
        }
        
        return $insights;
    }
    
    /**
     * Get count of failed searches
     *
     * @param int $period Days to analyze
     * @return int Failed searches count
     */
    private function get_failed_searches_count(int $period): int {
        global $wpdb;
        
        $date_limit = gmdate('Y-m-d H:i:s', strtotime("-{$period} days"));
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return intval($wpdb->get_var($wpdb->prepare("
            SELECT SUM(search_count)
            FROM {$wpdb->prefix}arabic_search_stats
            WHERE last_searched >= %s
            AND result_count = 0
        ", $date_limit)));
    }
    
    /**
     * Generate search suggestions for failed queries
     *
     * @param string $query Failed query
     * @return string Suggestions
     */
    private function generate_suggestions(string $query): string {
        // Simplified suggestion generation
        $suggestions = [
            __('Check spelling', 'arabic-search-enhancement'),
            __('Try fewer words', 'arabic-search-enhancement'),
            __('Use more general terms', 'arabic-search-enhancement')
        ];
        
        return implode(', ', array_slice($suggestions, 0, 2));
    }
    
    /**
     * Render CSS-based bar chart
     *
     * @param array $data Chart data
     */
    private function render_css_bar_chart(array $data): void {
        if (empty($data)) {
            echo '<p>' . esc_html__('No data available', 'arabic-search-enhancement') . '</p>';
            return;
        }
        
        $max_value = max(array_column($data, 'value'));
        
        echo '<div class="css-bar-chart">';
        foreach ($data as $item) {
            $percentage = $max_value > 0 ? ($item['value'] / $max_value) * 100 : 0;
            echo '<div class="bar-item">';
            echo '<div class="bar-label">' . esc_html($item['label']) . '</div>';
            echo '<div class="bar-container">';
            echo '<div class="bar-fill" style="width: ' . esc_attr($percentage) . '%"></div>';
            echo '<div class="bar-value">' . esc_html($item['value']) . '</div>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    }
    
    /**
     * Render CSS-based pie chart
     *
     * @param array $data Chart data
     */
    private function render_css_pie_chart(array $data): void {
        if (empty($data)) {
            echo '<p>' . esc_html__('No data available', 'arabic-search-enhancement') . '</p>';
            return;
        }
        
        $total = array_sum(array_column($data, 'value'));
        
        echo '<div class="css-pie-chart">';
        echo '<div class="pie-legend">';
        
        $colors = ['#3498db', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6', '#1abc9c'];
        $color_index = 0;
        
        foreach ($data as $item) {
            $percentage = $total > 0 ? round(($item['value'] / $total) * 100, 1) : 0;
            $color = $colors[$color_index % count($colors)];
            
            echo '<div class="legend-item">';
            echo '<div class="legend-color" style="background-color: ' . esc_attr($color) . '"></div>';
            echo '<div class="legend-text">';
            echo esc_html($item['label']) . ': ' . esc_html($item['value']) . ' (' . esc_html($percentage) . '%)';
            echo '</div>';
            echo '</div>';
            
            $color_index++;
        }
        
        echo '</div>';
        echo '</div>';
    }
    
    /**
     * Get search trends data for CSS charts
     *
     * @return array Chart data
     */
    private function get_search_trends_data(): array {
        global $wpdb;
        
        $trends = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = gmdate('Y-m-d', strtotime("-{$i} days"));
            $date_start = $date . ' 00:00:00';
            $date_end = $date . ' 23:59:59';
            
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $count = $wpdb->get_var($wpdb->prepare("
                SELECT COUNT(*) 
                FROM {$wpdb->prefix}arabic_search_stats 
                WHERE last_searched BETWEEN %s AND %s
            ", $date_start, $date_end));
            
            $trends[] = [
                'label' => gmdate('M j', strtotime($date)),
                'value' => intval($count ?: 0)
            ];
        }
        
        return $trends;
    }
    
    /**
     * Get language distribution data for CSS charts
     *
     * @return array Chart data
     */
    private function get_language_distribution_data(): array {
        global $wpdb;
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $languages = $wpdb->get_results("
            SELECT 
                detected_language,
                COUNT(*) as count
            FROM {$wpdb->prefix}arabic_search_stats 
            WHERE detected_language IS NOT NULL 
            GROUP BY detected_language 
            ORDER BY count DESC 
            LIMIT 5
        ");
        
        $data = [];
        $language_names = [
            'ar' => __('Arabic', 'arabic-search-enhancement'),
            'ur' => __('Urdu', 'arabic-search-enhancement'),
            'fa' => __('Persian', 'arabic-search-enhancement'),
            'ps' => __('Pashto', 'arabic-search-enhancement'),
            'sd' => __('Sindhi', 'arabic-search-enhancement')
        ];
        
        foreach ($languages as $lang) {
            $data[] = [
                'label' => $language_names[$lang->detected_language] ?? $lang->detected_language,
                'value' => intval($lang->count)
            ];
        }
        
        return $data;
    }
}