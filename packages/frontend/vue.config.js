/* eslint-disable @typescript-eslint/no-require-imports */
/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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
const webpack = require('webpack');
const proc = require('child_process');

const baseUrl = process.env.VUE_APP_BASE_URL;
const gitCommitHash = proc.execSync('git rev-parse --short HEAD').toString().trim();

module.exports = {
	publicPath: baseUrl,
	runtimeCompiler: true,
	outputDir: 'www/dist',
	chainWebpack: config => {
		config.module.rules.delete('svg');
	},
	configureWebpack: {
		plugins: [
			new webpack.DefinePlugin({
				__GIT_COMMIT_HASH__: JSON.stringify(gitCommitHash),
			}),
		],
		module: {
			rules: [
				{
					test: /\.svg$/,
					loader: 'vue-svg-loader',
				},
			],
		}
	}
};
