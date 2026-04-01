import js from '@eslint/js';
import tseslint from 'typescript-eslint';
import pluginVue from 'eslint-plugin-vue';
import vueParser from 'vue-eslint-parser';

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
				// Browser
				window: 'readonly',
				document: 'readonly',
				navigator: 'readonly',
				// Node/CommonJS
				module: 'readonly',
				require: 'readonly',
				process: 'readonly',
				// Jest
				describe: 'readonly',
				it: 'readonly',
				test: 'readonly',
				expect: 'readonly',
				beforeEach: 'readonly',
				afterEach: 'readonly',
			},
		},
		rules: {
			'@typescript-eslint/no-explicit-any': 'warn',
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

