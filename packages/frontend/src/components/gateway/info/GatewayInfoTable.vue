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
	<ICard>
		<template #title>
			{{ $t('pages.gateway.information.title') }}
		</template>
		<template #titleActions>
			<IActionBtn
				:action='Action.Reload'
				:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
				:disabled='componentState === ComponentState.Action'
				container-type='card-title'
				@click='getInformation()'
			/>
		</template>
		<v-alert
			v-if='componentState === ComponentState.FetchFailed'
			type='error'
			variant='tonal'
			:text='$t("components.gateway.information.messages.fetchInfo.failed")'
		/>
		<v-skeleton-loader
			v-else
			class='table-compact-skeleton-loader'
			:loading='componentState === ComponentState.Loading'
			:type='SkeletonLoaders.simpleTableSkeletonLoader(16)'
		>
			<v-responsive>
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
								<a v-if='info?.os.homePage !== null' :href='info?.os.homePage' class='text-primary'>{{ info?.os.name }}</a>
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
						<tr v-if='info?.versions.influxdbBridge'>
							<td>
								<strong>{{ $t('components.gateway.information.version.iqrfGatewayInfluxdbBridge') }}</strong>
							</td>
							<td>{{ info.versions.influxdbBridge }}</td>
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
								<strong>{{ $t('components.gateway.information.hostname.label') }}</strong>
							</td>
							<td class='d-flex justify-space-between align-center'>
								{{ info.hostname }}
								<HostnameChangeDialog
									:current-hostname='info.hostname'
									:disabled='[ComponentState.Action, ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
									@saved='getInformation()'
								/>
							</td>
						</tr>
						<tr>
							<td>
								<strong>{{ $t('components.gateway.information.addresses.ip') }}</strong>
							</td>
							<td>
								<div class='py-2'>
									<span v-for='{ name, ipAddresses } of ipAddrs' :key='name'>
										<strong>{{ `${name}:` }}</strong> {{ ipAddresses?.join(', ') }}<br>
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
										v-for='{ name, macAddress } of macAddrs'
										:key='name'
									>
										<strong>{{ `${name}:` }}</strong> {{ macAddress }}<br>
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
								<CoordinatorInfo
									v-if='enumData'
									:component-state='componentState'
									:data='enumData'
								/>
								<span v-else>
									{{ $t('components.gateway.information.messages.fetchTr.failed') }}
								</span>
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
						<tr v-if='info?.emmcHealth'>
							<td>
								<strong>{{ $t('components.gateway.information.emmcHealth.emmcHealth') }}</strong>
							</td>
							<td>
								<EmmcHealthInfo :emmc-health='info.emmcHealth' />
							</td>
						</tr>
					</tbody>
				</v-table>
			</v-responsive>
		</v-skeleton-loader>
		<template #actions>
			<IActionBtn
				:action='Action.Custom'
				:icon='mdiDownload'
				:loading='componentState === ComponentState.Action'
				:disabled='[ComponentState.FetchFailed, ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
				:text='$t("components.gateway.information.diagnostics")'
				@click='getDiagnostics()'
			/>
		</template>
	</ICard>
</template>

<script lang='ts' setup>
import { IqmeshServiceMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { EnumerationService } from '@iqrf/iqrf-gateway-daemon-utils/services/iqmesh';
import { DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { type InfoService } from '@iqrf/iqrf-gateway-webapp-client/services/Gateway';
import { type GatewayInformation, type NetworkInterface } from '@iqrf/iqrf-gateway-webapp-client/types/Gateway';
import { FileDownloader } from '@iqrf/iqrf-gateway-webapp-client/utils';
import { Action, ComponentState, IActionBtn, ICard, SkeletonLoaders } from '@iqrf/iqrf-vue-ui';
import { mdiDownload } from '@mdi/js';
import { computed, onBeforeUnmount, onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import CoordinatorInfo from '@/components/gateway/info/CoordinatorInfo.vue';
import DaemonModeInfo from '@/components/gateway/info/DaemonModeInfo.vue';
import DiskResourceUsage from '@/components/gateway/info/DiskResourceUsage.vue';
import HostnameChangeDialog from '@/components/gateway/info/HostnameChangeDialog.vue';
import ResourceUsage from '@/components/gateway/info/ResourceUsage.vue';
import { useApiClient } from '@/services/ApiClient';
import { useDaemonStore } from '@/store/daemonSocket';
import { DeviceEnumeration } from '@/types/DaemonApi/Iqmesh';
import EmmcHealthInfo from './EmmcHealthInfo.vue';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const service: InfoService = useApiClient().getGatewayServices().getInfoService();
const info: Ref<GatewayInformation | null> = ref(null);
const daemonStore = useDaemonStore();
const enumData: Ref<DeviceEnumeration | null> = ref(null);
const msgId: Ref<string | null> = ref(null);
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

daemonStore.$onAction(
	({ name, after }) => {
		if (name === 'onMessage') {
			after((rsp: DaemonApiResponse) => {
				if (rsp.data.msgId !== msgId.value) {
					return;
				}
				daemonStore.removeMessage(msgId.value);
				if (rsp.mType === IqmeshServiceMessages.Enumerate) {
					handleEnumerateResponse(rsp);
				}
			});
		}
	},
);

async function getInformation(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		info.value = await service.getDetailed();
		enumerate();
	} catch {
		toast.error(
			i18n.t('components.gateway.information.messages.fetchInfo.failed'),
		);
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
	}
}

async function enumerate(): Promise<void> {
	const opts = new DaemonMessageOptions(
		null,
		10_000,
		i18n.t('components.gateway.information.messages.fetchTr.timeout'),
		() => {
			msgId.value = null;
			componentState.value = ComponentState.Ready;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		EnumerationService.enumerate({}, { deviceAddr: 0 }, opts),
	);
}

function handleEnumerateResponse(rsp: DaemonApiResponse): void {
	if (rsp.data.status === 0) {
		enumData.value = rsp.data.rsp as DeviceEnumeration;
	}
	componentState.value = ComponentState.Ready;
}

async function getDiagnostics(): Promise<void> {
	componentState.value = ComponentState.Action;
	try {
		const file = await service.getDiagnostics();
		FileDownloader.downloadFileResponse(
			file,
			file.name,
		);
	} catch {
		toast.error(
			i18n.t('components.gateway.information.messages.diagnostics.failed'),
		);
	}
	componentState.value = ComponentState.Ready;
}

onMounted(() => {
	getInformation();
});

onBeforeUnmount(() => {
	daemonStore.removeMessage(msgId.value);
});
</script>
