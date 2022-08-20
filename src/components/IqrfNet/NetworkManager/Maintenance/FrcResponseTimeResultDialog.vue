<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

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
		v-model='show'
		width='50%'
		persistent
		no-click-animation
	>
		<template #activator='{attrs, on}'>
			<v-btn
				color='primary'
				:disabled='defaultState'
				v-bind='attrs'
				v-on='on'
				@click='openDialog'
			>
				{{ $t('iqrfnet.networkManager.maintenance.frcResponseTime.showResult') }}
			</v-btn>
		</template>
		<v-card>
			<v-card-title>{{ $t('iqrfnet.networkManager.maintenance.frcResponseTime.modal.title') }}</v-card-title>
			<v-card-text>
				<table>
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
				</table>
				<v-divider class='my-4' />
				<v-data-table
					:headers='headers'
					:items='result.nodes'
				>
					<template #[`item.responded`]='{item}'>
						<v-icon :color='item.responded ? "success" : "error"'>
							{{ item.responded ? 'mdi-check-circle-outline' : 'mdi-close-circle-outline' }}
						</v-icon>
					</template>
					<template #[`item.handled`]='{item}'>
						<v-icon
							v-if='item.responded'
							:color='item.handled ? "success" : "error"'
						>
							{{ item.handled ? 'mdi-check-circle-outline' : 'mdi-close-circle-outline' }}
						</v-icon>
						<span v-else>
							{{ $t('iqrfnet.networkManager.maintenance.frcResponseTime.modal.notAvailable') }}
						</span>
					</template>
					<template #responseTime='{item}'>
						<td>
							{{ item.responded && item.handled ? item.responseTime : $t('iqrfnet.networkManager.maintenance.frcResponseTime.modal.notAvailable') }}
						</td>
					</template>
				</v-data-table>
				<div v-if='!sameResponseTime'>
					<v-divider class='my-4' />
					{{ $t('iqrfnet.networkManager.maintenance.frcResponseTime.modal.prompt') }}<br>
					<v-btn
						class='mt-2'
						color='primary'
						@click='setFrcResponseTime'
					>
						{{ $t('forms.set') }}
					</v-btn>
				</div>
			</v-card-text>
			<v-card-actions>
				<v-spacer />
				<v-btn
					class='mr-1'
					@click='closeDialog'
				>
					{{ $t('forms.close') }}
				</v-btn>	
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<script lang='ts'>
import {Component} from 'vue-property-decorator';
import DialogBase from '@/components/DialogBase.vue';

import {FrcCommands} from '@/enums/IqrfNet/maintenance';

import {DataTableHeader} from 'vuetify';
import {IMaintenanceFrcResponseTimeResult} from '@/interfaces/iqmeshServices';

/**
 * Maintenance FRC Response Time result component
 */
@Component
export default class FrcResponseTimeResultDialog extends DialogBase {
	/**
	 * @var {boolean} defaultState Default state
	 */
	private defaultState = true;

	/**
	 * @var {IMaintenanceFrcResponseTimeResult} result Maintenance FRC Response Time result
	 */
	private result: IMaintenanceFrcResponseTimeResult = {
		command: FrcCommands.IQRF1BYTE,
		inaccessibleNodes: 0,
		unhandledNodes: 0,
		nodes: [],
		currentResponseTime: 40,
		recommendedResponseTime: 40,
	};

	/**
	 * @var {Array<IOption>} headers FRC response times table fields
	 */
	private headers: Array<DataTableHeader> = [
		{
			value: 'deviceAddr',
			text: this.$t('iqrfnet.networkManager.maintenance.frcResponseTime.modal.fields.address').toString(),
			width: '25%'
		},
		{
			value: 'responded',
			text: this.$t('iqrfnet.networkManager.maintenance.frcResponseTime.modal.fields.responded').toString(),
			filterable: false,
			width: '25%'
		},
		{
			value: 'handled',
			text: this.$t('iqrfnet.networkManager.maintenance.frcResponseTime.modal.fields.handled').toString(),
			filterable: false,
			width: '25%'
		},
		{
			value: 'responseTime',
			text: this.$t('iqrfnet.networkManager.maintenance.frcResponseTime.modal.fields.responseTime').toString(),
			width: '25%'
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
		if (this.result === null) {
			return false;
		}
		return this.result.currentResponseTime === this.result.recommendedResponseTime;
	}

	/**
	 * Closes the modal window and sets FRC response time
	 */
	private setFrcResponseTime(): void {
		if (this.result === null) {
			return;
		}
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
		this.closeDialog();
		this.$emit('set-frc-response-time', responseTime);
	}

	/**
	 * Stores FRC Response Time result and renders the modal window
	 * @param {IMaintenanceFrcResponseTimeResult} result FRC response time result
	 */
	public showDialog(result: IMaintenanceFrcResponseTimeResult): void {
		this.result = result;
		this.commandLabel = FrcCommands[result.command].toLowerCase();
		this.defaultState = false;
		this.show = true;
	}

	/**
	 * Clears FRC Response Time result and closes the modal window
	 */
	private hideDialog(): void {
		this.closeDialog();
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

}
</script>

<style scoped>
table {
	width: 100%;
}

th {
	text-align: left;
}

td {
	text-align: right;
}

</style>