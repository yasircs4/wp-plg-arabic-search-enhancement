// @ts-check
const { defineConfig, devices } = require('@playwright/test');

module.exports = defineConfig({
	testDir: '.',
	testMatch: ['wporg-assets/src/*.spec.js'],
	timeout: 120000,
	expect: {
		timeout: 15000,
	},
	use: {
		trace: 'retain-on-failure',
	},
	projects: [
		{
			name: 'chromium',
			use: { ...devices['Desktop Chrome'] },
		},
	],
});
