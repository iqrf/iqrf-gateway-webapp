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

import { type ConfigEnv, type UserConfig } from 'vite';
import { defineConfig, mergeConfig } from 'vitest/config';

import viteConfig from './vite.config';

// https://vitest.dev/config/
export default defineConfig((env: ConfigEnv): UserConfig => mergeConfig(
	viteConfig(env),
	{
		test: {
			coverage: {
				provider: 'istanbul',
				reporter: ['text', 'html', 'clover'],
				include: [
					'src/',
				],
				exclude: [
					'src/tests/',
				],
			},
			environment: 'happy-dom',
			globals: true,
			include: [
				'src/tests/unit/**/*.{test,spec}.ts',
			],
			outputFile: {
				junit: 'junit.xml',
			},
			reporters: ['verbose', 'junit'],
			server: {
				deps: {
					inline: ['vuetify'],
				},
			},
		},
	},
));
