<template>
	<div class='panel panel-default'>
		<div class='panel-body'>
			<table class='table table-striped' v-if="info !== null">
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
						<span v-for='{iface, addresses} in this.getIpAddresses'>
							<strong>{{ iface }}: </strong> {{ addresses }}<br/>
						</span>
					</td>
				</tr>
				<tr>
					<th>{{ $t('gateway.info.addresses.mac') }}</th>
					<td>
						<span v-for='{iface, address} in this.getMacAddresses'>
							<strong>{{ iface }}: </strong> {{ address }}<br/>
						</span>
					</td>
				</tr>
				<tr>
					<th>{{ $t('gateway.info.usages.disks') }}</th>
					<td>
						<div v-for='usage in info.diskUsages'>
							<strong>{{ usage.fsName }} ({{ usage.fsType }}):</strong>
							{{ $t('gateway.info.usages.used') }}
							{{ usage.used }} / {{ usage.size }}
							<div class='progress'>
								<div class='progress-bar usage-progress-bar' role='progressbar'
										 v-bind:style='{ width: usage.usage }'>
									{{ usage.used }} ({{ usage.usage }})
								</div>
							</div>
							<br/>
						</div>
					</td>
				</tr>
				<tr>
					<th>{{ $t('gateway.info.usages.memory') }}</th>
					<td>
						{{ $t('gateway.info.usages.used') }}
						{{ info.memoryUsage.used }} / {{ info.memoryUsage.size }}
						<div class='progress'>
							<div class='progress-bar usage-progress-bar' role='progressbar'
									 v-bind:style='{width: info.memoryUsage.usage }'>
								{{ info.memoryUsage.used }} ({{ info.memoryUsage.usage }})
							</div>
						</div>
					</td>
				</tr>
				<tr v-if='this.info.swapUsage'>
					<th>{{ $t('gateway.info.usages.swap') }}</th>
					<td>
						{{ $t('gateway.info.usages.used') }}
						{{ info.swapUsage.used }} / {{ info.swapUsage.size }}
						<div class='progress'>
							<div class='progress-bar usage-progress-bar' role='progressbar'
									 v-bind:style='{ width: info.swapUsage.usage }'>
								{{ info.swapUsage.used }} ({{ info.swapUsage.usage }})
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<th>{{ $t('gateway.info.tr.title') }}</th>
					<td>
						<coordinator-info/>
					</td>
				</tr>
				<tr>
					<th>{{ $t('gateway.info.gwMode') }}</th>
					<td>{{ $t('gateway.info.gwModes.' + gwMode) }}</td>
				</tr>
				</tbody>
			</table>
			<button class='btn btn-primary' @click='downloadDiagnostics()'
							role='button'>
				{{ $t('gateway.diagnostics.download') }}
			</button>
		</div>
	</div>
</template>

<script>
import CoordinatorInfo from './CoordinatorInfo';
import GatewayService from '../../services/GatewayService';
import spinner from '../../spinner';

export default {
	name: 'gateway-info',
	components: {CoordinatorInfo},
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
	data() {
		return {
			coordinator: null,
			info: null,
			gwMode: 'unknown'
		};
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
	created() {
		spinner.showSpinner();
		GatewayService.getInfo()
				.then(
						(response) => {
							this.info = response.data;
							spinner.hideSpinner();
						}
				)
				.catch(() => spinner.hideSpinner());
	}

};
</script>

<style scoped>

</style>
