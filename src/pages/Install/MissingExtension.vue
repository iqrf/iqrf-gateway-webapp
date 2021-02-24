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
	private fixCommands = 'sudo apt-get update && sudo apt-get install php7.4-common php7.4-cli php7.4-curl php7.4-fpm php7.4-json php7.4-mbstring php7.4-sqlite3 php7.4-xml php7.4-zip'

	/**
	 * @property {string} extensionString String of missing extensions
	 */
	@Prop({required: true}) extensionString!: string

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
