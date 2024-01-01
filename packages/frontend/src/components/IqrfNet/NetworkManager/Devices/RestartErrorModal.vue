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
	<v-dialog
		v-model='showModal'
		width='50%'
		persistent
		no-click-animation
	>
		<v-card>
			<v-card-title>{{ $t('iqrfnet.networkManager.devicesInfo.restart.title') }}</v-card-title>
			<v-card-text>
				<div>
					{{ $t('iqrfnet.networkManager.devicesInfo.restart.prompt') }}
				</div>
				<v-chip
					v-for='(addr, i) of nodes'
					:key='i'
					class='mr-1'
					color='error'
					small
					label
				>
					<span style='font-size: 18px;'>
						{{ addr }}
					</span>
				</v-chip>
			</v-card-text>
			<v-card-actions>
				<v-spacer />
				<v-btn
					@click='hideModal'
				>
					{{ $t('forms.close') }}
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<script lang='ts'>
import {Component, VModel, Vue} from 'vue-property-decorator';

/**
 * IQMESH Network Restart error result modal window component
 */
@Component
export default class RestartErrorDialog extends Vue {

	/**
	 * @property {Array<number>} Nodes that failed to restart
	 */
	@VModel({required: true, type: Array, default: []}) nodes!: Array<number>;

	/**
	 * Computes modal display condition
	 */
	get showModal(): boolean {
		return this.nodes.length > 0;
	}

	/**
	 * Hides modal window
	 */
	private hideModal(): void {
		this.nodes = [];
	}
}
</script>
