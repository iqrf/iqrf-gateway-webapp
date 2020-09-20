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

'use strict';

import 'jquery';
import 'nette.ajax.js';
import 'ublaboo-datagrid';
import Nette from 'nette-forms';
import * as Sentry from '@sentry/browser';
import { Vue as VueIntegration } from '@sentry/integrations';
import axios from 'axios';
import '@coreui/coreui';
import CoreuiVue from '@coreui/vue';
import Vue from 'vue';
import VueMeta from 'vue-meta';
import VueToast from 'vue-toast-notification';
import VueNativeSock from 'vue-native-websocket';

import i18n from './i18n.ts';
import store from './store';
import router from './router';

import '../css/app.scss';
import 'vue-toast-notification/dist/theme-sugar.css';

import './iqrfNet/sendPacket';

import App from './components/App';
import DaemonStatus from './components/DaemonStatus';
import LoadingSpinner from './components/LoadingSpinner';
import TheHeader from './components/TheHeader';
import TheSidebar from './components/TheSidebar';
import PixlaControl from './components/Cloud/PixlaControl';
import TrConfiguration from './components/IqrfNet/TrConfiguration';
import AWSCreator from './components/Cloud/AWSCreator';

Sentry.init({
	dsn: 'https://435ee2b55f994e5f85e21a9ca93ea7a7@sentry.iqrf.org/5',
	integrations: [new VueIntegration({Vue: Vue, attachProps: true, logErrors: true})],
});

store.commit('SOCKET_ONCLOSE');
store.commit('spinner/HIDE');

const wsApi = 'ws://' + window.location.hostname + ':1338';
//const wsApi ='ws://tunnel.rehivetech.com:45117/ws';
Vue.use(VueNativeSock, wsApi, {
	store: store,
	format: 'json',
	reconnection: true,
});

Nette.initOnLoad();

$(function () {
	$.nette.init();
});

$.nette.ext('spinner', {
	start: function () {
		store.commit('spinner/SHOW');
	},
	complete: function () {
		store.commit('spinner/HIDE');
	}
});

$.nette.ext('confirm', {
	before: function (xhr, settings) {
		if (!settings.nette) {
			return;
		}

		let question = settings.nette.el.data('confirm');
		if (question) {
			let retVal = confirm(question);
			if (retVal) {
				store.commit('spinner/HIDE');
			}
			return retVal;
		}
	}
});

axios.defaults.baseURL = '//' + window.location.host + '/api/v0/';

axios.interceptors.response.use(
	(response) => {
		return response;
	},
	(error) => {
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

Vue.prototype.$appName = 'IQRF Gateway Webapp frontend';

Vue.use(CoreuiVue);
Vue.use(VueMeta);
Vue.use(VueToast,{
	position: 'top',
	duration: 10000
});

new Vue({
	el: '#app',
	components: {
		App,
		DaemonStatus,
		LoadingSpinner,
		TheHeader,
		TheSidebar,
		PixlaControl,
		TrConfiguration,
		AWSCreator
	},
	router: router,
	store: store,
	i18n: i18n,
	metaInfo: {
		titleTemplate: (titleChunk) => {
			return (titleChunk ? `${i18n.t(titleChunk)} | ` : '') + i18n.t('core.title');
		}
	},
});
