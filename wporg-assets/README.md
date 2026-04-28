# WordPress.org Assets

This directory contains reproducible sources and generated assets for the WordPress.org plugin directory.

Generated files in `dist/` map directly to the top-level SVN `/assets` directory:

- `banner-772x250.png`
- `banner-1544x500.png`
- `icon-128x128.png`
- `icon-256x256.png`
- `screenshot-1.png`
- `screenshot-2.png`
- `screenshot-3.png`
- `screenshot-4.png`

To regenerate against the local WordPress test site:

```bash
export ASE_WPORG_PLUGIN_DIR="$(bash wporg-assets/src/build-runtime-package.sh)"
export ASE_WPORG_PROJECT=ase-wporg-runtime
export ASE_WPORG_PORT=8099
export ASE_WPORG_BASE_URL=http://localhost:8099
export ASE_WPORG_COMPOSE_FILE="$PWD/wporg-assets/src/docker-compose.wporg.yml"

bash wporg-assets/src/setup-local-wp.sh
npx playwright test wporg-assets/src/capture-assets.spec.js --project=chromium
php wporg-assets/src/validate-assets.php
```

The Docker setup uses WordPress 6.9.4 and mounts a runtime-only plugin package read-only into `wp-content/plugins/arabic-search-enhancement`.
