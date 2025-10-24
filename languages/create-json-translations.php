<?php
/**
 * JavaScript Translation Generator for Arabic Search Enhancement Plugin
 *
 * This script creates JSON translation files for JavaScript from PO files
 *
 * @copyright 2025 yasircs4
 * @license   GPL v2 or later
 */

// Prevent direct access
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
 * Convert PO file to JSON format for JavaScript translations
 *
 * @param string $po_file Path to PO file
 * @param string $json_file Path to output JSON file
 * @return bool Success status
 */
function create_js_translations($po_file, $json_file) {
    if (!file_exists($po_file)) {
        ase_cli_echo("PO file not found: $po_file\n");
        return false;
    }
    
    $translations = [];
    $content = file_get_contents($po_file);
    
    // Parse PO file
    preg_match_all('/msgid\s+"([^"]+)"\s+msgstr\s+"([^"]*)"/', $content, $matches, PREG_SET_ORDER);
    
    foreach ($matches as $match) {
        $msgid = $match[1];
        $msgstr = $match[2];
        
        // Skip empty translations
        if (empty($msgstr) || $msgid === $msgstr) {
            continue;
        }
        
        $translations[$msgid] = $msgstr;
    }
    
    // Create WordPress i18n compatible JSON
    $json_data = [
        'locale_data' => [
            'messages' => array_merge([''], $translations)
        ]
    ];
    
    $json_content = json_encode($json_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
    if (file_put_contents($json_file, $json_content) !== false) {
        ase_cli_echo("Created JSON translation: $json_file\n");
        return true;
    }
    
    ase_cli_echo("Failed to create JSON file: $json_file\n");
    return false;
}

// Get script directory
$script_dir = __DIR__;

// Define file paths
$po_files = [
    'ar' => $script_dir . '/arabic-search-enhancement-ar.po',
];

// Create JSON translations
foreach ($po_files as $locale => $po_file) {
    $json_file = $script_dir . '/arabic-search-enhancement-' . $locale . '-json.json';
    create_js_translations($po_file, $json_file);
}

echo "JavaScript translation generation completed!\n";
?>