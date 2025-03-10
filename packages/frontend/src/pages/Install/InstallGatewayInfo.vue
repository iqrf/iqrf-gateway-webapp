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
	<v-card>
		<v-card-title>{{ $t('install.gwInfo.title') }}</v-card-title>
		<v-card-text>
			<div class='table-responsive'>
				<v-simple-table v-if='info !== null' class='table table-striped'>
					<tbody class='table-paddings'>
						<tr>
							<th>{{ $t('gateway.info.board') }}</th>
							<td>{{ info.board }}</td>
						</tr>
						<tr v-if='info.gwId'>
							<th>{{ $t('gateway.info.gwId') }}</th>
							<td>{{ info.gwId }}</td>
						</tr>
						<tr v-if='info.versions.controller'>
							<th>{{ $t('gateway.info.version.iqrfGatewayController') }}</th>
							<td>{{ info.versions.controller }}</td>
						</tr>
						<tr>
							<th>{{ $t('gateway.info.version.iqrfGatewayDaemon') }}</th>
							<td>{{ info.versions.daemon }}</td>
						</tr>
						<tr v-if='info.versions.setter'>
							<th>{{ $t('gateway.info.version.iqrfGatewaySetter') }}</th>
							<td>{{ info.versions.setter }}</td>
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
				</v-simple-table>
			</div>
			<v-btn color='primary' @click='downloadDiagnostics()'>
				<v-icon small>
					mdi-file-download
				</v-icon>
				{{ $t('install.gwInfo.download') }}
			</v-btn>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import type {InfoService} from '@iqrf/iqrf-gateway-webapp-client/services/Gateway';
import {
	GatewayInformation
} from '@iqrf/iqrf-gateway-webapp-client/types/Gateway';
import {FileDownloader} from '@iqrf/iqrf-gateway-webapp-client/utils';
import {Component, Vue} from 'vue-property-decorator';

import CoordinatorInfo from '@/components/Gateway/Information/CoordinatorInfo.vue';
import DaemonModeInfo from '@/components/Gateway/Information/DaemonModeInfo.vue';
import {IpAddress, MacAddress} from '@/interfaces/Gateway/Information';
import {useApiClient} from '@/services/ApiClient';

@Component({
	components: {
		CoordinatorInfo,
		DaemonModeInfo
	},
	metaInfo: {
		title: 'install.gwInfo.title',
	}
})

/**
 * Gateway information component for installation wizard
 */
export default class InstallGatewayInfo extends Vue {
	/**
	 * @var {GatewayInformation|null} info Gateway information object
	 */
	private info: GatewayInformation|null = null;

	/**
	 * @var {boolean} showCoordinator Controls whether coordinator information component can be shown
	 */
	private showCoordinator = false;

	/**
   * @property {InfoService} service Gateway info service
   */
	private service: InfoService = useApiClient().getGatewayServices().getInfoService();

	/**
	 * Computes array of IP address objects from network interfaces
	 * @returns {Array<IpAddress>} Array of IP address objects
	 */
	get getIpAddresses(): Array<IpAddress> {
		if (this.info === null) {
			return [];
		}
		const addresses: Array<IpAddress> = [];
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
	 * Computes array of MAC address objects from network interfaces
	 * @returns {Array<MacAddress>} Array of MAC address objects
	 */
	get getMacAddresses(): Array<MacAddress> {
		if (this.info === null) {
			return [];
		}
		const addresses: Array<MacAddress> = [];
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
	 * Vue lifecycle hook created
	 */
	protected created(): void {
		this.$store.commit('spinner/SHOW');
		this.service.getDetailed()
			.then(
				(response: GatewayInformation) => {
					this.info = response;
					this.$store.commit('spinner/HIDE');
				}
			)
			.catch(() => this.$store.commit('spinner/HIDE'));
	}

	/**
	 * Creates daemon diagnostics file blob and prompts file download
	 */
	private downloadDiagnostics(): void {
		this.$store.commit('spinner/SHOW');
		this.service.getDetailed()
			.then(
				(response: GatewayInformation) => {
					let fileName = 'iqrf-gateway-info';
					if (this.info?.gwId) {
						fileName += '_' + this.info.gwId.toLowerCase();
					}
					FileDownloader.downloadFromData(response, 'application/json', `${fileName}.json`);
					this.$store.commit('spinner/HIDE');
				}
			)
			.catch(() => (this.$store.commit('spinner/HIDE')));
	}
}
</script>
