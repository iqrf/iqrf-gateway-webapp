<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

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
	<CCard>
		<CCardHeader color='danger' class='text-white'>
			{{ $t('install.error.missingExtension.title') }}
		</CCardHeader>
		<CCardBody>
			{{ $t('install.error.missingExtension.description', {extensions: extensionString}) }}
		</CCardBody>
		<CCardFooter>
			<strong>{{ $t('install.error.howToFix') }}</strong>
			<br>
			{{ $t('install.error.missingExtension.fixDescription' + (packageString !== '' ? 'Debian': '')) }}
			<prism-editor
				v-if='packageString !== ""'
				v-model='fixCommands'
				:highlight='highlighter'
				:readonly='true'
			/>
		</CCardFooter>
	</CCard>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {CCard, CCardBody, CCardFooter, CCardHeader} from '@coreui/vue/src';

import {PrismEditor} from 'vue-prism-editor';
import 'vue-prism-editor/dist/prismeditor.min.css';
import Prism from 'prismjs/components/prism-core';
import 'prismjs/components/prism-bash';
import 'prismjs/themes/prism.css';

@Component({
	components: {
		CCard,
		CCardBody,
		CCardFooter,
		CCardHeader,
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
	private fixCommands = 'sudo apt-get update\nsudo apt-get install '

	/**
	 * @property {string} extensionString String of missing extensions
	 */
	@Prop({required: true}) extensionString!: string

	/**
	 * @property {string} packageString String of packages to install
	 */
	@Prop({required: true}) packageString!: string

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
