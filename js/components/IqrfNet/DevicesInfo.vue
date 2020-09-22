<template>
	<CCard>
		<CCardHeader>{{ $t('iqrfnet.networkManager.devicesInfo.title') }}</CCardHeader>
		<CCardBody>
			<table class='table text-center'>
				<tbody>
					<tr>
						<td>
							<CIcon class='text-info' :content='$options.icons.coordinator' />
							{{ $t('iqrfnet.networkManager.devicesInfo.icons.coordinator') }}
						</td>
						<td>
							<CIcon class='text-danger' :content='$options.icons.unbonded' />
							{{ $t('iqrfnet.networkManager.devicesInfo.icons.unbonded') }}
						</td>
					</tr>
					<tr>
						<td>
							<CIcon class='text-info' :content='$options.icons.bonded' />
							{{ $t('iqrfnet.networkManager.devicesInfo.icons.bonded') }}
						</td>
						<td>
							<CIcon class='text-info' :content='$options.icons.discovered' />
							{{ $t('iqrfnet.networkManager.devicesInfo.icons.discovered') }}
						</td>
					</tr>
					<tr>
						<td>
							<CIcon class='text-success' :content='$options.icons.bonded' />
							{{ $t('iqrfnet.networkManager.devicesInfo.icons.bondedOnline') }}
						</td>
						<td>
							<CIcon class='text-success' :content='$options.icons.discovered' />
							{{ $t('iqrfnet.networkManager.devicesInfo.icons.discoveredOnline') }}
						</td>
					</tr>
				</tbody>
			</table>
			<CButton color='primary' class='w-100' @click='frcPing'>
				{{ $t('forms.pingNodes') }}
			</CButton>
			<table v-if='!timedOut' class='table table-striped'>
				<tbody>
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
			</table>
			<CAlert v-else color='danger'>
				{{ $t('iqrfnet.networkManager.devicesInfo.messages.empty') }}
			</CAlert>
		</CCardBody>
	</CCard>
</template>

<script>
import {cilHome, cilX, cilCheckAlt, cilSignalCellular4} from '@coreui/icons';
import {CAlert, CButton, CCard, CCardBody, CCardHeader, CIcon} from '@coreui/vue/src';
import Device from '../../helpers/Device';
import DeviceIcon from './DeviceIcon';
import IqrfNetService from '../../services/IqrfNetService';

export default {
	name: 'DevicesInfo',
	components: {
		CAlert,
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CIcon,
		DeviceIcon,
	},
	data() {
		return {
			allowedMTypes: [
				'iqrfEmbedCoordinator_BondedDevices',
				'iqrfEmbedCoordinator_DiscoveredDevices',
				'iqrfEmbedFrc_Send',
			],
			devices: [],
			manual: false,
			responseReceived: false,
			timedOut: false
		};
	},
	created() {
		this.generateDevices();
		this.unsubscribe = this.$store.subscribe(mutation => {
			if (mutation.type === 'SOCKET_ONOPEN') {
				this.getDevices();
				return;
			}
			if (mutation.type === 'SOCKET_ONSEND') {
				if (!this.allowedMTypes.includes(mutation.payload.mType)) {
					return;
				}
				this.responseReceived = false;
				setTimeout(() => {this.timeOut();}, 10000);
				return;
			}
			if (mutation.type === 'SOCKET_ONMESSAGE') {
				if (!this.allowedMTypes.includes(mutation.payload.mType)) {
					return;
				}
				this.responseReceived = true;
				this.$store.commit('spinner/HIDE');
				if (mutation.payload.mType === 'iqrfEmbedCoordinator_BondedDevices') {
					this.parseBondedDevices(mutation.payload);
				} else if (mutation.payload.mType === 'iqrfEmbedCoordinator_DiscoveredDevices') {
					this.parseDiscoveredDevices(mutation.payload);
				} else if (mutation.payload.mType === 'iqrfEmbedFrc_Send') {
					this.parseFrcPing(mutation.payload);
				}
			}
		});
		if (this.$store.getters.isSocketConnected) {
			this.getDevices();
		}	
	},
	beforeDestroy() {
		this.unsubscribe();
	},
	methods: {
		frcPing() {
			this.$store.commit('spinner/SHOW');
			IqrfNetService.ping();
		},
		generateDevices() {
			this.devices.push(new Device(0, true));
			for (let i = 1; i <= 239; i++) {
				this.devices.push(new Device(i, false));
			}
		},
		getAddress(row, col) {
			return row * 10 + col;
		},
		getBondedDevices() {
			this.$store.commit('spinner/SHOW');
			IqrfNetService.getBonded();
		},
		getDiscoveredDevices() {
			this.$store.commit('spinner/SHOW');
			IqrfNetService.getDiscovered();
		},
		parseBondedDevices(response) {
			switch (response.data.status) {
				case 0: {
					this.devices.forEach(item => {
						item.bonded = false;
					});
					const bonded = response.data.rsp.result.bondedDevices;
					bonded.forEach(item => {
						this.devices[item].bonded = true;
					});
					break;
				}
				default:
					this.$toast.error(this.$t('iqrfnet.networkManager.devicesInfo.messages.bonded.failure'));
					this.timedOut = true;
					break;
			}
		},
		parseDiscoveredDevices(response) {
			switch (response.data.status) {
				case 0: {
					this.devices.forEach(item => {
						item.discovered = false;
					});
					const discovered = response.data.rsp.result.discoveredDevices;
					discovered.forEach(item => {
						this.devices[item].discovered = true;
					});
					break;
				}
				default:
					this.$toast.error(this.$t('iqrfnet.networkManager.devicesInfo.messages.discovered.failure'));
					this.timedOut = true;
					break;
			}
		},
		parseFrcPing(response) {
			switch(response.data.status) {
				case 0: {
					const online = response.data.rsp.result.frcData.slice(0, 30);
					let k = 0;
					online.forEach(item => {
						for (let i = 0; i < 8; ++i) {
							const device = (item & (1 << i)) >> i;
							this.devices[k++].online = (device === 1);
						}
					});
					if (this.manual) {
						this.manual = false;
						this.$forceUpdate();
					}
					break;
				}
				default:
					this.$toast.error(this.$t('iqrfnet.networkManager.devicesInfo.messages.ping.failure'));
					this.timedOut = false;
					break;
			}
		},
		getDevices() {
			this.getBondedDevices();
			this.getDiscoveredDevices();
			this.frcPing();
		},
		timeOut() {
			if (!this.responseReceived) {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(this.$t('iqrfnet.networkManager.messages.submit.timeout'));
			}
		},
		submitFrcPing() {
			this.manual = true;
			this.frcPing();
		},
	},
	icons: {
		coordinator: cilHome,
		bonded: cilCheckAlt,
		discovered: cilSignalCellular4,
		unbonded: cilX
	}
};
</script>
