/// <reference types='vite/client' />

import { type AppTheme } from '@/types/vuetify';

/**
 * Environment variables
 */
interface ImportMetaEnv {
	/// REST API base URL
	VITE_BASE_URL: string
	/// Default language
	VITE_I18N_LOCALE: string
	/// Fallback language
	VITE_I18N_FALLBACK_LOCALE: string
	/// Sentry enablement
	VITE_SENTRY_ENABLED: boolean
	/// Sentry DSN
	VITE_SENTRY_DSN: string
	/// IQRF Gateway Daemon JSON API URL
	VITE_URL_DAEMON_API: string
	/// IQRF Gateway Daemon Monitor URL
	VITE_URL_DAEMON_MONITOR: string
	/// IQRF network sync URL
	VITE_URL_IQRF_SYNC: string
	/// REST API URL
	VITE_URL_REST_API: string
	/// Theme
	VITE_THEME: AppTheme
}

interface ImportMeta {
	readonly env: ImportMetaEnv
}
