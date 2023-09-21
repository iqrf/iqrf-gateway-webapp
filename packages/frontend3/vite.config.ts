/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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
import VueI18nPlugin from '@intlify/unplugin-vue-i18n/vite';
import UnheadVite from '@unhead/addons/vite';
import vue from '@vitejs/plugin-vue';
import * as proc from 'child_process';
import path from 'path';
import {fileURLToPath, URL} from 'node:url';
import {defineConfig, loadEnv} from 'vite';
import {ViteEjsPlugin} from 'vite-plugin-ejs';
import vuetify, { transformAssetUrls } from 'vite-plugin-vuetify';
import svgLoader from 'vite-svg-loader';
import VueDevTools from 'vite-plugin-vue-devtools';
import Pages from 'vite-plugin-pages';
import Layouts from 'vite-plugin-vue-layouts';

const gitCommitHash = proc.execSync('git rev-parse --short HEAD').toString().trim();

export default defineConfig(({mode}) => {
	const env: Record<string, string> = loadEnv(mode, process.cwd(), '');
	return {
		base: env.VITE_BASE_URL,
		build: {
			outDir: path.resolve(__dirname, './dist'),
		},
		plugins: [
			Pages(),
			Layouts({
				layoutsDirs: 'src/layouts',
				defaultLayout: 'DefaultLayout',
			}),
			VueDevTools(),
			vue({
				template: { transformAssetUrls },
			}),
			vuetify({
				autoImport: true,
				styles: {
					configFile: 'src/styles/vuetify-settings.scss',
				}
			}),
			ViteEjsPlugin({
				theme: env.VITE_THEME,
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
		resolve: {
			alias: {
				'@': fileURLToPath(new URL('./src', import.meta.url)),
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
	};
});
