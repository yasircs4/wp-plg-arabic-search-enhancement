# Verification Evidence

Run this command from the repo root to regenerate the proof report:

```bash
npm run launch:verify
```

The generated report is written to:

```text
docs/verification/latest.md
```

The report records fresh command evidence for:

- Git state and origin sync
- GitHub Pages workflow status
- Public GitHub Pages URLs
- WordPress.org plugin page, API, ZIP, SVN listing, and assets
- Runtime ZIP artifact scan
- PHP syntax checks
- PHPUnit
- WordPress.org asset validation
- Local WordPress launch blog posts

Known limitation: no safe remote WordPress publishing credential or WP-CLI alias exists in this workspace, so remote WordPress blog publishing is not claimed. The local Docker WordPress posts are verified instead.
