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

import { UserThemePreference } from '@iqrf/iqrf-gateway-webapp-client/types';
import { Theme } from '@iqrf/iqrf-ui-common-types';
import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, test } from 'vitest';
import MatchMediaMock from 'vitest-matchmedia-mock';

import { useThemeStore } from '@/store/theme';

describe('Theme store', (): void => {

	/**
	 * @const {MatchMediaMock} matchMediaMock Mock for the matchMedia function
	 */
	const matchMediaMock = new MatchMediaMock();

	/**
	 * Sets up the test environment before each test
	 * Initializes a new Pinia instance and sets it as active
	 */
	beforeEach((): void => {
		matchMediaMock.clear();
		setActivePinia(createPinia());
	});

	test('default theme - dark', (): void => {
		expect.assertions(2);
		matchMediaMock.useMediaQuery('(prefers-color-scheme: dark)');
		const store = useThemeStore();
		expect(store.current).toBe(Theme.Dark);
		expect(store.toggled).toBe(false);
	});

	test('default theme - light', (): void => {
		expect.assertions(3);
		matchMediaMock.useMediaQuery('(prefers-color-scheme: light)');
		const store = useThemeStore();
		expect(store.current).toBe(Theme.Light);
		expect(store.getTheme).toBe(Theme.Light);
		expect(store.toggled).toBe(false);
	});

	test('sets theme', (): void => {
		expect.assertions(8);
		matchMediaMock.useMediaQuery('(prefers-color-scheme: light)');
		const store = useThemeStore();
		expect(store.current).toBe(Theme.Light);
		expect(store.toggled).toBe(false);
		store.setTheme(UserThemePreference.Dark);
		expect(store.current).toBe(Theme.Dark);
		expect(store.toggled).toBe(false);
		store.setTheme(UserThemePreference.Light);
		expect(store.current).toBe(Theme.Light);
		expect(store.toggled).toBe(false);
		store.setTheme(UserThemePreference.Auto);
		expect(store.current).toBe(Theme.Light);
		expect(store.toggled).toBe(false);
	});

	test('toggles theme', (): void => {
		expect.assertions(6);
		matchMediaMock.useMediaQuery('(prefers-color-scheme: dark)');
		const store = useThemeStore();
		expect(store.current).toBe(Theme.Dark);
		expect(store.toggled).toBe(false);
		store.toggleTheme();
		expect(store.current).toBe(Theme.Light);
		expect(store.toggled).toBe(true);
		store.toggleTheme();
		expect(store.current).toBe(Theme.Dark);
		expect(store.toggled).toBe(true);
	});

});
