# Arabic Search Enhancement Plugin

A production-ready WordPress plugin that improves search functionality for Arabic content by normalizing text variations, diacritics, and letter forms.

## Features

- **Arabic Text Normalization**: Removes diacritics and normalizes letter forms for better search results
- **Multi-term Search Support**: Properly handles multiple search terms with AND logic
- **Caching System**: Improves performance with intelligent caching
- **Configurable**: Full admin interface for customization
- **Arabic Language Support**: Complete RTL support with Arabic translations
- **Production Ready**: Built with SOLID principles, proper error handling, and performance optimization
- **Internationalization**: Full i18n support with gettext and JSON translations

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher

## Architecture

This plugin follows modern PHP and WordPress development practices:

### SOLID Principles
- **Single Responsibility**: Each class has one specific purpose
- **Open/Closed**: Extensible through interfaces without modifying core code
- **Liskov Substitution**: All implementations are interchangeable through interfaces
- **Interface Segregation**: Small, focused interfaces
- **Dependency Inversion**: Dependencies are injected through interfaces

### Design Patterns
- **Factory Pattern**: `PluginFactory` manages object creation and dependencies
- **Singleton Pattern**: Plugin instance management
- **Strategy Pattern**: Text normalization strategies

### Structure

```
src/
├── Autoloader.php              # PSR-4 compatible autoloader
├── Core/
│   ├── Plugin.php              # Main plugin orchestrator
│   ├── PluginFactory.php       # Dependency injection factory
│   ├── Configuration.php       # Centralized configuration management
│   ├── Cache.php              # WordPress cache wrapper
│   ├── ArabicTextNormalizer.php # Arabic text processing
│   └── SearchQueryModifier.php # Search query enhancement
├── Admin/
│   └── SettingsPage.php       # Admin interface
└── Interfaces/
    ├── ConfigurationInterface.php
    ├── CacheInterface.php
    ├── TextNormalizerInterface.php
    └── SearchQueryModifierInterface.php
languages/
├── arabic-search-enhancement.pot    # Translation template
├── arabic-search-enhancement-ar.po  # Arabic translation source
├── arabic-search-enhancement-ar.mo  # Compiled Arabic translation
├── arabic-search-enhancement-ar-json.json # JavaScript translations
└── build-translations.php          # Translation build script
```

## Language Support

The plugin includes comprehensive Arabic language support:

- **RTL Interface**: Automatic right-to-left layout for Arabic locales
- **Complete Translation**: All strings translated to Arabic
- **WordPress Integration**: Uses WordPress i18n system (gettext)
- **JavaScript Support**: Client-side translations for dynamic content
- **Build System**: Automated translation compilation from PO to MO/JSON

To use Arabic interface:
1. Set WordPress language to Arabic (`ar`)
2. Plugin automatically detects and applies RTL layout
3. All text appears in Arabic with proper formatting

## Normalization Rules

The plugin automatically normalizes:

1. **Diacritics Removal**: All Tashkeel marks (َ ُ ِ ّ ْ ً ٌ ٍ etc.)
2. **Alef Normalization**: أ إ آ ٱ → ا
3. **Taa Marbuta**: ة → ه
4. **Yaa Normalization**: ى → ي
5. **Hamza Variations**: ؤ → و, ئ → ي
6. **Tatweel Removal**: ـ (kashida)

## Performance Features
- **SQL-level Normalization**: Database queries use optimized REPLACE chains
- **Intelligent Caching**: Normalized text and SQL expressions are cached
- **Performance Monitoring**: Optional performance tracking for debugging
- **Minimal Resource Usage**: Only processes Arabic text when needed

## Configuration Options

- **Enable Enhancement**: Toggle Arabic normalization on/off
- **Search Fields**: Include/exclude post excerpts from search
- **Post Types**: Select which post types to search
- **Results Per Page**: Control pagination
- **Debug Mode**: Enable detailed logging
- **Performance Monitoring**: Track query performance

## Development

### Running Tests

```bash
# Unit tests (when implemented)
composer test

# Code style check
composer cs-check

# Code style fix
composer cs-fix
```

### Contributing

1. Follow PSR-12 coding standards
2. Add type hints to all methods
3. Include PHPDoc comments
4. Write unit tests for new features
5. Ensure backward compatibility

## Installation

1. Upload the plugin files to `/wp-content/plugins/arabic-search-enhancement/`
2. Activate the plugin through the WordPress admin
3. Configure settings at Settings → Arabic Search

## Backward Compatibility

The plugin maintains compatibility with the previous API:

```php
// Old way (still works)
$plugin = Arabic_Search_Enhancement::get_instance();

// New way (recommended)
$plugin = arabic_search_enhancement_get_plugin();
```

## License

GPL v2 or later

Copyright (C) 2024 Yasir Najeep

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

## Support

For support and bug reports, please contact the developer.

## Changelog

### 1.1.0
- Complete rewrite with modern architecture
- Improved performance with caching
- Better error handling and validation
- SOLID principles implementation
- Enhanced admin interface
- Multi-term search support
- Full Arabic language support with RTL
- Complete internationalization system
- Translation files (PO/MO/JSON) included

### 1.0.1
- Initial release
- Basic Arabic text normalization
- Simple admin interface