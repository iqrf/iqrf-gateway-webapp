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
						@click='enumModal.start()'
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
						@click='getDevices()'
					>
						<CIcon :content='cilSync' size='sm' />
						<span class='d-none d-lg-inline'>
							{{ $t('iqrfnet.standard.table.actions.refresh') }}
						</span>
					</CButton>
					<CButton
						color='danger'
						size='sm'
						@click='resetModal.open()'
					>
						<CIcon :content='cilReload' size='sm' />
						<span class='d-none d-lg-inline'>
							{{ $t('iqrfnet.standard.table.actions.reset') }}
						</span>
					</CButton>
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
					:items='filteredDevices'
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
					<template #address-filter>
						<input
							class='form-control form-control-sm'
							:value='filters.address'
							@input='e => filters.address = e.target.value'
						>
					</template>
					<template #product='{item}'>
						<td>
							{{ item.getProductName() }}
						</td>
					</template>
					<template #product-filter>
						<input
							class='form-control form-control-sm'
							:value='filters.product'
							@input='e => filters.product = e.target.value'
						>
					</template>
					<template #os='{item}'>
						<td>
							{{ item.getOs() }}
						</td>
					</template>
					<template #os-filter>
						<input
							class='form-control form-control-sm'
							:value='filters.os'
							@input='e => filters.os = e.target.value'
						>
					</template>
					<template #dpa='{item}'>
						<td>
							{{ item.getDpa() }}
						</td>
					</template>
					<template #dpa-filter>
						<input
							class='form-control form-control-sm'
							:value='filters.dpa'
							@input='e => filters.dpa = e.target.value'
						>
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
													<th>{{ $t('iqrfnet.standard.table.sensor.value') }}</th>
													<th>{{ $t('iqrfnet.standard.table.sensor.updated') }}</th>
												</tr>
												<tr v-for='(sensor, i) of item.getSensors()' :key='i'>
													<td>{{ sensor.name }}</td>
													<td>{{ sensor.type }}</td>
													<td>{{ sensor.index }}</td>
													<td>{{ formatSensorValue(sensor.value, sensor.unit) }}</td>
													<td>{{ sensor.updated ?? $t('forms.notAvailable') }}</td>
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
		<EnumerationModal
			ref='enumModal'
			@finished='getDevices'
		/>
		<DatabaseResetModal
			ref='resetModal'
			@reset='devices = []; getDevices()'
		/>
	</div>
</template>

<script lang='ts'>
import {Component, Ref, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CCollapse, CDataTable, CIcon, CMedia} from '@coreui/vue/src';
import DatabaseResetModal from '@/components/IqrfNet/StandardManager/DatabaseResetModal.vue';
import EnumerationModal from '@/components/IqrfNet/StandardManager/EnumerationModal.vue';

import {cilCheckAlt, cilCheckCircle, cilHome, cilInfo, cilReload, cilSignalCellular4, cilSpreadsheet, cilSync, cilXCircle} from '@coreui/icons';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

import StandardDevice from '@/entities/StandardDevice';
import DbService from '@/services/DaemonApi/DbService';

import {IField} from '@/interfaces/Coreui';
import {MutationPayload} from 'vuex';
import { IqrfDbBo, IqrfDbDeviceFull, IqrfDbSensor } from '@/interfaces/DaemonApi/IqrfDb';
import { Product } from '@iqrf/iqrf-repository-client/types';
import IqmeshNetworkService from '@/services/DaemonApi/IqmeshNetworkService';
import ProductService from '@/services/IqrfRepository/ProductService';

interface StandardDevicesFilters {
	address: string;
	product: string;
	os: string;
	dpa: string;
}

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
		EnumerationModal,
	},
	data: () => ({
		cilCheckAlt,
		cilCheckCircle,
		cilHome,
		cilInfo,
		cilReload,
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
	 * @property {EnumerationModal} enumModal Enumeration component
	 */
	@Ref('enumModal') readonly enumModal!: EnumerationModal;

	/**
	 * @property {DatabaseResetModal} resetModal Database reset component
	 */
	@Ref('resetModal') readonly resetModal!: DatabaseResetModal;

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
			key: 'show_details',
			label: '',
			_style: 'width: 1%',
			sorter: false,
			filter: false,
		}
	];

	/**
	 * @var {StandardDeviceFilters} filters Filters
	 */
	private filters: StandardDevicesFilters = {
		address: '',
		product: '',
		os: '',
		dpa: '',
	};

	/**
	 * @var {Array<StandardDevice>} filteredDevices Filtered devices
	 */
	get filteredDevices(): Array<StandardDevice> {
		return this.devices.filter((item: StandardDevice) => {
			if (this.filters.address.length > 0 && item.getAddress().toString().toUpperCase() !== this.filters.address.toUpperCase()) {
				return false;
			}
			if (this.filters.product.length > 0 && !item.getProductName().toUpperCase().includes(this.filters.product.toUpperCase())) {
				return false;
			}
			if (this.filters.os.length > 0 && !item.getOs().toUpperCase().includes(this.filters.os.toUpperCase())) {
				return false;
			}
			if (this.filters.dpa.length > 0 && !item.getDpa().toUpperCase().includes(this.filters.dpa.toUpperCase())) {
				return false;
			}
			return true;
		});
	}

	/**
	 * Websocket store unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * Subscribes a mutation handler to websocket store
	 */
	async created(): Promise<void> {
		
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
		response.rsp.devices.forEach((device: IqrfDbDeviceFull) => {
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
		response.rsp.binoutDevices.forEach((device: IqrfDbBo) => {
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
		response.rsp.sensorDevices.forEach((device: IqrfDbSensor) => {
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
			await ProductService.get(hwpid)
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

	/**
	 * Formats measured sensor value to string
	 * @param {number|number[]|null} value Measured value
	 * @param {string|null} unit Unit of measurement
	 */
	private formatSensorValue(value: number|number[]|null, unit: string|null): string {
		if (value === null) {
			return this.$t('forms.notAvailable').toString();
		}
		if (Array.isArray(value)) {
			return value.toString();
		}
		if (unit === null || unit.length === 0) {
			return value.toString();
		}
		return `${value} ${unit}`;
	}
}
</script>
