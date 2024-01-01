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
	<span v-if='requestRunning && mode === "unknown"'>
		<v-progress-circular
			color='info'
			indeterminate
		/>
	</span>
	<span v-else>{{ $t(mode !== 'unknown' ? 'gateway.mode.modes.' + mode: 'gateway.mode.messages.getFailed') }}</span>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';

import {buildDaemonMessageOptions} from '@/store/modules/daemonClient.module';
import {DaemonModeEnum} from '@/enums/Gateway/DaemonMode';

import ManagementService from '@/services/DaemonApi/ManagementService';

import {DaemonClientState} from '@/interfaces/wsClient';
import {MutationPayload} from 'vuex';

/**
 * Daemon mode information component for gateway information
 */
@Component
export default class DaemonModeInfo extends Vue {
	/**
	 * @constant {Array<string>} allowedMTypes Array of allowed daemon api messages
	 */
	private allowedMTypes: Array<string> = [
		'mngDaemon_Mode',
		'messageError'
	];

	/**
	 * @var {DaemonModeEnum} mode Current daemon mode
	 */
	private mode: DaemonModeEnum = DaemonModeEnum.unknown;

	/**
	 * @var {string|null} msgId daemon api message id
	 */
	private msgId: string|null = null;

	/**
	 * @var {boolean} requestRunning indicates whether a daemon api request has been completed
	 */
	private requestRunning = false;

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * Component unwatch function
	 */
	private unwatch: CallableFunction = () => {return;};

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'daemonClient/SOCKET_ONMESSAGE') {
				const response = mutation.payload;
				if (!this.allowedMTypes.includes(response.mType)) {
					return;
				}
				this.requestRunning = false;
				if (response.data.msgId === this.msgId) {
					this.$store.dispatch('daemonClient/removeMessage', this.msgId);
					this.mode = ManagementService.parseModeResponse(response);
					this.$emit('notify-cinfo');
				}
			}
		});
		if (this.$store.getters['daemonClient/isConnected']) {
			this.getMode();
		} else {
			this.unwatch = this.$store.watch(
				(state: DaemonClientState, getter) => getter['daemonClient/isConnected'],
				(newVal: boolean, oldVal: boolean) => {
					if (!oldVal && newVal) {
						this.getMode();
						this.unwatch();
					}
				}
			);
		}
	}

	/**
	 * Vue lifecycle hook beforeDestroy
	 */
	beforeDestroy(): void {
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		this.unwatch();
		this.unsubscribe();
	}

	/**
	 * Retrieves current Daemon mode
	 */
	private getMode(): void {
		const options = buildDaemonMessageOptions(5000, 'gateway.mode.messages.getFailed', () => this.timedOut());
		ManagementService.getMode(options)
			.then((msgId: string) => this.msgId = msgId);
		this.requestRunning = true;
	}

	/**
	 * Daemon api request timeout handler
	 */
	private timedOut(): void {
		this.requestRunning = false;
		this.msgId = null;
		this.$emit('notify-cinfo');
	}
}
</script>
