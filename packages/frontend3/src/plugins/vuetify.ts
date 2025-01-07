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

import '@/styles/main.scss';

import { useI18n } from 'vue-i18n';
import { createVuetify } from 'vuetify';
import { aliases, mdi } from 'vuetify/iconsets/mdi-svg';
import * as labs from 'vuetify/labs/components';
import { createVueI18nAdapter } from 'vuetify/locale/adapters/vue-i18n';

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
		adapter: createVueI18nAdapter({ i18n, useI18n }),
	},
	theme: {
		themes: {
			dark: {
				colors: {
					primary: '#367fa9',
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
