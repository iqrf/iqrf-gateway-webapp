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
	root: true,
	env: {
		browser: true,
		node: true,
	},
	extends: [
		'eslint:recommended',
		'plugin:@typescript-eslint/stylistic-type-checked',
		'plugin:import/recommended',
		'plugin:import/typescript',
		'plugin:promise/recommended',
		"plugin:typescript-sort-keys/recommended",
	],
	parser: '@typescript-eslint/parser',
	parserOptions: {
		project: './tsconfig.json',
		sourceType: 'module',
	},
	plugins: [
		'@typescript-eslint',
		'@stylistic',
	],
	rules: {
		'@stylistic/comma-dangle': [
			'error',
			'always-multiline',
		],
		'@stylistic/eol-last': [
			'error',
			'always',
		],
		'@stylistic/object-curly-spacing': [
			'error',
			'always',
		],
		'@typescript-eslint/ban-ts-comment': 'off',
		'@typescript-eslint/consistent-type-imports': [
			'error',
			{
				prefer: 'type-imports',
				fixStyle: 'inline-type-imports',
			},
		],
		'@typescript-eslint/explicit-member-accessibility': 'error',
		'@typescript-eslint/no-explicit-any': 'warn',
		'comma-dangle': [
			'error',
			'always-multiline',
		],
		'eqeqeq': [
			'error',
			'always',
		],
		'indent': [
			'error',
			'tab',
			{
				'SwitchCase': 1
			}
		],
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
		'linebreak-style': [
			'error',
			'unix'
		],
		'no-unused-vars': 'warn',
		'no-use-before-define': 'error',
		'quotes': [
			'error',
			'single'
		],
		'semi': [
			'error',
			'always'
		],
	},
	overrides: [
		{
			files: ['tests/**'],
			rules: {
				'promise/always-return': 'off',
			}
		},
	],
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
