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

import { BrowserTracing } from '@sentry/tracing';
import * as Sentry from '@sentry/vue';
import { type App } from 'vue';
import { type Router } from 'vue-router';


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
