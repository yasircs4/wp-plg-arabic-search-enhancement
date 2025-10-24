<?php
/**
 * Arabic Text Normalizer
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

use ArabicSearchEnhancement\Interfaces\TextNormalizerInterface;
use ArabicSearchEnhancement\Interfaces\CacheInterface;

class ArabicTextNormalizer implements TextNormalizerInterface {
    
    /**
     * Cache instance
     *
     * @var CacheInterface
     */
    private $cache;
    
    /**
     * Arabic character ranges for detection
     */
    private const ARABIC_RANGES = [
        [0x0600, 0x06FF], // Arabic
        [0x0750, 0x077F], // Arabic Supplement  
        [0x08A0, 0x08FF], // Arabic Extended-A
        [0xFB50, 0xFDFF], // Arabic Presentation Forms-A
        [0xFE70, 0xFEFF], // Arabic Presentation Forms-B
    ];
    
    /**
     * Diacritic removal pattern
     */
    private const DIACRITIC_PATTERN = '/[\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06DC}\x{06DF}-\x{06E4}\x{06E7}-\x{06E8}\x{06EA}-\x{06ED}]/u';
    
    /**
     * Letter normalization map
     */
    private const NORMALIZATION_MAP = [
        // Alef variations → plain Alef
        'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ٱ' => 'ا',
        // Taa Marbuta → Haa
        'ة' => 'ه',
        // Alef Maksura → Yaa
        'ى' => 'ي',
        // Hamza variations
        'ؤ' => 'و', 'ئ' => 'ي',
        // Remove Tatweel (kashida)
        'ـ' => '',
    ];
    
    /**
     * SQL normalization replacements for performance
     */
    private const SQL_REPLACEMENTS = [
        ['from' => 'أ', 'to' => 'ا'],
        ['from' => 'إ', 'to' => 'ا'],
        ['from' => 'آ', 'to' => 'ا'],
        ['from' => 'ٱ', 'to' => 'ا'],
        ['from' => 'ة', 'to' => 'ه'],
        ['from' => 'ى', 'to' => 'ي'],
        ['from' => 'ؤ', 'to' => 'و'],
        ['from' => 'ئ', 'to' => 'ي'],
        ['from' => 'ـ', 'to' => ''],
    ];

    /**
     * Diacritic characters to strip at the SQL level.
     */
    private const SQL_DIACRITICS = [
        'ً', 'ٌ', 'ٍ', 'َ', 'ُ', 'ِ', 'ّ', 'ْ', 'ٰ',
        'ۖ', 'ۗ', 'ۘ', 'ۙ', 'ۚ', 'ۛ', 'ۜ', '۝', '۞',
        '۟', '۠', 'ۡ', 'ۢ', 'ۣ', 'ۤ'
    ];
    
    /**
     * Constructor
     *
     * @param CacheInterface $cache Cache instance
     */
    public function __construct(CacheInterface $cache) {
        $this->cache = $cache;
    }
    
    /**
     * Normalize Arabic text
     *
     * @param string $text Text to normalize
     * @return string Normalized text
     * @throws \InvalidArgumentException If input is not a string
     */
    public function normalize_text(string $text): string {
        return $this->normalize($text);
    }
    
    /**
     * Normalize Arabic text (main implementation)
     *
     * @param string $text Text to normalize
     * @return string Normalized text
     */
    public function normalize(string $text): string {
        if ($text === '') {
            return '';
        }
        
        // Check cache first
        $cache_key = 'normalized_text_' . md5($text);
        $cached = $this->cache->get($cache_key);
        
        if ($cached !== null) {
            return $cached;
        }
        
        $normalized = $this->perform_normalization($text);
        
        // Cache the result if text is reasonably sized
        if (strlen($text) < 1000) {
            $this->cache->set($cache_key, $normalized, 1800); // 30 minutes
        }
        
        return $normalized;
    }
    
    /**
     * Get SQL expression for normalizing a database column
     *
     * @param string $column_name Database column name
     * @return string SQL expression
     * @throws \InvalidArgumentException If column name is invalid
     */
    public function get_normalization_sql(string $column_name): string {
        if (empty($column_name) || !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*(\.[a-zA-Z_][a-zA-Z0-9_]*)?$/', $column_name)) {
            throw new \InvalidArgumentException('Invalid column name provided');
        }
        
        // Check cache first
        $cache_key = 'sql_normalization_' . md5($column_name);
        $cached = $this->cache->get($cache_key);
        
        if ($cached !== null) {
            return $cached;
        }
        
        $sql = $this->build_normalization_sql($column_name);
        
        // Cache the SQL for 1 hour
        $this->cache->set($cache_key, $sql, 3600);
        
        return $sql;
    }
    
    /**
     * Check if text contains Arabic characters
     *
     * @param string $text Text to check
     * @return bool True if contains Arabic characters
     */
    public function contains_arabic(string $text): bool {
        if ($text === '') {
            return false;
        }
        
        // Quick check for common Arabic characters
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
            return true;
        }
        
        // More comprehensive check
        foreach (self::ARABIC_RANGES as [$start, $end]) {
            $pattern = sprintf('/[\x{%04X}-\x{%04X}]/u', $start, $end);
            if (preg_match($pattern, $text)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Perform the actual text normalization
     *
     * @param string $text Text to normalize
     * @return string Normalized text
     */
    private function perform_normalization(string $text): string {
        // Remove diacritics first
        $text = preg_replace(self::DIACRITIC_PATTERN, '', $text);
        
        // Apply letter normalizations
        $text = str_replace(array_keys(self::NORMALIZATION_MAP), array_values(self::NORMALIZATION_MAP), $text);
        
        // Normalize whitespace
        $text = preg_replace('/\s+/u', ' ', $text);
        
        return trim($text);
    }
    
    /**
     * Build SQL normalization expression
     *
     * @param string $column_name Database column name
     * @return string SQL expression
     */
    private function build_normalization_sql(string $column_name): string {
        global $wpdb;
        
        $sql = $column_name;
        
        foreach (self::SQL_REPLACEMENTS as $replacement) {
            // Use wpdb->prepare for safe SQL escaping
            $from = $replacement['from'];
            $to = $replacement['to'];
            
            // Manually escape since we're building SQL strings
            $from_escaped = str_replace(["'", "\\"], ["''", "\\\\"], $from);
            $to_escaped = str_replace(["'", "\\"], ["''", "\\\\"], $to);
            
            $sql = "REPLACE({$sql}, '{$from_escaped}', '{$to_escaped}')";
        }

        foreach (self::SQL_DIACRITICS as $mark) {
            $mark_escaped = str_replace(["'", "\\"], ["''", "\\\\"], $mark);
            $sql = "REPLACE({$sql}, '{$mark_escaped}', '')";
        }
        
        return $sql;
    }
    
    /**
     * Get normalization statistics
     *
     * @param string $text Original text
     * @return array Statistics about normalization
     */
    public function get_normalization_stats(string $text): array {
        $original_length = mb_strlen($text, 'UTF-8');
        $normalized = $this->normalize_text($text);
        $normalized_length = mb_strlen($normalized, 'UTF-8');
        
        return [
            'original_length' => $original_length,
            'normalized_length' => $normalized_length,
            'reduction_ratio' => $original_length > 0 ? ($original_length - $normalized_length) / $original_length : 0,
            'contains_arabic' => $this->contains_arabic($text),
            'has_diacritics' => preg_match(self::DIACRITIC_PATTERN, $text) === 1,
        ];
    }
}