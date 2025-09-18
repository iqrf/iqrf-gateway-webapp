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
			{{ $t('pages.gateway.logs.title') }}
		</template>
		<template #titleActions>
			<IActionBtn
				:icon='mdiFolderDownloadOutline'
				container-type='card-title'
				:disabled='[ComponentState.Action, ComponentState.Loading].includes(componentState)'
				:loading='componentState === ComponentState.Action'
				:tooltip='$t("components.common.actions.download")'
				@click='exportLogs()'
			/>
			<IActionBtn
				:action='Action.Reload'
				:loading='componentState === ComponentState.Loading'
				:disabled='componentState === ComponentState.Action'
				container-type='card-title'
				@click='listServices()'
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
	</ICard>
</template>

<script lang='ts' setup>
import { type LogService } from '@iqrf/iqrf-gateway-webapp-client/services/Gateway';
import { FileDownloader } from '@iqrf/iqrf-gateway-webapp-client/utils';
import { Action, IActionBtn, ICard } from '@iqrf/iqrf-vue-ui';
import { mdiFolderDownloadOutline } from '@mdi/js';
import { onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import JournalViewer from '@/components/gateway/logs/JournalViewer.vue';
import LogViewer from '@/components/gateway/logs/LogViewer.vue';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const { t } = useI18n();
const service: LogService = useApiClient().getGatewayServices().getLogService();
const services: Ref<string[]> = ref([]);
const tab: Ref<string | null> = ref(null);

async function listServices(): Promise<void> {
	componentState.value = ComponentState.Loading;
	try {
		services.value = await service.listAvailable();
		if (tab.value === null && services.value.length > 0) {
			tab.value = services.value[0];
		}
	} catch {
		toast.error(
			t('components.gateway.logs.services.messages.list.failed'),
		);
	}
	componentState.value = ComponentState.Idle;
}

async function exportLogs(): Promise<void> {
	componentState.value = ComponentState.Action;
	try {
		const data = await service.exportLogs();
		FileDownloader.downloadFileResponse(
			data,
			`iqrf-gateway-logs_${new Date().toISOString()}.zip`,
		);
	} catch {
		toast.error(
			t('components.gateway.logs.messages.export.failed'),
		);
	}
	componentState.value = ComponentState.Idle;
}

onMounted(() => {
	listServices();
});
</script>
