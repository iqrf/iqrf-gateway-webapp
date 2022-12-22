/**
 * Copyright 2017-2022 IQRF Tech s.r.o.
 * Copyright 2019-2022 MICRORISC s.r.o.
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

import axios, {AxiosError, AxiosResponse} from 'axios';
import CoreuiVue from '@coreui/vue/src';
import * as Sentry from '@sentry/vue';
import {BrowserTracing} from '@sentry/tracing';
import Vue from 'vue';
import VueMeta from 'vue-meta';
import VueToast from 'vue-toast-notification';
import Clipboard from 'v-clipboard';
import {config, library} from '@fortawesome/fontawesome-svg-core';
import {faEye, faEyeSlash} from '@fortawesome/free-regular-svg-icons';

import store from './store';
import router from './router';
import i18n from './i18n';
import UrlBuilder from './helpers/urlBuilder';
import ThemeManager from './helpers/themeManager';
import ClientSocket from './ws/ClientSocket';

import App from './App.vue';

import './css/themes/generic.scss';
import './css/app.scss';
import 'vue-datetime/dist/vue-datetime.css';
import 'vue-select/dist/vue-select.css';
import 'vue-toast-notification/dist/theme-sugar.css';
import '@fortawesome/fontawesome-svg-core/styles.css';

config.autoAddCss = true;
library.add(faEye, faEyeSlash);

import * as version from '../version.json';

let release = version.version;
if (version.pipeline !== '') {
	release += '~' + version.pipeline;
}

if (process.env.NODE_ENV === 'production') {
	Sentry.init({
		dsn: 'https://435ee2b55f994e5f85e21a9ca93ea7a7@sentry.iqrf.org/5',
		integrations: [
			new BrowserTracing({
				routingInstrumentation: Sentry.vueRouterInstrumentation(router),
				tracePropagationTargets: ['localhost', window.location.hostname, /^\//],
			}),
		],
		release: release,
		tracesSampleRate: 1.0,
		Vue,
	});
}

Vue.prototype.$appName = i18n.t(ThemeManager.getTitleKey());

const urlBuilder: UrlBuilder = new UrlBuilder();

store.dispatch('daemon_initSocket', new ClientSocket(
	{
		url: urlBuilder.getWsApiUrl(),
		prefix: 'daemonClient/',
		autoConnect: true,
		reconnect: true,
		reconnectDelay: 5000,
	},
	store,
));

store.dispatch('monitor_initSocket', new ClientSocket(
	{
		url: urlBuilder.getWsMonitorUrl(),
		prefix: 'monitorClient/',
		autoConnect: true,
		reconnect: true,
		reconnectDelay: 5000,
	},
	store,
));

Vue.use(CoreuiVue);
Vue.use(VueMeta);
Vue.use(VueToast,{
	position: 'top',
	duration: 5000
});
Vue.use(Clipboard);

axios.defaults.baseURL = urlBuilder.getRestApiUrl();
axios.defaults.timeout = 30000;

// Enable sending cookie to the backend (to enable xdebug)
if (process.env.NODE_ENV === 'development') {
	axios.defaults.withCredentials = false;
}

axios.interceptors.response.use(
	(response: AxiosResponse) => {
		return response;
	},
	(error: AxiosError) => {
		if (error.response === undefined) {
			// TODO: Add Network error toaster notification
			return Promise.reject(error);
		}
		if (error.response.status === 401) {
			store.dispatch('user/signOut')
				.then(() => {
					router.push({path: '/sign/in', query: {redirect: router.currentRoute.path}});
					Vue.$toast.warning(
						i18n.t('core.sign.out.expired').toString(),
					);
				});
			return;
		}
		return Promise.reject(error);
	}
);

const app = new Vue({
	router,
	store,
	i18n: i18n,
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
