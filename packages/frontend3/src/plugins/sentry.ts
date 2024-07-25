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

import * as Sentry from '@sentry/vue';
import { browserTracingIntegration } from '@sentry/vue';
import { type App } from 'vue';
import { type Router } from 'vue-router';

/**
 * Registers Sentry error tracking
 * @param {App} app Vue app
 * @param {Router} router Vue router
 */
export default function registerSentry(app: App, router: Router): void {
	if (!import.meta.env.VITE_SENTRY_ENABLED) {
		return;
	}
	Sentry.init({
		app,
		dsn: import.meta.env.VITE_SENTRY_DSN,
		integrations: [
			browserTracingIntegration({
				router: router,
				routeLabel: 'path',
			}),
		],
		release: __GIT_COMMIT_HASH__,
		tracePropagationTargets: ['localhost', window.location.hostname, /^\//],
		tracesSampleRate: 1,
		trackComponents: false,
	});
}
