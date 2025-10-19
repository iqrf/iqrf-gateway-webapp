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
	<v-form
		ref='form'
		v-slot='{ isValid }'
		:disabled='[ComponentState.Action, ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
	>
		<ICard>
			<template #title>
				{{ $t('pages.gateway.logs.title') }}
			</template>
			<template #titleActions>
				<IActionBtn
					:icon='mdiFolderDownloadOutline'
					container-type='card-title'
					:loading='componentState === ComponentState.Action && actionType === ActionType.Export'
					:disabled='[ComponentState.Loading, ComponentState.Reloading].includes(componentState) || (componentState === ComponentState.Action && actionType !== ActionType.Export)'
					:tooltip='$t("components.common.actions.download")'
					@click='exportLogs()'
				/>
				<IActionBtn
					:action='Action.Reload'
					container-type='card-title'
					:loading='componentState === ComponentState.Loading'
					:disabled='componentState === ComponentState.Action'
					@click='listServices()'
				/>
			</template>
			<v-alert
				v-if='componentState === ComponentState.FetchFailed'
				type='error'
				variant='tonal'
				:text='$t("components.gateway.logs.messages.list.failed")'
			/>
			<v-skeleton-loader
				v-else
				class='input-skeleton-loader'
				:loading='componentState === ComponentState.Loading'
				type='heading, button'
			>
				<v-responsive>
					<ISelectInput
						v-model='service'
						:label='$t("components.gateway.logs.service")'
						:placeholder='$t("components.gateway.logs.placeholder")'
						:rules='[
							(v: string|null) => ValidationRules.required(v, $t("components.gateway.logs.validation.service.required")),
						]'
						:items='serviceOptions'
						required
					/>
					<IActionBtn
						:action='Action.Custom'
						:icon='mdiTextLong'
						:loading='componentState === ComponentState.Action && actionType === ActionType.Load'
						:disabled='!isValid.value || [ComponentState.Loading, ComponentState.Reloading].includes(componentState) || (componentState === ComponentState.Action && actionType !== ActionType.Load)'
						:text='$t("components.gateway.logs.actions.load")'
						@click='getLog()'
					/>
				</v-responsive>
			</v-skeleton-loader>
			<span v-if='serviceType === ServiceType.Iqrf'>
				<v-divider class='my-2' />
				<v-alert
					v-if='componentState === ComponentState.Error'
					type='error'
					variant='tonal'
					:text='$t("components.gateway.logs.messages.fetch.failed")'
				/>
				<LogViewer
					v-else-if='serviceLog !== null'
					:title='logTitle'
					:log='serviceLog'
				/>
			</span>
			<JournalViewer
				v-if='serviceType === ServiceType.Journal'
				ref='journalViewer'
			/>
		</ICard>
	</v-form>
</template>

<script lang='ts' setup>
import { type LogService } from '@iqrf/iqrf-gateway-webapp-client/services/Gateway';
import { FileDownloader } from '@iqrf/iqrf-gateway-webapp-client/utils';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	ISelectInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiFolderDownloadOutline, mdiTextLong } from '@mdi/js';
import { nextTick, onMounted, ref, type Ref, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import JournalViewer from '@/components/gateway/logs/JournalViewer.vue';
import LogViewer from '@/components/gateway/logs/LogViewer.vue';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';
import { SelectItem } from '@/types/vuetify';

enum ActionType {
	Export = 0,
	Load = 1,
}

enum ServiceType {
	Iqrf = 0,
	Journal = 1,
}

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const form: Ref<VForm | null> = useTemplateRef('form');
const logService: LogService = useApiClient().getGatewayServices().getLogService();
const service: Ref<string | null> = ref(null);
const serviceOptions: Ref<SelectItem[]> = ref([]);
const actionType: Ref<ActionType> = ref(ActionType.Export);
const serviceType: Ref<ServiceType | null> = ref(null);
const logTitle: Ref<string> = ref('');
const serviceLog: Ref<string | null> = ref(null);
const journalViewer: Ref<InstanceType<typeof JournalViewer> | null> = useTemplateRef('journalViewer');

async function listServices(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		const services: SelectItem[] = [];
		const data = await logService.listAvailable();
		for (const item of data) {
			services.push({
				title: i18n.t(`components.gateway.logs.services.tabs.${item}`),
				value: item,
			});
		}
		services.push({
			title: i18n.t('components.gateway.logs.services.tabs.systemd-journald'),
			value: 'systemd-journald',
		});
		serviceOptions.value = services;
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error(
			i18n.t('components.gateway.logs.messages.list.failed'),
		);
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
	}
}

async function getLog(): Promise<void> {
	if (service.value === 'systemd-journald') {
		serviceType.value = ServiceType.Journal;
		await nextTick();
		journalViewer.value?.fetch();
	} else {
		serviceType.value = ServiceType.Iqrf;
		getServiceLog();
	}
}

async function getServiceLog(): Promise<void> {
	if (!await validateForm(form.value) || service.value === null) {
		return;
	}
	actionType.value = ActionType.Load;
	componentState.value = ComponentState.Action;
	try {
		serviceLog.value = await logService.getServiceLog(service.value);
		logTitle.value = i18n.t(`components.gateway.logs.services.tabs.${service.value}`);
		componentState.value = ComponentState.Ready;
	} catch {
		serviceLog.value = null;
		toast.error(
			i18n.t('components.gateway.logs.messages.fetch.failed'),
		);
		componentState.value = ComponentState.Error;
	}
}

async function exportLogs(): Promise<void> {
	actionType.value = ActionType.Export;
	componentState.value = ComponentState.Action;
	try {
		const data = await logService.exportLogs();
		FileDownloader.downloadFileResponse(
			data,
			`iqrf-gateway-logs_${new Date().toISOString()}.zip`,
		);
	} catch {
		toast.error(
			i18n.t('components.gateway.logs.messages.export.failed'),
		);
	}
	componentState.value = ComponentState.Idle;
}

onMounted(() => {
	listServices();
});
</script>
