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
	<div>
		<CCard>
			<CCardHeader class='datatable-header'>
				<div>
					{{ $t('iqrfnet.standard.table.title') }}
				</div>
				<div>
					<CButton
						class='mr-1'
						color='primary'
						size='sm'
						@click='enumerateNetwork'
					>
						<CIcon :content='cilSpreadsheet' size='sm' />
						<span class='d-none d-lg-inline'>
							{{ $t('iqrfnet.standard.table.actions.enumerate') }}
						</span>
					</CButton>
					<CButton
						class='mr-1'
						color='primary'
						size='sm'
						@click='getDevices'
					>
						<CIcon :content='cilSync' size='sm' />
						<span class='d-none d-lg-inline'>
							{{ $t('iqrfnet.standard.table.actions.refresh') }}
						</span>
					</CButton>
					<DatabaseResetModal
						@reset='devices = []; getDevices()'
					/>
				</div>
			</CCardHeader>
			<CCardBody>
				<div class='datatable-legend'>
					<div>
						<CIcon
							class='text-info'
							:content='cilHome'
							size='lg'
						/>
						{{ $t('forms.fields.coordinator') }}
					</div>
					<div>
						<CIcon
							class='text-info'
							:content='cilCheckAlt'
							size='lg'
						/>
						{{ $t('iqrfnet.networkManager.devicesInfo.icons.bonded') }}
					</div>
					<div>
						<CIcon
							class='text-info'
							:content='cilSignalCellular4'
							size='lg'
						/>
						{{ $t('iqrfnet.networkManager.devicesInfo.icons.discovered') }}
					</div>
					<div>
						<CIcon
							class='text-success'
							:content='cilCheckAlt'
							size='lg'
						/>
						{{ $t('iqrfnet.networkManager.devicesInfo.icons.bondedOnline') }}
					</div>
					<div>
						<CIcon
							class='text-success'
							:content='cilSignalCellular4'
							size='lg'
						/>
						{{ $t('iqrfnet.networkManager.devicesInfo.icons.discoveredOnline') }}
					</div>
					<div>
						<CIcon
							class='text-success'
							:content='cilCheckCircle'
							size='lg'
						/>
						{{ $t('iqrfnet.standard.table.supported') }}
					</div>
					<div>
						<CIcon
							class='text-danger'
							:content='cilXCircle'
							size='lg'
						/>
						{{ $t('iqrfnet.standard.table.unsupported') }}
					</div>
				</div>
				<CDataTable
					:fields='fields'
					:items='devices'
					:column-filter='true'
					:pagination='true'
					:items-per-page='10'
					:sorter='{external: false, resetable: true}'
				>
					<template #no-items-view='{}'>
						{{ $t('iqrfnet.standard.table.fields.noDevices') }}
					</template>
					<template #address='{item}'>
						<td>
							<router-link :to='"/iqrfnet/enumeration/" + item.getAddress()'>
								{{ item.getAddress() }}
							</router-link>
						</td>
					</template>
					<template #product='{item}'>
						<td>
							{{ item.getProductName() }}
						</td>
					</template>
					<template #os='{item}'>
						<td>
							{{ item.getOs() }}
						</td>
					</template>
					<template #dpa='{item}'>
						<td>
							{{ item.getDpa() }}
						</td>
					</template>
					<template #status='{item}'>
						<td>
							<CIcon
								size='xl'
								:class='item.getIconColor()'
								:content='item.getIcon()'
							/>
						</td>
					</template>
					<template #sensor='{item}'>
						<td>
							<CIcon
								size='xl'
								:class='item.hasSensor() ? "text-success" : "text-danger"'
								:content='item.getSensorIcon()'
							/>
						</td>
					</template>
					<template #binout='{item}'>
						<td>
							<CIcon
								size='xl'
								:class='item.hasBinout() ? "text-success" : "text-danger"'
								:content='item.getBinoutIcon()'
							/>
						</td>
					</template>
					<template #light='{item}'>
						<td>
							<CIcon
								size='xl'
								:class='item.hasLight() ? "text-success" : "text-danger"'
								:content='item.getLightIcon()'
							/>
						</td>
					</template>
					<template #dali='{item}'>
						<td>
							<CIcon
								size='xl'
								:class='item.hasDali() ? "text-success" : "text-danger"'
								:content='item.getDaliIcon()'
							/>
						</td>
					</template>
					<template #show_details='{item}'>
						<td class='py-2'>
							<CButton
								color='info'
								size='sm'
								@click='item.showDetails = !item.showDetails'
							>
								<CIcon :content='cilInfo' />
							</CButton>
						</td>
					</template>
					<template #details='{item}'>
						<CCollapse :show='item.showDetails'>
							<CCardBody>
								<CRow align-vertical='center'>
									<CCol sm='auto'>
										<CMedia
											:aside-image-props='{
												src: item.getImg(),
												block: true,
												width: `150px`,
												height: `150px`,
											}'
										/>
									</CCol>
									<CCol>
										<div class='datatable-expansion-table'>
											<table>
												<caption>
													<strong>{{ $t('iqrfnet.standard.table.info') }}</strong>
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
													<td>{{ item.getHwpid() + ' [' + item.getHwpidHex() + ']' }}</td>
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
														{{ item.getMid() + ' [' + item.getMidHex() + ']' }}
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
									</CCol>
								</CRow>
							</CCardBody>
						</CCollapse>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CCollapse, CDataTable, CIcon, CMedia} from '@coreui/vue/src';
import DatabaseResetModal from '@/components/IqrfNet/StandardManager/DatabaseResetModal.vue';

import {cilCheckAlt, cilCheckCircle, cilHome, cilInfo, cilSignalCellular4, cilSpreadsheet, cilSync, cilXCircle} from '@coreui/icons';
import {EnumerateCommand} from '@/enums/IqrfNet/info';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

import StandardDevice from '@/iqrfNet/StandardDevice';
import InfoService from '@/services/DaemonApi/InfoService';
import IqrfNetService from '@/services/IqrfNetService';
import ProductService from '@/services/IqrfRepository/ProductService';

import {AxiosResponse} from 'axios';
import {IField} from '@/interfaces/coreui';
import {IInfoBinout, IInfoDevice, IInfoLight, IInfoNode, IInfoSensor} from '@/interfaces/iqrfInfo';
import {MutationPayload} from 'vuex';
import DpaService, {OsDpaVersion} from '@/services/IqrfRepository/OsDpaService';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CCollapse,
		CDataTable,
		CIcon,
		CMedia,
		DatabaseResetModal,
	},
	data: () => ({
		cilCheckAlt,
		cilCheckCircle,
		cilHome,
		cilInfo,
		cilSignalCellular4,
		cilSpreadsheet,
		cilSync,
		cilXCircle,
	}),
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
	 * @constant {Array<IField>} fields Array of CoreUI data table fields
	 */
	private fields: Array<IField> = [
		{
			key: 'address',
			label: this.$t('iqrfnet.standard.table.fields.address'),
		},
		{
			key: 'product',
			label: this.$t('iqrfnet.standard.table.fields.product'),
		},
		{
			key: 'os',
			label: this.$t('iqrfnet.standard.table.fields.os'),
		},
		{
			key: 'dpa',
			label: this.$t('iqrfnet.standard.table.fields.dpa'),
		},
		{
			key: 'status',
			label: this.$t('iqrfnet.standard.table.fields.status'),
			filter: false,
			sorter: false,
		},
		{
			key: 'sensor',
			label: this.$t('iqrfnet.standard.table.fields.sensor'),
			filter: false,
			sorter: false,
		},
		{
			key: 'binout',
			label: this.$t('iqrfnet.standard.table.fields.binout'),
			filter: false,
			sorter: false,
		},
		{
			key: 'light',
			label: this.$t('iqrfnet.standard.table.fields.light'),
			filter: false,
			sorter: false,
		},
		{
			key: 'dali',
			label: this.$t('iqrfnet.standard.table.fields.dali'),
			filter: false,
			sorter: false,
		},
		{
			key: 'show_details',
			label: '',
			_style: 'width: 1%',
			sorter: false,
			filter: false,
		}
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
		const nodes: Array<number> = this.auxDevices.map((device: StandardDevice) => (device.getAddress()));
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
