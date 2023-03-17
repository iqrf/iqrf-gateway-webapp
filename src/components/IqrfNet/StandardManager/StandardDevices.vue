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
			<v-toolbar flat>
				<v-toolbar-title>
					{{ $t('iqrfnet.standard.table.title') }}
				</v-toolbar-title>
				<v-spacer />
				<v-btn
					class='mr-1'
					color='primary'
					small
					@click='enumerateNetwork'
				>
					<v-icon small>
						mdi-google-spreadsheet
					</v-icon>
					<span class='d-none d-lg-inline'>
						{{ $t('iqrfnet.standard.table.actions.enumerate') }}
					</span>
				</v-btn>
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
					<template #[`item.dali`]='{item}'>
						<v-icon
							:color='item.hasDali() ? "success" : "error"'
							size='xl'
						>
							{{ item.getDaliIcon() }}
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

import {EnumerateCommand} from '@/enums/IqrfNet/info';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

import StandardDevice from '@/entities/StandardDevice';
import InfoService from '@/services/DaemonApi/InfoService';
import IqrfNetService from '@/services/IqrfNetService';
import ProductService from '@/services/IqrfRepository/ProductService';

import {AxiosResponse} from 'axios';
import {DataTableHeader} from 'vuetify';
import {IInfoBinout, IInfoDevice, IInfoLight, IInfoNode, IInfoSensor} from '@/interfaces/DaemonApi/IqrfInfo';
import {MutationPayload} from 'vuex';
import DpaService, {OsDpaVersion} from '@/services/IqrfRepository/OsDpaService';

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
			value: 'dali',
			text: this.$t('iqrfnet.standard.table.fields.dali').toString(),
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
	 * Subscribes a mutation handler to websocket store
	 */
	created(): void {
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type !== 'daemonClient/SOCKET_ONMESSAGE') {
				return;
			}
			if (mutation.payload.data.msgId !== this.msgId) {
				return;
			}
			if (mutation.payload.mType === 'messageError') {
				this.handleMessageError(mutation.payload.data);
			} else if (mutation.payload.mType === 'infoDaemon_Enumeration') {
				const command = mutation.payload.data.rsp.command;
				if (command === EnumerateCommand.NOW) {
					this.handleEnumerationNow(mutation.payload.data);
				}
			} else if (mutation.payload.mType === 'infoDaemon_GetNodes') {
				this.handleGetDevices(mutation.payload.data);
			} else if (mutation.payload.mType === 'infoDaemon_GetBinaryOutputs') {
				this.handleGetBinouts(mutation.payload.data);
			} else if (mutation.payload.mType === 'infoDaemon_GetDalis') {
				this.handleGetDalis(mutation.payload.data);
			} else if (mutation.payload.mType === 'infoDaemon_GetLights') {
				this.handleGetLights(mutation.payload.data);
			} else if (mutation.payload.mType === 'infoDaemon_GetSensors') {
				this.handleGetSensors(mutation.payload.data);
			} else if (mutation.payload.mType === 'iqrfEmbedFrc_SendSelective') {
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
	 * Executes network enumeration to populate database tables
	 */
	private enumerateNetwork(): void {
		this.$store.commit('spinner/SHOW');
		InfoService.enumerate(EnumerateCommand.NOW)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles enumeration now response
	 * @param response Daemon API response
	 */
	private handleEnumerationNow(response): void {
		if (response.status !== 0) {
			this.$store.dispatch('daemonClient/removeMessage', this.msgId);
			this.$store.commit('spinner/HIDE');
			this.$toast.success(
				this.$t(
					'iqrfnet.standard.table.messages.enumNowFailed',
					{error: response.rsp.errorStr},
				).toString()
			);
			return;
		}
		const process = response.rsp;
		if (process.percentage === 100) {
			this.$store.dispatch('daemonClient/removeMessage', this.msgId);
			this.$store.commit('spinner/HIDE');
			this.$toast.success(
				this.$t('iqrfnet.standard.table.messages.enumNowSuccess').toString()
			);
			this.getDevices();
			return;
		}
		this.$store.commit('spinner/UPDATE_TEXT',
			this.$t(
				'iqrfnet.standard.table.messages.enumNowProgress',
				{
					progress: process.percentage,
					phase: process.enumPhase,
					current: process.step,
					total: process.steps,
				}
			).toString()
		);
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
		InfoService.nodes(11000, this.$t('iqrfnet.standard.table.messages.device.fetchTimeout'), () => this.msgId = null)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles GetNodes Daemon API response
	 * @param response Daemon API response
	 */
	private handleGetDevices(response): void {
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		if (response.status !== 0) {
			this.$store.dispatch('spinner/hide');
			this.$toast.error(
				this.$t('iqrfnet.standard.table.messages.device.fetchFailed').toString()
			);
			return;
		}
		const devices: Array<StandardDevice> = [];
		response.rsp.nodes.forEach((device: IInfoNode) => {
			devices.push(new StandardDevice(device.nAdr, device.mid, device.hwpid, device.hwpidVer, device.dpaVer, device.osBuild, device.disc));
		});
		this.auxDevices = devices;
		if (this.auxDevices.length > 0) {
			this.getBinouts();
		} else {
			this.$store.dispatch('spinner/hide');
			this.devices = [];
		}
	}

	/**
	 * Retrieves information about binary output devices stored in database
	 */
	private getBinouts(): void {
		this.$store.commit(
			'spinner/UPDATE_TEXT',
			this.$t('iqrfnet.standard.table.messages.binout.fetch').toString()
		);
		InfoService.binouts(11000, this.$t('iqrfnet.standard.table.messages.binout.fetchTimeout'), () => this.msgId = null)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles GetBinaryOutputs Daemon API response
	 * @param response Daemon API response
	 */
	private handleGetBinouts(response): void {
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		if (response.status !== 0) {
			this.$store.commit('spinner/HIDE');
			this.$toast.error(
				this.$t('iqrfnet.standard.table.messages.binout.fetchFailed').toString()
			);
			return;
		}
		response.rsp.binOutDevices.forEach((device: IInfoBinout) => {
			const idx = this.getDeviceIndex(device.nAdr);
			if (idx !== -1) {
				this.auxDevices[idx]?.setBinouts(device.binOuts);
			}
		});
		this.getDalis();
	}

	/**
	 * Retrieves information about dali devices stored in database
	 */
	private getDalis(): void {
		this.$store.commit(
			'spinner/UPDATE_TEXT',
			this.$t('iqrfnet.standard.table.messages.dali.fetch').toString()
		);
		InfoService.dalis(11000, this.$t('iqrfnet.standard.table.messages.dali.fetchTimeout'), () => this.msgId = null)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles GetDalis Daemon API response
	 * @param response Daemon API response
	 */
	private handleGetDalis(response): void {
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		if (response.status !== 0) {
			this.$store.commit('spinner/HIDE');
			this.$toast.error(
				this.$t('iqrfnet.standard.table.messages.dali.fetchFailed').toString()
			);
			return;
		}
		response.rsp.daliDevices.forEach((device: IInfoDevice) => {
			const idx = this.getDeviceIndex(device.nAdr);
			if (idx !== -1) {
				this.auxDevices[idx]?.setDali(true);
			}
		});
		this.getLights();
	}

	/**
	 * Retrieves information about light devices stored in database
	 */
	private getLights(): void {
		this.$store.commit(
			'spinner/UPDATE_TEXT',
			this.$t('iqrfnet.standard.table.messages.light.fetch').toString()
		);
		InfoService.lights(11000, this.$t('iqrfnet.standard.table.messages.light.fetchTimeout'), () => this.msgId = null)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles GetLights Daemon API response
	 * @param response Daemon API response
	 */
	private handleGetLights(response): void {
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		if (response.status !== 0) {
			this.$store.commit('spinner/HIDE');
			this.$toast.error(
				this.$t('iqrfnet.standard.table.messages.light.fetchFailed').toString()
			);
			return;
		}
		response.rsp.lightDevices.forEach((device: IInfoLight) => {
			const idx = this.getDeviceIndex(device.nAdr);
			if (idx !== -1) {
				this.auxDevices[idx]?.setLights(device.lights);
			}
		});
		this.getSensors();
	}

	/**
	 * Retrieves information about sensor devices stored in database
	 */
	private getSensors(): void {
		this.$store.commit(
			'spinner/UPDATE_TEXT',
			this.$t('iqrfnet.standard.table.messages.sensor.fetch').toString()
		);
		InfoService.sensors(11000, this.$t('iqrfnet.standard.table.messages.sensor.fetchTimeout'), () => this.msgId = null)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles GetSensors Daemon API response
	 * @param response Daemon API response
	 */
	private async handleGetSensors(response): Promise<void> {
		await this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		if (response.status !== 0) {
			this.$store.commit('spinner/HIDE');
			this.$toast.error(
				this.$t('iqrfnet.standard.table.messages.sensor.fetchFailed').toString()
			);
			return;
		}
		response.rsp.sensorDevices.forEach((device: IInfoSensor) => {
			const idx = this.getDeviceIndex(device.nAdr);
			if (idx !== -1) {
				this.auxDevices[idx]?.setSensors(device.sensors);
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
		const osVersions = new Map();
		for (const auxDevice of this.auxDevices) {
			const osBuild = auxDevice.getOsBuild();
			if (!osVersions.has(osBuild)) {
				await DpaService.getVersions(osBuild)
					.then((versions: OsDpaVersion[]) => {
						if (versions.length === 0) {
							return;
						}
						osVersions.set(osBuild, versions[0].getOsVersion());
					})
					.catch(() => {
					// IQRF OS not found in repository, ignore
					});
			}
			auxDevice.setOsVersion(osVersions.get(osBuild));
			const hwpid = auxDevice.getHwpid();
			if (hwpids.has(hwpid)) {
				auxDevice.setProduct(hwpids.get(hwpid));
				continue;
			}
			if ((hwpid & 0xf) === 0xf) {
				continue;
			}
			await ProductService.get(hwpid)
				.then((response: AxiosResponse) => {
					hwpids.set(hwpid, response.data);
					auxDevice.setProduct(hwpids.get(hwpid));
				})
				.catch(() => {
					// Device not found in repository, ignore
				});
		}
		this.devices = this.auxDevices;
	}

	/**
	 * Pings devices in network to check which devices are online
	 */
	private pingDevices(): void {
		const nodes: Array<number> = this.auxDevices.map((device: StandardDevice) => (device.getAddress())).filter((addr: number) => addr > 0);
		if (nodes.length === 0) {
			return;
		}
		this.$store.dispatch('spinner/show', {timeout: 100000});
		this.$store.commit(
			'spinner/UPDATE_TEXT',
			this.$t('iqrfnet.standard.table.messages.ping.fetch').toString()
		);
		const options = new DaemonMessageOptions(null, 100000, this.$t('iqrfnet.standard.table.messages.ping.fetchFailed'));
		IqrfNetService.pingSelective(nodes, options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles Frc_Send Daemon API response
	 * @param response Daemon API response
	 */
	private handlePingDevices(response): void {
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		if (response.status !== 0) {
			this.$store.dispatch('spinner/hide');
			this.$toast.error(
				this.$t('iqrfnet.standard.table.messages.ping.fetchFailed').toString()
			);
			return;
		}
		const map = response.rsp.result.frcData.slice(0, 30);
		const addrs = this.devices.map((device: StandardDevice) => {return device.getAddress();});
		map.forEach((byte: number, idx: number) => {
			if (byte === 0) {
				return;
			}
			const bitString = byte.toString(2).padStart(8, '0');
			for (let i = 0; i < 8; i++) {
				const addr = idx * 8 + i;
				if (addrs.includes(addr)) {
					this.devices[addrs.indexOf(addr)].setOnline((bitString[(7 - i)] === '1'));
				}
			}
		});
		this.$store.dispatch('spinner/hide');
	}

	/**
	 * Handles message error response from Daemon API
	 * @param response Daemon API response
	 */
	private handleMessageError(response): void {
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
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
