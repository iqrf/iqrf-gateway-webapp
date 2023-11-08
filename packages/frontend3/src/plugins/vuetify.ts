import '@/styles/main.scss';

import {useI18n} from 'vue-i18n';
import {createVuetify} from 'vuetify';
import {aliases, mdi} from 'vuetify/iconsets/mdi-svg';
import * as labs from 'vuetify/labs/components';
import {createVueI18nAdapter} from 'vuetify/locale/adapters/vue-i18n';

import i18n from '@/plugins/i18n';

/**
 * Create Vuetify instance
 */
export default createVuetify({
	components: {
		...labs,
	},
	icons: {
		defaultSet: 'mdi',
		aliases,
		sets: {
			mdi,
		},
	},
	locale: {
		// @ts-ignore
		adapter: createVueI18nAdapter({i18n, useI18n}),
	},
	theme: {
		themes: {
			dark: {
				colors: {
					primary: '#367fa9',
					background: '#ebedef',
				},
			},
			light: {
				colors: {
					primary: '#367fa9',
					background: '#ebedef',
				},
			},
		},
	},
});
