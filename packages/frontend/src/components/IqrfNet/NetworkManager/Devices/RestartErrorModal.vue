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
	<CModal
		:show.sync='show'
		color='danger'
		size='lg'
		:close-on-backdrop='false'
		:fade='false'
	>
		<template #header>
			<h5 class='modal-title'>
				{{ $t('iqrfnet.networkManager.devicesInfo.restart.title') }}
			</h5>
		</template>
		{{ $t('iqrfnet.networkManager.devicesInfo.restart.prompt') }}<br>
		<CBadge
			v-for='(addr, idx) of nodes'
			:key='idx'
			class='mr-1'
			color='danger'
		>
			<span style='font-size: 0.9rem;'>
				{{ addr }}
			</span>
		</CBadge>
		<template #footer>
			<CButton
				color='secondary'
				@click='closeModal'
			>
				{{ $t('forms.close') }}
			</CButton>
		</template>
	</CModal>
</template>

<script lang='ts'>
import {Component} from 'vue-property-decorator';
import {CBadge, CButton, CModal} from '@coreui/vue/src';
import ModalBase from '@/components/ModalBase.vue';

/**
 * IQMESH Network Restart error result modal window component
 */
@Component({
	components: {
		CBadge,
		CButton,
		CModal,
	},
})
export default class RestartErrorDialog extends ModalBase {
	/**
	 * @var {Array<number>} nodes Nodes that failed to restart
	 */
	private nodes: Array<number> = [];

	/**
	 * Activates restart result modal
	 */
	public showModal(nodes): void {
		this.nodes = nodes;
		this.openModal();
	}
}
</script>
