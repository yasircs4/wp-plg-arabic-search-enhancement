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

ase_cli_echo("=== Arabic Search Enhancement Translation Builder ===\n\n");

// Include compilation scripts
require_once __DIR__ . '/compile-translations.php';
require_once __DIR__ . '/create-json-translations.php';

ase_cli_echo("\n=== Translation Build Complete ===\n");
ase_cli_echo("Files generated:\n");
ase_cli_echo("- MO files for server-side translations\n");
ase_cli_echo("- JSON files for JavaScript translations\n");
ase_cli_echo("- Ready for WordPress deployment\n\n");

// Check if files exist
$files_to_check = [
    'arabic-search-enhancement-ar.mo',
    'arabic-search-enhancement-ar-json.json'
];

foreach ($files_to_check as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        ase_cli_echo("✓ $file (size: " . filesize($path) . " bytes)\n");
    } else {
            ase_cli_echo("✗ $file (missing)\n");
    }
}

ase_cli_echo("\nTranslation system ready for production!\n");
?>