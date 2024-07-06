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

module.exports = {
	extends: [
		'plugin:@iqrf/base',
		'plugin:vue/vue3-recommended',
		'plugin:vuetify/recommended',
		'plugin:@intlify/vue-i18n/recommended',
		'plugin:import/recommended',
		'plugin:import/typescript',
		//		'plugin:promise/recommended',
		//		"plugin:typescript-sort-keys/recommended",
		'@vue/eslint-config-typescript',
	],
	parser: 'vue-eslint-parser',
	parserOptions: {
		parser: '@typescript-eslint/parser',
		ecmaVersion: 'latest',
		sourceType: 'module',
		tsconfigRootDir: __dirname,
		project: ['./tsconfig.json'],
		extraFileExtensions: ['.vue'],
	},
	rules: {
		'@typescript-eslint/explicit-member-accessibility': 'off',
		'@typescript-eslint/no-floating-promises': 'warn',
		'@typescript-eslint/no-misused-promises': 'warn',
		'@typescript-eslint/no-unsafe-argument': 'warn',
		'@typescript-eslint/no-unsafe-call': 'warn',
		'@typescript-eslint/no-unsafe-member-access': 'warn',
		'@typescript-eslint/no-unsafe-return': 'warn',
		'@typescript-eslint/require-await': 'warn',
		'import/consistent-type-specifier-style': [
			'error',
			'prefer-inline',
		],
		'import/no-unresolved': [
			'error',
			{
				ignore: ['^virtual:'],
			},
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
			'unix',
		],
		'no-use-before-define': 'off',
		//		'promise/always-return': 'warn',
		'regexp/no-unused-capturing-group': 'warn',
		'vue/html-indent': [
			'warn',
			'tab',
		],
		'vue/html-quotes': [
			'warn',
			'single',
		],
		'vue/max-attributes-per-line': [
			'warn',
			{ singleline: 3 },
		],
		'vue/multi-word-component-names': 'off',
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
		'vue-i18n': {
			localeDir: './src/locales/*.json',
			messageSyntaxVersion: '^9.0.0',
		},
	},
};
