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
		<v-card-title>
			{{ $t('install.gwInfo.title') }}
		</v-card-title>
		<v-card-text>
			<v-table v-if='info !== null'>
				<tbody class='table-paddings'>
					<tr>
						<th>{{ $t('components.gateway.information.board') }}</th>
						<td>{{ info.board }}</td>
					</tr>
					<tr v-if='info.gwId'>
						<th>{{ $t('components.gateway.information.gwId') }}</th>
						<td>{{ info.gwId }}</td>
					</tr>
					<tr v-if='info.versions.controller'>
						<th>{{ $t('components.gateway.information.version.iqrfGatewayController') }}</th>
						<td>{{ info.versions.controller }}</td>
					</tr>
					<tr>
						<th>{{ $t('components.gateway.information.version.iqrfGatewayDaemon') }}</th>
						<td>{{ info.versions.daemon }}</td>
					</tr>
					<tr v-if='info.versions.setter'>
						<th>{{ $t('components.gateway.information.version.iqrfGatewaySetter') }}</th>
						<td>{{ info.versions.setter }}</td>
					</tr>
					<tr v-if='info.versions.uploader'>
						<th>{{ $t('components.gateway.information.version.iqrfGatewayUploader') }}</th>
						<td>{{ info.versions.uploader }}</td>
					</tr>
					<tr>
						<th>{{ $t('components.gateway.information.version.iqrfGatewayWebapp') }}</th>
						<td>{{ info.versions.webapp }}</td>
					</tr>
					<tr>
						<th>{{ $t('components.gateway.information.hostname') }}</th>
						<td>{{ info.hostname }}</td>
					</tr>
					<tr>
						<th>{{ $t('components.gateway.information.addresses.ip') }}</th>
						<td>
							<span v-for='{ name, ipAddresses } of ipAddrs' :key='name'>
								<strong>{{ name }}: </strong> {{ ipAddresses?.join(', ') }}<br>
							</span>
						</td>
					</tr>
					<tr>
						<th>{{ $t('components.gateway.information.addresses.mac') }}</th>
						<td>
							<span v-for='{ name, macAddress } of macAddrs' :key='name'>
								<strong>{{ name }}: </strong> {{ macAddress }}<br>
							</span>
						</td>
					</tr>
					<tr>
						<th>{{ $t('components.gateway.information.tr.title') }}</th>
						<td>
							<CoordinatorInfo />
						</td>
					</tr>
					<tr>
						<th>{{ $t('components.gateway.information.gwMode') }}</th>
						<td>
							<DaemonModeInfo />
						</td>
					</tr>
				</tbody>
			</v-table>
			<v-btn
				color='primary'
				:prepend-icon='mdiFileDownload'
				@click='downloadDiagnostics'
			>
				{{ $t('install.gwInfo.download') }}
			</v-btn>
		</v-card-text>
	</v-card>
</template>

<script lang='ts' setup>
import { type InfoService } from '@iqrf/iqrf-gateway-webapp-client/services/Gateway';
import { type GatewayInformation, type NetworkInterface } from '@iqrf/iqrf-gateway-webapp-client/types/Gateway';
import { FileDownloader } from '@iqrf/iqrf-gateway-webapp-client/utils';
import { mdiFileDownload } from '@mdi/js';
import { computed, onMounted, ref, type Ref } from 'vue';
import { toast } from 'vue3-toastify';

import CoordinatorInfo from '@/components/gateway/info/CoordinatorInfo.vue';
import DaemonModeInfo from '@/components/gateway/info/DaemonModeInfo.vue';
import { useApiClient } from '@/services/ApiClient';


const service: InfoService = useApiClient().getGatewayServices().getInfoService();
const info: Ref<GatewayInformation | null> = ref(null);
const ipAddrs = computed(() => {
	if (info.value === null) {
		return [];
	}
	return info.value.interfaces.filter((item: NetworkInterface) => item.ipAddresses !== null && item.ipAddresses.length > 0);
});
const macAddrs = computed(() => {
	if (info.value === null) {
		return [];
	}
	return info.value.interfaces.filter((item: NetworkInterface) => item.macAddress !== null);
});

onMounted(() => {
	getInfo();
});

function getInfo(): void {
	service.getDetailed()
		.then((rsp: GatewayInformation) => info.value = rsp)
		.catch(() => toast.error('TODO ERROR HANDLING'));
}

function downloadDiagnostics(): void {
	service.getDetailed()
		.then((rsp: GatewayInformation) => {
			let fileName = 'iqrf-gateway-info';
			if (info.value?.gwId) {
				fileName += `_${ info.value.gwId.toLowerCase()}`;
			}
			FileDownloader.downloadFromData(rsp, 'application/json', `${fileName }.json`);
		})
		.catch(() => toast.error('TODO ERROR HANDLING'));
}

</script>
