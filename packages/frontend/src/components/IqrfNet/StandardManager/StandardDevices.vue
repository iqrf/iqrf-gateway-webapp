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
	<div>
		<v-card>
			<v-toolbar flat>
				<v-toolbar-title>
					{{ $t('iqrfnet.standard.table.title') }}
				</v-toolbar-title>
				<v-spacer />
				<EnumerationModal @finished='getDevices' />
				<v-btn
					class='mr-1'
					color='primary'
					small
					@click='getDevices'
				>
					<v-icon small>
						mdi-sync
					</v-icon>
					<span class='d-none d-lg-inline'>
						{{ $t('iqrfnet.standard.table.actions.refresh') }}
					</span>
				</v-btn>
				<DatabaseResetModal
					@reset='devices = []; getDevices()'
				/>
			</v-toolbar>
			<v-card-text>
				<div class='datatable-legend'>
					<div>
						<v-icon>
							mdi-information-outline
						</v-icon>
						{{ $t('iqrfnet.standard.table.info') }}
					</div>
					<div>
						<v-icon color='info'>
							mdi-home-outline
						</v-icon>
						{{ $t('forms.fields.coordinator') }}
					</div>
					<div>
						<v-icon color='info'>
							mdi-check
						</v-icon>
						{{ $t('iqrfnet.networkManager.devicesInfo.icons.bonded') }}
					</div>
					<div>
						<v-icon color='info'>
							mdi-signal-cellular-outline
						</v-icon>
						{{ $t('iqrfnet.networkManager.devicesInfo.icons.discovered') }}
					</div>
					<div>
						<v-icon color='success'>
							mdi-check
						</v-icon>
						{{ $t('iqrfnet.networkManager.devicesInfo.icons.bondedOnline') }}
					</div>
					<div>
						<v-icon color='success'>
							mdi-signal-cellular-outline
						</v-icon>
						{{ $t('iqrfnet.networkManager.devicesInfo.icons.discoveredOnline') }}
					</div>
					<div>
						<v-icon color='success'>
							mdi-check-circle-outline
						</v-icon>
						{{ $t('iqrfnet.standard.table.supported') }}
					</div>
					<div>
						<v-icon color='error'>
							mdi-close-circle-outline
						</v-icon>
						{{ $t('iqrfnet.standard.table.unsupported') }}
					</div>
				</div>
				<v-data-table
					:headers='headers'
					:items='devices'
					:no-data-text='$t("iqrfnet.standard.table.fields.noDevices")'
					show-expand
					:expanded.sync='expanded'
					item-key='address'
				>
					<template #[`item.address`]='{item}'>
						<router-link :to='"/iqrfnet/enumeration/" + item.getAddress()'>
							{{ item.getAddress() }}
						</router-link>
					</template>
					<template #[`item.product`]='{item}'>
						{{ item.getProductName() }}
					</template>
					<template #[`item.os`]='{item}'>
						{{ item.getOs() }}
					</template>
					<template #[`item.dpa`]='{item}'>
						{{ item.getDpa() }}
					</template>
					<template #[`item.status`]='{item}'>
						<v-icon
							:color='item.getIconColor()'
							size='xl'
						>
							{{ item.getIcon() }}
						</v-icon>
					</template>
					<template #[`item.sensor`]='{item}'>
						<v-icon
							:color='item.hasSensor() ? "success" : "error"'
							size='xl'
						>
							{{ item.getSensorIcon() }}
						</v-icon>
					</template>
					<template #[`item.binout`]='{item}'>
						<v-icon
							:color='item.hasBinout() ? "success" : "error"'
							size='xl'
						>
							{{ item.getBinoutIcon() }}
						</v-icon>
					</template>
					<template #[`item.light`]='{item}'>
						<v-icon
							:color='item.hasLight() ? "success" : "error"'
							size='xl'
						>
							{{ item.getLightIcon() }}
						</v-icon>
					</template>
					<template #expanded-item='{headers, item}'>
						<td :colspan='headers.length'>
							<v-container fluid>
								<v-row>
									<v-col cols='auto' align-self='center'>
										<v-img
											:src='item.getImg()'
											max-width='150'
											max-height='150'
										/>
									</v-col>
									<v-divider vertical />
									<v-col cols='auto'>
										<div class='datatable-expansion-table'>
											<table>
												<caption>
													<b>{{ $t('iqrfnet.standard.table.info') }}</b>
												</caption>
												<tr>
													<th>
														{{ $t('iqrfnet.standard.table.fields.manufacturer') }}
													</th>
													<td>
														{{ item.getManufacturer() }}
													</td>
												</tr>
												<tr>
													<th>
														{{ $t('iqrfnet.standard.table.fields.hwpid') }}
													</th>
													<td>{{ `${item.getHwpid()} [${item.getHwpidHex()}]` }}</td>
												</tr>
												<tr>
													<th>
														{{ $t('iqrfnet.standard.table.fields.hwpidVer') }}
													</th>
													<td>{{ item.getHwpidVer() }}</td>
												</tr>
												<tr>
													<th>
														{{ $t('iqrfnet.standard.table.fields.mid') }}
													</th>
													<td>
														{{ `${item.getMid()} [${item.getMidHex()}]` }}
													</td>
												</tr>
											</table>
											<table v-if='item.hasSensor()'>
												<caption class='sensor-caption'>
													<b>{{ $t('iqrfnet.standard.table.sensor.title') }}</b>
												</caption>
												<tr>
													<th>{{ $t('iqrfnet.standard.table.sensor.name') }}</th>
													<th>{{ $t('iqrfnet.standard.table.sensor.type') }}</th>
													<th>{{ $t('iqrfnet.standard.table.sensor.index') }}</th>
												</tr>
												<tr v-for='(sensor, i) of item.getSensors()' :key='i'>
													<td>{{ sensor.name }}</td>
													<td>{{ sensor.type }}</td>
													<td>{{ sensor.idx }}</td>
												</tr>
											</table>
										</div>
									</v-col>
								</v-row>
							</v-container>
						</td>
					</template>
				</v-data-table>
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import DatabaseResetModal from './DatabaseResetModal.vue';

import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

import DbService from '@/services/DaemonApi/DbService';
import IqmeshNetworkService from '@/services/DaemonApi/IqmeshNetworkService';
import StandardDevice from '@/entities/StandardDevice';

import {DataTableHeader} from 'vuetify';
import {IIqrfDbBo, IIqrfDbDeviceFull, IIqrfDbSensor} from '@/interfaces/DaemonApi/IqrfDb';
import {MutationPayload} from 'vuex';
import {useRepositoryClient} from '@/services/IqrfRepositoryClient';
import {ProductService} from '@iqrf/iqrf-repository-client/services';
import {Product} from '@iqrf/iqrf-repository-client/types';

@Component({
	components: {
		DatabaseResetModal,
	},
})

/**
 * Standard devices component
 */
export default class StandardDevices extends Vue {
	/**
	 * @var {Array<standardDevice>} devices Auxiliary array of devices before the final grid is rendered
	 */
	private auxDevices: Array<StandardDevice> = [];

	/**
	 * @var {Array<standardDevice>} devices Standard devices for datatable
	 */
	private devices: Array<StandardDevice> = [];

	/**
	 * @var {string|null} msgId Daemon API message ID
	 */
	private msgId: string|null = null;

	/**
	 * @var {Array<StandardDevices>} expanded Expanded devices
	 */
	private expanded: Array<StandardDevices> = [];

	/**
	 * @constant {Array<DataTableHeader>} headers Data table headers
	 */
	private readonly headers: Array<DataTableHeader> = [
		{
			value: 'address',
			text: this.$t('iqrfnet.standard.table.fields.address').toString(),
		},
		{
			value: 'product',
			text: this.$t('iqrfnet.standard.table.fields.product').toString(),
		},
		{
			value: 'os',
			text: this.$t('iqrfnet.standard.table.fields.os').toString(),
		},
		{
			value: 'dpa',
			text: this.$t('iqrfnet.standard.table.fields.dpa').toString(),
		},
		{
			value: 'status',
			text: this.$t('iqrfnet.standard.table.fields.status').toString(),
			filterable: false,
			sortable: false,
		},
		{
			value: 'sensor',
			text: this.$t('iqrfnet.standard.table.fields.sensor').toString(),
			filterable: false,
			sortable: false,
		},
		{
			value: 'binout',
			text: this.$t('iqrfnet.standard.table.fields.binout').toString(),
			filterable: false,
			sortable: false,
		},
		{
			value: 'light',
			text: this.$t('iqrfnet.standard.table.fields.light').toString(),
			filterable: false,
			sortable: false,
		},
		{
			value: 'data-table-expand',
			text: '',
		},
	];

	/**
	 * Websocket store unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
   * @property {ProductService|undefined} productService Product service
   * @private
   */
	private productService!: ProductService;

	/**
	 * Subscribes a mutation handler to websocket store
	 */
	async created(): Promise<void> {
		const repositoryClient = await useRepositoryClient();
		this.productService = repositoryClient.getProductService();
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type !== 'daemonClient/SOCKET_ONMESSAGE') {
				return;
			}
			if (mutation.payload.data.msgId !== this.msgId) {
				return;
			}
			this.$store.dispatch('daemonClient/removeMessage', this.msgId);
			if (mutation.payload.mType === 'messageError') {
				this.handleMessageError(mutation.payload.data);
			} else if (mutation.payload.mType === 'iqrfDb_GetDevices') {
				this.handleGetDevices(mutation.payload.data);
			} else if (mutation.payload.mType === 'iqrfDb_GetBinaryOutputs') {
				this.handleGetBinaryOutputs(mutation.payload.data);
			} else if (mutation.payload.mType === 'iqrfDb_GetLights') {
				this.handleGetLights(mutation.payload.data);
			} else if (mutation.payload.mType === 'iqrfDb_GetSensors') {
				this.handleGetSensors(mutation.payload.data);
			} else if (mutation.payload.mType === 'iqmeshNetwork_Ping') {
				this.handlePingDevices(mutation.payload.data);
			}
		});
	}

	/**
	 * Starts request chain to build grid of devices implementing IQRF standards
	 */
	mounted(): void {
		this.getDevices();
	}

	/**
	 * Unsubscribes handler from websocket store
	 */
	beforeDestroy(): void {
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		this.unsubscribe();
	}



	/**
	 * Retrieves information about devices stored in database
	 */
	private getDevices(): void {
		this.$store.dispatch('spinner/show', {timeout: 90000});
		this.$store.commit(
			'spinner/UPDATE_TEXT',
			this.$t('iqrfnet.standard.table.messages.device.fetch').toString()
		);
		const options = new DaemonMessageOptions(null, 10000, 'iqrfnet.standard.table.messages.device.fetchTimeout', () => this.msgId = null);
		DbService.getDevices(false, options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles GetDevices Daemon API response
	 * @param response Daemon API response
	 */
	private handleGetDevices(response): void {
		if (response.status !== 0) {
			this.$store.dispatch('spinner/hide');
			this.$toast.error(
				this.$t('iqrfnet.standard.table.messages.device.fetchFailed').toString()
			);
			return;
		}
		const devices: Array<StandardDevice> = [];
		response.rsp.devices.forEach((device: IIqrfDbDeviceFull) => {
			devices.push(new StandardDevice(device));
		});
		if (devices.length > 0) {
			this.auxDevices = devices;
			this.getBinaryOutputs();
		} else {
			this.$store.dispatch('spinner/hide');
		}
	}

	/**
	 * Retrieves information about devices implementing binary output standard in database
	 */
	private getBinaryOutputs(): void {
		this.$store.commit(
			'spinner/UPDATE_TEXT',
			this.$t('iqrfnet.standard.table.messages.binout.fetch').toString()
		);
		const options = new DaemonMessageOptions(null, 10000, 'iqrfnet.standard.table.messages.binout.fetchTimeout', () => this.msgId = null);
		DbService.getBinaryOutputs(options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles GetBinaryOutputs Daemon API response
	 * @param response Daemon API response
	 */
	private handleGetBinaryOutputs(response): void {
		if (response.status !== 0) {
			this.$store.dispatch('spinner/hide');
			this.$toast.error(
				this.$t('iqrfnet.standard.table.messages.binout.fetchFailed').toString()
			);
			return;
		}
		response.rsp.binoutDevices.forEach((device: IIqrfDbBo) => {
			const idx = this.getDeviceIndex(device.address);
			if (idx !== -1) {
				this.auxDevices[idx].setBinouts(device.count);
			}
		});
		this.getLights();
	}

	/**
	 * Retrieves information about devices implementing light standard
	 */
	private getLights(): void {
		this.$store.commit(
			'spinner/UPDATE_TEXT',
			this.$t('iqrfnet.standard.table.messages.light.fetch').toString()
		);
		const options = new DaemonMessageOptions(null, 10000, 'iqrfnet.standard.table.messages.light.fetchTimeout', () => this.msgId = null);
		DbService.getLights(options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles GetLights Daemon API response
	 * @param response Daemon API response
	 */
	private handleGetLights(response): void {
		if (response.status !== 0) {
			this.$store.dispatch('spinner/hide');
			this.$toast.error(
				this.$t('iqrfnet.standard.table.messages.light.fetchFailed').toString()
			);
			return;
		}
		response.rsp.lightDevices.forEach((device: number) => {
			const idx = this.getDeviceIndex(device);
			if (idx !== -1) {
				this.auxDevices[idx].setLight(true);
			}
		});
		this.getSensors();
	}

	/**
	 * Retrieves information about devices implementing sensor standard
	 */
	private getSensors(): void {
		this.$store.commit(
			'spinner/UPDATE_TEXT',
			this.$t('iqrfnet.standard.table.messages.sensor.fetch').toString()
		);
		const options = new DaemonMessageOptions(null, 10000, 'iqrfnet.standard.table.messages.sensor.fetch', () => this.msgId = null);
		DbService.getSensors(options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles GetSensors Daemon API response
	 * @param response Daemon API response
	 */
	private async handleGetSensors(response): Promise<void> {
		if (response.status !== 0) {
			this.$store.dispatch('spinner/hide');
			this.$toast.error(
				this.$t('iqrfnet.standard.table.messages.sensor.fetchFailed').toString()
			);
			return;
		}
		response.rsp.sensorDevices.forEach((device: IIqrfDbSensor) => {
			const idx = this.getDeviceIndex(device.address);
			if (idx !== -1) {
				this.auxDevices[idx].setSensors(device.sensors);
			}
		});
		await this.fetchDeviceDetails();
		await this.$store.dispatch('spinner/hide');
		this.pingDevices();
	}

	/**
	 * Filters devices that do not implement any standards from the array
	 */
	private async fetchDeviceDetails(): Promise<void> {
		const hwpids = new Map();
		for (const auxDevice of this.auxDevices) {
			const hwpid = auxDevice.getHwpid();
			if (hwpids.has(hwpid)) {
				auxDevice.setProduct(hwpids.get(hwpid));
				continue;
			}
			if ((hwpid & 0xf) === 0xf) {
				continue;
			}
			await this.productService.get(hwpid)
				.then((response: Product) => {
					hwpids.set(hwpid, response);
					auxDevice.setProduct(hwpids.get(hwpid));
				})
				.catch(() => {
					// Device not found in repository, ignore
				});
		}
		this.devices = this.auxDevices;
		this.auxDevices = [];
	}

	/**
	 * Pings devices in network to check which devices are online
	 */
	private pingDevices(): void {
		const nodes: Array<number> = this.devices.map((device: StandardDevice) => (device.getAddress())).filter((addr: number) => addr > 0);
		if (nodes.length === 0) {
			return;
		}
		this.$store.dispatch('spinner/show', {timeout: 100000});
		this.$store.commit(
			'spinner/UPDATE_TEXT',
			this.$t('iqrfnet.standard.table.messages.ping.fetch').toString()
		);
		const options = new DaemonMessageOptions(null, 100000, this.$t('iqrfnet.standard.table.messages.ping.fetchFailed'));
		IqmeshNetworkService.ping(options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles Frc_Send Daemon API response
	 * @param response Daemon API response
	 */
	private handlePingDevices(response): void {
		if (response.status !== 0) {
			this.$store.dispatch('spinner/hide');
			this.$toast.error(
				this.$t('iqrfnet.standard.table.messages.ping.fetchFailed').toString()
			);
			return;
		}
		const nodes = response.rsp.pingResult;
		const addrs = this.devices.map((device: StandardDevice) => {return device.getAddress();});
		for (const node of nodes) {
			if (node.address === 0) {
				continue;
			}
			const idx = addrs.indexOf(node.address);
			if (idx === -1) {
				continue;
			}
			this.devices[idx].setOnline(node.result);
		}
		this.$store.dispatch('spinner/hide');
	}

	/**
	 * Handles message error response from Daemon API
	 * @param response Daemon API response
	 */
	private handleMessageError(response): void {
		this.$store.dispatch('spinner/hide');
		this.$toast.error(
			this.$t('messageError', {error: response.rsp.errorStr}).toString()
		);
	}

	/**
	 * Finds index of device in auxDevices array
	 * @param {number} address Address of device from response
	 * @returns {number} Index of device in auxDevices array
	 */
	private getDeviceIndex(address: number): number {
		return this.auxDevices.findIndex((device: StandardDevice) => address === device.getAddress());
	}
}
</script>

<style scoped lang='scss'>
.datatable-header {
	display: flex;
	flex-wrap: nowrap;
	align-items: center;
	justify-content: space-between;
}

.datatable-legend {
	display: flex;
	flex-wrap: wrap;
	align-items: center;
	justify-content: space-evenly;
	margin-bottom: 1.25em;
}
</style>
