module.exports = {
	env: {
		browser: true,
		commonjs: true,
		es6: true
	},
	extends: [
		'eslint:recommended',
		'plugin:@typescript-eslint/recommended',
		'plugin:vue/vue3-essential',
		'plugin:vuetify/recommended',
		'@vue/eslint-config-typescript',
	],
	plugins: [
		'@typescript-eslint',
	],
	rules: {
		'@typescript-eslint/ban-ts-comment': 'off',
		'@typescript-eslint/no-explicit-any': 'warn',
		'@typescript-eslint/no-unused-vars': 'warn',
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
		'quotes': [
			'error',
			'single'
		],
		'semi': [
			'error',
			'always'
		],
		'no-console': [
			'error',
			{
				'allow': [
					'warn',
					'error'
				]
			}
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
};
