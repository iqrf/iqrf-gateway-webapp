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
		<h1>{{ $t('gateway.info.title') }}</h1>
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
						<tr v-if='info.gwImage'>
							<th>{{ $t('gateway.info.gwImage') }}</th>
							<td>{{ info.gwImage }}</td>
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
						<tr v-if='info.versions.uploader'>
							<th>{{ $t('gateway.info.version.iqrfGatewayUploader') }}</th>
							<td>{{ info.versions.uploader }}</td>
						</tr>
						<tr>
							<th>{{ $t('gateway.info.version.iqrfGatewayWebapp') }}</th>
							<td>{{ info.versions.webapp }}</td>
						</tr>
						<tr>
							<th>{{ $t('gateway.info.hostname') }}</th>
							<td
								style='display: flex; justify-content: space-between; margin-top: -1px;'
							>
								{{ info.hostname }}
								<CButton
									color='primary'
									size='sm'
									@click='showHostnameModal'
								>
									<CIcon :content='icon' size='sm' />
									<span class='d-none d-lg-inline'>
										{{ $t('forms.edit') }}
									</span>
								</CButton>
							</td>
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
							<th>{{ $t('gateway.info.uptime') }}</th>
							<td>{{ info.uptime }}</td>
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
				{{ $t('gateway.info.diagnostics') }}
			</CButton>
		</CCard>
		<HostnameChange ref='hostname' @hostname-changed='getInformation' />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard} from '@coreui/vue/src';
import CoordinatorInfo from '../../components/Gateway/CoordinatorInfo.vue';
import DaemonModeInfo from '../../components/Gateway/DaemonModeInfo.vue';
import ResourceUsage from '../../components/Gateway/ResourceUsage.vue';
import GatewayService from '../../services/GatewayService';
import HostnameChange from '../../components/Gateway/HostnameChange.vue';

import {cilPencil} from '@coreui/icons';
import {DaemonModeEnum} from '../../services/DaemonModeService';
import {fileDownloader} from '../../helpers/fileDownloader';

import {AxiosResponse} from 'axios';
import {IGatewayInfo, IpAddress, MacAddress} from '../../interfaces/gatewayInfo';

@Component({
	components: {
		CButton,
		CCard,
		CoordinatorInfo,
		DaemonModeInfo,
		HostnameChange,
		ResourceUsage,
	},
	metaInfo: {
		title: 'gateway.info.title',
	},
})

/**
 * Gateway information component
 */
export default class GatewayInfo extends Vue {
	/**
	 * @var {IGatewayInfo|null} info Gateway information object
	 */
	private info: IGatewayInfo|null = null

	/**
	 * @var {DaemonModeEnum} mode Current Daemon mode
	 */
	private mode: DaemonModeEnum = DaemonModeEnum.unknown

	/**
	 * @var {boolean} showCoordinator Controls whether coordinator information component can be shown
	 */
	private showCoordinator = false

	private icon: Array<string> = cilPencil;
	
	/**
	 * Computes array of ip address objects from network interfaces
	 * @returns {Array<IpAddress} Array of ip address objects
	 */
	get getIpAddresses(): Array<IpAddress> {
		if (this.info === null) {
			return [];
		}
		let addresses: Array<IpAddress> = [];
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
	}

	/**
	 * Computes array of mac address objects from network interfaces
	 * @returns {Array<MacAddress>} Array of mac address objects
	 */
	get getMacAddresses(): Array<MacAddress> {
		if (this.info === null) {
			return [];
		}
		let addresses: Array<MacAddress> = [];
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

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		this.getInformation();
	}

	/**
	 * Retrieves gateway information
	 */
	private getInformation(): void {
		this.$store.commit('spinner/SHOW');
		GatewayService.getInfo()
			.then(
				(response: AxiosResponse) => {
					this.info = response.data;
					this.$store.commit('spinner/HIDE');
				}
			)
			.catch(() => this.$store.commit('spinner/HIDE'));
	}

	/**
	 * Creates a daemon diagnostics blob and prompts file download
	 */
	private downloadDiagnostics(): void {
		this.$store.commit('spinner/SHOW');
		GatewayService.getDiagnosticsArchive().then(
			(response: AxiosResponse) => {
				const file = fileDownloader(response, 'application/zip', 'iqrf-gateway-diagnostics.zip');
				this.$store.commit('spinner/HIDE');
				file.click();
			}
		).catch(() => (this.$store.commit('spinner/HIDE')));
	}

	/**
	 * Show hostname change modal
	 */
	private showHostnameModal(): void {
		(this.$refs.hostname as HostnameChange).show();
	}
}
</script>
