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

/// <reference types="vite/client" />

/**
 * Environment variables
 */
interface ImportMetaEnv {
	/// REST API base URL
	VITE_BASE_URL: string
	readonly VITE_CYPRESS_ENABLED: string
	/// Sentry enablement
	VITE_SENTRY_ENABLED: boolean
	/// Sentry DSN
	VITE_SENTRY_DSN: string
	/// IQRF Gateway Daemon JSON API URL
	VITE_URL_DAEMON_API: string
	/// IQRF Gateway Daemon Monitor URL
	VITE_URL_DAEMON_MONITOR: string
	/// REST API URL
	VITE_URL_REST_API: string
}

interface ImportMeta {
	readonly env: ImportMetaEnv
}
