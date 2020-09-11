/* eslint-disable no-console */
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
						<th>&nbsp;</th>
						<th v-for='num of Array(10).keys()' :key='num'>
							{{ num }}
						</th>
					</tr>
					<tr v-for='row of Array(24).keys()' :key='row'>
						<th>{{ row }}0</th>
						<td v-for='col of Array(10).keys()' :key='col'>
							<CIcon :class='devices[(row*10)+col].getIconColor()' :content='devices[(row*10)+col].getIcon()' />
						</td>
					</tr>
				</tbody>
			</table>
			<CAlert color='danger'>
				{{ $t('iqrfnet.networkManager.devicesInfo.messages.empty') }}
			</CAlert>
		</CCardBody>
	</CCard>
</template>

<script>
import {cilHome, cilX, cilCheckAlt, cilSignalCellular4} from '@coreui/icons';
import {CButton, CCard, CCardBody, CCardHeader, CIcon} from '@coreui/vue';
import Device from '../../helpers/Device';
import IqmeshNetworkService from '../../services/IqmeshNetworkService';

export default {
	name: 'DevicesInfo',
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CIcon
	},
	data() {
		return {
			devices: [],
			responseReceived: false,
			timedOut: false
		};
	},
	created() {
		this.generateDevices();
		this.unsubscribe = this.$store.subscribe(mutation => {
			if (mutation.type === 'SOCKET_ONOPEN') {
				this.responseReceived = false;
				this.getBondedDevices();
				this.responseReceived = false;
				this.getDiscoveredDevices();
				this.responseReceived = false;
				this.frcPing();
			}
			if (mutation.type === 'SOCKET_ONSEND') {
				if (mutation.payload.mType === ('iqrfEmbedCoordinator_BondedDevices' ||
					'iqrfEmbedCoordinator_DiscoveredDevices' || 'iqrfEmbedFrc_Send')) {
					setTimeout(() => {this.timeOut();}, 10000);
				}
			}
			if (mutation.type === 'SOCKET_ONMESSAGE') {
				if (mutation.payload.mType === 'iqrfEmbedCoordinator_BondedDevices') {
					this.responseReceived = true;
					this.$store.commit('spinner/HIDE');
					switch(mutation.payload.data.status) {
						case 0:
							var bonded = mutation.payload.data.rsp.result.bondedDevices;
							/*for(var i in bonded) {
								this.devices[bonded[i]].bonded = true;
							}*/
							bonded.forEach(item => {
								this.devices[item].bonded = true;
							});
							break;
						default:
							this.$toast.error(this.$t('iqrfnet.networkManager.devicesInfo.messages.bonded.failure'));
							break;
					}
				} else if (mutation.payload.mType === 'iqrfEmbedCoordinator_DiscoveredDevices') {
					this.responseReceived = true;
					this.$store.commit('spinner/HIDE');
					switch(mutation.payload.data.status) {
						case 0:
							var discovered = mutation.payload.data.rsp.result.discoveredDevices;
							for(var j in discovered) {
								this.devices[discovered[j]].discovered = true;
							}
							break;
						default:
							this.$toast.error(this.$t('iqrfnet.networkManager.devicesInfo.messages.discovered.failure'));
							break;
					}
				} else if (mutation.payload.mType === 'iqrfEmbedFrc_Send') {
					this.responseReceived = true;
					this.$store.commit('spinner/HIDE');
					switch(mutation.payload.data.status) {
						case 0:
							var online = mutation.payload.data.rsp.result.frcData;
							var k = 0;
							online.forEach(item => {
								for(var i = 0; i < 8; ++i) {
									var device = (item & (1 << i)) >> i;
									this.devices[k].online = (device === 1);
									k++;
								}
							});
							break;
						default:
							this.$toast.error(this.$t('iqrfnet.networkManager.devicesInfo.messages.ping.failure'));
							break;
					}
				}
			}
		});		
	},
	beforeDestroy() {
		this.unsubscribe();
	},
	methods: {
		frcPing() {
			this.$store.commit('spinner/SHOW');
			IqmeshNetworkService.ping();
		},
		generateDevices() {
			this.devices.push(new Device(0, true));
			for (var i = 1; i <= 239; i++) {
				this.devices.push(new Device(i, false));
			}
		},
		getBondedDevices() {
			this.$store.commit('spinner/SHOW');
			IqmeshNetworkService.getBonded();
		},
		getDiscoveredDevices() {
			this.$store.commit('spinner/SHOW');
			IqmeshNetworkService.getDiscovered();
		},
		timeOut() {
			if (!this.responseReceived) {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(this.$t('iqrfnet.networkManager.messages.submit.timeout'));
				this.timedOut = true;
			}
		}
	},
	icons: {
		coordinator: cilHome,
		bonded: cilCheckAlt,
		discovered: cilSignalCellular4,
		unbonded: cilX
	}
};
</script>

<style scoped>

table {
	border-collapse: collapse;
}

.table {
	width: 100%;
	margin-bottom: 1rem;
	color: #3c4b64;
}

.text-center {
    text-align: center !important;
}

</style>