/**
 * Copyright 2017-2022 IQRF Tech s.r.o.
 * Copyright 2019-2022 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/// <reference types="cypress" />

import '@cypress/code-coverage/task';
import Browser = Cypress.Browser;
import BrowserLaunchOptions = Cypress.BrowserLaunchOptions;

/**
 * This function is called when a project is opened or re-opened (e.g. due to
 * the project's config changing)
 * @type {Cypress.PluginConfig}
 */
module.exports = (on, config) => {
	require('@cypress/code-coverage/task')(on, config);

	/*
	 * Let's increase the browser window size when running headlessly
	 * this will produce higher resolution images and videos
	 * https://on.cypress.io/browser-launch-api
	 */
	on('before:browser:launch', (browser: Browser, launchOptions: BrowserLaunchOptions) => {
		console.log('launching browser %s is headless? %s', browser.name, browser.isHeadless,);

		// the browser width and height we want to get
		// our screenshots and videos will be of that resolution
		const width = 2560;
		const height = 1440;
		console.log('setting the browser window size to %dx%d', width, height);

		if (browser.name === 'chrome' && browser.isHeadless) {
			launchOptions.args.push(`--window-size=${width},${height}`);
			// force screen to be non-retina and just use our given resolution
			launchOptions.args.push('--force-device-scale-factor=1');
		}
		if (browser.name === 'electron' && browser.isHeadless) {
			// might not work on CI for some reason
			launchOptions.preferences.width = width;
			launchOptions.preferences.height = height;
		}
		if (browser.name === 'firefox' && browser.isHeadless) {
			launchOptions.args.push(`--width=${width}`);
			launchOptions.args.push(`--height=${height}`);
		}

		return launchOptions;
	});

	return config;
};
