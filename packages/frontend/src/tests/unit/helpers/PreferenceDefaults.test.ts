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
import { beforeEach, describe, expect, test, vi } from 'vitest';
import MatchMediaMock from 'vitest-matchmedia-mock';

import { PreferenceDefaults } from '@/helpers/PreferenceDefaults';
import { mockPreferredLocale } from '@/tests/mocks/preferedLocale';
import { TimeFormat } from '@/types/time';

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
		vi.restoreAllMocks();
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

	// Tests for getSystemTimeFormat
	test('get system time format - 12h (h12)', (): void => {
		expect.assertions(1);
		vi.spyOn(Intl.DateTimeFormat.prototype, 'resolvedOptions')
			.mockReturnValue({ hourCycle: 'h12' } as Intl.ResolvedDateTimeFormatOptions);
		expect(PreferenceDefaults.getSystemTimeFormat()).toStrictEqual(TimeFormat.Hour12);
	});

	test('get system time format - 12h (h11)', (): void => {
		expect.assertions(1);
		vi.spyOn(Intl.DateTimeFormat.prototype, 'resolvedOptions')
			.mockReturnValue({ hourCycle: 'h11' } as Intl.ResolvedDateTimeFormatOptions);
		expect(PreferenceDefaults.getSystemTimeFormat()).toStrictEqual(TimeFormat.Hour12);
	});

	test('get system time format - 24h (h23)', (): void => {
		expect.assertions(1);
		vi.spyOn(Intl.DateTimeFormat.prototype, 'resolvedOptions')
			.mockReturnValue({ hourCycle: 'h23' } as Intl.ResolvedDateTimeFormatOptions);
		expect(PreferenceDefaults.getSystemTimeFormat()).toStrictEqual(TimeFormat.Hour24);
	});

	test('get system time format - 24h (h24)', (): void => {
		expect.assertions(1);
		vi.spyOn(Intl.DateTimeFormat.prototype, 'resolvedOptions')
			.mockReturnValue({ hourCycle: 'h24' } as Intl.ResolvedDateTimeFormatOptions);
		expect(PreferenceDefaults.getSystemTimeFormat()).toStrictEqual(TimeFormat.Hour24);
	});

	test('get system time format - undefined (default to 24h)', (): void => {
		expect.assertions(1);
		vi.spyOn(Intl.DateTimeFormat.prototype, 'resolvedOptions')
			.mockReturnValue({} as Intl.ResolvedDateTimeFormatOptions);
		mockPreferredLocale('cs-CZ');
		expect(PreferenceDefaults.getSystemTimeFormat()).toStrictEqual(TimeFormat.Hour24);
	});

	test('get system time format - undefined (default to 12h)', (): void => {
		expect.assertions(1);
		vi.spyOn(Intl.DateTimeFormat.prototype, 'resolvedOptions')
			.mockReturnValue({} as Intl.ResolvedDateTimeFormatOptions);
		mockPreferredLocale('en-US');
		expect(PreferenceDefaults.getSystemTimeFormat()).toStrictEqual(TimeFormat.Hour24);
	});

});
