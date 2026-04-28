# Next Developer Handoff

This repo is ready for normal development after the WordPress.org `1.4.8` launch.

## Current State

- Main branch: `main`
- Public repo: https://github.com/yasircs4/wp-plg-arabic-search-enhancement
- GitHub Pages source: `docs/`
- WordPress.org release: `1.4.8`
- SVN release revision: `3517783`
- Local WordPress QA compose file: `wporg-assets/src/docker-compose.wporg.yml`

## Local Setup

```bash
composer install
npm install
```

Run the core checks:

```bash
find arabic-search-enhancement.php src tests -name '*.php' -print0 | xargs -0 -n1 php -l
vendor/bin/phpunit
npm run wporg:validate-assets
```

Run the full evidence pass:

```bash
npm run launch:verify
```

This regenerates:

```text
docs/verification/latest.md
```

The proof report covers public GitHub Pages, WordPress.org page/API/ZIP/SVN/assets, PHP syntax, PHPUnit, asset validation, and local WordPress blog posts.

## Local WordPress QA

Start the seeded WordPress QA site:

```bash
wporg-assets/src/setup-local-wp.sh
```

Default URL:

```text
http://localhost:8098
```

Runtime-package QA can be run with:

```bash
runtime_dir="$(wporg-assets/src/build-runtime-package.sh)"
ASE_WPORG_PLUGIN_DIR="$runtime_dir" ASE_WPORG_PORT=8099 ASE_WPORG_PROJECT=ase-wporg-runtime wporg-assets/src/setup-local-wp.sh
```

Do not run `setup-local-wp.sh` against a site that contains content you need to keep. The script deletes local posts/pages and reseeds demo content.

## Blog Publishing Reality

The launch post is committed as static content in:

```text
docs/blog/launch-1-4-8.html
docs/blog/launch-1-4-8.md
```

It is also published to the two local Docker WordPress QA sites:

```text
http://localhost:8098/arabic-search-enhancement-1-4-8-live/
http://localhost:8099/arabic-search-enhancement-1-4-8-live/
```

No safe remote WordPress REST/WP-CLI credential or alias is present in this workspace, so remote WordPress blog publishing is not verified and should not be claimed until credentials and a target site are explicitly available.

## WordPress.org Asset Workflow

Generate assets:

```bash
npm run wporg:assets
npm run wporg:validate-assets
```

Generated files are intentionally committed from:

```text
wporg-assets/dist/
```

Top-level SVN `/assets` needs these exact files:

```text
banner-772x250.png
banner-1544x500.png
icon-128x128.png
icon-256x256.png
screenshot-1.png
screenshot-2.png
screenshot-3.png
screenshot-4.png
```

## GitHub Pages

The public site is static and lives in:

```text
docs/
```

The active deployment workflow is:

```text
.github/workflows/static.yml
```

Keep Pages compact. Prefer short sections, real screenshots, absolute canonical URLs, and direct WordPress.org download links.

## SVN Release Checklist

Before a future WordPress.org release:

1. Update `Version:` in `arabic-search-enhancement.php`.
2. Update `ARABIC_SEARCH_ENHANCEMENT_VERSION`.
3. Update `Stable tag:` and changelog/upgrade notice in `readme.txt`.
4. Build a runtime package.
5. Run PHP syntax checks and PHPUnit.
6. Run Plugin Check against the runtime-only package.
7. Generate and validate WordPress.org assets.
8. Stage only runtime files in SVN `trunk`.
9. Copy `trunk` to `tags/<version>`.
10. Set `svn:mime-type image/png` on asset PNGs.
11. Inspect `svn status` and `svn diff --summarize`.
12. Commit to SVN with the WordPress.org SVN password.

## Do Not Commit

- `SVN credentials.txt`
- `.env*`
- `wp-config.php`
- `vendor/`
- `node_modules/`
- `test-results/`
- `playwright-report/`
- `.DS_Store`
- ZIP release artifacts

## Current Known Warnings

Plugin Check on the runtime package reports no errors. It still reports warnings around existing logging and direct database query usage. Treat those as future hardening work, not a blocker for the current published release.
