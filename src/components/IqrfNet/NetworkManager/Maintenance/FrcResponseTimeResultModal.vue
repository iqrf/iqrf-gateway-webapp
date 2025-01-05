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
		color='primary'
		size='lg'
		:show.sync='show'
		:close-on-backdrop='false'
		:fade='false'
	>
		<template #header>
			<h5 class='modal-title'>
				{{ $t('iqrfnet.networkManager.maintenance.frcResponseTime.modal.title') }}
			</h5>
		</template>
		<table class='modal-table'>
			<tr>
				<th>{{ $t('iqrfnet.networkManager.maintenance.frcResponseTime.modal.command') }}</th>
				<td>{{ $t('iqrfnet.networkManager.maintenance.frcResponseTime.commands.' + commandLabel) }}</td>
			</tr>
			<tr>
				<th>{{ $t('iqrfnet.networkManager.maintenance.frcResponseTime.modal.inaccessibleNodes') }}</th>
				<td>{{ result.inaccessibleNodes }}</td>
			</tr>
			<tr>
				<th>{{ $t('iqrfnet.networkManager.maintenance.frcResponseTime.modal.unhandledNodes') }}</th>
				<td>{{ result.unhandledNodes }}</td>
			</tr>
			<tr>
				<th>{{ $t('iqrfnet.networkManager.maintenance.frcResponseTime.modal.current') }}</th>
				<td>{{ result.currentResponseTime }}</td>
			</tr>
			<tr>
				<th>{{ $t('iqrfnet.networkManager.maintenance.frcResponseTime.modal.recommended') }}</th>
				<td>{{ result.recommendedResponseTime }}</td>
			</tr>
		</table><hr>
		<CDataTable
			v-if='result !== null'
			:fields='fields'
			:items='result.nodes'
			:column-filter='true'
			:items-per-page='5'
			:pagination='true'
			:striped='true'
			:sorter='{external: false, resetable: true}'
		>
			<template #responded='{item}'>
				<td>
					<CIcon
						size='lg'
						:class='item.responded ? "text-success" : "text-danger"'
						:content='item.responded ? cilCheckCircle : cilXCircle'
					/>
				</td>
			</template>
			<template #handled='{item}'>
				<td>
					<CIcon
						v-if='item.responded'
						size='lg'
						:class='item.handled ? "text-success" : "text-danger"'
						:content='item.handled ? cilCheckCircle : cilXCircle'
					/>
					<span v-else>
						{{ $t('iqrfnet.networkManager.maintenance.frcResponseTime.modal.notAvailable') }}
					</span>
				</td>
			</template>
			<template #responseTime='{item}'>
				<td>
					{{ item.responded && item.handled ? item.responseTime : $t('iqrfnet.networkManager.maintenance.frcResponseTime.modal.notAvailable') }}
				</td>
			</template>
		</CDataTable>
		<hr v-if='!sameResponseTime'>
		<span v-if='!sameResponseTime'>
			{{ $t('iqrfnet.networkManager.maintenance.frcResponseTime.modal.prompt') }}
		</span>
		<template #footer>
			<CButton
				v-if='!sameResponseTime'
				color='primary'
				@click='setFrcResponseTime'
			>
				{{ $t('forms.set') }}
			</CButton> <CButton
				color='secondary'
				@click='deactivateModal'
			>
				{{ $t('forms.close') }}
			</CButton>
		</template>
	</CModal>
</template>

<script lang='ts'>
import {Component} from 'vue-property-decorator';
import {CButton, CModal} from '@coreui/vue/src';

import {cilCheckCircle, cilXCircle} from '@coreui/icons';
import {FrcCommands} from '@/enums/IqrfNet/Maintenance';

import {IFrcResponseTimeResult} from '@/interfaces/DaemonApi/Iqmesh/Maintenance';
import {IField} from '@/interfaces/Coreui';
import ModalBase from '@/components/ModalBase.vue';

@Component({
	components: {
		CButton,
		CModal,
	},
	data: () => ({
		cilCheckCircle,
		cilXCircle,
	}),
})

/**
 * Maintenance FRC Response Time result component
 */
export default class FrcResponseTimeResultModal extends ModalBase {
	/**
	 * @var {IFrcResponseTimeResult} result Maintenance FRC Response Time result
	 */
	private result: IFrcResponseTimeResult = {
		command: FrcCommands.IQRF1BYTE,
		inaccessibleNodes: 0,
		unhandledNodes: 0,
		nodes: [],
		currentResponseTime: 40,
		recommendedResponseTime: 40,
	};

	/**
	 * @var {Array<IOption>} fields FRC response times table fields
	 */
	private fields: Array<IField> = [
		{
			label: this.$t('iqrfnet.networkManager.maintenance.frcResponseTime.modal.fields.address'),
			key: 'deviceAddr',
		},
		{
			label: this.$t('iqrfnet.networkManager.maintenance.frcResponseTime.modal.fields.responded'),
			key: 'responded',
			filter: false,
		},
		{
			label: this.$t('iqrfnet.networkManager.maintenance.frcResponseTime.modal.fields.handled'),
			key: 'handled',
			filter: false,
		},
		{
			label: this.$t('iqrfnet.networkManager.maintenance.frcResponseTime.modal.fields.responseTime'),
			key: 'responseTime',
		},
	];

	/**
	 * @var {string} commandLabel FRC command label for translation
	 */
	private commandLabel = FrcCommands[144].toLowerCase();

	/**
	 * Computes whether current and recommended response time is the same
	 */
	get sameResponseTime(): boolean {
		return this.result.currentResponseTime === this.result.recommendedResponseTime;
	}

	/**
	 * Stores FRC Response Time result and renders the modal window
	 * @param {IFrcResponseTimeResult} result FRC response time result
	 */
	public activateModal(result: IFrcResponseTimeResult): void {
		this.result = result;
		this.commandLabel = FrcCommands[result.command].toLowerCase();
		this.show = true;
	}

	/**
	 * Clears FRC Response Time result and closes the modal window
	 */
	private deactivateModal(): void {
		this.show = false;
		this.result = {
			command: FrcCommands.IQRF1BYTE,
			nodes: [],
			inaccessibleNodes: 0,
			unhandledNodes: 0,
			currentResponseTime: 40,
			recommendedResponseTime: 40,
		};
		this.commandLabel = FrcCommands[FrcCommands.IQRF1BYTE].toLowerCase();
	}

	/**
	 * Closes the modal window and sets FRC response time
	 */
	private setFrcResponseTime(): void {
		let responseTime;
		switch (this.result.recommendedResponseTime) {
			case 360:
				responseTime = 16;
				break;
			case 680:
				responseTime = 32;
				break;
			case 1320:
				responseTime = 48;
				break;
			case 2600:
				responseTime = 64;
				break;
			case 5160:
				responseTime = 80;
				break;
			case 10280:
				responseTime = 96;
				break;
			case 20620:
				responseTime = 112;
				break;
			default:
				responseTime = 0;
		}
		this.deactivateModal();
		this.$emit('set-frc-response-time', responseTime);
	}
}
</script>
