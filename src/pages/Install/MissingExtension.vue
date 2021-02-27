<template>
	<CCard>
		<CCardHeader color='danger' class='text-white'>
			{{ $t('install.error.missingExtension.title') }}
		</CCardHeader>
		<CCardBody>
			{{ $t('install.error.missingExtension.description', {extensions: extensionString}) }}
		</CCardBody>
		<CCardFooter
			v-if='debianBased === "true"'
		>
			<strong>{{ $t('install.error.howToFix') }}</strong>
			<br>
			{{ $t('install.error.missingExtension.fixDescription') }}
			<prism-editor
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
	 * @property {string} debianBased Debian based distribution
	 */
	@Prop({required: true}) debianBased!: string

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
