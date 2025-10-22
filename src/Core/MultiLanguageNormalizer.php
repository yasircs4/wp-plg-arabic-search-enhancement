<?php
/**
 * Multi-language Support for Arabic-script Languages
 *
 * Extends Arabic search to support Urdu, Persian, and other related languages
 *
 * @copyright 2024 Yasir Najeep
 * @license   GPL v2 or later
 */

namespace ArabicSearchEnhancement\Core;

use ArabicSearchEnhancement\Interfaces\TextNormalizerInterface;
use ArabicSearchEnhancement\Interfaces\CacheInterface;

class MultiLanguageNormalizer implements TextNormalizerInterface {
    
    private CacheInterface $cache;
    private array $language_configs;
    private string $current_language;
    
    public function __construct(CacheInterface $cache) {
        $this->cache = $cache;
        $this->init_language_configs();
        $this->detect_language();
    }
    
    /**
     * Initialize language-specific configurations
     */
    private function init_language_configs(): void {
        $this->language_configs = [
            'ar' => [ // Arabic
                'name' => 'Arabic',
                'script' => 'arabic',
                'diacritics' => '/[\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}]/u',
                'normalizations' => [
                    'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ٱ' => 'ا',
                    'ة' => 'ه', 'ى' => 'ي', 'ؤ' => 'و', 'ئ' => 'ي'
                ],
                'remove_chars' => '/[\x{0640}]/u', // Tatweel
            ],
            'ur' => [ // Urdu
                'name' => 'Urdu',
                'script' => 'arabic',
                'diacritics' => '/[\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}]/u',
                'normalizations' => [
                    'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ٱ' => 'ا',
                    'ة' => 'ه', 'ى' => 'ي', 'ؤ' => 'و', 'ئ' => 'ي',
                    // Urdu-specific
                    'ھ' => 'ه', 'ک' => 'ك', 'گ' => 'ك', 'ی' => 'ي'
                ],
                'remove_chars' => '/[\x{0640}]/u',
                'urdu_chars' => '/[\x{06A9}\x{06AF}\x{06BE}\x{06CC}\x{06D2}]/u', // Special Urdu characters
            ],
            'fa' => [ // Persian/Farsi
                'name' => 'Persian',
                'script' => 'arabic',
                'diacritics' => '/[\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}]/u',
                'normalizations' => [
                    'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ٱ' => 'ا',
                    'ة' => 'ه', 'ى' => 'ي', 'ؤ' => 'و', 'ئ' => 'ي',
                    // Persian-specific
                    'ک' => 'ك', 'ی' => 'ي', 'پ' => 'ب', 'چ' => 'ج',
                    'ژ' => 'ز', 'گ' => 'ك'
                ],
                'remove_chars' => '/[\x{0640}]/u',
                'persian_chars' => '/[\x{067E}\x{0686}\x{0698}\x{06A9}\x{06AF}\x{06CC}]/u', // Special Persian characters
            ],
            'ps' => [ // Pashto
                'name' => 'Pashto',
                'script' => 'arabic',
                'diacritics' => '/[\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}]/u',
                'normalizations' => [
                    'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ٱ' => 'ا',
                    'ة' => 'ه', 'ى' => 'ي', 'ؤ' => 'و', 'ئ' => 'ي',
                    // Pashto-specific
                    'ښ' => 'س', 'ږ' => 'ر', 'ې' => 'ي', 'ۍ' => 'ي'
                ],
                'remove_chars' => '/[\x{0640}]/u',
            ],
            'sd' => [ // Sindhi
                'name' => 'Sindhi',
                'script' => 'arabic',
                'diacritics' => '/[\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}]/u',
                'normalizations' => [
                    'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ٱ' => 'ا',
                    'ة' => 'ه', 'ى' => 'ي', 'ؤ' => 'و', 'ئ' => 'ي',
                    // Sindhi-specific normalizations
                    'ڪ' => 'ك', 'ڳ' => 'ك'
                ],
                'remove_chars' => '/[\x{0640}]/u',
            ]
        ];
    }
    
    /**
     * Detect current language from WordPress locale
     */
    private function detect_language(): void {
        $locale = get_locale();
        $language_code = substr($locale, 0, 2);
        
        // Default to Arabic if language not supported
        $this->current_language = isset($this->language_configs[$language_code]) ? $language_code : 'ar';
    }
    
    /**
     * Set the current language manually
     *
     * @param string $language_code Language code (ar, ur, fa, ps, sd)
     * @throws InvalidArgumentException If language not supported
     */
    public function set_language(string $language_code): void {
        if (!isset($this->language_configs[$language_code])) {
            // translators: %s: unsupported language code
            throw new InvalidArgumentException(sprintf(esc_html__('Language %s is not supported.', 'arabic-search-enhancement'), esc_html($language_code)));
        }
        
        $this->current_language = $language_code;
    }
    
    /**
     * Get supported languages
     *
     * @return array Array of supported language codes and names
     */
    public function get_supported_languages(): array {
        $languages = [];
        foreach ($this->language_configs as $code => $config) {
            $languages[$code] = $config['name'];
        }
        return $languages;
    }
    
    /**
     * Normalize text based on current language settings
     *
     * @param string $text Text to normalize
     * @return string Normalized text
     */
    public function normalize(string $text): string {
        if (empty($text)) {
            return '';
        }
        
        $cache_key = 'multilang_norm_' . $this->current_language . '_' . md5($text);
        $cached = $this->cache->get($cache_key);
        
        if ($cached !== false) {
            return $cached;
        }
        
        $config = $this->language_configs[$this->current_language];
        $normalized = $text;
        
        // Remove diacritics
        $normalized = preg_replace($config['diacritics'], '', $normalized);
        
        // Apply language-specific normalizations
        $normalized = str_replace(
            array_keys($config['normalizations']),
            array_values($config['normalizations']),
            $normalized
        );
        
        // Remove special characters like Tatweel
        $normalized = preg_replace($config['remove_chars'], '', $normalized);
        
        // Language-specific processing
        $normalized = $this->apply_language_specific_rules($normalized, $config);
        
        // Clean up whitespace
        $normalized = preg_replace('/\s+/', ' ', trim($normalized));
        
        // Cache the result
        $this->cache->set($cache_key, $normalized, 3600);
        
        return $normalized;
    }
    
    /**
     * Apply language-specific normalization rules
     *
     * @param string $text Text to process
     * @param array $config Language configuration
     * @return string Processed text
     */
    private function apply_language_specific_rules(string $text, array $config): string {
        switch ($this->current_language) {
            case 'ur': // Urdu
                return $this->apply_urdu_rules($text);
                
            case 'fa': // Persian
                return $this->apply_persian_rules($text);
                
            case 'ps': // Pashto
                return $this->apply_pashto_rules($text);
                
            case 'ar': // Arabic
            default:
                return $this->apply_arabic_rules($text);
        }
    }
    
    /**
     * Apply Urdu-specific normalization rules
     *
     * @param string $text Text to process
     * @return string Processed text
     */
    private function apply_urdu_rules(string $text): string {
        // Handle Urdu-specific letter variations
        $urdu_normalizations = [
            'ھ' => 'ه',  // Urdu Heh Doachashmee to Arabic Heh
            'ک' => 'ك',  // Urdu Kaf to Arabic Kaf
            'گ' => 'ك',  // Urdu Gaf to Kaf (for search purposes)
            'ی' => 'ي',  // Urdu Yeh to Arabic Yeh
            'ے' => 'ي',  // Urdu Yeh Barree to Yeh
        ];
        
        return str_replace(array_keys($urdu_normalizations), array_values($urdu_normalizations), $text);
    }
    
    /**
     * Apply Persian-specific normalization rules
     *
     * @param string $text Text to process
     * @return string Processed text
     */
    private function apply_persian_rules(string $text): string {
        // Handle Persian-specific letter variations
        $persian_normalizations = [
            'ک' => 'ك',  // Persian Kaf to Arabic Kaf
            'ی' => 'ي',  // Persian Yeh to Arabic Yeh
            'پ' => 'ب',  // Persian Peh to Beh (for search similarity)
            'چ' => 'ج',  // Persian Cheh to Jeem
            'ژ' => 'ز',  // Persian Zheh to Zain
            'گ' => 'ك',  // Persian Gaf to Kaf
        ];
        
        return str_replace(array_keys($persian_normalizations), array_values($persian_normalizations), $text);
    }
    
    /**
     * Apply Pashto-specific normalization rules
     *
     * @param string $text Text to process
     * @return string Processed text
     */
    private function apply_pashto_rules(string $text): string {
        // Handle Pashto-specific letter variations
        $pashto_normalizations = [
            'ښ' => 'س',  // Pashto Xeh to Seen
            'ږ' => 'ر',  // Pashto Zheh to Reh
            'ې' => 'ي',  // Pashto Yeh to Arabic Yeh
            'ۍ' => 'ي',  // Pashto Ye to Arabic Yeh
            'ړ' => 'ر',  // Pashto Reh to Arabic Reh
            'ډ' => 'د',  // Pashto Dal to Arabic Dal
        ];
        
        return str_replace(array_keys($pashto_normalizations), array_values($pashto_normalizations), $text);
    }
    
    /**
     * Apply Arabic-specific normalization rules
     *
     * @param string $text Text to process
     * @return string Processed text
     */
    private function apply_arabic_rules(string $text): string {
        // Standard Arabic normalization (already handled in main normalize function)
        return $text;
    }
    
    /**
     * Detect the script/language of given text
     *
     * @param string $text Text to analyze
     * @return string Detected language code
     */
    public function detect_text_language(string $text): string {
        $text_sample = mb_substr($text, 0, 100, 'UTF-8'); // Sample first 100 characters
        
        foreach ($this->language_configs as $lang_code => $config) {
            if ($lang_code === 'ar') continue; // Check Arabic last as fallback
            
            // Check for language-specific characters
            if (isset($config['urdu_chars']) && preg_match($config['urdu_chars'], $text_sample)) {
                return 'ur';
            }
            if (isset($config['persian_chars']) && preg_match($config['persian_chars'], $text_sample)) {
                return 'fa';
            }
        }
        
        // Check for Arabic script in general
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $text_sample)) {
            return 'ar'; // Default to Arabic for Arabic script
        }
        
        return $this->current_language; // Return current language as fallback
    }
    
    /**
     * Check if text contains content in supported language
     *
     * @param string $text Text to check
     * @return bool True if contains supported language content
     */
    public function contains_supported_language(string $text): bool {
        // Check for Arabic script characters (covers all supported languages)
        return preg_match('/[\x{0600}-\x{06FF}]/u', $text) === 1;
    }
    
    /**
     * Get language-specific search suggestions
     *
     * @param string $query Search query
     * @return array Language-specific suggestions
     */
    public function get_language_suggestions(string $query): array {
        $suggestions = [];
        $detected_lang = $this->detect_text_language($query);
        
        // Generate variations based on common substitutions in the detected language
        switch ($detected_lang) {
            case 'ur':
                $suggestions = $this->get_urdu_suggestions($query);
                break;
            case 'fa':
                $suggestions = $this->get_persian_suggestions($query);
                break;
            case 'ps':
                $suggestions = $this->get_pashto_suggestions($query);
                break;
            default:
                $suggestions = $this->get_arabic_suggestions($query);
        }
        
        return array_unique($suggestions);
    }
    
    /**
     * Get Urdu-specific suggestions
     *
     * @param string $query Query text
     * @return array Suggestions
     */
    private function get_urdu_suggestions(string $query): array {
        $suggestions = [$query];
        
        // Generate variations with different Urdu letter forms
        $variations = [
            'ک' => 'ك', 'ك' => 'ک',
            'ی' => 'ي', 'ي' => 'ی',
            'ھ' => 'ه', 'ه' => 'ھ'
        ];
        
        foreach ($variations as $from => $to) {
            if (strpos($query, $from) !== false) {
                $suggestions[] = str_replace($from, $to, $query);
            }
        }
        
        return $suggestions;
    }
    
    /**
     * Get Persian-specific suggestions
     *
     * @param string $query Query text
     * @return array Suggestions
     */
    private function get_persian_suggestions(string $query): array {
        $suggestions = [$query];
        
        // Generate variations with different Persian letter forms
        $variations = [
            'ک' => 'ك', 'ك' => 'ک',
            'ی' => 'ي', 'ي' => 'ی'
        ];
        
        foreach ($variations as $from => $to) {
            if (strpos($query, $from) !== false) {
                $suggestions[] = str_replace($from, $to, $query);
            }
        }
        
        return $suggestions;
    }
    
    /**
     * Get Pashto-specific suggestions
     *
     * @param string $query Query text
     * @return array Suggestions
     */
    private function get_pashto_suggestions(string $query): array {
        $suggestions = [$query];
        
        // Generate variations with Pashto letter alternatives
        $variations = [
            'ښ' => 'س', 'س' => 'ښ',
            'ږ' => 'ر', 'ر' => 'ږ'
        ];
        
        foreach ($variations as $from => $to) {
            if (strpos($query, $from) !== false) {
                $suggestions[] = str_replace($from, $to, $query);
            }
        }
        
        return $suggestions;
    }
    
    /**
     * Get Arabic-specific suggestions
     *
     * @param string $query Query text
     * @return array Suggestions
     */
    private function get_arabic_suggestions(string $query): array {
        $suggestions = [$query];
        
        // Generate variations with different Arabic letter forms
        $variations = [
            'أ' => 'ا', 'ا' => 'أ',
            'إ' => 'ا', 'ا' => 'إ',
            'ة' => 'ه', 'ه' => 'ة',
            'ى' => 'ي', 'ي' => 'ى'
        ];
        
        foreach ($variations as $from => $to) {
            if (strpos($query, $from) !== false) {
                $suggestions[] = str_replace($from, $to, $query);
            }
        }
        
        return $suggestions;
    }
    
    /**
     * Normalize text (required by TextNormalizerInterface)
     *
     * @param string $text Text to normalize
     * @return string Normalized text
     */
    public function normalize_text(string $text): string {
        return $this->normalize($text);
    }
    
    /**
     * Get SQL for normalization (required by TextNormalizerInterface)
     *
     * @param string $column_name Database column name
     * @return string SQL for normalization
     */
    public function get_normalization_sql(string $column_name): string {
        // For multi-language support, we use the basic Arabic normalization
        // This could be enhanced to support language-specific SQL functions
        return "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE({$column_name}, 
            'أ', 'ا'), 'إ', 'ا'), 'آ', 'ا'), 'ة', 'ه'), 'ى', 'ي'), 'ؤ', 'و'), 'ئ', 'ي'), 'ء', '')";
    }
    
    /**
     * Contains Arabic text detection (required by TextNormalizerInterface)
     *
     * @param string $text Text to check
     * @return bool True if text contains Arabic characters
     */
    public function contains_arabic(string $text): bool {
        return $this->contains_supported_language($text);
    }
}