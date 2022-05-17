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
	<span v-if='requestRunning && mode === "unknown"'>
		<CSpinner color='info' class='cinfo-spinner' />
	</span>
	<span v-else>{{ $t(mode !== 'unknown' ? 'gateway.mode.modes.' + mode: 'gateway.mode.messages.getFailed') }}</span>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {MutationPayload} from 'vuex';
import DaemonModeService, {DaemonModeEnum} from '../../services/DaemonModeService';
import {DaemonClientState} from '../../interfaces/wsClient';

@Component({})

/**
 * Daemon mode information component for gateway information
 */
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
					this.mode = DaemonModeService.parse(response);
					this.$emit('notify-cinfo');
				}
			}
		});
		if (this.$store.getters['daemonClient/isConnected']) {
			this.getMode();
		} else {
			this.unwatch = this.$store.watch(
				(state: DaemonClientState, getter: any) => getter['daemonClient/isConnected'],
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
		DaemonModeService.get(5000, 'gateway.mode.messages.getFailed', () => this.timedOut())
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

<style scoped>
.cinfo-spinner {
	width: 2rem;
	height: 2rem;
}
</style>
