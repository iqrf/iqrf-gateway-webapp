/**
 * Copyright 2023 MICRORISC s.r.o.
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

import type {Config} from 'jest';

const config: Config = {
	preset: 'ts-jest',
	verbose: true,
	testEnvironment: 'node',
	collectCoverage: true,
	collectCoverageFrom: ['src/**/*.{js,ts}', '!src/__tests__/**/*.{js,ts}'],
	testPathIgnorePatterns: [
		'<rootDir>/src/__tests__/mocks/.*.ts',
		'<rootDir>/dist/.*',
	],
};

export default config;
