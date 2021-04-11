<template>
	<div>
		<CCard class='card-margin-bottom'>
			<CCardHeader>
				{{ $t('iqrfnet.standard.grid.title') }}
				<CButton
					color='primary'
					@click='enumerateNetwork'
				>
					{{ $t('iqrfnet.standard.grid.actions.enumerate') }}
				</CButton>
			</CCardHeader>
			<CCardBody>
				<table class='table text-center'>
					<tbody>
						<tr>
							<td class='table-toprow'>
								<CIcon class='text-info' :content='icons.coordinator' />
								{{ $t('forms.fields.coordinator') }}
							</td>
							<td class='table-toprow'>
								<CIcon class='text-danger' :content='icons.unbonded' />
								{{ $t('iqrfnet.networkManager.devicesInfo.icons.unbonded') }}
							</td>
						</tr>
						<tr>
							<td>
								<CIcon class='text-info' :content='icons.bonded' />
								{{ $t('iqrfnet.networkManager.devicesInfo.icons.bonded') }}
							</td>
							<td>
								<CIcon class='text-info' :content='icons.discovered' />
								{{ $t('iqrfnet.networkManager.devicesInfo.icons.discovered') }}
							</td>
						</tr>
						<tr>
							<td>
								<CIcon class='text-success' :content='icons.bonded' />
								{{ $t('iqrfnet.networkManager.devicesInfo.icons.bondedOnline') }}
							</td>
							<td>
								<CIcon class='text-success' :content='icons.discovered' />
								{{ $t('iqrfnet.networkManager.devicesInfo.icons.discoveredOnline') }}
							</td>
						</tr>
						<tr>
							<td>
								<CProgress
									size='xs'
									color='info'
									class='progress-legend'
									:value='100'
								/>
								<span>
									{{ $t('iqrfnet.standard.binaryOutput.title') }}
								</span>
							</td>
							<td>
								<CProgress
									size='xs'
									color='success'
									class='progress-legend'
									:value='100'
								/>
								<span>
									{{ $t('iqrfnet.standard.dali.title') }}
								</span>
							</td>
						</tr>
						<tr>
							<td>
								<CProgress
									size='xs'
									color='warning'
									class='progress-legend'
									:value='100'
								/>
								<span>
									{{ $t('iqrfnet.standard.light.title') }}
								</span>
							</td>
							<td>
								<CProgress
									size='xs'
									color='danger'
									class='progress-legend'
									:value='100'
								/> <span>
									{{ $t('iqrfnet.standard.sensor.title') }}
								</span>
							</td>
						</tr>
					</tbody>
				</table>
				<CButton
					class='w-100'
					color='primary'
					@click='getDevices'
				>
					{{ $t('iqrfnet.standard.grid.actions.refresh') }}
				</CButton>
				<div v-if='devices.length !== 0' class='table-responsive'>
					<table class='table table-striped device-info card-margin-bottom'>
						<tbody>
							<tr>
								<th />
								<th v-for='col of Array(10).keys()' :key='col'>
									{{ col }}
								</th>
							</tr>
							<tr v-for='row of Array(24).keys()' :key='row'>
								<th>{{ row }}0</th>
								<td v-for='col of Array(10).keys()' :key='col'>
									<CIcon
										v-if='devices[getAddress(row, col)] === null'
										class='text-danger'
										:content='icons.unbonded'
									/>
									<div v-else>
										<CIcon
											v-c-tooltip='{
												content: devices[getAddress(row, col)].getDetails(),
												placement: "left",
											}'
											:class='devices[getAddress(row, col)].getIconColor()'
											:content='devices[getAddress(row, col)].getIcon()'
										/>
										<CProgress
											v-if='devices[getAddress(row, col)].hasBinout()'
											size='xs'
											color='info'
											:value='100'
										/>
										<CProgress
											v-if='devices[getAddress(row, col)].hasDali()'
											size='xs'
											color='success'
											:value='100'
										/>
										<CProgress
											v-if='devices[getAddress(row, col)].hasLight()'
											size='xs'
											color='warning'
											:value='100'
										/>
										<CProgress
											v-if='devices[getAddress(row, col)].hasSensor()'
											size='xs'
											color='danger'
											:value='100'
										/>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CIcon, CProgress, CProgressBar} from '@coreui/vue/src';

import {cilCheckAlt, cilHome, cilSignalCellular4, cilX} from '@coreui/icons';
import {EnumerateCommand} from '../../enums/IqrfNet/info';
import {WebSocketOptions} from '../../store/modules/webSocketClient.module';

import StandardDevice from '../../iqrfNet/StandardDevice';
import InfoService from '../../services/DaemonApi/InfoService';
import IqrfNetService from '../../services/IqrfNetService';

import {Dictionary} from 'vue-router/types/router';
import {IInfoBinout, IInfoDevice, IInfoLight, IInfoNode, IInfoSensor} from '../../interfaces/iqrfInfo';
import {MutationPayload} from 'vuex';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CIcon,
		CProgress,
		CProgressBar,
	},
})

/**
 * Standard devices grid component
 */
export default class StandardGrid extends Vue {

	/**
	 * @var {Array<standardDevice>} auxDevices Auxiliary array of devices before the final grid is rendered
	 */
	private auxDevices: Array<StandardDevice> = []

	/**
	 * @var {Array<StandardDevice|null>} devices Array of devices implementing standards
	 */
	private devices: Array<StandardDevice|null> = []

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
		unbonded: cilX
	}

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
	 * Computes index of device based on row and column number
	 * @param {number} row Row number
	 * @param {number} col Column number
	 * @returns {number} Device array index
	 */
	private getAddress(row: number, col: number): number {
		return row * 10 + col;
	}

	/**
	 * Generates a fresh grid for devices
	 */
	private generateGrid(): void {
		this.devices = new Array(240).fill(null);
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
		this.generateGrid();
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
		devices.push(new StandardDevice(0, 0, 0));
		response.rsp.nodes.forEach((device: IInfoNode) => {
			devices[device.nAdr] = new StandardDevice(device.nAdr, device.hwpid, device.mid, device.disc);
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
		this.generateGrid();
		response.rsp.sensorDevices.forEach((device: IInfoSensor) => {
			this.auxDevices[device.nAdr]?.setSensors(device.sensors);
		});
		this.auxDevices.forEach((device: StandardDevice) => {
			this.devices[device.getAddress()] = device;
		});
		this.pingDevices();
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
		map.forEach((byte: number, idx: number) => {
			const bitString = byte.toString(2).padStart(8, '0');
			for (let i = 0; i < 8; i++) {
				this.devices[idx * 8 + i]?.setOnline((bitString[(7 - i)] === '1'));
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
@media (min-width: 440px) and (max-width: 1400px) {
	.device-info {
		td, th {
			padding: 0.5rem;
		}
	}
}

@media (max-width: 440px) {
	.device-info {
		td, th {
			padding: 0.25rem;
		}
	}
}

.table-toprow {
	border: none;
}

td span {
   width: 90%;
   text-align: center;
}
.progress-legend {
   width: 10%;
   align-self: center;
   vertical-align: middle;
}


</style>