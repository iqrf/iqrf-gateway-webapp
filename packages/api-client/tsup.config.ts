/**
 * Copyright 2023-2025 MICRORISC s.r.o.
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

import { defineConfig } from 'tsup';

export default defineConfig({
	clean: true,
	dts: true,
	entry: {
		'index': 'src/index.ts',
		'services/index': 'src/services/index.ts',
		'services/Cloud': 'src/services/Cloud/index.ts',
		'services/Config': 'src/services/Config/index.ts',
		'services/Gateway': 'src/services/Gateway/index.ts',
		'services/Iqrf': 'src/services/Iqrf/index.ts',
		'services/Maintenance': 'src/services/Maintenance/index.ts',
		'services/Network': 'src/services/Network/index.ts',
		'services/Security': 'src/services/Security/index.ts',
		'types/index': 'src/types/index.ts',
		'types/Cloud': 'src/types/Cloud/index.ts',
		'types/Config': 'src/types/Config/index.ts',
		'types/Gateway': 'src/types/Gateway/index.ts',
		'types/Iqrf': 'src/types/Iqrf/index.ts',
		'types/Maintenance': 'src/types/Maintenance/index.ts',
		'types/Network': 'src/types/Network/index.ts',
		'types/Security': 'src/types/Security/index.ts',
		'utils/index': 'src/utils/index.ts',
	},
	format: ['esm', 'cjs'],
	outDir: 'dist',
	sourcemap: true,
	splitting: false,
	bundle: true,
	skipNodeModulesBundle: true,
	keepNames: true,
	target: 'es2022',
	tsconfig: 'tsconfig.build.json',
});
