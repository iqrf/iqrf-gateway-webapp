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
	<v-card flat tile>
		<v-card-title>{{ $t('iqrfnet.networkManager.maintenance.frcResponseTime.title') }}</v-card-title>
		<v-card-text>
			<v-form @submit.prevent=''>
				<v-select
					v-model='command'
					:items='commands'
					:label='$t("iqrfnet.networkManager.maintenance.frcResponseTime.command")'
				/>
				<v-btn
					class='mr-1'
					color='primary'
					@click='getResponseTime'
				>
					{{ $t('forms.get') }}
				</v-btn>
				<FrcResponseTimeResultDialog ref='resultDialog' @set-frc-response-time='setFrcResponseTime' />
			</v-form>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import FrcResponseTimeResultDialog from './FrcResponseTimeResultDialog.vue';

import {FrcCommands} from '@/enums/IqrfNet/maintenance';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

import IqmeshNetworkService from '@/services/DaemonApi/IqmeshNetworkService';

import {IMaintenanceFrcResponseTimeResult} from '@/interfaces/iqmeshServices';
import {IOption} from '@/interfaces/coreui';
import {MutationPayload} from 'vuex';
import IqrfNetService from '@/services/IqrfNetService';

/**
 * Maintenance FRC response time component
 */
@Component({
	components: {
		FrcResponseTimeResultDialog,
	},
})
export default class FrcResponseTime extends Vue {
	/**
	 * @var {string} msgId Daemon API msg ID
	 */
	private msgId = '';

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * @var {FrcCommands} command FRC command
	 */
	private command: FrcCommands = FrcCommands.IQRF1BYTE;

	/**
	 * Generates FRC commands for select component
	 * @returns {Array<IOption>} FRC commands select options
	 */
	get commands(): Array<IOption> {
		const commands: Array<IOption> = [];
		const items: Array<string> = Object.keys(FrcCommands).filter((v) => Number.isNaN(Number(v)));
		items.forEach((item: string) => {
			commands.push(
				{
					text: this.$t('iqrfnet.networkManager.maintenance.frcResponseTime.commands.' + item.toLowerCase()).toString(),
					value: FrcCommands[item],
				},
			);
		});
		return commands;
	}

	/**
	 * @var {IMaintenanceFrcResponseTimeResult|null} result FRC Response Time result
	 */
	private result: IMaintenanceFrcResponseTimeResult|null = null;

	/**
	 * Registers mutation handling
	 */
	created(): void {
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type !== 'daemonClient/SOCKET_ONMESSAGE') {
				return;
			}
			if (mutation.payload.data.msgId !== this.msgId) {
				return;
			}
			this.$store.dispatch('spinner/hide');
			this.$store.dispatch('daemonClient/removeMessage', this.msgId);
			if (mutation.payload.mType === 'iqmeshNetwork_MaintenanceFrcResponseTime') {
				this.handleGetResponseTime(mutation.payload.data);
			} else if (mutation.payload.mType === 'iqrfEmbedFrc_SetParams') {
				this.handleSetFrcResponseTime(mutation.payload.data);
			}
		});
	}

	/**
	 * Unregisters mutation handling
	 */
	beforeDestroy(): void {
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		this.unsubscribe();
	}

	/**
	 * Gets FRC response time
	 */
	private getResponseTime(): void {
		this.result = null;
		this.$store.dispatch('spinner/show', {
			timeout: 400000,
			text: this.$t('iqrfnet.networkManager.maintenance.frcResponseTime.messages.progress').toString(),
		});
		const options = new DaemonMessageOptions(null, 400000, 'iqrfnet.networkManager.maintenance.frcResponseTime.messages.timeout', () => this.msgId = '');
		IqmeshNetworkService.maintenanceFrcResponseTime(this.command, options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles MaintenanceFRCResponseTime response
	 * @param response Response
	 */
	private handleGetResponseTime(response): void {
		if (response.status !== 0) {
			this.$store.dispatch('spinner/hide');
			this.$toast.error(
				this.$t(
					'iqrfnet.networkManager.maintenance.frcResponseTime.messages.failed',
					{error: response.statusStr},
				).toString()
			);
			return;
		}
		response.rsp.command = this.command;
		this.result = response.rsp;
		(this.$refs.resultDialog as FrcResponseTimeResultDialog).showDialog(response.rsp);
	}

	/**
	 * Sets FRC response time
	 * @param {number} responseTime FRC response time
	 */
	private setFrcResponseTime(responseTime: number): void {
		this.$store.dispatch('spinner/show', {
			timeout: 10000,
			text: this.$t('iqrfnet.networkManager.maintenance.frcResponseTime.messages.setProgress').toString(),
		});
		const options = new DaemonMessageOptions(null, 10000, 'iqrfnet.networkManager.maintenance.frcResponseTime.messages.setTimeout', () => this.msgId = '');
		IqrfNetService.setFrcResponseTime(responseTime, options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles FRC SetParams response
	 * @param response Response
	 */
	private handleSetFrcResponseTime(response): void {
		if (response.status !== 0) {
			this.$toast.error(
				this.$t('iqrfnet.networkManager.maintenance.frcResponseTime.messages.setFailed').toString()
			);
		} else {
			this.$toast.success(
				this.$t('iqrfnet.networkManager.maintenance.frcResponseTime.messages.setSuccess').toString()
			);
		}
	}
}
</script>
