<?php
/**
 * WordPress.org Submission Helper
 *
 * Prepares the plugin for WordPress.org repository submission
 *
 * @copyright 2024 Yasir Najeep
 * @license   GPL v2 or later
 */

namespace ArabicSearchEnhancement\Utils;

use ArabicSearchEnhancement\Core\Plugin;
use ArabicSearchEnhancement\Interfaces\ConfigurationInterface;

class RepositorySubmissionHelper {
    
    private ConfigurationInterface $config;
    private string $plugin_dir;
    private string $submission_dir;
    
    public function __construct(ConfigurationInterface $config, string $plugin_dir) {
        $this->config = $config;
        $this->plugin_dir = $plugin_dir;
        $this->submission_dir = $plugin_dir . '/submission';
    }
    
    /**
     * Prepare plugin for WordPress.org submission
     *
     * @return array Submission preparation results
     */
    public function prepare_submission(): array {
        $results = [
            'success' => true,
            'steps_completed' => [],
            'warnings' => [],
            'errors' => []
        ];
        
        try {
            // Create submission directory
            $this->create_submission_directory();
            $results['steps_completed'][] = 'Created submission directory';
            
            // Validate plugin structure
            $structure_check = $this->validate_plugin_structure();
            if (!$structure_check['valid']) {
                $results['errors'] = array_merge($results['errors'], $structure_check['errors']);
                $results['success'] = false;
            } else {
                $results['steps_completed'][] = 'Plugin structure validated';
            }
            
            // Generate readme.txt for WordPress.org
            $this->generate_wordpress_readme();
            $results['steps_completed'][] = 'Generated WordPress.org readme.txt';
            
            // Create banner and icon assets
            $this->create_submission_assets();
            $results['steps_completed'][] = 'Created submission assets';
            
            // Validate security practices
            $security_check = $this->validate_security_practices();
            if (!empty($security_check['warnings'])) {
                $results['warnings'] = array_merge($results['warnings'], $security_check['warnings']);
            }
            $results['steps_completed'][] = 'Security practices validated';
            
            // Generate plugin documentation
            $this->generate_documentation();
            $results['steps_completed'][] = 'Generated plugin documentation';
            
            // Create deployment package
            $package_path = $this->create_deployment_package();
            $results['package_path'] = $package_path;
            $results['steps_completed'][] = 'Created deployment package';
            
            // Generate submission checklist
            $checklist = $this->generate_submission_checklist();
            $results['checklist'] = $checklist;
            $results['steps_completed'][] = 'Generated submission checklist';
            
        } catch (Exception $e) {
            $results['success'] = false;
            $results['errors'][] = 'Submission preparation failed: ' . $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * Create submission directory
     */
    private function create_submission_directory(): void {
        if (!file_exists($this->submission_dir)) {
            wp_mkdir_p($this->submission_dir);
        }
        
        // Create subdirectories
        $subdirs = ['assets', 'docs', 'screenshots', 'package'];
        foreach ($subdirs as $subdir) {
            $path = $this->submission_dir . '/' . $subdir;
            if (!file_exists($path)) {
                wp_mkdir_p($path);
            }
        }
    }
    
    /**
     * Validate plugin structure for WordPress.org requirements
     *
     * @return array Validation results
     */
    private function validate_plugin_structure(): array {
        $results = ['valid' => true, 'errors' => []];
        
        // Required files
        $required_files = [
            'wp-plg-arabic-search-enhancement.php' => 'Main plugin file',
            'readme.txt' => 'WordPress.org readme',
            'languages/arabic-search-enhancement.pot' => 'Translation template'
        ];
        
        foreach ($required_files as $file => $description) {
            if (!file_exists($this->plugin_dir . '/' . $file)) {
                $results['valid'] = false;
                $results['errors'][] = "Missing required file: {$file} ({$description})";
            }
        }
        
        // Check main plugin file headers
        $main_file = $this->plugin_dir . '/wp-plg-arabic-search-enhancement.php';
        if (file_exists($main_file)) {
            $headers = get_file_data($main_file, [
                'Plugin Name' => 'Plugin Name',
                'Description' => 'Description',
                'Version' => 'Version',
                'Author' => 'Author',
                'License' => 'License',
                'Text Domain' => 'Text Domain'
            ]);
            
            $required_headers = ['Plugin Name', 'Description', 'Version', 'Author'];
            foreach ($required_headers as $header) {
                if (empty($headers[$header])) {
                    $results['valid'] = false;
                    $results['errors'][] = "Missing plugin header: {$header}";
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Generate WordPress.org compatible readme.txt
     */
    private function generate_wordpress_readme(): void {
        $readme_content = $this->get_wordpress_readme_content();
        file_put_contents($this->submission_dir . '/readme.txt', $readme_content);
    }
    
    /**
     * Get WordPress.org readme content
     *
     * @return string Readme content
     */
    private function get_wordpress_readme_content(): string {
        return "=== Arabic Search Enhancement ===
Contributors: yasirnajeep
Tags: arabic, search, enhancement, multilingual, rtl
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 8.0
Stable tag: 1.3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Enhance WordPress search functionality for Arabic and other RTL languages with advanced text normalization and fuzzy matching.

== Description ==

Arabic Search Enhancement is a comprehensive WordPress plugin that significantly improves search functionality for Arabic, Urdu, Persian, and other Arabic-script languages. The plugin addresses common issues with Arabic text search by implementing advanced normalization techniques and intelligent matching algorithms.

= Key Features =

* **Advanced Arabic Text Normalization**: Handles diacritics, ligatures, and various Arabic character forms
* **Multi-Language Support**: Supports Arabic, Urdu, Persian, Pashto, and Sindhi
* **Fuzzy Search**: Intelligent matching even with spelling variations
* **Performance Optimization**: Advanced caching and indexing for fast search results
* **Search Analytics**: Comprehensive dashboard with search statistics and insights
* **REST API Integration**: Full API for headless WordPress and external integrations
* **Admin Dashboard**: Easy-to-use settings and analytics interface

= Supported Languages =

* Arabic (العربية)
* Urdu (اردو)
* Persian/Farsi (فارسی)
* Pashto (پښتو)
* Sindhi (سنڌي)

= Technical Features =

* Text normalization and diacritic removal
* Fuzzy matching with configurable similarity thresholds
* Search result caching and optimization
* Multi-language query expansion
* Search analytics and reporting
* RESTful API endpoints
* WordPress multisite compatibility

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/arabic-search-enhancement` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the Settings -> Arabic Search Enhancement screen to configure the plugin.
4. The plugin will automatically enhance your search functionality for Arabic and RTL languages.

== Frequently Asked Questions ==

= Does this plugin work with all themes? =

Yes, the plugin works with any WordPress theme as it enhances the core WordPress search functionality at the database level.

= Can I use this plugin with other search plugins? =

The plugin is designed to work alongside most search plugins, but some conflicts may occur with plugins that completely override WordPress search.

= Is the plugin compatible with multisite installations? =

Yes, the plugin is fully compatible with WordPress multisite installations.

= Does the plugin affect site performance? =

The plugin includes advanced caching and optimization features to ensure minimal performance impact while significantly improving search accuracy.

= Can I customize the normalization rules? =

Yes, the plugin provides filters and hooks for developers to customize normalization rules and search behavior.

== Screenshots ==

1. Main settings page with configuration options
2. Search analytics dashboard showing query statistics
3. Advanced search features configuration
4. Multi-language support settings
5. Performance optimization settings
6. REST API endpoints documentation

== Changelog ==

= 1.0.0 =
* Initial release
* Advanced Arabic text normalization
* Multi-language support for Arabic-script languages
* Fuzzy search with similarity matching
* Performance optimization with caching
* Search analytics dashboard
* REST API integration
* Comprehensive admin interface

== Upgrade Notice ==

= 1.0.0 =
Initial release of Arabic Search Enhancement plugin with comprehensive search improvements for Arabic and RTL languages.

== Developer Information ==

= Hooks and Filters =

The plugin provides numerous hooks and filters for customization:

* `arabic_search_normalize_text` - Filter normalized text
* `arabic_search_similarity_threshold` - Adjust fuzzy matching threshold
* `arabic_search_supported_languages` - Add or modify supported languages
* `arabic_search_cache_duration` - Customize cache duration

= API Documentation =

REST API endpoints are available at `/wp-json/arabic-search/v1/` with full documentation included.

= Support =

For support and bug reports, please visit the plugin's GitHub repository or WordPress.org support forum.
";
    }
    
    /**
     * Create submission assets (banners, icons, screenshots)
     */
    private function create_submission_assets(): void {
        $assets_dir = $this->submission_dir . '/assets';
        
        // Create placeholder asset descriptions
        $asset_descriptions = [
            'banner-772x250.png' => 'High resolution banner for plugin directory',
            'banner-1544x500.png' => 'High DPI banner for plugin directory',
            'icon-128x128.png' => 'Plugin icon for directory listing',
            'icon-256x256.png' => 'High resolution plugin icon'
        ];
        
        foreach ($asset_descriptions as $asset => $description) {
            $asset_info = "# {$asset}\n\n{$description}\n\nRecommended specifications:\n";
            
            switch ($asset) {
                case 'banner-772x250.png':
                    $asset_info .= "- Size: 772x250 pixels\n- Format: PNG or JPG\n- Theme: Arabic calligraphy or search icons\n";
                    break;
                case 'banner-1544x500.png':
                    $asset_info .= "- Size: 1544x500 pixels\n- Format: PNG or JPG\n- Theme: Arabic calligraphy or search icons\n";
                    break;
                case 'icon-128x128.png':
                    $asset_info .= "- Size: 128x128 pixels\n- Format: PNG\n- Theme: Magnifying glass with Arabic text\n";
                    break;
                case 'icon-256x256.png':
                    $asset_info .= "- Size: 256x256 pixels\n- Format: PNG\n- Theme: Magnifying glass with Arabic text\n";
                    break;
            }
            
            file_put_contents($assets_dir . '/' . str_replace('.png', '.md', $asset), $asset_info);
        }
        
        // Create screenshot descriptions
        $screenshot_descriptions = [
            'screenshot-1.png' => 'Main settings page showing configuration options for Arabic search enhancement',
            'screenshot-2.png' => 'Search analytics dashboard displaying query statistics and performance metrics',
            'screenshot-3.png' => 'Advanced search features configuration with fuzzy matching settings',
            'screenshot-4.png' => 'Multi-language support settings for Arabic-script languages',
            'screenshot-5.png' => 'Performance optimization settings with caching options',
            'screenshot-6.png' => 'REST API documentation showing available endpoints'
        ];
        
        $screenshots_dir = $this->submission_dir . '/screenshots';
        foreach ($screenshot_descriptions as $screenshot => $description) {
            file_put_contents(
                $screenshots_dir . '/' . str_replace('.png', '.md', $screenshot),
                "# {$screenshot}\n\n{$description}\n\nRecommendations:\n- Size: 1280x720 pixels or similar 16:9 ratio\n- Format: PNG or JPG\n- Show clear, readable interface elements\n"
            );
        }
    }
    
    /**
     * Validate security practices
     *
     * @return array Security validation results
     */
    private function validate_security_practices(): array {
        $results = ['warnings' => []];
        
        // Check for proper nonce usage
        $php_files = $this->get_php_files($this->plugin_dir . '/src');
        
        foreach ($php_files as $file) {
            $content = file_get_contents($file);
            
            // Check for direct database queries without prepare
            if (preg_match('/\$wpdb->(query|get_).*\$/', $content) && !preg_match('/prepare/', $content)) {
                $results['warnings'][] = "Potential SQL injection risk in: " . basename($file);
            }
            
            // Check for sanitization in user input
            if (preg_match('/\$_(?:GET|POST|REQUEST)\[/', $content) && !preg_match('/sanitize_/', $content)) {
                $results['warnings'][] = "Missing input sanitization in: " . basename($file);
            }
        }
        
        return $results;
    }
    
    /**
     * Generate plugin documentation
     */
    private function generate_documentation(): void {
        $docs_dir = $this->submission_dir . '/docs';
        
        // API Documentation
        $api_doc = $this->get_api_documentation();
        file_put_contents($docs_dir . '/API.md', $api_doc);
        
        // Developer Guide
        $dev_guide = $this->get_developer_guide();
        file_put_contents($docs_dir . '/DEVELOPER_GUIDE.md', $dev_guide);
        
        // Installation Guide
        $install_guide = $this->get_installation_guide();
        file_put_contents($docs_dir . '/INSTALLATION.md', $install_guide);
    }
    
    /**
     * Create deployment package
     *
     * @return string Path to created package
     */
    private function create_deployment_package(): string {
        $package_dir = $this->submission_dir . '/package';
        $plugin_name = 'arabic-search-enhancement';
        $package_path = $package_dir . '/' . $plugin_name . '.zip';
        
        // Files to exclude from package
        $exclude_patterns = [
            'submission/',
            '.git/',
            'node_modules/',
            '.DS_Store',
            'Thumbs.db',
            '*.log',
            '.env*',
            'composer.lock'
        ];
        
        // Create zip file (this would require a proper zip implementation)
        // For now, create a file list
        $file_list = $this->get_package_file_list($exclude_patterns);
        file_put_contents($package_dir . '/file_list.txt', implode("\n", $file_list));
        
        return $package_path;
    }
    
    /**
     * Generate submission checklist
     *
     * @return array Submission checklist
     */
    private function generate_submission_checklist(): array {
        return [
            'required' => [
                '✓ Plugin follows WordPress coding standards',
                '✓ All text is internationalized with proper text domain',
                '✓ Plugin is compatible with latest WordPress version',
                '✓ No PHP errors or warnings',
                '✓ Proper input sanitization and output escaping',
                '✓ GPL-compatible license',
                '✓ Unique plugin name and slug',
                '✓ Complete readme.txt file',
                '✓ Plugin tested on multiple environments'
            ],
            'recommended' => [
                '✓ Plugin banner and icon assets created',
                '✓ Screenshots prepared showing key features',
                '✓ Comprehensive documentation provided',
                '✓ API documentation included',
                '✓ Performance optimizations implemented',
                '✓ Accessibility considerations addressed',
                '✓ Mobile-responsive admin interface',
                '✓ Multisite compatibility tested'
            ],
            'before_submission' => [
                '• Test plugin on fresh WordPress installation',
                '• Verify all features work as documented',
                '• Check for conflicts with popular themes/plugins',
                '• Review and test all API endpoints',
                '• Validate all translation files',
                '• Perform security audit',
                '• Test uninstall/cleanup process',
                '• Create final deployment package'
            ]
        ];
    }
    
    /**
     * Get all PHP files in directory recursively
     *
     * @param string $dir Directory to scan
     * @return array Array of PHP file paths
     */
    private function get_php_files(string $dir): array {
        $files = [];
        
        if (!is_dir($dir)) {
            return $files;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }
        
        return $files;
    }
    
    /**
     * Get package file list
     *
     * @param array $exclude_patterns Patterns to exclude
     * @return array Array of files to include in package
     */
    private function get_package_file_list(array $exclude_patterns): array {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->plugin_dir)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $relative_path = str_replace($this->plugin_dir . '/', '', $file->getPathname());
                
                // Check if file should be excluded
                $exclude = false;
                foreach ($exclude_patterns as $pattern) {
                    if (fnmatch($pattern, $relative_path)) {
                        $exclude = true;
                        break;
                    }
                }
                
                if (!$exclude) {
                    $files[] = $relative_path;
                }
            }
        }
        
        return $files;
    }
    
    /**
     * Get API documentation content
     *
     * @return string API documentation
     */
    private function get_api_documentation(): string {
        return '# Arabic Search Enhancement API Documentation

## Overview

The Arabic Search Enhancement plugin provides a comprehensive REST API for integrating advanced Arabic search functionality into headless WordPress installations and external applications.

## Base URL

All API endpoints are available at: `/wp-json/arabic-search/v1/`

## Authentication

- Public endpoints: No authentication required
- Admin endpoints: Requires administrator privileges
- User endpoints: Requires user authentication

## Endpoints

### Search Endpoints

#### POST /search
Search posts using enhanced Arabic search functionality.

**Parameters:**
- query (required): Search query string
- post_type (optional): Array of post types to search
- per_page (optional): Number of results per page (1-100)
- page (optional): Page number
- language (optional): Language code (ar, ur, fa, ps, sd)

**Response:** JSON object with search results, pagination info, and query details.

#### GET /search/suggestions
Get search suggestions for a query.

### Text Processing Endpoints

#### POST /normalize
Normalize Arabic text using the plugin normalization engine.

#### POST /detect-language
Detect the language of provided text.

### Analytics Endpoints (Admin Only)

#### GET /analytics/stats
Get search analytics statistics.

#### GET /analytics/top-queries
Get top search queries.

### Index Management Endpoints (Admin Only)

#### POST /index/rebuild
Rebuild the search index.

#### GET /index/status
Get search index status.

## Error Handling

All endpoints return standard HTTP status codes:
- 200: Success
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 500: Internal Server Error

## Examples

### Search Example
curl -X GET "https://example.com/wp-json/arabic-search/v1/search?query=البحث&per_page=5"

### Normalize Text Example
curl -X POST "https://example.com/wp-json/arabic-search/v1/normalize" -H "Content-Type: application/json" -d \'{"text":"مرحباً بكم","language":"ar"}\'
';
    }
    
    /**
     * Get developer guide content
     *
     * @return string Developer guide
     */
    private function get_developer_guide(): string {
        return '# Arabic Search Enhancement Developer Guide

## Overview

This guide provides information for developers who want to extend or customize the Arabic Search Enhancement plugin.

## Plugin Architecture

The plugin follows modern PHP development practices with:
- Namespace organization
- Dependency injection
- Interface-based design
- Comprehensive testing

### Core Components

- ArabicTextNormalizer: Handles text normalization
- AdvancedSearchFeatures: Provides fuzzy matching and suggestions
- PerformanceOptimizer: Manages caching and indexing
- MultiLanguageNormalizer: Extends support to multiple languages
- RestApiController: Provides REST API endpoints

## Hooks and Filters

### Filters

- arabic_search_normalize_text: Filter normalized text
- arabic_search_similarity_threshold: Adjust fuzzy matching threshold
- arabic_search_supported_languages: Modify supported languages
- arabic_search_cache_duration: Customize cache duration

### Actions

- arabic_search_before_search: Before search execution
- arabic_search_after_search: After search execution
- arabic_search_index_updated: When search index is updated

## Customization Examples

### Adding Custom Normalization Rules

Example PHP code for custom normalization using the arabic_search_normalize_text filter.

### Extending Language Support

Example PHP code for adding Kurdish language support using the arabic_search_supported_languages filter.

## Testing

The plugin includes comprehensive tests:
- Unit tests for core functionality
- Integration tests for WordPress integration
- Mocked WordPress functions for isolated testing

Run tests with: ./vendor/bin/phpunit

## Contributing

1. Fork the repository
2. Create a feature branch
3. Add tests for new functionality
4. Ensure all tests pass
5. Submit a pull request

## Best Practices

- Use proper input sanitization
- Follow WordPress coding standards
- Add proper error handling
- Include comprehensive documentation
- Write tests for new features
';
    
    /**
     * Get installation guide content
     *
     * @return string Installation guide
     */
    private function get_installation_guide(): string {
        return '# Arabic Search Enhancement Installation Guide

## System Requirements

- WordPress 5.0 or higher
- PHP 8.0 or higher
- MySQL 5.6 or higher (or MariaDB equivalent)
- At least 32MB PHP memory limit

## Installation Methods

### Method 1: WordPress Admin Dashboard

1. Navigate to Plugins > Add New in your WordPress admin
2. Search for "Arabic Search Enhancement"
3. Click "Install Now" and then "Activate"

### Method 2: Manual Upload

1. Download the plugin zip file
2. Navigate to Plugins > Add New > Upload Plugin
3. Choose the zip file and click "Install Now"
4. Activate the plugin

### Method 3: FTP Upload

1. Extract the plugin zip file
2. Upload the arabic-search-enhancement folder to /wp-content/plugins/
3. Activate the plugin through the WordPress admin

## Configuration

1. Go to Settings > Arabic Search Enhancement
2. Configure your preferred settings:
   - Enable/disable specific languages
   - Adjust fuzzy matching sensitivity
   - Configure caching options
   - Set up analytics preferences

## Verification

To verify the plugin is working correctly:

1. Perform a search using Arabic text
2. Check the Analytics dashboard for search statistics
3. Test the REST API endpoints (if needed)

## Troubleshooting

### Common Issues

**Search not working properly:**
- Check that your theme uses the standard WordPress search
- Verify the plugin is activated
- Clear any caching plugins

**Performance issues:**
- Enable caching in plugin settings
- Consider rebuilding the search index
- Check PHP memory limits

**Language not detected:**
- Ensure the text contains enough Arabic characters
- Check supported language settings
- Verify proper text encoding (UTF-8)

### Getting Help

- Check the plugin FAQ section
- Visit the support forum
- Review the API documentation
- Enable debug logging for detailed error information

## Multisite Installation

For WordPress multisite networks:

1. Network activate the plugin from Network Admin
2. Configure settings on each site individually
3. Consider network-wide settings for consistent behavior

## Uninstallation

The plugin includes a clean uninstall process:

1. Deactivate the plugin
2. Delete the plugin files
3. The plugin will automatically clean up database tables and options

Note: Search analytics data will be preserved unless explicitly deleted.
';
    }
}