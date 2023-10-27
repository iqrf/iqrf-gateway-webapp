<template>
	<Card>
		<template #title>
			{{ $t('pages.gateway.information.title') }}
		</template>
		<v-table
			density='compact'
			:hover='true'
		>
			<tbody>
				<tr>
					<td>
						<strong>{{ $t('components.gateway.information.board') }}</strong>
					</td>
					<td>{{ info?.board }}</td>
				</tr>
				<tr>
					<td>
						<strong>{{ $t('components.gateway.information.os') }}</strong>
					</td>
					<td>
						<a v-if='info?.os.homePage !== null' :href='info?.os.homePage'>{{ info?.os.name }}</a>
						<span v-else>{{ info?.os.name }}</span>
					</td>
				</tr>
				<tr v-if='info?.gwId'>
					<td>
						<strong>{{ $t('components.gateway.information.gwId') }}</strong>
					</td>
					<td>{{ info.gwId }}</td>
				</tr>
				<tr v-if='info?.gwImage'>
					<td>
						<strong>{{ $t('components.gateway.information.gwImage') }}</strong>
					</td>
					<td>{{ info.gwImage }}</td>
				</tr>
				<tr v-if='info?.versions.cloudProvisioning'>
					<td>
						<strong>{{ $t('components.gateway.information.version.iqrfCloudProvisioning') }}</strong>
					</td>
					<td>{{ info.versions.cloudProvisioning }}</td>
				</tr>
				<tr v-if='info?.versions.controller'>
					<td>
						<strong>{{ $t('components.gateway.information.version.iqrfGatewayController') }}</strong>
					</td>
					<td>{{ info.versions.controller }}</td>
				</tr>
				<tr v-if='info?.versions.daemon'>
					<td>
						<strong>{{ $t('components.gateway.information.version.iqrfGatewayDaemon') }}</strong>
					</td>
					<td>{{ info.versions.daemon }}</td>
				</tr>
				<tr v-if='info?.versions.setter'>
					<td>
						<strong>{{ $t('components.gateway.information.version.iqrfGatewaySetter') }}</strong>
					</td>
					<td>{{ info.versions.setter }}</td>
				</tr>
				<tr v-if='info?.versions.uploader'>
					<td>
						<strong>{{ $t('components.gateway.information.version.iqrfGatewayUploader') }}</strong>
					</td>
					<td>{{ info.versions.uploader }}</td>
				</tr>
				<tr v-if='info?.versions.webapp'>
					<td>
						<strong>{{ $t('components.gateway.information.version.iqrfGatewayWebapp') }}</strong>
					</td>
					<td>{{ info.versions.webapp }}</td>
				</tr>
				<tr v-if='info?.hostname'>
					<td>
						<strong>{{ $t('components.gateway.information.hostname') }}</strong>
					</td>
					<td>
						{{ info.hostname }}
						<HostnameChangeDialog
							:current-hostname='info.hostname'
							@saved='getInformation'
						/>
					</td>
				</tr>
				<tr>
					<td>
						<strong>{{ $t('components.gateway.information.addresses.ip') }}</strong>
					</td>
					<td>
						<div class='py-2'>
							<span v-for='{name, ipAddresses} of ipAddrs' :key='name'>
								<strong>{{ name }}: </strong> {{ ipAddresses?.join(', ') }}<br>
							</span>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<strong>
							{{ $t('components.gateway.information.addresses.mac') }}
						</strong>
					</td>
					<td>
						<div class='py-2'>
							<span
								v-for='{name, macAddress} of macAddrs'
								:key='name'

							>
								<strong>{{ name }}: </strong> {{ macAddress }}<br>
							</span>
						</div>
					</td>
				</tr>
				<tr v-if='info?.diskUsages'>
					<td>
						<strong>{{ $t('components.gateway.information.usages.disks') }}</strong>
					</td>
					<td>
						<div class='py-2'>
							<DiskResourceUsage
								v-for='(usage, idx) in info.diskUsages'
								:key='usage.fsName'
								:usage='usage'
								:last='idx === (info.diskUsages.length - 1)'
							/>
						</div>
					</td>
				</tr>
				<tr v-if='info?.memoryUsage'>
					<td>
						<strong>{{ $t('components.gateway.information.usages.memory') }}</strong>
					</td>
					<td>
						<div class='py-2'>
							<ResourceUsage :usage='info.memoryUsage' />
						</div>
					</td>
				</tr>
				<tr v-if='info?.swapUsage'>
					<td>
						<strong>{{ $t('components.gateway.information.usages.swap') }}</strong>
					</td>
					<td>
						<div class='py-2'>
							<ResourceUsage :usage='info.swapUsage' />
						</div>
					</td>
				</tr>
				<tr v-if='info?.uptime'>
					<td>
						<strong>{{ $t('components.gateway.information.uptime') }}</strong>
					</td>
					<td>{{ info.uptime }}</td>
				</tr>
				<tr>
					<td>
						<strong>{{ $t('components.gateway.information.tr.title') }}</strong>
					</td>
					<td>
						<CoordinatorInfo />
					</td>
				</tr>
				<tr>
					<td>
						<strong>{{ $t('components.gateway.information.gwMode') }}</strong>
					</td>
					<td>
						<DaemonModeInfo />
					</td>
				</tr>
			</tbody>
		</v-table>
		<template #actions>
			<v-btn
				color='primary'
				variant='elevated'
				@click='getDiagnostics'
			>
				<v-icon :icon='mdiDownload' />
				{{ $t('components.gateway.information.diagnostics') }}
			</v-btn>
		</template>
	</Card>
</template>

<script lang='ts' setup>
import { computed, onMounted, ref, Ref } from 'vue';
import CoordinatorInfo from '@/components/gateway/info/CoordinatorInfo.vue';
import DaemonModeInfo from '@/components/gateway/info/DaemonModeInfo.vue';
import HostnameChangeDialog from '@/components/gateway/info/HostnameChangeDialog.vue';
import DiskResourceUsage from '@/components/gateway/info/DiskResourceUsage.vue';
import ResourceUsage from '@/components/gateway/info/ResourceUsage.vue';

import Card from '@/components/Card.vue';

import { InfoService } from '@iqrf/iqrf-gateway-webapp-client/services/Gateway';
import { useApiClient } from '@/services/ApiClient';
import { GatewayInformation, NetworkInterface } from '@iqrf/iqrf-gateway-webapp-client/types/Gateway';
import { mdiDownload } from '@mdi/js';

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
	getInformation();
});

function getInformation(): void {
	service.fetchDetailed()
		.then((rsp: GatewayInformation) => info.value = rsp)
		.catch(() => {});
}

function getDiagnostics(): void {
}
</script>
