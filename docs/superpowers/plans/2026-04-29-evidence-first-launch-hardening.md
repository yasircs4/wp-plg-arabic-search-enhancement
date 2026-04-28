# Evidence-First Launch Hardening Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Turn the WordPress.org launch, GitHub Pages update, marketing pages, and WordPress blog publishing work into a reproducible, evidence-backed release state with no unsupported completion claims.

**Architecture:** Add verification scripts under `scripts/` that test public URLs, WordPress.org package contents, CDN assets, GitHub Pages, local WordPress posts, and core plugin checks. Store a timestamped proof report under `docs/verification/` generated from fresh command output. Keep the proof honest by recording verified facts and explicit gaps, especially remote WordPress publishing limitations.

**Tech Stack:** zsh/bash, PHP CLI, PHPUnit, Node.js, Playwright, curl, jq/PHP JSON parsing, GitHub CLI, Docker Compose, WP-CLI.

---

## File Structure

- Create `scripts/verify-pages.js`
  - Playwright smoke test for GitHub Pages and local static Pages builds.
  - Checks home, blog, and marketing pages on desktop and mobile.
  - Fails on broken images, missing text, or horizontal overflow.
- Create `scripts/verify-launch.sh`
  - Main proof runner.
  - Runs Git, GitHub Actions, public Pages, WordPress.org API/page/download, asset CDN, package artifact, PHP syntax, PHPUnit, WordPress.org asset validation, and local WordPress post checks.
  - Writes Markdown evidence to `docs/verification/latest.md`.
- Create `docs/verification/README.md`
  - Explains how to regenerate proof and what evidence is covered.
- Modify `package.json`
  - Add `pages:smoke` and `launch:verify` scripts.
- Modify `README.md`
  - Link to verification docs and clarify remote WordPress publishing gap.
- Modify `docs/NEXT_DEV.md`
  - Add proof workflow and exact commands.

---

### Task 1: Add GitHub Pages Smoke Test

**Files:**
- Create: `scripts/verify-pages.js`
- Modify: `package.json`

- [ ] **Step 1: Create the Playwright smoke test**

Create `scripts/verify-pages.js`:

```js
const { chromium } = require('@playwright/test');

const baseUrl = process.env.ASE_PAGES_BASE_URL || 'https://yasircs4.github.io/wp-plg-arabic-search-enhancement';
const paths = ['/', '/blog/', '/blog/launch-1-4-8.html', '/marketing/'];
const viewports = [
  ['desktop', { width: 1440, height: 950 }],
  ['mobile', { width: 390, height: 844 }],
];

async function main() {
  const browser = await chromium.launch({ headless: true });
  const failures = [];

  for (const [viewportName, viewport] of viewports) {
    const page = await browser.newPage({ viewport });
    for (const path of paths) {
      const url = new URL(path, baseUrl).toString();
      await page.goto(url, { waitUntil: 'networkidle' });
      const title = await page.title();
      const metrics = await page.evaluate(() => ({
        scrollWidth: document.documentElement.scrollWidth,
        clientWidth: document.documentElement.clientWidth,
        bodyTextLength: document.body.innerText.length,
        hasProductName: document.body.innerText.includes('Arabic Search Enhancement'),
        brokenImages: [...document.images]
          .filter((img) => !img.complete || img.naturalWidth === 0)
          .map((img) => img.currentSrc || img.src),
      }));

      if (metrics.scrollWidth > metrics.clientWidth + 2) {
        failures.push(`${viewportName} ${path}: horizontal overflow ${metrics.scrollWidth}>${metrics.clientWidth}`);
      }
      if (metrics.bodyTextLength < 500 || !metrics.hasProductName) {
        failures.push(`${viewportName} ${path}: missing expected page content`);
      }
      if (metrics.brokenImages.length > 0) {
        failures.push(`${viewportName} ${path}: broken images ${metrics.brokenImages.join(', ')}`);
      }

      console.log(`${viewportName} ${path} ok title="${title}" text=${metrics.bodyTextLength}`);
    }
    await page.close();
  }

  await browser.close();

  if (failures.length > 0) {
    console.error(failures.join('\n'));
    process.exit(1);
  }
}

main().catch((error) => {
  console.error(error);
  process.exit(1);
});
```

- [ ] **Step 2: Add npm script**

Add to `package.json`:

```json
"pages:smoke": "node scripts/verify-pages.js"
```

- [ ] **Step 3: Run the smoke test**

Run:

```bash
npm run pages:smoke
```

Expected: all six desktop/mobile page checks print `ok`.

---

### Task 2: Add Main Launch Verification Runner

**Files:**
- Create: `scripts/verify-launch.sh`
- Modify: `package.json`
- Create: `docs/verification/README.md`

- [ ] **Step 1: Create verification directory docs**

Create `docs/verification/README.md`:

```md
# Verification Evidence

Run `npm run launch:verify` from the repo root to regenerate `docs/verification/latest.md`.

The report records fresh command evidence for:

- Git state and origin sync
- GitHub Pages workflow status
- Public GitHub Pages URLs
- WordPress.org plugin page, API, ZIP, and assets
- Runtime ZIP artifact scan
- PHP syntax checks
- PHPUnit
- WordPress.org asset validation
- Local WordPress launch blog posts

The report also records known gaps. At launch time, no remote WordPress publishing credentials were present in this workspace, so only the local Docker WordPress sites can be verified for blog-post publishing.
```

- [ ] **Step 2: Create shell verifier**

Create `scripts/verify-launch.sh` with functions:

```bash
#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
REPORT="$ROOT/docs/verification/latest.md"
TMP_DIR="${TMPDIR:-/tmp}/ase-launch-verify"
mkdir -p "$TMP_DIR" "$(dirname "$REPORT")"

run_section() {
  local title="$1"
  shift
  {
    printf '\n## %s\n\n' "$title"
    printf 'Command:\n\n```bash\n%s\n```\n\n' "$*"
    printf 'Output:\n\n```text\n'
  } >> "$REPORT"
  if "$@" >> "$REPORT" 2>&1; then
    printf '```\n\nResult: PASS\n' >> "$REPORT"
  else
    local code=$?
    printf '```\n\nResult: FAIL (exit %s)\n' "$code" >> "$REPORT"
    return "$code"
  fi
}

write_header() {
  {
    printf '# Launch Verification Proof\n\n'
    printf 'Generated: %s\n\n' "$(date -u '+%Y-%m-%d %H:%M:%S UTC')"
    printf 'Repo: `%s`\n\n' "$ROOT"
    printf 'This file is generated by `scripts/verify-launch.sh`.\n'
  } > "$REPORT"
}

write_header
cd "$ROOT"

run_section "Git state" bash -lc 'git rev-parse HEAD && git ls-remote origin refs/heads/main && git status --short --ignored'
run_section "GitHub Pages workflow" bash -lc 'gh run list --workflow "Deploy static content to Pages" --branch main --limit 1 --json databaseId,headSha,status,conclusion,url | php -r '\''$runs=json_decode(stream_get_contents(STDIN), true); foreach($runs as $r){echo "run=".$r["databaseId"]."\nheadSha=".$r["headSha"]."\nstatus=".$r["status"]."\nconclusion=".($r["conclusion"]??"")."\nurl=".$r["url"]."\n"; if(($r["status"]??"")!=="completed" || ($r["conclusion"]??"")!=="success") exit(1);}'\'''
run_section "Public GitHub Pages URLs" bash -lc 'for url in "https://yasircs4.github.io/wp-plg-arabic-search-enhancement/?verify=1" "https://yasircs4.github.io/wp-plg-arabic-search-enhancement/blog/?verify=1" "https://yasircs4.github.io/wp-plg-arabic-search-enhancement/blog/launch-1-4-8.html?verify=1" "https://yasircs4.github.io/wp-plg-arabic-search-enhancement/marketing/?verify=1"; do code=$(curl -L -sS -A "ASEVerify/1.0" -o /tmp/ase-page.html -w "%{http_code}" "$url"); title=$(php -r '\''$html=file_get_contents("/tmp/ase-page.html"); preg_match("/<title>(.*?)<\\/title>/is",$html,$m); echo trim(preg_replace("/\\s+/"," ",$m[1]??""));'\''); echo "$url http=$code title=$title"; test "$code" = "200"; rg -q "Arabic Search Enhancement" /tmp/ase-page.html; done'
run_section "Public Pages Playwright smoke" npm run pages:smoke
run_section "WordPress.org API" bash -lc 'curl -L -sS -A "ASEVerify/1.0" "https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&request%5Bslug%5D=arabic-search-enhancement" -o /tmp/ase-wporg-api.json && php -r '\''$j=json_decode(file_get_contents("/tmp/ase-wporg-api.json"), true); foreach(["name","slug","version","tested","download_link"] as $k){echo "$k=".($j[$k]??"")."\n";} if(($j["version"]??"") !== "1.4.8") exit(1);'\'''
run_section "WordPress.org ZIP scan" bash -lc 'zip=/tmp/arabic-search-enhancement.1.4.8.verify.zip; curl -L -sS -A "ASEVerify/1.0" -o "$zip" https://downloads.wordpress.org/plugin/arabic-search-enhancement.1.4.8.zip; unzip -tq "$zip"; unzip -Z1 "$zip" | tee /tmp/ase-zip-list.txt; ! rg -n "(^|/)(\\.git|\\.github|docs|tests|vendor|node_modules|wporg-assets|composer\\.|package(-lock)?\\.json|phpunit\\.xml|SVN credentials\\.txt|\\.DS_Store|.*\\.zip)$" /tmp/ase-zip-list.txt'
run_section "WordPress.org asset CDN" bash -lc 'for asset in banner-772x250.png banner-1544x500.png icon-128x128.png icon-256x256.png screenshot-1.png screenshot-2.png screenshot-3.png screenshot-4.png; do out="/tmp/ase-$asset"; code=$(curl -L -sS -A "ASEVerify/1.0" -o "$out" -w "%{http_code} %{content_type} %{size_download}" "https://ps.w.org/arabic-search-enhancement/assets/$asset"); echo "$asset $code"; file "$out"; done'
run_section "PHP syntax" bash -lc 'find arabic-search-enhancement.php src tests -name "*.php" -print0 | xargs -0 -n1 php -l'
run_section "PHPUnit" vendor/bin/phpunit
run_section "WordPress.org asset dimensions" npm run wporg:validate-assets
run_section "Local WordPress blog posts" bash -lc 'for url in "http://localhost:8098/arabic-search-enhancement-1-4-8-live/" "http://localhost:8099/arabic-search-enhancement-1-4-8-live/"; do code=$(curl -L -sS -o /tmp/ase-local-post.html -w "%{http_code}" "$url"); echo "$url http=$code"; test "$code" = "200"; rg -q "Download Arabic Search Enhancement from WordPress.org" /tmp/ase-local-post.html; done'

cat >> "$REPORT" <<'EOF'

## Known Gaps

- Remote WordPress blog publishing is not verified because this workspace does not contain a safe remote WordPress REST/WP-CLI credential or alias.
- Local Docker WordPress blog posts are verified on ports `8098` and `8099`.
EOF

echo "$REPORT"
```

- [ ] **Step 3: Make executable and add npm script**

Run:

```bash
chmod +x scripts/verify-launch.sh
```

Add to `package.json`:

```json
"launch:verify": "bash scripts/verify-launch.sh"
```

- [ ] **Step 4: Run launch verification**

Run:

```bash
npm run launch:verify
```

Expected: command exits `0` and writes `docs/verification/latest.md`.

---

### Task 3: Wire Documentation To Proof Workflow

**Files:**
- Modify: `README.md`
- Modify: `docs/NEXT_DEV.md`

- [ ] **Step 1: Link proof command from README**

Add a `Verification` section:

```md
## Verification

Run the full evidence pass:

```bash
npm run launch:verify
```

The generated report is written to `docs/verification/latest.md`.
```

- [ ] **Step 2: Link proof command from next-dev handoff**

Add the same command to `docs/NEXT_DEV.md` under local setup.

- [ ] **Step 3: Run Markdown/content sanity checks**

Run:

```bash
git diff --check
rg -n "remote WordPress.*published|published.*remote WordPress" README.md docs
```

Expected: no whitespace errors; no false claim that a remote WordPress site was published.

---

### Task 4: Final Proof, Commit, Push

**Files:**
- All files above

- [ ] **Step 1: Run full verification**

Run:

```bash
npm run launch:verify
```

Expected: exit `0`.

- [ ] **Step 2: Check staged content**

Run:

```bash
git status --short --ignored
git diff --check
git diff --name-only
rg -n 'svn_[A-Za-z0-9_:-]+|gho_[A-Za-z0-9_]+|BEGIN (RSA|OPENSSH|PRIVATE) KEY|Password:' . -g '!node_modules' -g '!vendor' -g '!SVN credentials.txt' -g '!*.png' -g '!*.mo'
```

Expected: only intended files changed; no secret values.

- [ ] **Step 3: Commit and push**

Run:

```bash
git add docs README.md package.json package-lock.json scripts
git commit -m "Add evidence-first launch verification"
git push origin main
```

- [ ] **Step 4: Verify GitHub Actions and public URLs after push**

Run:

```bash
gh run list --workflow "Deploy static content to Pages" --branch main --limit 1 --json status,conclusion,headSha,url
npm run pages:smoke
```

Expected: latest Pages workflow completes successfully and public smoke test passes.

---

## Self-Review

- Spec coverage: The plan covers proof standards, subagent audits, scripts, generated report, known gaps, docs updates, commit, push, and public verification.
- Placeholder scan: No `TBD`, `TODO`, or undefined implementation steps remain.
- Type consistency: Script names are consistent: `scripts/verify-pages.js`, `scripts/verify-launch.sh`, `npm run pages:smoke`, and `npm run launch:verify`.
