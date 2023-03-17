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
	<div>
		<v-card>
			<v-card-title>{{ $t('iqrfnet.networkManager.devicesInfo.title') }}</v-card-title>
			<v-card-text>
				<v-simple-table class='text-center'>
					<tbody>
						<tr>
							<td class='table-toprow'>
								<v-icon color='info'>
									mdi-home-outline
								</v-icon>
								{{ $t('forms.fields.coordinator') }}
							</td>
							<td class='table-toprow'>
								<v-icon color='error'>
									mdi-close
								</v-icon>
								{{ $t('iqrfnet.networkManager.devicesInfo.icons.unbonded') }}
							</td>
						</tr>
						<tr>
							<td>
								<v-icon color='info'>
									mdi-check
								</v-icon>
								{{ $t('iqrfnet.networkManager.devicesInfo.icons.bonded') }}
							</td>
							<td>
								<v-icon color='info'>
									mdi-signal-cellular-outline
								</v-icon>
								{{ $t('iqrfnet.networkManager.devicesInfo.icons.discovered') }}
							</td>
						</tr>
						<tr>
							<td>
								<v-icon color='success'>
									mdi-check
								</v-icon>
								{{ $t('iqrfnet.networkManager.devicesInfo.icons.bondedOnline') }}
							</td>
							<td>
								<v-icon color='success'>
									mdi-signal-cellular-outline
								</v-icon>
								{{ $t('iqrfnet.networkManager.devicesInfo.icons.discoveredOnline') }}
							</td>
						</tr>
					</tbody>
				</v-simple-table>
				<v-row no-gutters>
					<v-col cols='4'>
						<v-btn
							class='first-button'
							color='info'
							block
							elevation='0'
							@click='indicateCoordinator'
						>
							{{ $t('forms.indicateCoordinator') }}
						</v-btn>
					</v-col>
					<v-col cols='4'>
						<v-btn
							class='middle-button'
							color='primary'
							block
							elevation='0'
							@click='ping'
						>
							{{ $t('forms.pingNodes') }}
						</v-btn>
					</v-col>
					<v-col cols='4'>
						<v-btn
							class='last-button'
							color='error'
							block
							elevation='0'
							@click='restart'
						>
							{{ $t('forms.restartNodes') }}
						</v-btn>
					</v-col>
				</v-row>
				<div v-if='devices.length !== 0' class='table-responsive'>
					<v-simple-table class='device-info'>
						<tbody>
							<tr>
								<td colspan='11' class='text-center'>
									{{ $t('iqrfnet.networkManager.devicesInfo.messages.clickEnumerate') }}
								</td>
							</tr>
							<tr>
								<th />
								<th v-for='num of Array(10).keys()' :key='num'>
									{{ num }}
								</th>
							</tr>
							<tr v-for='row of Array(24).keys()' :key='row'>
								<th>{{ row }}0</th>
								<td v-for='col of Array(10).keys()' :key='col'>
									<DeviceIcon :device='devices[getAddress(row, col)]' />
								</td>
							</tr>
						</tbody>
					</v-simple-table>
				</div>
				<v-alert
					v-else
					type='error'
					text
				>
					{{ $t('iqrfnet.networkManager.devicesInfo.messages.empty') }}
				</v-alert>
			</v-card-text>
		</v-card>
		<RestartErrorModal v-model='restartNodesModel' />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import DeviceIcon from './DeviceIcon.vue';
import RestartErrorModal from '@/components/IqrfNet/NetworkManager/Devices/RestartErrorModal.vue';

import DaemonMessageOptions from '@/ws/DaemonMessageOptions';
import Device from '@/helpers/Device';
import {ToastOptions} from 'vue-toast-notification';

import IqmeshNetworkService from '@/services/DaemonApi/IqmeshNetworkService';
import IqrfNetService from '@/services/IqrfNetService';
import {MutationPayload} from 'vuex';

/**
 * Card of devices in network for Network Manager
 */
@Component({
	components: {
		DeviceIcon,
		RestartErrorModal,
	}
})
export default class DevicesInfo extends Vue {
	/**
	 * @var {Array<Device>} devices Array of devices in network
	 */
	private devices: Array<Device> = [];

	/**
	 * @var {string|null} msgId Daemon api message id
	 */
	private msgId: string|null = null;

	/**
	 * @var {ToastOptions|null} toastMessage Toast message from other components to show after grid is refreshed
	 */
	private toastMessage: ToastOptions|null = null;

	/**
	 * @var {Array<number>} restartNodesModel Nodes failed to restart
	 */
	private restartNodesModel: Array<number> = [];

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
		this.generateDevices();
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'daemonClient/SOCKET_ONMESSAGE') {
				if (mutation.payload.data.msgId !== this.msgId) {
					return;
				}
				this.$store.dispatch('spinner/hide');
				this.$store.dispatch('daemonClient/removeMessage', this.msgId);
				if (mutation.payload.mType === 'iqrfEmbedCoordinator_BondedDevices') {
					this.parseBondedDevices(mutation.payload);
				} else if (mutation.payload.mType === 'iqrfEmbedCoordinator_DiscoveredDevices') {
					this.parseDiscoveredDevices(mutation.payload);
				} else if (mutation.payload.mType === 'iqmeshNetwork_Ping') {
					this.handlePing(mutation.payload.data);
				} else if (mutation.payload.mType === 'iqmeshNetwork_Restart') {
					this.handleRestart(mutation.payload.data);
				} else if (mutation.payload.mType === 'iqrfRaw') {
					this.handleIndicate(mutation.payload);
				} else if (mutation.payload.mType === 'messageError') {
					this.$toast.error(
						this.$t('iqrfnet.messages.genericErrorMessage', {error: mutation.payload.data.rsp.errorStr}).toString()
					);
				}
			}
		});
		if (this.$store.getters['daemonClient/isConnected']) {
			this.getBondedDevices();
		} else {
			this.unwatch = this.$store.watch(
				(state, getter) => getter['daemonClient/isConnected'],
				(newVal, oldVal) => {
					if (!oldVal && newVal) {
						this.getBondedDevices();
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
	 * Creates WebSocket request options object
	 * @param {number} timeout Request timeout in milliseconds
	 * @param {string} message Request timeout message
	 * @returns {DaemonMessageOptions} WebSocket request options
	 */
	private buildOptions(timeout: number, message: string): DaemonMessageOptions {
		return new DaemonMessageOptions(null, timeout, message, () => this.msgId = null);
	}

	/**
	 * Creates array of devices filled with default values
	 */
	private generateDevices(): void {
		this.devices.push(new Device(0, true));
		for (let i = 1; i <= 239; i++) {
			this.devices.push(new Device(i, false));
		}
	}

	/**
	 * Performs BondedDevices api call
	 * @param {ToastOptions|null} message Message from other components to show after grid is refreshed
	 */
	public getBondedDevices(message: ToastOptions|null = null): void {
		if (message !== null) {
			this.toastMessage = message;
		}
		this.$store.dispatch('spinner/show', {timeout: 30000});
		this.$store.commit('spinner/UPDATE_TEXT', this.$t('iqrfnet.networkManager.devicesInfo.messages.getBonded').toString());
		IqrfNetService.getBonded(this.buildOptions(30000, 'iqrfnet.networkManager.devicesInfo.messages.bondedFailed'))
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles BondedDevices api call response
	 * @param {any} response Daemon api response
	 */
	private parseBondedDevices(response): void {
		switch (response.data.status) {
			case 0: {
				this.devices.forEach((item: Device) => {
					item.bonded = false;
				});
				const bonded = response.data.rsp.result.bondedDevices;
				bonded.forEach((item: number) => {
					this.devices[item].bonded = true;
				});
				if (bonded.length > 0) {
					this.getDiscoveredDevices();
				} else {
					this.$store.dispatch('spinner/hide');
					if (this.toastMessage !== null) {
						this.$toast.open(this.toastMessage);
						this.toastMessage = null;
					}
				}
				break;
			}
			default:
				this.$toast.error(
					this.$t('iqrfnet.networkManager.devicesInfo.messages.bondedFailed')
						.toString()
				);
				break;
		}
	}

	/**
	 * Performs DiscoveredDevices api call
	 */
	private getDiscoveredDevices(): void {
		this.$store.dispatch('spinner/show', {timeout: 30000});
		this.$store.commit('spinner/UPDATE_TEXT', this.$t('iqrfnet.networkManager.devicesInfo.messages.getDiscovered').toString());
		IqrfNetService.getDiscovered(this.buildOptions(30000, 'iqrfnet.networkManager.devicesInfo.messages.discoveredFailed'))
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles DiscoveredDevices api call response
	 * @param response Daemon api response
	 */
	private parseDiscoveredDevices(response): void {
		switch (response.data.status) {
			case 0: {
				this.devices.forEach((item: Device) => {
					item.discovered = false;
				});
				const discovered = response.data.rsp.result.discoveredDevices;
				discovered.forEach((item: number) => {
					this.devices[item].discovered = true;
				});
				this.ping();
				break;
			}
			default:
				this.$toast.error(
					this.$t('iqrfnet.networkManager.devicesInfo.messages.discoveredFailed')
						.toString()
				);
				break;
		}
	}

	/**
	 * Performs Ping API call
	 */
	private ping(): void {
		this.$store.dispatch('spinner/show', {timeout: 90000});
		this.$store.commit('spinner/UPDATE_TEXT', this.$t('iqrfnet.networkManager.devicesInfo.messages.getOnline').toString());
		IqmeshNetworkService.ping(this.buildOptions(90000, 'iqrfnet.networkManager.devicesInfo.messages.pingFailed'))
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles IQMESH Ping API call response
	 * @param response Response
	 */
	private handlePing(response): void {
		if (response.status === 0) {
			response.rsp.pingResult.forEach((device) => {
				this.devices[device.address].online = device.result;
			});
			if (this.toastMessage !== null) {
				this.$toast.open(this.toastMessage);
				this.toastMessage = null;
			}
		} else if (response.status === 1003) {
			this.$toast.info(
				this.$t('forms.messages.noBondedNodes').toString()
			);
		} else {
			this.$toast.error(
				this.$t('iqrfnet.networkManager.devicesInfo.messages.pingFailed').toString()
			);
		}
	}

	/**
	 * Indicates coordinator
	 */
	private indicateCoordinator(): void {
		this.$store.dispatch('spinner/show', {timeout: 5000});
		IqrfNetService.indicateCoordinator(this.buildOptions(5000, 'iqrfnet.networkManager.devicesInfo.messages.indicateFailed'))
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles indicate request response
	 * @param response Daemon API response
	 */
	private handleIndicate(response): void {
		if (response.data.status === 0) {
			this.$toast.success(
				this.$t('iqrfnet.networkManager.devicesInfo.messages.indicateSuccess').toString()
			);
		} else {
			this.$toast.error(
				this.$t('iqrfnet.networkManager.devicesInfo.messages.indicateFailed').toString()
			);
		}
	}

	/**
	 * Restarts nodes in network
	 */
	private restart(): void {
		this.$store.dispatch('spinner/show', {timeout: 90000});
		IqrfNetService.restart(0xFFFF, this.buildOptions(90000, 'iqrfnet.networkManager.devicesInfo.messages.restartFailed'))
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles restart request response
	 * @param response Daemon API response
	 */
	private handleRestart(response): void {
		if (response.status === 0) {
			if (response.rsp.inaccessibleNodesNr > 0) {
				this.restartNodesModel = response.rsp.restartResult
					.filter(e => !e.result)
					.map(n => {return n.address;});
			} else {
				this.$toast.success(
					this.$t('iqrfnet.networkManager.devicesInfo.messages.restartSuccess').toString()
				);
			}
			return;
		} else if (response.status === 1003) {
			this.$toast.info(
				this.$t('forms.messages.noBondedNodes').toString()
			);
		} else {
			this.$toast.error(
				this.$t('iqrfnet.networkManager.devicesInfo.messages.restartFailed').toString()
			);
		}
	}

	/**
	 * Computes index of device based on row and column number
	 * @param {number} row Row number
	 * @param {number} col Column number
	 * @returns {number} Device array index
	 */
	private getAddress(row: number, col: number): number {
		return row * 10 + col;
	}
}
</script>

<style scoped lang='scss'>
@media (min-width: 440px) and (max-width: 1400px) {
	.device-info {
		td,
		th {
			padding: 0.5rem;
		}
	}
}

@media (max-width: 440px) {
	.device-info {
		td,
		th {
			padding: 0.25rem;
		}
	}
}

.table-toprow {
	border: none;
}
</style>
