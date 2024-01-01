<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software,
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied
See the License for the specific language governing permissions and
limitations under the License.
-->
<template>
	<v-card>
		<v-card-title
			color='error'
			class='text-white'
		>
			{{ $t('install.error.missingExtension.title') }}
		</v-card-title>
		<v-card-text>
			{{ $t('install.error.missingExtension.description', {extensions: extensionString}) }}
			<v-divider class='my-2' />
			<strong>{{ $t('install.error.howToFix') }}</strong>
			<br>
			<span v-if='packageString !== ""'>
				{{ $t('install.error.missingExtension.fixDescriptionDebian') }}
			</span>
			<span v-else>
				{{ $t('install.error.missingExtension.fixDescription') }}
			</span>
			<prism-editor
				v-if='packageString !== ""'
				v-model='fixCommands'
				:highlight='highlighter'
				:readonly='true'
			/>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';

import {PrismEditor} from 'vue-prism-editor';
import 'vue-prism-editor/dist/prismeditor.min.css';
import Prism from 'prismjs/components/prism-core';
import 'prismjs/components/prism-bash';
import 'prismjs/themes/prism.css';

@Component({
	components: {
		PrismEditor
	},
	metaInfo: {
		title: 'install.error.missingExtension.title'
	}
})

/**
 * Missing extension notification component
 */
export default class MissingExtension extends Vue {

	/**
	 * Commands to fix this issue
	 */
	private fixCommands = 'sudo apt-get update\nsudo apt-get install ';

	/**
	 * @property {string} extensionString String of missing extensions
	 */
	@Prop({required: true}) extensionString!: string;

	/**
	 * @property {string} packageString String of packages to install
	 */
	@Prop({required: true}) packageString!: string;

	/**
	 * Updates the fix command with packages
	 */
	mounted(): void {
		this.fixCommands += this.packageString;
	}

	/**
	 * JSON highlighter method
	 * @param code Code to highlight
	 * @return Highlighted code
	 */
	private highlighter(code: string): string {
		return Prism.highlight(code, Prism.languages.bash, 'bash');
	}

}
</script>
