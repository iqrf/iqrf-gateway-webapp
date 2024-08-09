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
import vue from '@vitejs/plugin-vue2';
import * as proc from 'child_process';
import path from 'path';
import Components from 'unplugin-vue-components/vite';
import {VuetifyResolver} from 'unplugin-vue-components/resolvers';
import {ConfigEnv, defineConfig, loadEnv} from 'vite';
import svgLoader from 'vite-svg-loader';
import {configDefaults, UserConfig} from 'vitest/config';
import tsconfigPaths from 'vite-tsconfig-paths';

const gitCommitHash = proc.execSync('git rev-parse --short HEAD').toString().trim();

export default defineConfig(({mode}: ConfigEnv): UserConfig => {
	const env: Record<string, string> = loadEnv(mode, process.cwd(), '');
	return {
		base: env.VITE_BASE_URL,
		build: {
			outDir: path.resolve(__dirname, './dist'),
		},
		css: {
			preprocessorOptions: {
				sass: {
					additionalData: [
						'@import "@/styles/variables.scss"',
						'@import "vuetify/src/styles/settings/_variables"',
						'',
					].join('\n'),
				},
			},
		},
		plugins: [
			tsconfigPaths(),
			vue(),
			svgLoader({defaultImport: 'url'}),
			Components({resolvers: [VuetifyResolver()]}),
		],
		define: {
			__GIT_COMMIT_HASH__: JSON.stringify(gitCommitHash),
		},
		resolve: {
			alias: {
				'@iqrf/iqrf-gateway-webapp-client': path.resolve(__dirname, '../api-client/src'),
				'@iqrf/iqrf-gateway-daemon-utils': path.resolve(__dirname, '../daemon-utils/src'),
				'@': path.resolve(__dirname, './src'),
			},
		},
		test: {
			...configDefaults,
			coverage: {
				provider: 'istanbul',
				reporter: ['text', 'html', 'clover'],
			},
			outputFile: {
				junit: 'junit.xml',
			},
			reporters: ['default', 'junit'],
		},
	};
});
