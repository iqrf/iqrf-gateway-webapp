/// <reference types='vite/client' />

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
}

interface ImportMeta {
	readonly env: ImportMetaEnv
}
