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

//import 'admin-lte';
import 'autosize';
//import 'bootstrap';
import 'jquery';
import 'nette.ajax.js';
import 'ublaboo-datagrid';
import autosize from 'autosize';
import Nette from 'nette-forms';
import * as Sentry from '@sentry/browser';
import { Vue as VueIntegration } from '@sentry/integrations';
import hljs from 'highlight.js/lib/highlight';
import bash from 'highlight.js/lib/languages/bash';
import json from 'highlight.js/lib/languages/json';
import spinner from './spinner';
import axios from 'axios';
import CoreuiVue from '@coreui/vue';
import Vue from 'vue';
import VueToast from 'vue-toast-notification';
import VueNativeSock from 'vue-native-websocket';

import i18n from './i18n';
import store from './store';
import router from './router';
import AuthenticationService from './services/AuthenticationService';

import 'highlight.js/styles/github.css';
import '../css/app.scss';
import 'vue-toast-notification/dist/theme-default.css';


import App from './components/App';
import DaemonStatus from './components/DaemonStatus';
import DisambiguationLink from './components/DisambiguationLink';
import NavBarLink from './components/NavBarLink';
import TheHeader from './components/TheHeader';
import TheSidebar from './components/TheSidebar';
import PixlaControl from './components/Cloud/PixlaControl';

Sentry.init({
	dsn: 'https://435ee2b55f994e5f85e21a9ca93ea7a7@sentry.iqrf.org/5',
	integrations: [new VueIntegration({Vue: Vue, attachProps: true, logErrors: true})],
});

const wsApi = 'ws://' + window.location.hostname + ':1338';
Vue.use(VueNativeSock, wsApi, {
	store: store,
	format: 'json',
	reconnection: true,
});

Nette.initOnLoad();

$(function () {
	$.nette.init();
});

autosize(document.querySelectorAll('textarea'));

hljs.initHighlightingOnLoad();
hljs.registerLanguage('bash', bash);
hljs.registerLanguage('json', json);

$.nette.ext('spinner', {
	start: function () {
		spinner.showSpinner();
	},
	complete: function () {
		spinner.hideSpinner();
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
				spinner.hideSpinner();
			}
			return retVal;
		}
	}
});

$.nette.ext('highlighter', {
	complete: function () {
		document.querySelectorAll('pre code').forEach((block) => {
			hljs.highlightBlock(block);
		});
	}
});

axios.defaults.baseURL = '//' + window.location.host + '/api/v0/';

Vue.prototype.$appName = 'IQRF Gateway Webapp frontend';

Vue.use(CoreuiVue);
Vue.use(VueToast,{
	position: 'top-right'
});

new Vue({
	el: '#app',
	components: {
		App,
		DaemonStatus,
		DisambiguationLink,
		NavBarLink,
		TheHeader,
		TheSidebar,
		PixlaControl
	},
	router: router,
	store: store,
	i18n: i18n
});

if (localStorage.getItem('jwt') === null) {
	AuthenticationService.login('iqrf', 'iqrf');
}
