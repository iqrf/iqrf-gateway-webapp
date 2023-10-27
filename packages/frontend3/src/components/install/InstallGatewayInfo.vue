<template>
	<v-card>
		<v-card-title>
			{{ $t('install.gwInfo.title') }}
		</v-card-title>
		<v-card-text>
			<v-table v-if='info !== null'>
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
							<span v-for='{name, ipAddresses} of ipAddrs' :key='name'>
								<strong>{{ name }}: </strong> {{ ipAddresses?.join(', ') }}<br>
							</span>
						</td>
					</tr>
					<tr>
						<th>{{ $t('gateway.info.addresses.mac') }}</th>
						<td>
							<span v-for='{name, macAddress} of macAddrs' :key='name'>
								<strong>{{ name }}: </strong> {{ macAddress }}<br>
							</span>
						</td>
					</tr>
					<tr>
						<th>{{ $t('gateway.info.tr.title') }}</th>
						<td>
							<CoordinatorInfo />
						</td>
					</tr>
					<tr>
						<th>{{ $t('gateway.info.gwMode') }}</th>
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
import  { type InfoService } from '@iqrf/iqrf-gateway-webapp-client/services/Gateway';
import  { type GatewayInformation, type NetworkInterface } from '@iqrf/iqrf-gateway-webapp-client/types/Gateway';
import { FileDownloader } from '@iqrf/iqrf-gateway-webapp-client/utils';
import { mdiFileDownload } from '@mdi/js';
import { computed, onMounted, ref, type Ref } from 'vue';

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
	service.fetchDetailed()
		.then((rsp: GatewayInformation) => info.value = rsp)
		.catch(() => {});
}

function downloadDiagnostics(): void {
	service.fetchDetailed()
		.then((rsp: GatewayInformation) => {
			let fileName = 'iqrf-gateway-info';
			if (info.value?.gwId) {
				fileName += '_' + info.value.gwId.toLowerCase();
			}
			FileDownloader.downloadFromData(rsp, 'application/json', fileName + '.json');
		})
		.catch(() => {});
}

</script>
