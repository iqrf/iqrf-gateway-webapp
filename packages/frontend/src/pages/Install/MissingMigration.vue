<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

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
			{{ $t('install.error.missingMigration.title') }}
		</v-card-title>
		<v-card-text>
			{{ $t('install.error.missingMigration.description') }}
			<v-divider class='my-2' />
			<strong>{{ $t('install.error.howToFix') }}</strong>
			<br>
			{{ $t('install.error.missingMigration.fixDescription') }}
			<prism-editor
				v-model='fixCommands'
				:highlight='highlighter'
				:readonly='true'
			/>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';

import {PrismEditor} from 'vue-prism-editor';
import 'vue-prism-editor/dist/prismeditor.min.css';
import Prism from 'prismjs/components/prism-core';
import 'prismjs/components/prism-bash';
import 'prismjs/themes/prism.css';

@Component({
	components: {
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
