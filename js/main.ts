/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2019 IQRF Tech s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
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
import {Vue as VueIntegration} from '@sentry/integrations/dist/vue';
import Vue from 'vue';
import VueMeta from 'vue-meta';
// @ts-ignore
import VueNativeSock from 'vue-native-websocket';
import VueToast from 'vue-toast-notification';

import store from './store';
import router from './router';
import i18n from './i18n';

import '../css/app.scss';
import 'vue-toast-notification/dist/theme-sugar.css';

import App from './components/App.vue';
import TheDashboard from './components/TheDashboard.vue';
import TrConfiguration from './components/IqrfNet/TrConfiguration.vue';
import MainDisambiguation from './components/MainDisambiguation.vue';
import SchedulerList from './pages/Config/SchedulerList.vue';
import SchedulerForm from './pages/Config/SchedulerForm.vue';
//import MonitorList from './pages/Config/MonitorList.vue';

Sentry.init({
	dsn: 'https://435ee2b55f994e5f85e21a9ca93ea7a7@sentry.iqrf.org/5',
	integrations: [new VueIntegration({Vue: Vue, attachProps: true, logErrors: true})],
});

store.commit('SOCKET_ONCLOSE');
store.commit('spinner/HIDE');

Vue.prototype.$appName = 'IQRF Gateway Webapp frontend';

const wsApi: string = 'ws://' + window.location.hostname + ':1338';
//const wsApi: string ='ws://tunnel.rehivetech.com:45117/ws';
Vue.use(VueNativeSock, wsApi, {
	store: store,
	format: 'json',
	reconnection: true,
});
Vue.use(CoreuiVue);
Vue.use(VueMeta);
Vue.use(VueToast,{
	position: 'top',
	duration: 10000
});

axios.defaults.baseURL = '//' + window.location.host + '/api/v0/';
axios.defaults.timeout = 30000; 

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
					location.replace('/sign/in');
				});
		}
		return Promise.reject(error);
	}
);

new Vue({
	el: '#app',
	components: {
		App,
		TheDashboard,
		TrConfiguration,
		MainDisambiguation,
		SchedulerList,
		SchedulerForm,
		//MonitorList,
	},
	router: router,
	store: store,
	i18n: i18n,
	metaInfo: {
		titleTemplate: (titleChunk: string): string => {
			return (titleChunk ? `${i18n.t(titleChunk).toString()} | ` : '') +
				i18n.t('core.title').toString();
		}
	},
});

