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
import path from 'path';
import Components from 'unplugin-vue-components/vite';
import {VuetifyResolver} from 'unplugin-vue-components/resolvers';
import {defineConfig, loadEnv} from 'vite';
import {ViteEjsPlugin} from 'vite-plugin-ejs';
import {createVuePlugin} from 'vite-plugin-vue2';
import svgLoader from 'vite-svg-loader';

export default defineConfig(({command, mode}) => {
	const env = loadEnv(mode, process.cwd(), '');
	const theme = env.VITE_THEME || 'generic';
	return {
		base: env.VITE_BASE_URL,
		build: {
			outDir: path.resolve(__dirname, './www/dist'),
		},
		css: {
			preprocessorOptions: {
				sass: {
					additionalData: [
						'@import "@/styles/themes/' + theme + '.scss"',
						'@import "@/styles/variables.scss"',
						'@import "vuetify/src/styles/settings/_variables"',
						'',
					].join('\n'),
				},
			},
		},
		plugins: [
			ViteEjsPlugin({
				theme: env.VITE_THEME,
			}),
			createVuePlugin(),
			svgLoader({defaultImport: 'url'}),
			Components({resolvers: [VuetifyResolver()]}),
		],
		resolve: {
			alias: {
				'@': path.resolve(__dirname, './src'),
			}
		},
	};
});
