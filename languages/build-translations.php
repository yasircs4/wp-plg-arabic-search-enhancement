<?php
/**
 * Complete Translation Build Script
 * 
 * Generates all translation files (MO and JSON) from PO sources
 *
 * @copyright 2025 yasircs4
 * @license   GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH') && php_sapi_name() !== 'cli') {
    exit;
}

if (!function_exists('arabic_search_enhancement_cli_escape')) {
    function arabic_search_enhancement_cli_escape(string $message): string {
        if (function_exists('esc_html')) {
            return esc_html($message);
        }

        return htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('arabic_search_enhancement_cli_echo')) {
    function arabic_search_enhancement_cli_echo(string $message): void {
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo arabic_search_enhancement_cli_escape($message);
    }
}

arabic_search_enhancement_cli_echo("=== Arabic Search Enhancement Translation Builder ===\n\n");

// Include compilation scripts
require_once __DIR__ . '/compile-translations.php';
require_once __DIR__ . '/create-json-translations.php';

arabic_search_enhancement_cli_echo("\n=== Translation Build Complete ===\n");
arabic_search_enhancement_cli_echo("Files generated:\n");
arabic_search_enhancement_cli_echo("- MO files for server-side translations\n");
arabic_search_enhancement_cli_echo("- JSON files for JavaScript translations\n");
arabic_search_enhancement_cli_echo("- Ready for WordPress deployment\n\n");

// Check if files exist
$arabic_search_enhancement_files_to_check = [
    'arabic-search-enhancement-ar.mo',
    'arabic-search-enhancement-ar-json.json'
];

foreach ($arabic_search_enhancement_files_to_check as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        arabic_search_enhancement_cli_echo("✓ $file (size: " . filesize($path) . " bytes)\n");
    } else {
            arabic_search_enhancement_cli_echo("✗ $file (missing)\n");
    }
}

arabic_search_enhancement_cli_echo("\nTranslation system ready for production!\n");
?>