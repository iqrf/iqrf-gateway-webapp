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
	<Card>
		<template #title>
			{{ $t('pages.gateway.logs.title') }}
		</template>
		<template #titleActions>
			<CardTitleActionBtn
				:icon='mdiFolderDownloadOutline'
				:tooltip='$t("components.gateway.logs.actions.download")'
				@click='exportLogs'
			/>
			<CardTitleActionBtn
				:action='Action.Reload'
				@click='listServices'
			/>
		</template>
		<v-skeleton-loader
			:loading='componentState === ComponentState.Loading'
			type='heading'
		>
			<v-responsive>
				<v-tabs
					v-model='tab'
					class='mb-2'
				>
					<v-tab
						v-for='item in services'
						:key='item'
						:value='item'
					>
						{{ $t(`components.gateway.services.service.${item}.name`) }}
					</v-tab>
					<v-tab value='journal'>
						{{ $t('components.gateway.services.service.systemd-journald.name') }}
					</v-tab>
				</v-tabs>
			</v-responsive>
		</v-skeleton-loader>
		<JournalViewer v-if='tab === "journal"' />
		<LogViewer v-else :service-name='tab' />
	</Card>
</template>

<script lang='ts' setup>
import { type LogService } from '@iqrf/iqrf-gateway-webapp-client/services/Gateway';
import { FileResponse } from '@iqrf/iqrf-gateway-webapp-client/types';
import { FileDownloader } from '@iqrf/iqrf-gateway-webapp-client/utils';
import { mdiFolderDownloadOutline } from '@mdi/js';
import { onMounted, ref, type Ref } from 'vue';
import { toast } from 'vue3-toastify';

import JournalViewer from '@/components/gateway/logs/JournalViewer.vue';
import LogViewer from '@/components/gateway/logs/LogViewer.vue';
import Card from '@/components/layout/card/Card.vue';
import CardTitleActionBtn from '@/components/layout/card/CardTitleActionBtn.vue';
import { useApiClient } from '@/services/ApiClient';
import { Action } from '@/types/Action';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const service: LogService = useApiClient().getGatewayServices().getLogService();
const services: Ref<string[]> = ref([]);
const tab: Ref<string | null> = ref(null);

function listServices(): void {
	componentState.value = ComponentState.Loading;
	service.listAvailable()
		.then((response: string[]) => {
			services.value = response;
			if (tab.value === null && services.value.length > 0) {
				tab.value = services.value[0];
			}
			componentState.value = ComponentState.Ready;
		})
		.catch(() => toast.error('TODO FETCH LIST ERROR'));
}

function exportLogs(): void {
	service.exportLogs()
		.then((response: FileResponse<Blob>) => {
			const filename = `iqrf-gateway-logs_${new Date().toISOString()}.zip`;
			FileDownloader.downloadFileResponse(response, filename);
		})
		.catch(() => toast.error('TODO EXPORT ERROR HANDLING'));
}

onMounted(() => {
	listServices();
});
</script>
