# 🔍 Arabic Search Enhancement for WordPress

[![WordPress Plugin Version](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org/plugins/arabic-search-enhancement/)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-GPL%20v2%2B-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![RTL Support](https://img.shields.io/badge/RTL-Supported-orange.svg)](https://codex.wordpress.org/Right_to_Left_Language_Support)

> **Transform your WordPress search into an Arabic-friendly powerhouse!** 🚀

A production-ready WordPress plugin that dramatically improves search functionality for Arabic content by intelligently normalizing text variations, diacritics, and letter forms. Say goodbye to "no results" frustration!

---

## ✨ Why This Plugin?

### The Problem 😰
Searching for Arabic content in WordPress is frustrating:
- Search for "محمد" but content has "مُحَمَّد" → **No results!**
- Try "الإسلام" but post uses "الاسلام" → **No results!**
- Different Alef forms, Tashkeel marks, letter variations → **Inconsistent results!**

### The Solution 🎯
Arabic Search Enhancement normalizes all text variations automatically, ensuring users **always** find what they're looking for, regardless of how it's written!

---

## 🎁 Features

### 🌟 Core Features
- **🔤 Smart Text Normalization** - Automatically handles diacritics, letter variations, and special forms
- **⚡ Lightning Fast** - SQL-level optimization with intelligent caching
- **🎨 Elementor Compatible** - Works perfectly with Elementor search widgets
- **🌐 Full RTL Support** - Beautiful right-to-left interface for Arabic
- **📊 Analytics Dashboard** - Track search performance and user behavior
- **🎛️ Flexible Configuration** - Customize every aspect through the admin panel
- **🌍 Fully Translated** - Complete Arabic localization included

### 🔧 Technical Excellence
- **🏗️ Modern Architecture** - Built with SOLID principles and design patterns
- **✅ Production Ready** - Comprehensive error handling and validation
- **🔒 Security First** - All outputs escaped, SQL injection protected
- **📱 REST API** - Programmatic access to analytics and settings
- **🧪 Well Tested** - Includes PHPUnit test suite
- **📦 Zero Dependencies** - Uses only WordPress core functionality

---

## 📋 Requirements

| Component | Version |
|-----------|---------|
| 🌐 WordPress | 5.0+ |
| 🐘 PHP | 7.4+ |
| 💾 MySQL | 5.6+ |

---

## 🚀 Quick Start

### Installation

#### 📥 From WordPress.org (Recommended)
1. Go to **Plugins → Add New** in your WordPress admin
2. Search for **"Arabic Search Enhancement"**
3. Click **Install Now** → **Activate**
4. Configure at **Settings → Arabic Search**

#### 📦 Manual Installation
1. Download the latest release ZIP file
2. Upload to `/wp-content/plugins/arabic-search-enhancement/`
3. Activate through the **Plugins** menu
4. Navigate to **Settings → Arabic Search**

#### 🔧 For Developers
```bash
cd wp-content/plugins
git clone https://github.com/yasircs4/wp-plg-arabic-search-enhancement.git arabic-search-enhancement
cd arabic-search-enhancement
composer install --no-dev
```

---

## 🎯 How It Works

### 📚 Normalization Rules

The plugin automatically normalizes:

| Issue | Example | Solution |
|-------|---------|----------|
| **Diacritics** | مُحَمَّد | محمد |
| **Alef Forms** | أ إ آ ٱ | ا |
| **Taa Marbuta** | ة | ه |
| **Yaa** | ى | ي |
| **Hamza on Waw** | ؤ | و |
| **Hamza on Yaa** | ئ | ي |
| **Tatweel** | ـ | (removed) |

### ⚡ Performance Features

- **🎯 SQL-Level Processing** - Normalization happens in the database query
- **💾 Smart Caching** - Normalized expressions cached for reuse
- **📊 Performance Monitoring** - Optional query performance tracking
- **🔍 Lazy Loading** - Only processes Arabic content

---

## 🎛️ Configuration

### Settings Page
Navigate to **Settings → Arabic Search** to configure:

#### 🔧 Basic Options
- ✅ **Enable Enhancement** - Toggle Arabic normalization
- 📄 **Search Fields** - Include/exclude excerpts
- 📝 **Post Types** - Select searchable content types
- 🔢 **Results Per Page** - Control pagination

#### 🚀 Advanced Options
- 🐛 **Debug Mode** - Enable detailed logging
- 📊 **Performance Monitoring** - Track query performance
- 💾 **Cache Duration** - Control cache lifetime
- 🔄 **Clear Cache** - Manual cache clearing

#### 📊 Analytics Dashboard
- 📈 **Search Trends** - Visual charts of search activity
- 🔝 **Top Queries** - Most searched terms
- ❌ **Failed Searches** - Queries with no results
- 📉 **Success Rate** - Overall search performance
- 🌍 **Language Distribution** - Arabic vs other languages

---

## 🏗️ Architecture

Built with modern PHP practices and WordPress standards:

### 🎯 SOLID Principles
- **S**ingle Responsibility - Each class has one job
- **O**pen/Closed - Extensible via interfaces
- **L**iskov Substitution - Interchangeable implementations
- **I**nterface Segregation - Small, focused interfaces
- **D**ependency Inversion - Dependencies injected through interfaces

### 🎨 Design Patterns
- **🏭 Factory Pattern** - Centralized object creation
- **🔒 Singleton Pattern** - Plugin instance management
- **🎯 Strategy Pattern** - Flexible text normalization

### 📁 Project Structure
```
arabic-search-enhancement/
├── 📄 arabic-search-enhancement.php  # Main plugin file
├── 📖 readme.txt                     # WordPress.org readme
├── 📚 README.md                      # This file
├── 🗂️ src/                          # Source code
│   ├── 🔧 Autoloader.php            # PSR-4 autoloader
│   ├── 🎯 Core/                     # Core functionality
│   │   ├── Plugin.php               # Main orchestrator
│   │   ├── PluginFactory.php        # DI container
│   │   ├── Configuration.php        # Settings management
│   │   ├── ArabicTextNormalizer.php # Text processing
│   │   ├── SearchQueryModifier.php  # Query enhancement
│   │   ├── Cache.php                # Caching system
│   │   └── PerformanceOptimizer.php # Performance tools
│   ├── 🎨 Admin/                    # Admin interface
│   │   ├── SettingsPage.php         # Settings UI
│   │   └── SearchAnalyticsDashboard.php # Analytics
│   ├── 🌐 API/                      # REST API
│   │   └── RestApiController.php    # API endpoints
│   ├── 🔌 Interfaces/               # PHP interfaces
│   │   ├── ConfigurationInterface.php
│   │   ├── CacheInterface.php
│   │   ├── TextNormalizerInterface.php
│   │   └── SearchQueryModifierInterface.php
│   └── 🛠️ Utils/                    # Utility classes
├── 🌍 languages/                    # Translations
│   ├── *.pot                        # Translation template
│   ├── *.po                         # Translation sources
│   ├── *.mo                         # Compiled translations
│   └── *.json                       # JS translations
├── 🎨 assets/                       # Static assets
│   └── admin/                       # Admin CSS/JS
├── 🧪 tests/                        # PHPUnit tests
│   ├── unit/                        # Unit tests
│   └── integration/                 # Integration tests
└── 📚 docs/                         # Documentation
```

---

## 🌍 Internationalization

### 🇸🇦 Arabic Language Support

**Complete RTL Experience:**
- ✅ Automatic RTL layout detection
- ✅ All UI elements in Arabic
- ✅ Proper text alignment and formatting
- ✅ Arabic number formatting

**To Enable Arabic Interface:**
1. Go to **Settings → General**
2. Set **Site Language** to **العربية** (Arabic)
3. Save changes
4. Plugin automatically switches to RTL with Arabic text

### 🌐 Translation Files Included
- `arabic-search-enhancement.pot` - Translation template
- `arabic-search-enhancement-ar.po` - Arabic source
- `arabic-search-enhancement-ar.mo` - Compiled Arabic
- `arabic-search-enhancement-ar-json.json` - JS translations

---

## 🔌 API & Extensibility

### REST API Endpoints

```php
// Get analytics data
GET /wp-json/arabic-search/v1/analytics

// Get plugin settings
GET /wp-json/arabic-search/v1/settings

// Update settings
POST /wp-json/arabic-search/v1/settings
```

### Hooks & Filters

```php
// Modify normalization before processing
add_filter('arabic_search_normalize_text', function($text) {
    return $text;
}, 10, 1);

// Extend searchable post types
add_filter('arabic_search_post_types', function($post_types) {
    $post_types[] = 'custom_type';
    return $post_types;
}, 10, 1);

// Customize search query
add_filter('arabic_search_query_args', function($args) {
    return $args;
}, 10, 1);

// Add custom frontend AJAX actions
add_filter('arabic_search_enhancement_frontend_ajax_actions', function($actions) {
    $actions[] = 'my_custom_search';
    return $actions;
}, 10, 1);
```

---

## 👨‍💻 Development

### 🛠️ Setup Development Environment

```bash
# Clone repository
git clone https://github.com/yasircs4/wp-plg-arabic-search-enhancement.git
cd wp-plg-arabic-search-enhancement

# Install dependencies
composer install

# Run tests
composer test

# Check code style
composer cs-check

# Fix code style
composer cs-fix
```

### 📏 Coding Standards

We follow:
- **PSR-12** coding style
- **WordPress Coding Standards**
- **PHPDoc** documentation for all methods
- **Type hints** for all parameters and returns

### 🧪 Testing

```bash
# Run all tests
composer test

# Run specific test
./vendor/bin/phpunit tests/unit/ArabicTextNormalizerTest.php

# Run with coverage
composer test-coverage
```

---

## 🤝 Contributing

We welcome contributions! Here's how:

1. **🍴 Fork** the repository
2. **🌿 Create** a feature branch (`git checkout -b feature/amazing-feature`)
3. **💻 Code** following our standards
4. **✅ Test** your changes thoroughly
5. **📝 Commit** with clear messages (`git commit -m 'Add amazing feature'`)
6. **🚀 Push** to your branch (`git push origin feature/amazing-feature`)
7. **🎯 Open** a Pull Request

### 📋 Contribution Guidelines

- Add **PHPUnit tests** for new features
- Follow **PSR-12** coding standards
- Update **documentation** as needed
- Ensure **backward compatibility**
- Add **translator comments** for new strings

---

## 📊 Changelog

### 🎉 Version 1.3.0 (October 24, 2025)
#### 🔧 Ownership & Compliance
- ✅ Updated all ownership references to `yasircs4`
- ✅ Consistent copyright notices across all files
- ✅ Complete WordPress.org compliance review

#### 🐛 Technical Fixes
- ✅ Replaced `_e()` with `esc_html_e()` throughout
- ✅ Removed inline `<style>` and `<script>` tags
- ✅ Replaced `date()` with `gmdate()` for timezone safety
- ✅ Made debug code conditional on `WP_DEBUG`
- ✅ Added CLI-safe escaping for build scripts
- ✅ Fixed SQL prepared statement issues
- ✅ Added translator comments for placeholders
- ✅ Added `load_plugin_textdomain()` call

### 🚀 Version 1.2.0
- 📊 Analytics Dashboard with visual charts
- 🎨 Enhanced admin interface
- 🔄 REST API endpoints
- 💾 Improved caching system
- 📈 Performance monitoring

### 🎯 Version 1.1.0
- 🎨 **Elementor Compatibility** - Full support for Elementor search widgets
- 🏗️ **Complete Architecture Rewrite** - Modern SOLID principles
- ⚡ **Enhanced Performance** - SQL-level optimization
- 🌐 **Full Internationalization** - Complete i18n/l10n support
- 🌍 **RTL Support** - Beautiful Arabic interface
- 📊 **Multi-term Search** - Handles multiple search terms with AND logic

### 🌱 Version 1.0.1
- 🎉 Initial release
- 🔤 Basic Arabic text normalization
- 🎛️ Simple admin interface

---

## ❓ FAQ

### ❔ Does this work with Elementor?
**✅ Yes!** Full support for Elementor search widgets and AJAX search.

### ❔ Will it slow down my site?
**⚡ No!** The plugin uses SQL-level optimization and intelligent caching to maintain excellent performance.

### ❔ Can I customize the normalization rules?
**🔧 Yes!** Use the provided filters to customize text normalization behavior.

### ❔ Does it support other languages?
**🌍 Yes!** While optimized for Arabic, the plugin works with any language and includes English interface.

### ❔ Is it compatible with WooCommerce?
**🛒 Yes!** Works with WooCommerce product search out of the box.

---

## 📞 Support

### 🆘 Need Help?

- **📚 Documentation**: [Plugin Documentation](https://yasircs4.github.io/wp-plg-arabic-search-enhancement/)
- **💬 Support Forum**: [WordPress.org Support](https://wordpress.org/support/plugin/arabic-search-enhancement/)
- **🐛 Bug Reports**: [GitHub Issues](https://github.com/yasircs4/wp-plg-arabic-search-enhancement/issues)
- **📧 Email**: yasircs4@live.com

### 🤔 Before Asking

1. Check the **Documentation**
2. Search **existing issues**
3. Read the **FAQ** section
4. Enable **Debug Mode** to gather information

---

## 📜 License

**GPL v2 or later**

```
Copyright (C) 2025 yasircs4

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

📄 [Full License Text](https://www.gnu.org/licenses/gpl-2.0.html)

---

## 🙏 Credits

**Developed with ❤️ by [yasircs4](https://github.com/yasircs4)**

### 🌟 Special Thanks To:
- 🌐 The WordPress Community
- 🇸🇦 Arabic Language Contributors
- 🧪 All Beta Testers
- 🐛 Bug Reporters
- 💡 Feature Requesters

---

## 🔗 Links

- 🌐 **Plugin Homepage**: [GitHub Pages](https://yasircs4.github.io/wp-plg-arabic-search-enhancement/)
- 📦 **WordPress.org**: [Plugin Directory](https://wordpress.org/plugins/arabic-search-enhancement/)
- 💻 **GitHub Repository**: [Source Code](https://github.com/yasircs4/wp-plg-arabic-search-enhancement)
- 📖 **Documentation**: [Full Docs](https://yasircs4.github.io/wp-plg-arabic-search-enhancement/)
- 🐛 **Issue Tracker**: [Report Bugs](https://github.com/yasircs4/wp-plg-arabic-search-enhancement/issues)

---

<div align="center">

### ⭐ If you find this plugin useful, please star it on GitHub! ⭐

[![GitHub stars](https://img.shields.io/github/stars/yasircs4/wp-plg-arabic-search-enhancement.svg?style=social&label=Star)](https://github.com/yasircs4/wp-plg-arabic-search-enhancement)

**Made with 💚 for the Arabic WordPress Community**

</div>
