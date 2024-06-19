/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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

import { UserLanguage } from '@iqrf/iqrf-gateway-webapp-client/types';
import { CZ, GB } from 'country-flag-icons/string/3x2';
import { Base64 } from 'js-base64';
import { defineStore } from 'pinia';
import { preferredLocale } from 'preferred-locale';

import i18n from '@/plugins/i18n';

export interface Locale {
	/// Locale code
	code: UserLanguage;
	/// Locale Unicode flag
	flag: string;
	/// Development build only?
	developmentOnly?: boolean;
}

/**
 * Locale store state
 */
interface LocaleState {
	locale: UserLanguage;
}


const locales: Locale[] = [
	{
		code: UserLanguage.English,
		flag: Base64.encode(GB),
	},
	{
		code: UserLanguage.Czech,
		flag: Base64.encode(CZ),
		developmentOnly: true,
	},
];

const filteredLocales: Locale[] = locales.filter((locale: Locale): boolean => import.meta.env.DEV || !(locale.developmentOnly ?? false));

export const useLocaleStore = defineStore('locale', {
	state: (): LocaleState => ({
		locale: preferredLocale('en', filteredLocales.map((locale: Locale): string => locale.code), { languageOnly: true }) as UserLanguage,
	}),
	actions: {
		/**
		 * Sets a new locale
		 * @param {UserLanguage} locale Locale to set
		 */
		setLocale(locale: UserLanguage): void {
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
		 * @return {UserLanguage} Current locale code
		 */
		getLocale(state: LocaleState): UserLanguage {
			return state.locale;
		},
		/**
		 * Returns current locale flag
		 * @param {LocaleState} state Current state
		 * @return {string} Current locale flag
		 */
		getLocaleFlag(state: LocaleState): string {
			const idx: number = locales.findIndex((item: Locale): boolean => item.code === state.locale);
			if (idx === -1) {
				return '🏴‍☠️';
			}
			return locales[idx].flag;
		},
	},
	persist: true,
});
