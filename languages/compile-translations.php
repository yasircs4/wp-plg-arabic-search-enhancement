<?php
/**
 * Translation Compiler for Arabic Search Enhancement
 * 
 * This script compiles .po files to .mo files for the plugin translations.
 * Run this script when you update translations.
 *
 * @package ArabicSearchEnhancement
 * @since 1.1.0
 * @author yasircs4 <yasircs4@live.com>
 * @copyright 2025 yasircs4
 * @license GPL-2.0-or-later
 * @link https://yasircs4.github.io/wp-plg-arabic-search-enhancement/
 */

// Exit if accessed directly
if (!defined('ABSPATH') && php_sapi_name() !== 'cli') {
    exit;
}

if (!function_exists('ase_cli_escape')) {
    function ase_cli_escape(string $message): string {
        if (function_exists('esc_html')) {
            return esc_html($message);
        }

        return htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('ase_cli_echo')) {
    function ase_cli_echo(string $message): void {
        echo ase_cli_escape($message);
    }
}

/**
 * Simple PO to MO converter
 */
class POToMOConverter {
    
    /**
     * Convert PO file to MO file
     *
     * @param string $po_file Path to .po file
     * @param string $mo_file Path to output .mo file
     * @return bool Success status
     */
    public static function convert($po_file, $mo_file) {
        if (!file_exists($po_file)) {
            return false;
        }
        
        $translations = self::parse_po_file($po_file);
        if (empty($translations)) {
            return false;
        }
        
        return self::write_mo_file($mo_file, $translations);
    }
    
    /**
     * Parse PO file and extract translations
     *
     * @param string $po_file Path to .po file
     * @return array Translations array
     */
    private static function parse_po_file($po_file) {
        $content = file_get_contents($po_file);
        if ($content === false) {
            return [];
        }
        
        $translations = [];
        $lines = explode("\n", $content);
        $current_msgid = '';
        $current_msgstr = '';
        $in_msgid = false;
        $in_msgstr = false;
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line) || $line[0] === '#') {
                continue;
            }
            
            if (strpos($line, 'msgid "') === 0) {
                if (!empty($current_msgid) && !empty($current_msgstr)) {
                    $translations[$current_msgid] = $current_msgstr;
                }
                $current_msgid = self::extract_string($line);
                $current_msgstr = '';
                $in_msgid = true;
                $in_msgstr = false;
            } elseif (strpos($line, 'msgstr "') === 0) {
                $current_msgstr = self::extract_string($line);
                $in_msgid = false;
                $in_msgstr = true;
            } elseif ($line[0] === '"' && $in_msgid) {
                $current_msgid .= self::extract_string($line);
            } elseif ($line[0] === '"' && $in_msgstr) {
                $current_msgstr .= self::extract_string($line);
            }
        }
        
        // Add the last translation
        if (!empty($current_msgid) && !empty($current_msgstr)) {
            $translations[$current_msgid] = $current_msgstr;
        }
        
        return $translations;
    }
    
    /**
     * Extract string from PO line
     *
     * @param string $line PO file line
     * @return string Extracted string
     */
    private static function extract_string($line) {
        if (preg_match('/msgid "(.*)"|msgstr "(.*)"|"(.*)"/', $line, $matches)) {
            $string = isset($matches[3]) ? $matches[3] : (isset($matches[2]) ? $matches[2] : $matches[1]);
            // Unescape quotes and newlines
            $string = str_replace(['\\"', '\\n', '\\t'], ['"', "\n", "\t"], $string);
            return $string;
        }
        return '';
    }
    
    /**
     * Write MO file
     *
     * @param string $mo_file Path to output .mo file
     * @param array $translations Translations array
     * @return bool Success status
     */
    private static function write_mo_file($mo_file, $translations) {
        $keys = array_keys($translations);
        $values = array_values($translations);
        
        // MO file header
        $magic = 0x950412de;
        $version = 0;
        $count = count($translations);
        $koffset = 7 * 4 + 16 * $count;
        $voffset = $koffset + array_sum(array_map('strlen', $keys)) + $count * 4;
        
        $keyoffsets = [];
        $valueoffsets = [];
        $offset = $koffset;
        
        foreach ($keys as $key) {
            $keyoffsets[] = [$offset, strlen($key)];
            $offset += strlen($key) + 1;
        }
        
        $offset = $voffset;
        foreach ($values as $value) {
            $valueoffsets[] = [$offset, strlen($value)];
            $offset += strlen($value) + 1;
        }
        
        // Create MO file content
        $mo_data = pack('V', $magic);
        $mo_data .= pack('V', $version);
        $mo_data .= pack('V', $count);
        $mo_data .= pack('V', $koffset);
        $mo_data .= pack('V', $voffset);
        $mo_data .= pack('V', 0); // hash table offset
        $mo_data .= pack('V', 0); // hash table size
        
        // Key table
        foreach ($keyoffsets as $offset_length) {
            $mo_data .= pack('V', $offset_length[1]);
            $mo_data .= pack('V', $offset_length[0]);
        }
        
        // Value table
        foreach ($valueoffsets as $offset_length) {
            $mo_data .= pack('V', $offset_length[1]);
            $mo_data .= pack('V', $offset_length[0]);
        }
        
        // Keys
        foreach ($keys as $key) {
            $mo_data .= $key . "\0";
        }
        
        // Values
        foreach ($values as $value) {
            $mo_data .= $value . "\0";
        }
        
        return file_put_contents($mo_file, $mo_data) !== false;
    }
}

// Auto-compile if running as script
if (php_sapi_name() === 'cli' || (defined('DOING_AJAX') && DOING_AJAX)) {
    $plugin_dir = dirname(__FILE__);
    $languages = ['ar'];
    
    foreach ($languages as $lang) {
        $po_file = $plugin_dir . "/arabic-search-enhancement-{$lang}.po";
        $mo_file = $plugin_dir . "/arabic-search-enhancement-{$lang}.mo";
        
        if (file_exists($po_file)) {
            if (POToMOConverter::convert($po_file, $mo_file)) {
                ase_cli_echo("Compiled {$lang} translation successfully.\n");
            } else {
                ase_cli_echo("Failed to compile {$lang} translation.\n");
            }
        }
    }
}