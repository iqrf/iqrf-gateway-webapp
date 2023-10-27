
import  {type App} from 'vue';

import registerDatetime from '@/plugins/datetime';
import head from '@/plugins/head';
import i18n from '@/plugins/i18n';
import registerSentry from '@/plugins/sentry';
import toastify, {ToastOptions} from '@/plugins/toastify';
import vuetify from '@/plugins/vuetify';
import registerSockets from '@/plugins/websocket';
import router from '@/router';
import pinia from '@/store';


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
