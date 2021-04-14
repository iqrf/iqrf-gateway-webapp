<template>
	<div>
		<CCard>
			<CCardHeader class='datatable-header'>
				<div>
					{{ $t('iqrfnet.standard.grid.title') }}
				</div>
				<div>
					<CButton
						color='primary'
						size='sm'
						@click='enumerateNetwork'
					>
						<CIcon :content='icons.enumerate' size='sm' />
						{{ $t('iqrfnet.standard.grid.actions.enumerate') }}
					</CButton> <CButton
						color='primary'
						size='sm'
						@click='getDevices'
					>
						<CIcon :content='icons.refresh' size='sm' />
						{{ $t('iqrfnet.standard.grid.actions.refresh') }}
					</CButton>
				</div>
			</CCardHeader>
			<CCardBody>
				<div class='datatable-legend'>
					<div>
						<CIcon 
							class='text-info' 
							:content='icons.coordinator'
							size='lg'
						/>
						{{ $t('forms.fields.coordinator') }}
					</div>
					<div>
						<CIcon 
							class='text-danger'
							:content='icons.unbonded'
							size='lg'
						/>
						{{ $t('iqrfnet.networkManager.devicesInfo.icons.unbonded') }}
					</div>
					<div>
						<CIcon 
							class='text-info'
							:content='icons.bonded'
							size='lg'
						/>
						{{ $t('iqrfnet.networkManager.devicesInfo.icons.bonded') }}
					</div>
					<div>
						<CIcon 
							class='text-info'
							:content='icons.discovered'
							size='lg'
						/>
						{{ $t('iqrfnet.networkManager.devicesInfo.icons.discovered') }}
					</div>
					<div>
						<CIcon 
							class='text-success'
							:content='icons.bonded'
							size='lg'
						/>
						{{ $t('iqrfnet.networkManager.devicesInfo.icons.bondedOnline') }}
					</div>
					<div>
						<CIcon 
							class='text-success'
							:content='icons.discovered'
							size='lg'
						/>
						{{ $t('iqrfnet.networkManager.devicesInfo.icons.discoveredOnline') }}
					</div>
					<div>
						<CIcon 
							class='text-info'
							:content='icons.info'
							size='lg'
						/>
						{{ $t('iqrfnet.standard.grid.info') }}
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
						{{ $t('iqrfnet.standard.grid.table.noDevices') }}
					</template>
					<template #address='{item}'>
						<td>
							{{ item.getAddress() }}
						</td>
					</template>
					<template #product='{item}'>
						<td>
							{{ item.getProductName() }}
						</td>
					</template>
					<template #mid='{item}'>
						<td>
							{{ item.getMid() }}
						</td>
					</template>
					<template #hwpid='{item}'>
						<td>
							{{ item.getHwpid() }}
						</td>
					</template>
					<template #status='{item}'>
						<td>
							<CIcon
								v-c-tooltip='{
									content: item.getGeneralDetails(),
									placement: "left",
								}'
								size='xl'
								:class='item.getIconColor()'
								:content='item.getIcon()'
							/>
						</td>
					</template>
					<template #sensor='{item}'>
						<td>
							<CIcon
								v-c-tooltip='{
									content: item.getSensorDetails(),
									placement: "left",
								}'
								size='xl'
								:class='item.hasSensor() ? "text-info" : "text-danger"'
								:content='item.hasSensor() ? icons.info : icons.unbonded'
							/>
						</td>
					</template>
					<template #binout='{item}'>
						<td>
							<CIcon
								v-c-tooltip='{
									content: item.getBinoutDetails(),
									placement: "left",
								}'
								size='xl'
								:class='item.hasBinout() ? "text-info" : "text-danger"'
								:content='item.hasBinout() ? icons.info : icons.unbonded'
							/>
						</td>
					</template>
					<template #light='{item}'>
						<td>
							<CIcon
								v-c-tooltip='{
									content: item.getLightDetails(),
									placement: "left",
								}'
								size='xl'
								:class='item.hasLight() ? "text-info" : "text-danger"'
								:content='item.hasLight() ? icons.info : icons.unbonded'
							/>
						</td>
					</template>
					<template #dali='{item}'>
						<td>
							<CIcon
								v-c-tooltip='{
									content: item.getDaliDetails(),
									placement: "left",
								}'
								size='xl'
								:class='item.hasDali() ? "text-info" : "text-danger"'
								:content='item.hasDali() ? icons.info : icons.unbonded'
							/>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CDataTable, CIcon, CProgress, CProgressBar} from '@coreui/vue/src';

import {cilCheckAlt, cilHome, cilInfo, cilReload, cilSignalCellular4, cilSpreadsheet, cilX} from '@coreui/icons';
import {EnumerateCommand} from '../../enums/IqrfNet/info';
import {WebSocketOptions} from '../../store/modules/webSocketClient.module';

import StandardDevice from '../../iqrfNet/StandardDevice';
import InfoService from '../../services/DaemonApi/InfoService';
import IqrfNetService from '../../services/IqrfNetService';
import ProductService from '../../services/IqrfRepository/ProductService';

import {AxiosResponse} from 'axios';
import {Dictionary} from 'vue-router/types/router';
import {IField} from '../../interfaces/coreui';
import {IInfoBinout, IInfoDevice, IInfoLight, IInfoNode, IInfoSensor} from '../../interfaces/iqrfInfo';
import {IProduct} from '../../interfaces/repository';
import {MutationPayload} from 'vuex';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CDataTable,
		CIcon,
		CProgress,
		CProgressBar,
	},
})

/**
 * Standard devices component
 */
export default class StandardDevices extends Vue {

	/**
	 * @var {Array<standardDevice>} devices Auxiliary array of devices before the final grid is rendered
	 */
	private auxDevices: Array<StandardDevice> = []

	/**
	 * @var {Array<standardDevice>} devices Standard devices for datatable
	 */
	private devices: Array<StandardDevice> = []

	/**
	 * @var {string|null} msgId Daemon API message ID
	 */
	private msgId: string|null = null

	/**
	 * @constant {Dictionary<Array<string>>} icons Dictionary of CoreUI icons
	 */
	private icons: Dictionary<Array<string>> = {
		coordinator: cilHome,
		bonded: cilCheckAlt,
		discovered: cilSignalCellular4,
		unbonded: cilX,
		info: cilInfo,
		enumerate: cilSpreadsheet,
		refresh: cilReload,
	}

	/**
	 * @constant {Array<IField>} fields Array of CoreUI data table fields
	 */
	private fields: Array<IField> = [
		{
			key: 'address',
			label: this.$t('iqrfnet.standard.grid.table.address'),
		},
		{
			key: 'product',
			label: this.$t('iqrfnet.standard.grid.table.product'),
		},
		{
			key: 'mid',
			label: this.$t('iqrfnet.standard.grid.table.mid'),
		},
		{
			key: 'hwpid',
			label: this.$t('iqrfnet.standard.grid.table.hwpid'),
		},
		{
			key: 'status',
			label: this.$t('iqrfnet.standard.grid.table.status'),
			filter: false,
			sorter: false,
		},
		{
			key: 'sensor',
			label: this.$t('iqrfnet.standard.grid.table.sensor'),
			filter: false,
			sorter: false,
		},
		{
			key: 'binout',
			label: this.$t('iqrfnet.standard.grid.table.binout'),
			filter: false,
			sorter: false,
		},
		{
			key: 'light',
			label: this.$t('iqrfnet.standard.grid.table.light'),
			filter: false,
			sorter: false,
		},
		{
			key: 'dali',
			label: this.$t('iqrfnet.standard.grid.table.dali'),
			filter: false,
			sorter: false,
		},
	]

	/**
	 * Websocket store unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;}

	/**
	 * Subscribes a mutation handler to websocket store
	 */
	created(): void {
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type !== 'SOCKET_ONMESSAGE') {
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
			} else if (mutation.payload.mType === 'iqrfEmbedFrc_Send') {
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
		this.$store.dispatch('removeMessage', this.msgId);
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
			//TODO: error handling
			return;
		}
		let process = response.rsp;
		if (process.percentage === 100) {
			this.$store.dispatch('removeMessage', this.msgId);
			this.$store.commit('spinner/HIDE');
			this.$toast.success(
				this.$t('iqrfnet.standard.grid.messages.enumNowSuccess').toString()
			);
			return;
		}
		this.$store.commit('spinner/UPDATE_TEXT',
			this.$t(
				'iqrfnet.standard.grid.messages.enumNowProgress',
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
		this.$store.commit('spinner/SHOW');
		this.$store.commit(
			'spinner/UPDATE_TEXT',
			this.$t('iqrfnet.standard.grid.messages.device.fetch').toString()
		);
		InfoService.nodes()
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles GetNodes Daemon API response
	 * @param response Daemon API response
	 */
	private handleGetDevices(response): void {
		this.$store.dispatch('removeMessage', this.msgId);
		if (response.status !== 0) {
			this.$store.commit('spinner/HIDE');
			this.$toast.error(
				this.$t('iqrfnet.standard.grid.messages.device.fetchFailed').toString()
			);
			return;
		}
		let devices: Array<StandardDevice> = [];
		response.rsp.nodes.forEach((device: IInfoNode) => {
			devices[device.nAdr] = new StandardDevice(device.nAdr, device.mid, device.hwpid, device.hwpidVer, device.dpaVer, device.osBuild, device.disc);
		});
		this.auxDevices = devices;
		this.getBinouts();
	}

	/**
	 * Retrieves information about binary output devices stored in database
	 */
	private getBinouts(): void {
		this.$store.commit(
			'spinner/UPDATE_TEXT',
			this.$t('iqrfnet.standard.grid.messages.binout.fetch').toString()
		);
		InfoService.binouts()
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles GetBinaryOutputs Daemon API response
	 * @param response Daemon API response
	 */
	private handleGetBinouts(response): void {
		this.$store.dispatch('removeMessage', this.msgId);
		if (response.status !== 0) {
			this.$store.commit('spinner/HIDE');
			this.$toast.error(
				this.$t('iqrfnet.standard.grid.messages.binout.fetchFailed').toString()
			);
			return;
		}
		response.rsp.binOutDevices.forEach((device: IInfoBinout) => {
			this.auxDevices[device.nAdr]?.setBinouts(device.binOuts);
		});
		this.getDalis();
	}

	/**
	 * Retrieves information about dali devices stored in database
	 */
	private getDalis(): void {
		this.$store.commit(
			'spinner/UPDATE_TEXT',
			this.$t('iqrfnet.standard.grid.messages.dali.fetch').toString()
		);
		InfoService.dalis()
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles GetDalis Daemon API response
	 * @param response Daemon API response
	 */
	private handleGetDalis(response): void {
		this.$store.dispatch('removeMessage', this.msgId);
		if (response.status !== 0) {
			this.$store.commit('spinner/HIDE');
			this.$toast.error(
				this.$t('iqrfnet.standard.grid.messages.dali.fetchFailed').toString()
			);
			return;
		}
		response.rsp.daliDevices.forEach((device: IInfoDevice) => {
			this.auxDevices[device.nAdr]?.setDali(true);
		});
		this.getLights();
	}

	/**
	 * Retrieves information about light devices stored in database
	 */
	private getLights(): void {
		this.$store.commit(
			'spinner/UPDATE_TEXT',
			this.$t('iqrfnet.standard.grid.messages.light.fetch').toString()
		);
		InfoService.lights()
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles GetLights Daemon API response
	 * @param response Daemon API response
	 */
	private handleGetLights(response): void {
		this.$store.dispatch('removeMessage', this.msgId);
		if (response.status !== 0) {
			this.$store.commit('spinner/HIDE');
			this.$toast.error(
				this.$t('iqrfnet.standard.grid.messages.light.fetchFailed').toString()
			);
			return;
		}
		response.rsp.lightDevices.forEach((device: IInfoLight) => {
			this.auxDevices[device.nAdr]?.setLights(device.lights);
		});
		this.getSensors();
	}

	/**
	 * Retrieves information about sensor devices stored in database
	 */
	private getSensors(): void {
		this.$store.commit(
			'spinner/UPDATE_TEXT',
			this.$t('iqrfnet.standard.grid.messages.sensor.fetch').toString()
		);
		InfoService.sensors()
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles GetSensors Daemon API response
	 * @param response Daemon API response
	 */
	private handleGetSensors(response): void {
		this.$store.dispatch('removeMessage', this.msgId);
		if (response.status !== 0) {
			this.$store.commit('spinner/HIDE');
			this.$toast.error(
				this.$t('iqrfnet.standard.grid.messages.sensor.fetchFailed').toString()
			);
			return;
		}
		response.rsp.sensorDevices.forEach((device: IInfoSensor) => {
			this.auxDevices[device.nAdr]?.setSensors(device.sensors);
		});
		this.filterStandards();
		this.pingDevices();
	}

	/**
	 * Filters devices that do not implement any standards from the array
	 */
	private async filterStandards(): Promise<void> {
		for (let i = 0; i < this.auxDevices.length; i++) {
			if (!this.auxDevices[i].hasStandard()) {
				this.auxDevices.splice(i, 1);
			}
		}
		for (let i = 0; i < this.auxDevices.length; i++) {
			await ProductService.get(this.auxDevices[i].getHwpid())
				.then((response: AxiosResponse) => {
					const product: IProduct = response.data;
					this.auxDevices[i].setProductName(product.name);
				});
			//todo error handling
		}
		this.devices = this.auxDevices;
		this.auxDevices = [];
	}

	/**
	 * Pings devices in network to check which devices are online
	 */
	private pingDevices(): void {
		if (!this.$store.getters['spinner/isEnabled']) {
			this.$store.commit('spinner/SHOW');
		}
		this.$store.commit(
			'spinner/UPDATE_TEXT',
			this.$t('iqrfnet.standard.grid.messages.ping.fetch').toString()
		);
		const options = new WebSocketOptions(null, 90000, 'iqrfnet.standard.grid.messages.ping.fetchFailed');
		IqrfNetService.ping(options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles Frc_Send Daemon API response
	 * @param response Daemon API response
	 */
	private handlePingDevices(response): void {
		this.$store.dispatch('removeMessage', this.msgId);
		if (response.status !== 0) {
			this.$store.commit('spinner/HIDE');
			this.$toast.error(
				this.$t('iqrfnet.standard.grid.messages.ping.fetchFailed').toString()
			);
			return;
		}
		const map = response.rsp.result.frcData.slice(0, 30);
		const addrs = this.devices.map((device: StandardDevice) => {return device.getAddress();});
		map.forEach((byte: number, idx: number) => {
			const bitString = byte.toString(2).padStart(8, '0');
			for (let i = 0; i < 8; i++) {
				let addr = idx * 8 + i;
				if (addrs.includes(addr)) {
					this.devices[addrs.indexOf(addr)].setOnline((bitString[(7 - i)] === '1'));
				}
			}
		});
		this.$store.commit('spinner/HIDE');
	}

	/**
	 * Handles message error response from Daemon API
	 * @param response Daemon API response
	 */
	private handleMessageError(response): void {
		this.$store.dispatch('removeMessage', this.msgId);
		this.$store.commit('spinner/HIDE');
		this.$toast.error(
			this.$t('messageError', {error: response.rsp.errorStr}).toString()
		);
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