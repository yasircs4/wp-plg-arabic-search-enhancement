<?php
/**
 * Text Normalizer Interface
 *
 * @package ArabicSearchEnhancement
 * @since 1.1.0
 * @author Yasser Nageep Maisra <info@maisra.net>
 * @copyright 2025 Yasser Nageep Maisra
 * @license GPL-2.0-or-later
 * @link https://maisra.net
 */

namespace ArabicSearchEnhancement\Interfaces;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

interface TextNormalizerInterface {
    /**
     * Normalize text by removing diacritics and standardizing letter forms
     *
     * @param string $text Text to normalize
     * @return string Normalized text
     */
    public function normalize_text(string $text): string;

    /**
     * Get SQL expression for normalizing a database column
     *
     * @param string $column_name Database column name
     * @return string SQL expression
     */
    public function get_normalization_sql(string $column_name): string;

    /**
     * Check if text contains Arabic characters
     *
     * @param string $text Text to check
     * @return bool True if contains Arabic characters
     */
    public function contains_arabic(string $text): bool;
}