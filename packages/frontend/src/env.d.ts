/// <reference types="vite/client" />

interface ImportMetaEnv {
	readonly VITE_BASE_URL: string
	readonly VITE_CYPRESS_ENABLED: string
	readonly VITE_THEME: string
	// more env variables...
}

interface ImportMeta {
	readonly env: ImportMetaEnv
}
