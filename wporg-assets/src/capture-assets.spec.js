const fs = require('fs');
const path = require('path');
const { execFileSync } = require('child_process');
const { test, expect } = require('playwright/test');

const repoRoot = path.resolve(__dirname, '../..');
const distDir = path.join(repoRoot, 'wporg-assets', 'dist');
const baseUrl = process.env.ASE_WPORG_BASE_URL || 'http://localhost:8098';
const wpUser = process.env.ASE_WPORG_USER || 'admin';
const wpPass = process.env.ASE_WPORG_PASS || 'password';
const composeFile = process.env.ASE_WPORG_COMPOSE_FILE || path.join(__dirname, 'docker-compose.wporg.yml');
const composeProject = process.env.ASE_WPORG_PROJECT || 'ase-wporg';

test.describe.configure({ mode: 'serial' });

function ensureDist() {
	fs.mkdirSync(distDir, { recursive: true });
}

function assetPath(file) {
	return path.join(distDir, file);
}

function wpCli(args) {
	execFileSync(
		'docker',
		['compose', '-p', composeProject, '-f', composeFile, 'run', '--rm', 'cli', 'wp', ...args],
		{ stdio: 'inherit' }
	);
}

async function renderHtml(page, html, width, height, output) {
	await page.setViewportSize({ width, height });
	await page.setContent(html, { waitUntil: 'load' });
	await page.locator('[data-asset-root]').screenshot({ path: assetPath(output) });
}

function bannerHtml(width, height) {
	const scale = width / 1544;
	return `<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<style>
html, body {
	margin: 0;
	width: ${width}px;
	height: ${height}px;
	overflow: hidden;
}
body {
	font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
}
.asset {
	position: relative;
	box-sizing: border-box;
	width: ${width}px;
	height: ${height}px;
	overflow: hidden;
	background:
		radial-gradient(circle at 16% 16%, rgba(45, 212, 191, 0.30), transparent 28%),
		radial-gradient(circle at 86% 78%, rgba(250, 204, 21, 0.24), transparent 24%),
		linear-gradient(135deg, #0f172a 0%, #164e63 48%, #064e3b 100%);
	color: #f8fafc;
}
.pattern {
	position: absolute;
	inset: 0;
	opacity: 0.18;
	background-image:
		linear-gradient(90deg, rgba(255, 255, 255, 0.20) 1px, transparent 1px),
		linear-gradient(rgba(255, 255, 255, 0.16) 1px, transparent 1px);
	background-size: ${48 * scale}px ${48 * scale}px;
}
.mark {
	position: absolute;
	right: ${88 * scale}px;
	top: ${56 * scale}px;
	width: ${352 * scale}px;
	height: ${352 * scale}px;
	border-radius: ${76 * scale}px;
	background: rgba(255, 255, 255, 0.10);
	border: ${3 * scale}px solid rgba(255, 255, 255, 0.24);
}
.mark::before {
	content: "ع";
	position: absolute;
	inset: ${30 * scale}px ${78 * scale}px auto auto;
	font-family: Georgia, "Times New Roman", serif;
	font-size: ${178 * scale}px;
	font-weight: 700;
	line-height: 1;
	color: #facc15;
}
.mark::after {
	content: "";
	position: absolute;
	left: ${82 * scale}px;
	bottom: ${72 * scale}px;
	width: ${116 * scale}px;
	height: ${116 * scale}px;
	border: ${16 * scale}px solid #ccfbf1;
	border-radius: 999px;
	box-shadow: ${76 * scale}px ${74 * scale}px 0 ${-56 * scale}px #ccfbf1;
	transform: rotate(-12deg);
}
.content {
	position: absolute;
	left: ${88 * scale}px;
	top: ${72 * scale}px;
	width: ${930 * scale}px;
}
.eyebrow {
	display: inline-block;
	padding: ${12 * scale}px ${18 * scale}px;
	border-radius: ${999 * scale}px;
	background: rgba(15, 23, 42, 0.46);
	color: #a7f3d0;
	font-size: ${28 * scale}px;
	font-weight: 700;
	letter-spacing: 0;
}
h1 {
	margin: ${26 * scale}px 0 ${18 * scale}px;
	font-size: ${82 * scale}px;
	line-height: 0.98;
	letter-spacing: 0;
}
p {
	margin: 0;
	max-width: ${760 * scale}px;
	color: #dbeafe;
	font-size: ${34 * scale}px;
	line-height: 1.3;
}
.sample {
	position: absolute;
	left: ${90 * scale}px;
	bottom: ${20 * scale}px;
	display: flex;
	gap: ${16 * scale}px;
	align-items: center;
	color: #fef3c7;
	font-size: ${31 * scale}px;
	font-weight: 700;
}
.chip {
	padding: ${10 * scale}px ${18 * scale}px;
	border-radius: ${16 * scale}px;
	background: rgba(255, 255, 255, 0.12);
	border: ${1.5 * scale}px solid rgba(255, 255, 255, 0.24);
}
</style>
</head>
<body>
<main class="asset" data-asset-root>
	<div class="pattern"></div>
	<div class="content">
		<div class="eyebrow">WordPress Arabic Search</div>
		<h1>Arabic Search Enhancement</h1>
		<p>Find Arabic content across diacritics, Alef variants, and common letter-form differences.</p>
	</div>
	<div class="sample" dir="rtl">
		<span class="chip">قران</span>
		<span>finds</span>
		<span class="chip">قرآن</span>
	</div>
	<div class="mark" aria-hidden="true"></div>
</main>
</body>
</html>`;
}

function iconHtml(size) {
	const scale = size / 256;
	return `<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<style>
html, body {
	margin: 0;
	width: ${size}px;
	height: ${size}px;
	overflow: hidden;
}
.icon {
	position: relative;
	box-sizing: border-box;
	width: ${size}px;
	height: ${size}px;
	overflow: hidden;
	border-radius: ${42 * scale}px;
	background:
		radial-gradient(circle at 25% 20%, rgba(250, 204, 21, 0.42), transparent 34%),
		linear-gradient(145deg, #0f766e 0%, #155e75 45%, #0f172a 100%);
	border: ${5 * scale}px solid #ccfbf1;
}
.letter {
	position: absolute;
	right: ${44 * scale}px;
	top: ${20 * scale}px;
	font-family: Georgia, "Times New Roman", serif;
	font-size: ${132 * scale}px;
	font-weight: 700;
	line-height: 1;
	color: #f8fafc;
	text-shadow: 0 ${4 * scale}px ${14 * scale}px rgba(15, 23, 42, 0.35);
}
.lens {
	position: absolute;
	left: ${42 * scale}px;
	bottom: ${48 * scale}px;
	width: ${84 * scale}px;
	height: ${84 * scale}px;
	border: ${14 * scale}px solid #facc15;
	border-radius: 999px;
}
.lens::after {
	content: "";
	position: absolute;
	width: ${72 * scale}px;
	height: ${16 * scale}px;
	right: ${-58 * scale}px;
	bottom: ${-34 * scale}px;
	border-radius: 999px;
	background: #facc15;
	transform: rotate(44deg);
	transform-origin: left center;
}
</style>
</head>
<body>
<main class="icon" data-asset-root>
	<div class="letter">ع</div>
	<div class="lens"></div>
</main>
</body>
</html>`;
}

async function login(page) {
	await page.goto(`${baseUrl}/wp-login.php`, { waitUntil: 'domcontentloaded' });
	await page.evaluate(
		({ user, pass }) => {
			const userField = document.getElementById('user_login');
			const passField = document.getElementById('user_pass');
			for (const [field, value] of [[userField, user], [passField, pass]]) {
				field.value = value;
				field.dispatchEvent(new Event('input', { bubbles: true }));
				field.dispatchEvent(new Event('change', { bubbles: true }));
			}
		},
		{ user: wpUser, pass: wpPass }
	);
	await expect(page.locator('#user_login')).toHaveValue(wpUser);
	await expect(page.locator('#user_pass')).toHaveValue(wpPass);
	await Promise.all([
		page.waitForURL(/wp-admin/, { waitUntil: 'domcontentloaded' }),
		page.locator('#wp-submit').click(),
	]);
	await expect(page.locator('#wpadminbar')).toBeVisible();
}

async function captureViewport(page, output) {
	await page.screenshot({ path: assetPath(output), fullPage: false });
}

test('generate banner and icon assets', async ({ page }) => {
	ensureDist();
	await renderHtml(page, bannerHtml(1544, 500), 1544, 500, 'banner-1544x500.png');
	await renderHtml(page, bannerHtml(772, 250), 772, 250, 'banner-772x250.png');
	await renderHtml(page, iconHtml(256), 256, 256, 'icon-256x256.png');
	await renderHtml(page, iconHtml(128), 128, 128, 'icon-128x128.png');
});

test('capture WordPress.org screenshots from local WordPress', async ({ browser }) => {
	ensureDist();

	const publicContext = await browser.newContext({ viewport: { width: 1440, height: 900 } });
	const publicPage = await publicContext.newPage();
	await publicPage.goto(`${baseUrl}/?s=${encodeURIComponent('قران')}`, { waitUntil: 'networkidle' });
	await expect(publicPage.getByRole('link', { name: 'فوائد قراءة القرآن الكريم' }).first()).toBeVisible();
	await captureViewport(publicPage, 'screenshot-2.png');
	await publicContext.close();

	wpCli(['site', 'switch-language', 'en_US']);

	const adminContext = await browser.newContext({ viewport: { width: 1440, height: 900 } });
	const adminPage = await adminContext.newPage();
	await login(adminPage);

	await adminPage.goto(`${baseUrl}/wp-admin/options-general.php?page=arabic-search-enhancement`, { waitUntil: 'domcontentloaded' });
	await expect(adminPage.getByRole('heading', { name: /Arabic Search Settings|إعدادات البحث العربي/ })).toBeVisible();
	await captureViewport(adminPage, 'screenshot-1.png');

	await adminPage.locator('#ase-run-test').scrollIntoViewIfNeeded();
	await adminPage.click('#ase-run-test');
	await expect(adminPage.locator('#ase-test-results')).toBeVisible();
	await captureViewport(adminPage, 'screenshot-3.png');

	wpCli(['language', 'core', 'install', 'ar', '--activate']);
	wpCli(['site', 'switch-language', 'ar']);

	await adminPage.goto(`${baseUrl}/wp-admin/options-general.php?page=arabic-search-enhancement`, { waitUntil: 'domcontentloaded' });
	await expect(adminPage.locator('.wrap.arabic-search-enhancement.rtl')).toBeVisible();
	await captureViewport(adminPage, 'screenshot-4.png');

	await adminContext.close();
});
