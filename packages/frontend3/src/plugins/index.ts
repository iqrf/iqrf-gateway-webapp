import type {App} from 'vue';

import head from '@/plugins/head';
import i18n from '@/plugins/i18n';
import toastify, {ToastOptions} from '@/plugins/toastify';
import vuetify from '@/plugins/vuetify';
import pinia from '@/store';
import router from '@/router';
import registerDatetime from '@/plugins/datetime';
import registerSentry from '@/plugins/sentry';
import registerSockets from '@/plugins/websocket';

export function registerPlugins(app: App) {
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
