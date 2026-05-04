import js from '@eslint/js';
import tseslint from 'typescript-eslint';
import pluginVue from 'eslint-plugin-vue';
import vueParser from 'vue-eslint-parser';
import globals from 'globals';

export default [
	{
		ignores: [
			'.pnpm-store/',
			'coverage/',
			'dist/',
			'node_modules/',
			'public/',
			'www/',
			'vendor/',
			'tmp/',
			'temp/',
		],
	},
	js.configs.recommended,
	...tseslint.configs.recommended,
	...pluginVue.configs['flat/vue2-recommended'],
	{
		files: ['**/*.{js,ts,vue}'],
		languageOptions: {
			parser: vueParser,
			parserOptions: {
				parser: tseslint.parser,
				ecmaVersion: 2019,
				sourceType: 'module',
				extraFileExtensions: ['.vue'],
			},
			globals: {
				...globals.es2019,
				...globals.browser,
				...globals.node,
			},
		},
		rules: {
			'@typescript-eslint/no-explicit-any': 'warn',
			'@typescript-eslint/no-unused-vars': [
				'error',
				{
					argsIgnorePattern: '^_',
					varsIgnorePattern: '^_',
					caughtErrorsIgnorePattern: '^_',
				},
			],
			indent: [
				'error',
				'tab',
				{ SwitchCase: 1 },
			],
			'linebreak-style': [
				'error',
				'unix',
			],
			quotes: [
				'error',
				'single',
			],
			semi: [
				'error',
				'always',
			],
			'no-console': [
				'error',
				{ allow: ['warn', 'error'] },
			],
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
		},
	},
];
