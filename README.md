# Arabic Search Enhancement

Arabic Search Enhancement is a WordPress plugin that improves Arabic search results by normalizing common Arabic text variations before WordPress searches content.

It helps visitors find content when they type Arabic without diacritics, with different Alef or Hamza forms, or with everyday spelling variants such as `قران` for content written as `قرآن`.

## Public Links

- WordPress.org plugin: https://wordpress.org/plugins/arabic-search-enhancement/
- Download ZIP: https://downloads.wordpress.org/plugin/arabic-search-enhancement.1.4.8.zip
- GitHub Pages: https://yasircs4.github.io/wp-plg-arabic-search-enhancement/
- Support forum: https://wordpress.org/support/plugin/arabic-search-enhancement/
- Public SVN: https://plugins.svn.wordpress.org/arabic-search-enhancement/

## Current Release

- Version: `1.4.8`
- WordPress.org SVN revision: `3517783`
- Stable tag: `1.4.8`
- Requires WordPress: `5.0`
- Tested up to: `6.9`
- Requires PHP: `7.4`

The WordPress.org API may display the patch-tested environment as `6.9.4`, while `readme.txt` intentionally uses `Tested up to: 6.9` because the WordPress.org readme validator expects major/minor format.

## What It Does

- Removes Arabic diacritics and Tatweel for search matching.
- Normalizes Alef forms, Hamza variants, Yaa, Waw, and Taa Marbuta variants.
- Enhances normal WordPress search without replacing public templates.
- Provides an admin settings page and self-test section.
- Includes optional local search analytics.
- Includes Arabic translation files and RTL admin support.
- Keeps search data inside the WordPress database.

## Installation

Use the WordPress.org plugin directory whenever possible:

1. Open `Plugins -> Add New` in WordPress admin.
2. Search for `Arabic Search Enhancement`.
3. Install and activate the plugin.
4. Configure it from `Settings -> Arabic Search`.

Manual install:

1. Download https://downloads.wordpress.org/plugin/arabic-search-enhancement.1.4.8.zip
2. Upload the ZIP from `Plugins -> Add New -> Upload Plugin`.
3. Activate and configure from `Settings -> Arabic Search`.

## Developer Setup

```bash
composer install
npm install
```

Useful commands:

```bash
vendor/bin/phpunit
find arabic-search-enhancement.php src tests -name '*.php' -print0 | xargs -0 -n1 php -l
npm run wporg:validate-assets
```

WordPress.org asset workflow:

```bash
wporg-assets/src/build-runtime-package.sh
wporg-assets/src/setup-local-wp.sh
npm run wporg:assets
npm run wporg:validate-assets
```

See [docs/NEXT_DEV.md](docs/NEXT_DEV.md) for the full next-developer handoff.

## Verification

Run the full evidence pass:

```bash
npm run launch:verify
```

The generated report is written to:

```text
docs/verification/latest.md
```

This report includes public GitHub Pages checks, WordPress.org page/API/ZIP/SVN/asset checks, PHP syntax checks, PHPUnit, asset validation, and local WordPress blog-post checks.

Remote WordPress blog publishing is not claimed from this workspace because no safe remote WordPress REST/WP-CLI credential or alias is present. The launch post is verified on the local Docker WordPress QA sites and as static GitHub Pages content.

## Runtime Package

The WordPress.org runtime package should include only:

- `arabic-search-enhancement.php`
- `readme.txt`
- `src/`
- `assets/admin/`
- `languages/`

Do not ship development files such as `.git`, `.github`, `.archive`, `docs`, `tests`, `vendor`, `node_modules`, `composer.*`, `package*.json`, `phpunit.xml`, local credentials, or ZIP files.

## Project Structure

```text
arabic-search-enhancement.php  Main plugin bootstrap
src/                           Plugin PHP source
assets/admin/                  Runtime admin CSS and JS
languages/                     Translation files
readme.txt                     WordPress.org readme
docs/                          GitHub Pages site and public docs
wporg-assets/                  Reproducible WordPress.org visual assets
tests/                         PHPUnit tests
```

## Marketing and Launch Docs

- Launch report: [docs/RELEASE-1.4.8.md](docs/RELEASE-1.4.8.md)
- Blog post source: [docs/blog/launch-1-4-8.md](docs/blog/launch-1-4-8.md)
- Marketing kit: [docs/marketing/copy.md](docs/marketing/copy.md)
- Public marketing page: https://yasircs4.github.io/wp-plg-arabic-search-enhancement/marketing/

## Security Notes

- Keep `SVN credentials.txt` local only. It is ignored by Git and must not be committed.
- Do not print SVN, GitHub, or WordPress passwords in terminal output.
- Search analytics are local to the site database and can be disabled in plugin settings.

## License

GPL v2 or later. See the plugin header and WordPress.org package metadata.
