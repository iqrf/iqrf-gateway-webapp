/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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
import * as Sentry from '@sentry/browser';
//import {Vue as VueIntegration} from '@sentry/integrations/dist/vue';
import {createApp} from 'vue';
import VueNativeSock from 'vue-native-websocket';
import Toast, {PluginOptions, POSITION} from 'vue-toastification';
import Clipboard from 'v-clipboard';
import {config, library} from '@fortawesome/fontawesome-svg-core';
import {faEye, faEyeSlash} from '@fortawesome/free-regular-svg-icons';

import store from './store';
import router from './router';
import i18n from './i18n';
import UrlBuilder from './helpers/urlBuilder';
import ThemeManager from './helpers/themeManager';

import App from './App.vue';

import './css/themes/generic.scss';
import './css/app.scss';
import 'vue-datetime/dist/vue-datetime.css';
import 'vue-select/dist/vue-select.css';
import 'vue-toast-notification/dist/theme-sugar.css';
import 'vue-toastification/dist/index.css';
import '@fortawesome/fontawesome-svg-core/styles.css';

//import Component from 'vue-class-component';
import * as version from '../version.json';
import {useI18n} from 'vue-i18n';
/*
// Register the router hooks with their names
// eslint-disable-next-line @typescript-eslint/ban-ts-comment
// @ts-ignore
Component.registerHooks([
	'beforeRouteEnter',
	'beforeRouteLeave',
	'beforeRouteUpdate'
]);*/

config.autoAddCss = false;
library.add(faEye);
library.add(faEyeSlash);

let release = version.version;
if (version.pipeline !== '') {
	release += '~' + version.pipeline;
}

const urlBuilder: UrlBuilder = new UrlBuilder();

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
					router.push({path: '/sign/in', query: {redirect: router.currentRoute.value.path}});
				});
			return;
		}
		return Promise.reject(error);
	}
);

const app = createApp({
	metaInfo: {
		titleTemplate: (titleChunk: string): string => {
			const i18n = useI18n();
			const title = i18n.t(ThemeManager.getTitleKey()).toString();
			return (titleChunk ? `${i18n.t(titleChunk).toString()} | ` : '') + title;
		}
	},
	...App,
});

app.config.globalProperties.$appName = 'IQRF Gateway Webapp';

app.use(store);
app.use(router);
app.use(i18n);

app.use(VueNativeSock, urlBuilder.getWsApiUrl(), {
	store: store,
	format: 'json',
	reconnection: true,
});

// eslint-disable-next-line @typescript-eslint/ban-ts-comment
// @ts-ignore
app.use(CoreuiVue);
const toastOptions: PluginOptions = {
	position: POSITION.TOP_CENTER,
	timeout: 5000,
};
app.use(Toast, toastOptions);
app.use(Clipboard);

app.mount('#app');

if (process.env.VUE_APP_CYPRESS_ENABLED === '1' && (window['Cypress'] ?? false)) {
	// eslint-disable-next-line @typescript-eslint/ban-ts-comment
	// @ts-ignore
	window['app'] = app;
}


if (process.env.NODE_ENV === 'production') {
	Sentry.init({
		dsn: 'https://435ee2b55f994e5f85e21a9ca93ea7a7@sentry.iqrf.org/5',
		integrations: [/*new VueIntegration({
			Vue: app,
			attachProps: true,
			logErrors: true,
		})*/],
		release: release,
	});
}
