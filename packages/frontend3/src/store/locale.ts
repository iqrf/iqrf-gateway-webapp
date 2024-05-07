import { CZ, GB } from 'country-flag-icons/string/3x2';
import { Base64 } from 'js-base64';
import { defineStore } from 'pinia';
import { preferredLocale } from 'preferred-locale';

import i18n from '@/plugins/i18n';

export interface Locale {
	/// Locale code
	code: string;
	/// Locale Unicode flag
	flag: string;
	/// Development build only?
	developmentOnly?: boolean;
}

/**
 * Locale store state
 */
interface LocaleState {
	locale: string;
}


const locales: Locale[] = [
	{
		code: 'en',
		flag: Base64.encode(GB),
	},
	{
		code: 'cs',
		flag: Base64.encode(CZ),
		developmentOnly: true,
	},
];

const filteredLocales: Locale[] = locales.filter((locale: Locale): boolean => (import.meta.env.DEV || !(locale.developmentOnly ?? false)));

export const useLocaleStore = defineStore('locale', {
	state: (): LocaleState => ({
		locale: preferredLocale('en', filteredLocales.map((locale: Locale): string => locale.code), { languageOnly: true }),
	}),
	actions: {
		/**
		 * Sets a new locale
		 * @param locale Locale to set
		 */
		setLocale(locale: string): void {
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
		 * @return {string} Current locale code
		 */
		getLocale(state: LocaleState): string {
			return state.locale;
		},
		/**
		 * Returns current locale flag
		 * @param {LocaleState} state Current state
		 * @return {string} Current locale flag
		 */
		getLocaleFlag(state: LocaleState): string {
			const idx = locales.findIndex((item: Locale): boolean => (item.code === state.locale));
			if (idx === -1) {
				return '🏴‍☠️';
			}
			return locales[idx].flag;
		},
	},
	persist: true,
});
