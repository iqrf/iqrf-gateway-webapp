<template>
	<Card>
		<template #title>
			{{ $t('pages.gateway.logs.title') }}
		</template>
		<template #titleActions>
			<v-btn
				color='white'
				:icon='mdiFolderDownloadOutline'
				@click='exportLogs'
			/>
			<v-btn
				color='white'
				:icon='mdiReload'
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
import { FileDownloader } from '@iqrf/iqrf-gateway-webapp-client/utils/FileDownloader';
import { mdiFolderDownloadOutline, mdiReload } from '@mdi/js';
import { onMounted, type Ref, ref } from 'vue';
import { toast } from 'vue3-toastify';

import Card from '@/components/Card.vue';
import JournalViewer from '@/components/gateway/logs/JournalViewer.vue';
import LogViewer from '@/components/gateway/logs/LogViewer.vue';
import { useApiClient } from '@/services/ApiClient';
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
		.then((response: ArrayBuffer) => {
			const filename = `iqrf-gateway-logs_${new Date().toISOString()}.zip`;
			FileDownloader.downloadFromData(response, 'application/zip', filename);
		})
		.catch(() => toast.error('TODO EXPORT ERROR HANDLING'));
}

onMounted(() => {
	listServices();
});
</script>
