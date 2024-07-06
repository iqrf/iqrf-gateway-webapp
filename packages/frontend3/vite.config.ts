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

import proc from 'node:child_process';
import path from 'node:path';
import { fileURLToPath, URL } from 'node:url';

import VueI18nPlugin from '@intlify/unplugin-vue-i18n/vite';
import UnheadVite from '@unhead/addons/vite';
import vue from '@vitejs/plugin-vue';
import { type ConfigEnv, defineConfig, loadEnv, type UserConfig } from 'vite';
import Pages from 'vite-plugin-pages';
import VueDevTools from 'vite-plugin-vue-devtools';
import Layouts from 'vite-plugin-vue-layouts';
import vuetify, { transformAssetUrls } from 'vite-plugin-vuetify';
import svgLoader from 'vite-svg-loader';
import tsconfigPaths from 'vite-tsconfig-paths';

const gitCommitHash = proc.execSync('git rev-parse --short HEAD').toString().trim();

export default defineConfig(({ mode }: ConfigEnv): UserConfig => {
	const environment = loadEnv(mode, process.cwd(), '') as ImportMetaEnv & Record<string, string>;
	return {
		base: environment.VITE_BASE_URL,
		build: {
			outDir: path.resolve(__dirname, './dist'),
		},
		plugins: [
			tsconfigPaths(),
			Pages(),
			Layouts({
				layoutsDirs: 'src/layouts',
				defaultLayout: 'DefaultLayout',
			}),
			VueDevTools({
				componentInspector: true,
				launchEditor: environment.VITE_EDITOR,
			}),
			vue({
				template: { transformAssetUrls },
			}),
			vuetify({
				autoImport: true,
				styles: {
					configFile: 'src/styles/vuetify-settings.scss',
				},
			}),
			UnheadVite(),
			VueI18nPlugin({
				include: [path.resolve(__dirname, './src/locales/**')],
				escapeHtml: true,
				strictMessage: false,
			}),
			svgLoader(),
		],
		define: {
			__GIT_COMMIT_HASH__: JSON.stringify(gitCommitHash),
		},
		optimizeDeps: {
			entries: [
				'./src/components/**/*.{js,ts,vue}',
				'./src/layouts/**/*.{js,ts,vue}',
				'./src/pages/**/*.{js,ts,vue}',
				'./src/App.vue',
				'./src/main.ts',
			],
			exclude: ['vuetify'],
		},
		resolve: {
			alias: {
				'@': fileURLToPath(new URL('src', import.meta.url)),
			},
			extensions: [
				'.js',
				'.json',
				'.jsx',
				'.mjs',
				'.ts',
				'.tsx',
				'.vue',
			],
		},
		test: {
			coverage: {
				provider: 'istanbul',
				reporter: ['text', 'html', 'clover'],
			},
			environment: 'happy-dom',
			outputFile: {
				junit: 'junit.xml',
			},
			reporters: ['verbose', 'junit'],
		},
	};
});
