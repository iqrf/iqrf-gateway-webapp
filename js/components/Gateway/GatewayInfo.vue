<template>
	<CCard body-wrapper>
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
					<th>{{ $t('gateway.info.version.iqrf-gw-controller') }}</th>
					<td>{{ info.versions.controller }}</td>
				</tr>
				<tr>
					<th>{{ $t('gateway.info.version.iqrf-gw-daemon') }}</th>
					<td>{{ info.versions.daemon }}</td>
				</tr>
				<tr>
					<th>{{ $t('gateway.info.version.iqrf-gw-webapp') }}</th>
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
					<td>{{ $t('gateway.info.gwModes.' + mode) }}</td>
				</tr>
			</tbody>
		</table>
		<CButton color='primary' @click='downloadDiagnostics()'>
			{{ $t('gateway.diagnostics.download') }}
		</CButton>
	</CCard>
</template>

<script>
import {CButton, CCard} from '@coreui/vue';
import CoordinatorInfo from './CoordinatorInfo';
import DaemonModeService from '../../services/DaemonModeService';
import GatewayService from '../../services/GatewayService';
import ResourceUsage from './ResourceUsage';
import spinner from '../../spinner';

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
		spinner.showSpinner();
		this.unsubscribe = this.$store.subscribe(mutation => {
			if (mutation.type === 'SOCKET_ONOPEN') {
				DaemonModeService.get();
				return;
			}
			if (mutation.type !== 'SOCKET_ONMESSAGE' ||
					mutation.payload.mType !== 'mngDaemon_Mode') {
				return;
			}
			try {
				this.mode = mutation.payload.data.rsp.operMode;
			} catch (e) {
				this.mode = 'unknown';
			}
		});
		if (this.$store.state.webSocketClient.socket.isConnected) {
			DaemonModeService.get();
		}
		GatewayService.getInfo()
			.then(
				(response) => {
					this.info = response.data;
					spinner.hideSpinner();
				}
			)
			.catch(() => spinner.hideSpinner());
	},
	beforeDestroy() {
		this.unsubscribe();
	},
	methods: {
		downloadDiagnostics() {
			spinner.showSpinner();
			GatewayService.getDiagnosticsArchive().then(
				(response) => {
					const contentDisposition = response.headers['content-disposition'];
					let fileName = 'iqrf-gateway-diagnostics.zip';
					if (contentDisposition) {
						const fileNameMatch = contentDisposition.match(/filename="(.+)"/);
						if (fileNameMatch.length === 2) {
							fileName = fileNameMatch[1];
						}
					}
					const blob = new Blob([response.data], {type: 'application/zip'});
					const fileUrl = window.URL.createObjectURL(blob);
					const file = document.createElement('a');
					file.href = fileUrl;
					file.setAttribute('download', fileName);
					document.body.appendChild(file);
					spinner.hideSpinner();
					file.click();
				}
			).catch(() => (spinner.hideSpinner()));
		}
	},
};
</script>

<style scoped>

</style>
