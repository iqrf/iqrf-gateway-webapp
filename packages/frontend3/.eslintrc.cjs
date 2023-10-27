module.exports = {
	env: {
		browser: true,
		node: true,
	},
	extends: [
		'eslint:recommended',
		'plugin:@typescript-eslint/stylistic-type-checked',
		'plugin:vue/vue3-recommended',
		'plugin:vuetify/recommended',
		'plugin:import/recommended',
		'plugin:import/typescript',
//		'plugin:promise/recommended',
//		"plugin:typescript-sort-keys/recommended",
		'@vue/eslint-config-typescript',
	],
	plugins: [
		'@typescript-eslint',
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
		'@typescript-eslint/ban-ts-comment': 'off',
		'@typescript-eslint/consistent-type-imports': [
			'error',
			{
				prefer: 'type-imports',
				fixStyle: 'inline-type-imports',
			},
		],
//		'@typescript-eslint/explicit-member-accessibility': 'error',
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
				'SwitchCase': 1,
			}
		],
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
			'unix'
		],
//		'no-use-before-define': 'error',
		'prefer-const': 'error',
//		'promise/always-return': 'warn',
		'quotes': [
			'error',
			'single'
		],
		'semi': [
			'error',
			'always'
		],
		'vue/html-indent': [
			'warn',
			'tab'
		],
		'vue/html-quotes': [
			'warn',
			'single'
		],
		'vue/max-attributes-per-line': [
			'warn',
			{singleline: 3}
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
	},
};
