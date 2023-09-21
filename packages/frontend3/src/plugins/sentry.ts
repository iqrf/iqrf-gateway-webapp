import {App} from 'vue';
import {Router} from 'vue-router';
import * as Sentry from '@sentry/vue';
import {BrowserTracing} from '@sentry/tracing';


export default function registerSentry(app: App, router: Router) {
	if (!import.meta.env.VITE_SENTRY_ENABLED) {
		return;
	}
	Sentry.init({
		app,
		dsn: import.meta.env.VITE_SENTRY_DSN,
		integrations: [
			new BrowserTracing({
				routingInstrumentation: Sentry.vueRouterInstrumentation(router),
				tracePropagationTargets: ['localhost', window.location.hostname, /^\//],
			}),
		],
		release: __GIT_COMMIT_HASH__,
		tracesSampleRate: 1.0,
	});
}
