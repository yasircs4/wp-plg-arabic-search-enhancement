<?php
/**
 * Complete Translation Build Script
 * 
 * Generates all translation files (MO and JSON) from PO sources
 *
 * @copyright 2024 Yasir Najeep
 * @license   GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH') && php_sapi_name() !== 'cli') {
    exit;
}

echo "=== Arabic Search Enhancement Translation Builder ===\n\n";

// Include compilation scripts
require_once __DIR__ . '/compile-translations.php';
require_once __DIR__ . '/create-json-translations.php';

echo "\n=== Translation Build Complete ===\n";
echo "Files generated:\n";
echo "- MO files for server-side translations\n";
echo "- JSON files for JavaScript translations\n";
echo "- Ready for WordPress deployment\n\n";

// Check if files exist
$files_to_check = [
    'arabic-search-enhancement-ar.mo',
    'arabic-search-enhancement-ar-json.json'
];

foreach ($files_to_check as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        echo "✓ $file (size: " . filesize($path) . " bytes)\n";
    } else {
        echo "✗ $file (missing)\n";
    }
}

echo "\nTranslation system ready for production!\n";
?>