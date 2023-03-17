import path from 'path';
import Components from 'unplugin-vue-components/vite';
import {VuetifyResolver} from 'unplugin-vue-components/resolvers';
import {defineConfig, loadEnv} from 'vite';
import {ViteEjsPlugin} from 'vite-plugin-ejs';
import {createVuePlugin} from 'vite-plugin-vue2';
import svgLoader from 'vite-svg-loader';

export default defineConfig(({command, mode}) => {
	const env = loadEnv(mode, process.cwd(), '');
	const theme = env.VITE_THEME || 'generic';
	return {
		base: env.VITE_BASE_URL,
		build: {
			outDir: path.resolve(__dirname, './www/dist'),
		},
		css: {
			preprocessorOptions: {
				sass: {
					additionalData: [
						'@import "@/styles/themes/' + theme + '.scss"',
						'@import "@/styles/variables.scss"',
						'@import "vuetify/src/styles/settings/_variables"',
						'',
					].join('\n'),
				},
			},
		},
		plugins: [
			ViteEjsPlugin({
				theme: env.VITE_THEME,
			}),
			createVuePlugin(),
			svgLoader({defaultImport: 'url'}),
			Components({resolvers: [VuetifyResolver()]}),
		],
		resolve: {
			alias: {
				'@': path.resolve(__dirname, './src'),
			}
		},
	};
});
