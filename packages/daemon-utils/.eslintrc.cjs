/**
 * Copyright 2023-2024 MICRORISC s.r.o.
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

module.exports = {
	env: {
		browser: true,
		node: true,
	},
	extends: [
		'plugin:@iqrf/base',
		'plugin:import/recommended',
		'plugin:import/typescript',
		'plugin:promise/recommended',
		'plugin:typescript-sort-keys/recommended',
	],
	parserOptions: {
		project: './tsconfig.json',
	},
	rules: {
		'@typescript-eslint/no-duplicate-enum-values': 'warn',
		'import/consistent-type-specifier-style': [
			'error',
			'prefer-inline',
		],
		'import/order': [
			'error',
			{
				'alphabetize': {
					'order': 'asc',
					'caseInsensitive': true,
				},
				'pathGroups': [
					{
						'pattern': '@/**',
						'group': 'internal',
					},
				],
				'groups': ['builtin', 'external', 'internal', 'parent', 'sibling', 'index', 'object', 'type'],
				'newlines-between': 'always',
			},
		],
	},
	settings: {
		'import/parsers': {
			'@typescript-eslint/parser': ['.ts', '.tsx'],
		},
		'import/resolver': {
			'node': true,
			'typescript': {
				'alwaysTryTypes': true,
				'project': './tsconfig.json',
			},
		},
	},
};
