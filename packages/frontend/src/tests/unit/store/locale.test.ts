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

import { Language } from '@iqrf/iqrf-ui-common-types';
import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, test, vi } from 'vitest';

import i18n from '@/plugins/i18n';
import { useLocaleStore } from '@/store/locale';
import { mockPreferredLocale } from '@/tests/mocks/preferedLocale';

describe('Locale store', (): void => {

	/**
	 * Sets up the test environment before each test
	 * Initializes a new Pinia instance and sets it as active
	 */
	beforeEach((): void => {
		setActivePinia(createPinia());
		vi.restoreAllMocks();
		vi.unstubAllEnvs();
	});

	test('default locale - English', (): void => {
		expect.assertions(2);
		mockPreferredLocale('en-US');
		const store = useLocaleStore();
		expect(store.locale).toBe(Language.English);
		expect(store.getLocale).toBe(Language.English);
	});

	test('default locale - Czech', (): void => {
		expect.assertions(2);
		mockPreferredLocale('cs-CZ');
		const store = useLocaleStore();
		expect(store.locale).toBe(Language.Czech);
		expect(store.getLocale).toBe(Language.Czech);
	});

	test('set locale', (): void => {
		expect.assertions(6);
		mockPreferredLocale('en-US');
		const store = useLocaleStore();
		expect(store.getLocale).toBe(Language.English);
		// @ts-ignore Accessing ComputedRef value
		expect(i18n.global.locale.value).toBe(import.meta.env.VITE_I18N_LOCALE);
		store.setLocale(Language.Czech);
		expect(store.getLocale).toBe(Language.Czech);
		// @ts-ignore Accessing ComputedRef value
		expect(i18n.global.locale.value).toBe(Language.Czech);
		store.setLocale(Language.English);
		expect(store.getLocale).toBe(Language.English);
		// @ts-ignore Accessing ComputedRef value
		expect(i18n.global.locale.value).toBe(Language.English);
	});

	test('get available locales - production', (): void => {
		expect.assertions(1);
		vi.stubEnv('DEV', false);
		const store = useLocaleStore();
		expect(store.getAvailableLocales).toStrictEqual([
			Language.English,
		]);
	});

	test('get available locales - development', (): void => {
		expect.assertions(1);
		vi.stubEnv('DEV', true);
		const store = useLocaleStore();
		expect(store.getAvailableLocales).toStrictEqual([
			Language.English,
			Language.Czech,
		]);
	});

});
