/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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
import Vue from 'vue';

import * as version from '@/../version.json';
import router from '@/router';

let release = version.version;
if (version.pipeline !== '') {
	release += '~' + version.pipeline;
}

if (process.env.NODE_ENV === 'production') {
	Sentry.init({
		dsn: 'https://435ee2b55f994e5f85e21a9ca93ea7a7@sentry.iqrf.org/5',
		integrations: [
			Sentry.browserTracingIntegration({
				router: router,
				routeLabel: 'path',
			}),
		],
		release: release,
		tracePropagationTargets: ['localhost', window.location.hostname, /^\//],
		tracesSampleRate: 1.0,
		Vue,
	});
}
