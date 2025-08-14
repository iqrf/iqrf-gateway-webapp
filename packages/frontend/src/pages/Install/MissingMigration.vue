<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

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
			{{ $t('install.error.missingMigration.title') }}
		</CCardHeader>
		<CCardBody>
			{{ $t('install.error.missingMigration.description') }}
		</CCardBody>
		<CCardFooter>
			<strong>{{ $t('install.error.howToFix') }}</strong>
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
