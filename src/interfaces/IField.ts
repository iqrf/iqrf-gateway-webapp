import VueI18n from 'vue-i18n';

/**
 * DataTable field interface
 */
export interface IField {
	key: string
	label: VueI18n.TranslateResult
	filter?: boolean
	sorter?: boolean
}
