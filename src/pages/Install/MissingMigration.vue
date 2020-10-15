<template>
	<CCard>
		<CCardHeader color='danger' class='text-white'>
			{{ $t('install.error.missingMigration.title') }}
		</CCardHeader>
		<CCardBody>
			{{ $t('install.error.missingMigration.description') }}
		</CCardBody>
		<CCardFooter>
			<strong>{{ $t('install.error.missingMigration.howToFix') }}</strong>
			<br>
			{{ $t('install.error.missingMigration.fixDescription') }}
			<prism-editor
				v-model='fixCommands'
				:highlight='highlighter'
				:readonly='true'
			/>
		</CCardFooter>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CCard, CCardBody, CCardHeader} from '@coreui/vue/src';

import {PrismEditor} from 'vue-prism-editor';
import 'vue-prism-editor/dist/prismeditor.min.css';
import Prism from 'prismjs/components/prism-core';
import 'prismjs/components/prism-bash';
import 'prismjs/themes/prism.css';

@Component({
	components: {
		CCard,
		CCardBody,
		CCardHeader,
		PrismEditor,
	},
	metaInfo: {
		title: 'install.error.missingMigration.title'
	},
})

/**
 * Missing migration notification
 */
export default class MissingMigration extends Vue {

	/**
	 * Commands to fix this issue
	 */
	private fixCommands = 'sudo iqrf-gateway-webapp-manager migrations:migrate --no-interaction';

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
