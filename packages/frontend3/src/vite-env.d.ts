/// <reference types="vite/client" />
/// <reference types="vite-plugin-vue-layouts/client" />
/// <reference types="vite-plugin-pages/client" />

declare module '*.vue' {
	import {type DefineComponent} from 'vue';
	// eslint-disable-next-line @typescript-eslint/ban-types,@typescript-eslint/no-explicit-any
	const component: DefineComponent<{}, {}, any>;
	export default component;
}

/// Git commit hash
declare const __GIT_COMMIT_HASH__: string;
