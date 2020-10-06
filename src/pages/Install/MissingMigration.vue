<template>
	<CCard>
		<CCardHeader color='danger' class='title'>
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
import Vue from 'vue';
import {CCard, CCardBody, CCardHeader} from '@coreui/vue/src';

import {PrismEditor} from 'vue-prism-editor';
import 'vue-prism-editor/dist/prismeditor.min.css';
// @ts-ignore
import Prism from 'prismjs/components/prism-core';
import 'prismjs/components/prism-bash';
import 'prismjs/themes/prism.css';

export default Vue.extend({
	name: 'MissingMigration',
	components: {
		CCard,
		CCardBody,
		CCardHeader,
		PrismEditor,
	},
	data(): any {
		return {
			fixCommands: 'sudo iqrf-gateway-webapp-manager migrations:migrate --no-interaction',
		};
	},
	methods: {
		/**
		 * JSON highlighter method
		 */
		highlighter(code: string) {
			return Prism.highlight(code, Prism.languages.bash, 'bash');
		},
	},
	metaInfo: {
		title: 'install.error.missingMigration.title'
	},
});
</script>

<style scoped>
.title {
	color: white;
}
</style>
