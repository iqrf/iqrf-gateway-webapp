<template>
	<CCard body-wrapper>
		<div class='table-responsive'>
			<table v-if='info !== null' class='table table-striped'>
				<tbody>
					<tr>
						<th>{{ $t('gateway.info.board') }}</th>
						<td>{{ info.board }}</td>
					</tr>
					<tr v-if='info.gwId'>
						<th>{{ $t('gateway.info.gwId') }}</th>
						<td>{{ info.gwId }}</td>
					</tr>
					<tr v-if='info.pixla'>
						<th>
							<a href='https://www.pixla.online/'>
								{{ $t('gateway.info.gwmonId') }}
							</a>
						</th>
						<td>{{ info.pixla }}</td>
					</tr>
					<tr v-if='info.versions.controller'>
						<th>{{ $t('gateway.info.version.iqrfGatewayController') }}</th>
						<td>{{ info.versions.controller }}</td>
					</tr>
					<tr>
						<th>{{ $t('gateway.info.version.iqrfGatewayDaemon') }}</th>
						<td>{{ info.versions.daemon }}</td>
					</tr>
					<tr>
						<th>{{ $t('gateway.info.version.iqrfGatewayWebapp') }}</th>
						<td>{{ info.versions.webapp }}</td>
					</tr>
					<tr>
						<th>{{ $t('gateway.info.hostname') }}</th>
						<td>{{ info.hostname }}</td>
					</tr>
					<tr>
						<th>{{ $t('gateway.info.addresses.ip') }}</th>
						<td>
							<span v-for='{iface, addresses} of getIpAddresses' :key='iface'>
								<strong>{{ iface }}: </strong> {{ addresses }}<br>
							</span>
						</td>
					</tr>
					<tr>
						<th>{{ $t('gateway.info.addresses.mac') }}</th>
						<td>
							<span v-for='{iface, address} of getMacAddresses' :key='iface'>
								<strong>{{ iface }}: </strong> {{ address }}<br>
							</span>
						</td>
					</tr>
					<tr>
						<th>{{ $t('gateway.info.usages.disks') }}</th>
						<td>
							<div v-for='usage of info.diskUsages' :key='usage.fsName'>
								<strong>{{ usage.fsName }} ({{ usage.fsType }}):</strong>
								<resource-usage :usage='usage' /><br>
							</div>
						</td>
					</tr>
					<tr>
						<th>{{ $t('gateway.info.usages.memory') }}</th>
						<td><resource-usage :usage='info.memoryUsage' /></td>
					</tr>
					<tr v-if='info.swapUsage'>
						<th>{{ $t('gateway.info.usages.swap') }}</th>
						<td><resource-usage :usage='info.swapUsage' /></td>
					</tr>
					<tr>
						<th>{{ $t('gateway.info.tr.title') }}</th>
						<td>
							<coordinator-info />
						</td>
					</tr>
					<tr>
						<th>{{ $t('gateway.info.gwMode') }}</th>
						<td>{{ $t('gateway.mode.modes.' + mode) }}</td>
					</tr>
				</tbody>
			</table>
		</div>
		<CButton color='primary' @click='downloadDiagnostics()'>
			{{ $t('gateway.diagnostics.download') }}
		</CButton>
	</CCard>
</template>

<script>
import {CButton, CCard} from '@coreui/vue/src';
import CoordinatorInfo from '../../components/Gateway/CoordinatorInfo';
import DaemonModeService, {DaemonMode} from '../../services/DaemonModeService';
import GatewayService from '../../services/GatewayService';
import ResourceUsage from '../../components/Gateway/ResourceUsage';
import {fileDownloader} from '../../helpers/fileDownloader';

export default {
	name: 'GatewayInfo',
	components: {CButton, CCard, CoordinatorInfo, ResourceUsage},
	data() {
		return {
			coordinator: null,
			info: null,
			mode: 'unknown'
		};
	},
	computed: {
		getIpAddresses() {
			let addresses = [];
			for (const nInterface of this.info.interfaces) {
				if (nInterface.ipAddresses === null) {
					continue;
				}
				addresses.push({
					iface: nInterface.name,
					addresses: nInterface.ipAddresses.join(', ')
				});
			}
			return addresses;
		},
		getMacAddresses() {
			let addresses = [];
			for (const nInterface of this.info.interfaces) {
				if (nInterface.macAddress === null) {
					continue;
				}
				addresses.push({
					iface: nInterface.name,
					address: nInterface.macAddress
				});
			}
			return addresses;
		}
	},
	created() {
		this.$store.commit('spinner/SHOW');
		if (this.$store.state.webSocketClient.socket.isConnected) {
			DaemonModeService.get();
		}
		this.unsubscribe = this.$store.subscribe(mutation => {
			if (mutation.type === 'SOCKET_ONOPEN' &&
					this.mode === DaemonMode.unknown) {
				DaemonModeService.get();
				return;
			}
			if (mutation.type !== 'SOCKET_ONMESSAGE' ||
					mutation.payload.mType !== 'mngDaemon_Mode') {
				return;
			}
			this.mode = DaemonModeService.parse(mutation.payload);
		});
		GatewayService.getInfo()
			.then(
				(response) => {
					this.info = response.data;
					this.$store.commit('spinner/HIDE');
				}
			)
			.catch(() => this.$store.commit('spinner/HIDE'));
	},
	beforeDestroy() {
		this.unsubscribe();
	},
	methods: {
		downloadDiagnostics() {
			this.$store.commit('spinner/SHOW');
			GatewayService.getDiagnosticsArchive().then(
				(response) => {
					const file = fileDownloader(response, 'application/zip', 'iqrf-gateway-diagnostics.zip');
					this.$store.commit('spinner/HIDE');
					file.click();
				}
			).catch(() => (this.$store.commit('spinner/HIDE')));
		}
	},
	metaInfo: {
		title: 'gateway.info.title',
	},
};
</script>
