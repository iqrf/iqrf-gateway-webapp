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

import Vue from 'vue';
import store from './store';
import router from './router';
import ThemeManager from './helpers/themeManager';
import App from '@/App.vue';

import './css/themes/generic.scss';
import './css/app.scss';
import 'vue-datetime/dist/vue-datetime.css';
import 'vue-select/dist/vue-select.css';
import 'vue-toast-notification/dist/theme-sugar.css';
import '@fortawesome/fontawesome-svg-core/styles.css';
import 'vue2-datepicker/index.css';

import {config, library} from '@fortawesome/fontawesome-svg-core';
import {faClipboard, faEye, faEyeSlash, faSquarePlus, faTrashAlt} from '@fortawesome/free-regular-svg-icons';
import {faKey, faPlus} from '@fortawesome/free-solid-svg-icons';

import '@/plugins/axios';
import '@/plugins/clipboard';
import '@/plugins/coreui';
import i18n from '@/plugins/i18n';
import '@/plugins/meta';
import '@/plugins/sentry';
import '@/plugins/toast';
import '@/plugins/webSocket';

config.autoAddCss = true;
library.add(
	faClipboard,
	faEye,
	faEyeSlash,
	faKey,
	faPlus,
	faSquarePlus,
	faTrashAlt
);

const app = new Vue({
	router,
	store,
	i18n,
	render: h => h(App),
	metaInfo: {
		titleTemplate: (titleChunk: string): string => {
			const title = i18n.t(ThemeManager.getTitleKey()).toString();
			return (titleChunk ? `${i18n.t(titleChunk).toString()} | ` : '') + title;
		}
	},
}).$mount('#app');

if (process.env.VUE_APP_CYPRESS_ENABLED === '1' && (window['Cypress'] ?? false)) {
	window['app'] = app;
}
