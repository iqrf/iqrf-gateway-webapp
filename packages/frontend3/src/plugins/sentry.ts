import {BrowserTracing} from '@sentry/tracing';
import * as Sentry from '@sentry/vue';
import {type App} from 'vue';
import {type Router} from 'vue-router';


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
