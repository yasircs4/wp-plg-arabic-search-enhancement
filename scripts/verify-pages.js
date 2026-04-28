const { chromium } = require('@playwright/test');

const baseUrl = process.env.ASE_PAGES_BASE_URL || 'https://yasircs4.github.io/wp-plg-arabic-search-enhancement/';
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
			if (metrics.bodyTextLength < 300 || !metrics.hasProductName) {
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
