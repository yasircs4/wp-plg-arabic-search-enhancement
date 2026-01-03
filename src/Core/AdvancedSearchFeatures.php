<?php
/**
 * Advanced Arabic Search Features
 *
 * Provides fuzzy matching, stemming, and relevance scoring for Arabic text
 *
 * @copyright 2025 yasircs4
 * @license   GPL v2 or later
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

namespace ArabicSearchEnhancement\Core;

use ArabicSearchEnhancement\Interfaces\CacheInterface;

class AdvancedSearchFeatures {
    
    private CacheInterface $cache;
    private array $arabic_roots;
    private array $fuzzy_patterns;
    
    public function __construct(CacheInterface $cache) {
        $this->cache = $cache;
        $this->init_arabic_roots();
        $this->init_fuzzy_patterns();
    }
    
    /**
     * Initialize common Arabic roots for stemming
     */
    private function init_arabic_roots(): void {
        $this->arabic_roots = [
            // Common 3-letter roots
            'كتب' => ['كتاب', 'مكتوب', 'كاتب', 'مكتبة', 'كتابة'],
            'درس' => ['درس', 'مدرسة', 'دراسة', 'دارس', 'مدرس'],
            'علم' => ['علم', 'معلم', 'تعليم', 'علمي', 'أعلم'],
            'شرب' => ['شرب', 'شراب', 'مشروب', 'شارب'],
            'أكل' => ['أكل', 'طعام', 'مأكل', 'آكل'],
            'ذهب' => ['ذهب', 'ذاهب', 'مذهب', 'ذهاب'],
            'جلس' => ['جلس', 'جلسة', 'مجلس', 'جالس'],
            'قرأ' => ['قرأ', 'قراءة', 'قارئ', 'مقروء'],
            // Add more roots as needed
        ];
    }
    
    /**
     * Initialize fuzzy matching patterns
     */
    private function init_fuzzy_patterns(): void {
        $this->fuzzy_patterns = [
            // Letter substitutions that sound similar
            'ظ' => 'ز',
            'ض' => 'د',
            'ث' => 'س',
            'ذ' => 'ز',
            // Common typos
            'أ' => 'ا',
            'إ' => 'ا',
            'آ' => 'ا',
            'ة' => 'ه',
            'ى' => 'ي',
        ];
    }
    
    /**
     * Generate search suggestions based on input
     *
     * @param string $query Search query
     * @return array Array of suggestions
     */
    public function get_search_suggestions(string $query): array {
        $cache_key = 'arabic_suggestions_' . md5($query);
        $cached = $this->cache->get($cache_key);
        
        if ($cached !== false) {
            return $cached;
        }
        
        $suggestions = [];
        
        // Normalize query
        $normalized_query = $this->normalize_for_suggestions($query);
        
        // Root-based suggestions
        $root_suggestions = $this->get_root_based_suggestions($normalized_query);
        $suggestions = array_merge($suggestions, $root_suggestions);
        
        // Fuzzy matching suggestions
        $fuzzy_suggestions = $this->get_fuzzy_suggestions($normalized_query);
        $suggestions = array_merge($suggestions, $fuzzy_suggestions);
        
        // Remove duplicates and limit results
        $suggestions = array_unique($suggestions);
        $suggestions = array_slice($suggestions, 0, 10);
        
        // Cache results
        $this->cache->set($cache_key, $suggestions, 3600); // 1 hour
        
        return $suggestions;
    }
    
    /**
     * Get suggestions based on Arabic roots
     *
     * @param string $query Normalized query
     * @return array Root-based suggestions
     */
    private function get_root_based_suggestions(string $query): array {
        $suggestions = [];
        
        foreach ($this->arabic_roots as $root => $variants) {
            // Check if query contains or is similar to root
            if (strpos($query, $root) !== false || $this->calculate_similarity($query, $root) > 0.6) {
                $suggestions = array_merge($suggestions, $variants);
            }
            
            // Check against variants
            foreach ($variants as $variant) {
                if ($this->calculate_similarity($query, $variant) > 0.7) {
                    $suggestions = array_merge($suggestions, $variants);
                    break;
                }
            }
        }
        
        return $suggestions;
    }
    
    /**
     * Get fuzzy matching suggestions
     *
     * @param string $query Normalized query
     * @return array Fuzzy suggestions
     */
    private function get_fuzzy_suggestions(string $query): array {
        $suggestions = [];
        
        // Generate variations using fuzzy patterns
        foreach ($this->fuzzy_patterns as $from => $to) {
            if (strpos($query, $from) !== false) {
                $variation = str_replace($from, $to, $query);
                $suggestions[] = $variation;
            }
            
            if (strpos($query, $to) !== false) {
                $variation = str_replace($to, $from, $query);
                $suggestions[] = $variation;
            }
        }
        
        return $suggestions;
    }
    
    /**
     * Calculate string similarity for Arabic text
     *
     * @param string $str1 First string
     * @param string $str2 Second string
     * @return float Similarity score (0-1)
     */
    private function calculate_similarity(string $str1, string $str2): float {
        // Use Levenshtein distance adapted for Arabic
        $len1 = mb_strlen($str1, 'UTF-8');
        $len2 = mb_strlen($str2, 'UTF-8');
        
        if ($len1 === 0) return $len2 === 0 ? 1.0 : 0.0;
        if ($len2 === 0) return 0.0;
        
        $distance = levenshtein($str1, $str2);
        $max_len = max($len1, $len2);
        
        return 1.0 - ($distance / $max_len);
    }
    
    /**
     * Normalize text for suggestion generation
     *
     * @param string $text Input text
     * @return string Normalized text
     */
    private function normalize_for_suggestions(string $text): string {
        // Remove diacritics
        $text = preg_replace('/[\x{064B}-\x{065F}]/u', '', $text);
        $text = preg_replace('/[\x{0670}]/u', '', $text);
        $text = preg_replace('/[\x{06D6}-\x{06ED}]/u', '', $text);
        
        // Normalize common variations
        $text = str_replace(['أ', 'إ', 'آ', 'ٱ'], 'ا', $text);
        $text = str_replace('ة', 'ه', $text);
        $text = str_replace('ى', 'ي', $text);
        
        return trim($text);
    }
    
    /**
     * Calculate relevance score for search results
     *
     * @param string $content Content to score
     * @param string $query Search query
     * @return float Relevance score
     */
    public function calculate_relevance_score(string $content, string $query): float {
        $score = 0.0;
        $query_terms = explode(' ', $this->normalize_for_suggestions($query));
        $content_normalized = $this->normalize_for_suggestions($content);
        
        foreach ($query_terms as $term) {
            if (empty($term)) continue;
            
            // Exact match bonus
            if (strpos($content_normalized, $term) !== false) {
                $score += 10.0;
            }
            
            // Root match bonus
            foreach ($this->arabic_roots as $root => $variants) {
                if (strpos($term, $root) !== false || in_array($term, $variants)) {
                    foreach ($variants as $variant) {
                        if (strpos($content_normalized, $variant) !== false) {
                            $score += 5.0;
                        }
                    }
                }
            }
            
            // Fuzzy match bonus
            $fuzzy_score = $this->get_fuzzy_match_score($content_normalized, $term);
            $score += $fuzzy_score;
        }
        
        // Position bonus (earlier appearance = higher score)
        foreach ($query_terms as $term) {
            $position = strpos($content_normalized, $term);
            if ($position !== false) {
                $score += (1000 - $position) / 100; // Earlier = higher score
            }
        }
        
        return $score;
    }
    
    /**
     * Get fuzzy match score for content
     *
     * @param string $content Content to check
     * @param string $term Search term
     * @return float Fuzzy match score
     */
    private function get_fuzzy_match_score(string $content, string $term): float {
        $score = 0.0;
        
        foreach ($this->fuzzy_patterns as $from => $to) {
            $fuzzy_term = str_replace($from, $to, $term);
            if ($fuzzy_term !== $term && strpos($content, $fuzzy_term) !== false) {
                $score += 3.0;
            }
            
            $fuzzy_term = str_replace($to, $from, $term);
            if ($fuzzy_term !== $term && strpos($content, $fuzzy_term) !== false) {
                $score += 3.0;
            }
        }
        
        return $score;
    }
    
    /**
     * Expand query with related terms
     *
     * @param string $query Original query
     * @return array Expanded query terms
     */
    public function expand_query(string $query): array {
        $expanded_terms = [$query];
        $normalized_query = $this->normalize_for_suggestions($query);
        $query_terms = explode(' ', $normalized_query);
        
        foreach ($query_terms as $term) {
            if (empty($term)) continue;
            
            // Add root-based expansions
            foreach ($this->arabic_roots as $root => $variants) {
                if (strpos($term, $root) !== false || in_array($term, $variants)) {
                    $expanded_terms = array_merge($expanded_terms, $variants);
                }
            }
            
            // Add fuzzy variations
            foreach ($this->fuzzy_patterns as $from => $to) {
                if (strpos($term, $from) !== false) {
                    $expanded_terms[] = str_replace($from, $to, $term);
                }
                if (strpos($term, $to) !== false) {
                    $expanded_terms[] = str_replace($to, $from, $term);
                }
            }
        }
        
        return array_unique($expanded_terms);
    }
}