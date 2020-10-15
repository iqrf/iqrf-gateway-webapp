<template>
	<CCard>
		<CCardHeader>{{ $t('install.gwInfo.title') }}</CCardHeader>
		<CCardBody>
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
						<tr v-if='showCoordinator'>
							<th>{{ $t('gateway.info.tr.title') }}</th>
							<td>
								<coordinator-info />
							</td>
						</tr>
						<tr>
							<th>{{ $t('gateway.info.gwMode') }}</th>
							<td>
								<DaemonModeInfo @notify-cinfo='showCoordinator = true' />
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<CButton color='primary' @click='downloadDiagnostics()'>
				{{ $t('install.gwInfo.download') }}
			</CButton>
		</CCardBody>
	</CCard>
</template>

<script>
import {CButton, CCardBody, CCardHeader} from '@coreui/vue/src';
import CoordinatorInfo from '../../components/Gateway/CoordinatorInfo';
import DaemonModeInfo from '../../components/Gateway/DaemonModeInfo';
import GatewayService from '../../services/GatewayService';
import {fileDownloader} from '../../helpers/fileDownloader';

export default {
	name: 'InstallGatewayInfo',
	components: {CButton, CCardBody, CCardHeader, CoordinatorInfo, DaemonModeInfo},
	data() {
		return {
			coordinator: null,
			info: null,
			showCoordinator: false,
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
		GatewayService.getInfo()
			.then(
				(response) => {
					this.info = response.data;
					this.$store.commit('spinner/HIDE');
				}
			)
			.catch(() => this.$store.commit('spinner/HIDE'));
	},
	methods: {
		downloadDiagnostics() {
			this.$store.commit('spinner/SHOW');
			GatewayService.getInfo().then(
				(response) => {
					const file = fileDownloader(response, 'application/json', 'iqrf-gateway-info.json');
					this.$store.commit('spinner/HIDE');
					file.click();
				}
			).catch(() => (this.$store.commit('spinner/HIDE')));
		}
	},
	metaInfo: {
		title: 'install.gwInfo.title',
	},
};
</script>
