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
	<v-card>
		<v-card-title>
			{{ $t('maintenance.mender.update.log.title') }}
		</v-card-title>
		<v-card-text>
			<v-alert
				v-if='log.length === 0'
				type='info'
				text
			>
				{{ $t('maintenance.mender.update.log.noLog') }}
			</v-alert>
			<pre class='log card-margin-bottom'>{{ log }}</pre>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';

@Component({})

/**
 * Mender execution log card
 */
export default class MenderUpdateLog extends Vue {

	/**
	 * @property {string} log Log
	 */
	@Prop({required: true, default: null}) log!: string;
}
</script>

<style scoped>
pre {
	overflow-x: auto;
	white-space: pre-wrap;
	word-wrap: break-word;
}
</style>
