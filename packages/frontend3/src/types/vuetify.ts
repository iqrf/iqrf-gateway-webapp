import type VueI18n from 'vue-i18n';

export interface SelectItem {
	title: string|VueI18n.TranslateResult
	value: string|number|boolean|null
}

/**
 * Application theme
 */
export enum AppTheme {
	/// Generic theme
	Generic = 'generic',
	/// IQAROS theme
	IQAROS = 'iqaros',
}
