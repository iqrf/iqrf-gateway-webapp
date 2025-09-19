/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

import { Language } from '@iqrf/iqrf-vue-ui';
import { defineStore } from 'pinia';
import { preferredLocale } from 'preferred-locale';

import i18n from '@/plugins/i18n';

export interface Locale {
	/// Locale code
	code: Language;
	/// Development build only?
	developmentOnly?: boolean;
}

/**
 * Locale store state
 */
interface LocaleState {
	locale: Language;
}


const locales: Locale[] = [
	{
		code: Language.English,
	},
	{
		code: Language.Czech,
		developmentOnly: true,
	},
];

const filteredLocales: Locale[] = locales.filter((locale: Locale): boolean => import.meta.env.DEV || !(locale.developmentOnly ?? false));

export const useLocaleStore = defineStore('locale', {
	state: (): LocaleState => ({
		locale: preferredLocale('en', filteredLocales.map((locale: Locale): string => locale.code), { languageOnly: true }) as Language,
	}),
	actions: {
		/**
		 * Sets a new locale
		 * @param {Language} locale Locale to set
		 */
		setLocale(locale: Language): void {
			// @ts-ignore
			i18n.global.locale.value = locale;
			this.locale = locale;
		},
	},
	getters: {
		/**
		 * Returns available locales
		 * @return {Locale[]} Available locales
		 */
		getAvailableLocales(): Locale[] {
			return filteredLocales;
		},
		/**
		 * Returns current locale code
		 * @param {LocaleState} state Current state
		 * @return {Language} Current locale code
		 */
		getLocale(state: LocaleState): Language {
			return state.locale;
		},
	},
	persist: true,
});
