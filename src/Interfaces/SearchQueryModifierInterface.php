<?php
/**
 * Search Query Modifier Interface
 *
 * @package ArabicSearchEnhancement
 * @since 1.1.0
 * @author yasircs4 <yasircs4@live.com>
 * @copyright 2025 yasircs4
 * @license GPL-2.0-or-later
 * @link https://yasircs4.github.io/wp-plg-arabic-search-enhancement/
 */

namespace ArabicSearchEnhancement\Interfaces;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

use WP_Query;

interface SearchQueryModifierInterface {
    /**
     * Modify WordPress search query to enhance Arabic text search
     *
     * @param string $search Original search SQL
     * @param WP_Query $wp_query WordPress query object
     * @return string Modified search SQL
     */
    public function modify_search_sql(string $search, WP_Query $wp_query): string;

    /**
     * Modify main query parameters for search
     *
     * @param WP_Query $query WordPress query object
     * @return void
     */
    public function modify_query_params(WP_Query $query): void;
}