=== Arabic Search Enhancement ===
Contributors: yasircs4
Tags: arabic, search, normalization, rtl, multilingual
Requires at least: 5.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.4.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Enhances WordPress search for Arabic content by normalizing text variations, diacritics, and letter forms for better search results.

== Description ==

**Arabic Search Enhancement** is a production-ready WordPress plugin that dramatically improves search functionality for Arabic content. It addresses the common problem where Arabic searches fail due to variations in diacritics, letter forms, and typing styles.

= Key Features =

* **Smart Arabic Text Normalization** - Removes diacritics and normalizes letter forms for comprehensive search results
* **Elementor Search Widget Support** - Full compatibility with Elementor's search widgets and AJAX functionality
* **Multi-term Search Support** - Handles multiple Arabic search terms with intelligent AND logic
* **Performance Optimized** - Built-in caching system for fast repeated searches
* **RTL Interface Support** - Complete right-to-left admin interface for Arabic users
* **Configurable Settings** - Full admin panel for customizing search behavior
* **Developer Friendly** - Built with SOLID principles and modern PHP practices

= Search Improvements =

**Before**: Searching for "مكتوب" won't find "مَكْتُوب" (with diacritics)
**After**: Smart normalization finds all variations automatically

**Before**: "الكتاب" won't match "ألكتاب" (different Alef forms)
**After**: All Alef variations (أ إ آ ٱ) treated as same letter

= Normalization Rules =

1. **Diacritics Removal**: All Tashkeel marks (َ ُ ِ ّ ْ ً ٌ ٍ etc.)
2. **Alef Unification**: أ إ آ ٱ → ا
3. **Taa Marbuta**: ة → ه
4. **Yaa Normalization**: ى → ي
5. **Hamza Variations**: ؤ → و, ئ → ي
6. **Tatweel Removal**: ـ (kashida)

= Technical Highlights =

* **SQL-level Processing** - Database queries optimized for Arabic text
* **Intelligent Caching** - Performance monitoring and optimization
* **WordPress Integration** - Uses WordPress hooks and APIs properly
* **Security First** - Input sanitization and output escaping
* **Translation Ready** - Full internationalization support

= Perfect For =

* Arabic news websites and blogs
* Educational institutions with Arabic content
* Government websites in Arabic-speaking countries
* E-commerce sites with Arabic product descriptions
* **Elementor-powered websites** with Arabic search functionality
* Any WordPress site serving Arabic-speaking users

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/arabic-search-enhancement/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to Settings > Arabic Search to configure options
4. Run the self-test to verify everything is working correctly

== Frequently Asked Questions ==

= Does this work with all Arabic dialects? =

Yes! The plugin normalizes standard Arabic text patterns that are common across all Arabic dialects and Modern Standard Arabic.

= Will this slow down my website? =

No, the plugin is performance-optimized with intelligent caching. Search queries are processed at the database level for maximum efficiency.

= Can I customize which post types are searched? =

Absolutely! The admin panel allows you to select exactly which post types should be included in enhanced searches.

= Does it work with other search plugins? =

The plugin modifies WordPress core search functionality, so compatibility with other search plugins depends on their implementation. Test thoroughly if using multiple search plugins.

= Is it translation ready? =

Yes! The plugin includes complete Arabic translations and RTL interface support. Additional languages can be added easily.

== Screenshots ==

1. Admin settings page with configuration options
2. Search results showing improved Arabic matching
3. Self-test functionality verifying normalization
4. RTL interface for Arabic administrators

== Changelog ==

= 1.4.4 =
*   **Fix:** Resolved `PreparedSQL.NotPrepared` issues by explicitly inlining SQL query strings.
*   **Fix:** Addressed `NamingConventions.PrefixAllGlobals` warnings in translation build scripts.
*   **Fix:** Handled `DirectDatabaseQuery` and `UnescapedDBParameter` warnings for core plugin functionality.

= 1.4.3 =
*   **Fix:** Resolved `WordPress.DB.PreparedSQL.NotPrepared` issues by inlining SQL queries into `$wpdb->prepare()` calls.

= 1.4.2 =
*   **Fix:** Added missing translator comments to resolve automated check errors.

= 1.4.1 =
*   **Fix:** Removed discouraged `load_plugin_textdomain()` call as translations are handled automatically by WordPress.org for hosted plugins.

= 1.4.0 =
* **Compliance Update** - Final WordPress.org Plugin Directory compliance fixes
* **Security Hardening** - Addressed all Plugin Check errors and warnings
* **Code Quality** - Improved adherence to WordPress Coding Standards
* **Ownership** - Verified ownership and updated contact information
* **Fixes** - Resolved output escaping, nonce verification, and database query issues

= 1.3.0 =
* WordPress.org Plugin Directory compliance fixes
* **Proper Output Escaping** - All translation functions now use esc_html_e() and esc_html__()
* **Asset Enqueuing** - Removed inline styles/scripts, implemented proper wp_enqueue_* functions
* **Ownership Verification** - Updated Plugin URI, Author, and Contributors to yasircs4
* **Security Enhancements** - Improved input sanitization and output escaping
* **Code Quality** - Full compliance with WordPress Coding Standards
* **Performance** - Optimized asset loading and caching
* **Documentation** - Updated all version references and documentation

= 1.1.0 =
* Complete rewrite with modern architecture
* **Elementor Search Widget Compatibility** - Full support for Elementor's search widgets and AJAX requests
* **Enhanced Search Detection** - Supports custom query variables (search_term, custom_search)
* **Frontend AJAX Support** - Works with Elementor Pro search forms and live search features
* **Filterable Action Support** - Extensible via `arabic_search_enhancement_frontend_ajax_actions` filter
* Improved performance with intelligent caching
* Better error handling and validation
* SOLID principles implementation
* Enhanced admin interface with self-test
* Multi-term search support with AND logic
* Full Arabic language support with RTL interface
* Complete internationalization system
* Translation files (PO/MO/JSON) included
* Production-ready with comprehensive testing

= 1.0.1 =
* Initial release
* Basic Arabic text normalization
* Simple search query modification

== Upgrade Notice ==

= 1.4.4 =
Comprehensive security and coding standards compliance update.

= 1.4.3 =

= 1.4.2 =
Resolved translation comment issues. Ready for review.

= 1.4.1 =
Addresses automated scan rejection regarding load_plugin_textdomain() call. This version is ready for re-submission.

= 1.4.0 =
Final compliance update for WordPress.org submission. Includes security hardening and code quality improvements.

= 1.3.0 =
WordPress.org compliance update. All security and code quality issues resolved. This version is fully compliant with WordPress Plugin Directory guidelines and ready for approval.

= 1.1.0 =
Major update with complete rewrite. Backup your site before upgrading. New features include performance improvements, better admin interface, and full Arabic language support.

== Technical Details ==

= System Requirements =
* WordPress 5.0 or higher
* PHP 7.4 or higher
* MySQL 5.6 or higher

= Architecture =
* Built with SOLID principles
* PSR-4 autoloading
* Dependency injection
* Interface-based design
* Comprehensive error handling

= Performance =
* SQL-level normalization
* Intelligent caching system
* Minimal resource usage
* Database query optimization

== Support ==

For support, bug reports, or feature requests, please contact the developer or visit the plugin support forum.

== Privacy ==

This plugin collects and stores anonymous search analytics data to improve search functionality. The following information is collected:

* Search queries (anonymized and used for statistics only)
* Search result counts and language detection
* Search timestamps (for trend analysis)

**No personal information is collected**, including:
* User names, emails, or IP addresses
* Personal identifiable information (PII)
* User browsing behavior outside of search functionality

All data collection can be disabled through the plugin settings. Data is stored locally in your WordPress database and is not transmitted to external servers.

For complete privacy transparency, you may review the source code at the plugin repository.