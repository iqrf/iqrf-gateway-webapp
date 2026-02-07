/**
 * Copyright 2017-2026 IQRF Tech s.r.o.
 * Copyright 2019-2026 MICRORISC s.r.o.
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


import { Theme } from '@iqrf/iqrf-ui-common-types';
import { beforeEach, describe, expect, test } from 'vitest';
import MatchMediaMock from 'vitest-matchmedia-mock';

import { PreferenceDefaults } from '@/helpers/PreferenceDefaults';


describe('User preferences defaults helper', (): void => {

	/**
	 * @const {MatchMediaMock} matchMediaMock Mock for the matchMedia function
	 */
	const matchMediaMock = new MatchMediaMock();

	/**
	 * Restore all mocks before each test
	 */
	beforeEach((): void => {
		matchMediaMock.clear();
	});

	test('get system theme - dark', (): void => {
		expect.assertions(1);
		matchMediaMock.useMediaQuery('(prefers-color-scheme: dark)');
		expect(PreferenceDefaults.getSystemTheme()).toStrictEqual(Theme.Dark);
	});

	test('get system theme - light', (): void => {
		expect.assertions(1);
		matchMediaMock.useMediaQuery('(prefers-color-scheme: light)');
		expect(PreferenceDefaults.getSystemTheme()).toStrictEqual(Theme.Light);
	});

	test('get system theme - none', (): void => {
		expect.assertions(1);
		expect(PreferenceDefaults.getSystemTheme()).toStrictEqual(Theme.Light);
	});

});
