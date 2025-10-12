# Arabic Search Enhancement Plugin - Language Support

This directory contains all translation files for the Arabic Search Enhancement WordPress plugin.

## Files Structure

### Translation Source Files
- `arabic-search-enhancement.pot` - Template file for translators
- `arabic-search-enhancement-ar.po` - Arabic translation source

### Compiled Translation Files  
- `arabic-search-enhancement-ar.mo` - Compiled binary translation for server-side
- `arabic-search-enhancement-ar-json.json` - JSON translation for JavaScript

### Build Scripts
- `compile-translations.php` - Converts PO files to MO format
- `create-json-translations.php` - Converts PO files to JSON for JavaScript
- `build-translations.php` - Complete build process for all formats

## Usage

### For Developers
Run the build script to generate all translation files:
```bash
php build-translations.php
```

### For Translators
1. Use the `.pot` template file to create new translations
2. Save as `arabic-search-enhancement-{locale}.po`
3. Run build script to compile

### Language Support
- Full RTL (Right-to-Left) support for Arabic
- Server-side translations via gettext (.mo files)
- JavaScript translations via JSON format
- WordPress i18n compatibility

## Adding New Languages

1. Create new PO file: `arabic-search-enhancement-{locale}.po`
2. Translate all strings from the POT template
3. Add locale to build scripts
4. Run build process
5. Test in WordPress admin

## Technical Notes

- Text domain: `arabic-search-enhancement`
- Encoding: UTF-8
- RTL detection: Automatic for Arabic locales
- Fallback: English for missing translations

## Copyright

© 2024 Yasir Najeep. All translation files licensed under GPL v2 or later.