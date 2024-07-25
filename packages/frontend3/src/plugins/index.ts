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

import { type App } from 'vue';

import registerDatetime from '@/plugins/datetime';
import head from '@/plugins/head';
import i18n from '@/plugins/i18n';
import registerSentry from '@/plugins/sentry';
import toastify, { ToastOptions } from '@/plugins/toastify';
import vuetify from '@/plugins/vuetify';
import registerSockets from '@/plugins/websocket';
import router from '@/router';
import pinia from '@/store';


/**
 * Register plugins
 * @param {App} app Vue.js app instance
 */
export function registerPlugins(app: App): void {
	app
		.use(pinia)
		.use(router)
		.use(i18n)
		.use(head)
		.use(toastify, ToastOptions)
		.use(vuetify);
	registerDatetime(app);
	registerSentry(app, router);
	registerSockets();
}
