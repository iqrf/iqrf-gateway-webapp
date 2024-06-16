/**
 * Copyright 2024 MICRORISC s.r.o.
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
		'eslint:recommended',
		'plugin:@typescript-eslint/recommended',
		'plugin:@typescript-eslint/stylistic-type-checked',
	],
	parser: '@typescript-eslint/parser',
	plugins: [
		'@iqrf',
		'@stylistic',
		'@typescript-eslint',
	],
	parserOptions: {
		sourceType: 'module',
	},
	env: {
		browser: true,
		node: true,
	},
	rules: {
		'@stylistic/comma-dangle': [
			'error',
			'always-multiline',
		],
		'@stylistic/eol-last': [
			'error',
			'always',
		],
		'@stylistic/no-extra-parens': [
			'error',
			'all',
			{
				'nestedBinaryExpressions': false,
				'ternaryOperandBinaryExpressions': false,
			},
		],
		'@stylistic/no-extra-semi': 'error',
		'@stylistic/no-floating-decimal': 'error',
		'@stylistic/no-mixed-operators': 'error',
		'@stylistic/no-multiple-empty-lines': 'error',
		'@stylistic/no-multi-spaces': 'error',
		'@stylistic/object-curly-spacing': [
			'error',
			'always',
		],
		'@stylistic/switch-colon-spacing': [
			'error',
			{
				'before': false,
				'after': true,
			},
		],
		'@stylistic/type-annotation-spacing': 'error',
		'@stylistic/type-generic-spacing': 'error',
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
		'linebreak-style': [
			'error',
			'unix'
		],
		'no-unused-vars': 'warn',
		'no-use-before-define': 'error',
		'prefer-const': 'error',
		'quotes': [
			'error',
			'single'
		],
		'semi': [
			'error',
			'always'
		],
	},
};
