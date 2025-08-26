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
	<span v-if='requestRunning'>
		<CSpinner color='info' class='cinfo-spinner' />
	</span>
	<span v-else>
		<span v-if='enumData'>
			<strong>{{ $t('gateway.info.tr.moduleType') }}: </strong>
			{{ enumData.osRead.trMcuType.trType }}<br>
			<strong>{{ $t('gateway.info.tr.mcuType') }}: </strong>
			{{ enumData.osRead.trMcuType.mcuType }}<br>
			<strong>{{ $t('gateway.info.tr.moduleId') }}: </strong>
			{{ Number.parseInt(enumData.osRead.mid, 16) }} [{{ enumData.osRead.mid }}]<br>
			<strong>{{ $t('gateway.info.tr.os') }}: </strong>
			{{ enumData.osRead.osVersion }} [{{ enumData.osRead.osBuild }}]<br>
			<strong>{{ $t('gateway.info.tr.dpa') }}: </strong>
			{{ enumData.peripheralEnumeration.dpaVer }}<br>
			<strong>{{ $t('gateway.info.tr.hwpid') }}: </strong>
			{{ enumData.peripheralEnumeration.hwpId }} [{{ enumData.peripheralEnumeration.hwpId.toString(16).padStart(4, '0') }}]<br>
			<strong>{{ $t('gateway.info.tr.hwpidVersion') }}: </strong>
			{{ enumData.peripheralEnumeration.hwpIdVer & 0x00FF }}.{{ enumData.peripheralEnumeration.hwpIdVer & 0xFF00 }}<br>
			<strong>{{ $t('gateway.info.tr.voltage') }}: </strong>
			{{ enumData.osRead.supplyVoltage }}<br>
		</span>
		<span v-else>
			{{ $t('gateway.info.tr.error') }}
		</span>
	</span>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {MutationPayload} from 'vuex';
import IqrfNetService from '@/services/IqrfNetService';
import {CSpinner} from '@coreui/vue/src';
import {DaemonClientState} from '@/interfaces/wsClient';
import {IDeviceEnumeration} from '@/interfaces/DaemonApi/Dpa';

@Component({
	components: {
		CSpinner,
	},
})

/**
 * Coordinator information block component for gateway information
 */
export default class CoordinatorInfo extends Vue {
	/**
	 * @constant {Array<string>} allowedMTypes Array of allowed daemon api messages
	 */
	private allowedMTypes: Array<string> = [
		'iqmeshNetwork_EnumerateDevice',
		'messageError'
	];

	/**
	 * @var {string|null} msgId Daemon api message id
	 */
	private msgId: string|null = null;

	/**
	 * @var {boolean} requestRunning Indicates whether a daemon api request has been completed
	 */
	private requestRunning = false;

	/**
	 * @var {IDeviceEnumeration | null} enumData Device enumeration data
	 */
	private enumData: IDeviceEnumeration | null = null;

	/**
	 * Component unwatch function
	 */
	private unwatch: CallableFunction = () => {return;};

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'daemonClient/SOCKET_ONMESSAGE') {
				if (!this.allowedMTypes.includes(mutation.payload.mType)) {
					return;
				}
				this.requestRunning = false;
				if (mutation.payload.data.msgId === this.msgId) {
					this.$store.dispatch('daemonClient/removeMessage', this.msgId);
					this.enumData = mutation.payload.data.rsp as IDeviceEnumeration;
				}
			}

		});
		if (this.$store.getters['daemonClient/isConnected']) {
			this.enumerate();
		} else {
			this.unwatch = this.$store.watch(
				(state: DaemonClientState, getter: any) => getter['daemonClient/isConnected'],
				(newVal: boolean, oldVal: boolean) => {
					if (!oldVal && newVal) {
						this.enumerate();
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
	 * Performs enumeration of the coordinator device
	 */
	private enumerate(): void {
		IqrfNetService.enumerateDevice(0, 5000, 'iqrfnet.enumeration.messages.failure', () => this.timedOut())
			.then((msgId: string) => this.msgId = msgId);
		this.requestRunning = true;
	}

	/**
	 * Daemon api request timeout handler
	 */
	private timedOut(): void {
		this.requestRunning = false;
		this.msgId = null;
	}
}
</script>

<style scoped>
.cinfo-spinner {
	width: 2rem;
	height: 2rem;
}
</style>
