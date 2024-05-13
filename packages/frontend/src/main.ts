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

import Vue from 'vue';
import store from './store';
import router from './router';
import App from '@/App.vue';

import './styles/app.scss';
import 'vue-datetime/dist/vue-datetime.css';
import 'vue-select/dist/vue-select.css';
import 'vue-toast-notification/dist/theme-sugar.css';
import 'vue2-datepicker/index.css';

import '@/plugins/axios';
import '@/plugins/clipboard';
import i18n from '@/plugins/i18n';
import '@/plugins/meta';
import '@/plugins/sentry';
import '@/plugins/toast';
import vuetify from '@/plugins/vuetify';
import '@/plugins/webSocket';

const app = new Vue({
	router,
	store,
	i18n,
	render: h => h(App),
	vuetify,
	metaInfo: {
		titleTemplate: (titleChunk: string): string => {
			const title = i18n.t('core.title.generic').toString();
			return (titleChunk ? `${i18n.t(titleChunk).toString()} | ` : '') + title;
		}
	},
}).$mount('#app');

if (import.meta.env.VITE_CYPRESS_ENABLED === '1' && (window['Cypress'] ?? false)) {
	window['app'] = app;
}
