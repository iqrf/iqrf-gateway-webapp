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
			{{ $t('pages.config.daemon.scheduler.title') }}
		</template>
		<template #titleActions>
			<TaskForm
				:action='Action.Add'
				:messagings='messagings'
				:disabled='[ComponentState.Action, ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
				@saved='listTasks()'
			/>
			<TasksImportDialog
				:disabled='[ComponentState.Action, ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
				@imported='listTasks()'
			/>
			<IActionBtn
				:action='Action.Export'
				container-type='card-title'
				:disabled='[ComponentState.Action, ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
				:tooltip='$t("components.config.daemon.scheduler.actions.export")'
				@click='exportTasks()'
			/>
			<IActionBtn
				:action='Action.Reload'
				container-type='card-title'
				:loading='componentState === ComponentState.Action'
				:disabled='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
				:tooltip='$t("components.config.daemon.scheduler.actions.reload")'
				@click='listTasks()'
			/>
			<TasksDeleteDialog
				:disabled='[ComponentState.Action, ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
				@deleted='listTasks()'
			/>
		</template>
		<IDataTable
			:headers='headers'
			:items='tasks'
			:hover='true'
			:dense='true'
			:loading='[ComponentState.Action, ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
			:no-data-text='noDataText'
		>
			<template #item.time='{ item }'>
				{{ timeString(item.timeSpec) }}
			</template>
			<template #item.active='{ item }'>
				<IBooleanIcon
					v-if='item.active !== undefined'
					:value='item.active'
				/>
			</template>
			<template #item.actions='{ item }'>
				<IDataTableAction
					v-if='item.active'
					:action='Action.Stop'
					:disabled='[ComponentState.Action, ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
					:tooltip='$t("components.config.daemon.scheduler.actions.stop")'
					@click='stopTask(item.taskId)'
				/>
				<IDataTableAction
					v-else
					:action='Action.Start'
					:disabled='[ComponentState.Action, ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
					:tooltip='$t("components.config.daemon.scheduler.actions.start")'
					@click='startTask(item.taskId)'
				/>
				<TaskForm
					:action='Action.Edit'
					:messagings='messagings'
					:scheduler-task='item'
					:disabled='[ComponentState.Action, ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
					@saved='listTasks()'
				/>
				<TaskDeleteDialog
					:task-id='item.taskId'
					:disabled='[ComponentState.Action, ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
					@deleted='listTasks()'
				/>
			</template>
		</IDataTable>
	</ICard>
</template>

<script lang='ts' setup>
import { SchedulerMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { SchedulerService } from '@iqrf/iqrf-gateway-daemon-utils/services';
import { DaemonApiResponse, SchedulerRecord, SchedulerRecordTimeSpec } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { IqrfGatewayDaemonMessagingInstances } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { FileDownloader } from '@iqrf/iqrf-gateway-webapp-client/utils';
import {
	Action,
	ComponentState,
	IActionBtn,
	IBooleanIcon,
	ICard,
	IDataTable,
	IDataTableAction,
} from '@iqrf/iqrf-vue-ui';
import cronstrue from 'cronstrue';
import { DateTime, Duration } from 'luxon';
import { computed, onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import TaskDeleteDialog from '@/components/config/daemon/scheduler/TaskDeleteDialog.vue';
import TaskForm from '@/components/config/daemon/scheduler/TaskForm.vue';
import TasksDeleteDialog from '@/components/config/daemon/scheduler/TasksDeleteDialog.vue';
import TasksImportDialog from '@/components/config/daemon/scheduler/TasksImportDialog.vue';
import { useApiClient } from '@/services/ApiClient';
import { useDaemonStore } from '@/store/daemonSocket';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const daemonStore = useDaemonStore();
const webappSchedulerService: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const headers = computed(() => [
	{ key: 'taskId', title: i18n.t('components.config.daemon.scheduler.taskId') },
	{ key: 'description', title: i18n.t('common.labels.description') },
	{ key: 'time', title: i18n.t('components.config.daemon.scheduler.time'), sortable: false },
	{ key: 'active', title: i18n.t('common.states.active') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end' },
]);
const msgId: Ref<string | null> = ref(null);
const tasks: Ref<SchedulerRecord[]> = ref([]);
const messagings: Ref<IqrfGatewayDaemonMessagingInstances | null> = ref(null);

daemonStore.$onAction(
	({ name, after }) => {
		if (name !== 'onMessage') {
			return;
		}
		after((rsp: DaemonApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			daemonStore.removeMessage(msgId.value);
			switch (rsp.mType) {
				case SchedulerMessages.ListTasks:
					handleListTasks(rsp);
					break;
				case SchedulerMessages.GetTask:
					handleGetTask(rsp);
					break;
				case SchedulerMessages.StartTask:
					handleStartTask(rsp);
					break;
				case SchedulerMessages.StopTask:
					handleStopTask(rsp);
					break;
				default:
				//
			}
		});
	},
);

const noDataText = computed(() => {
	if (componentState.value === ComponentState.FetchFailed) {
		return 'components.config.daemon.scheduler.noData.fetchError';
	}
	return 'components.config.daemon.scheduler.noData.empty';
});

function timeString(timespec: SchedulerRecordTimeSpec): string {
	if (timespec.exactTime) {
		return `At ${DateTime.fromISO(timespec.startTime).toLocaleString(DateTime.DATETIME_FULL)}`;
	}
	if (timespec.periodic) {
		const duration = Duration.fromMillis(timespec.period * 1_000).normalize().rescale();
		return `Every ${duration.toHuman({ listStyle: 'long' })}`;
	}
	return cronstrue.toString(timespec.cronTime as string);
}

async function exportTasks(): Promise<void> {
	componentState.value = ComponentState.Action;
	try {
		const file = await webappSchedulerService.exportScheduler();
		FileDownloader.downloadFileResponse(
			file,
			`iqrf-gateway-scheduler_${new Date().toISOString()}.zip`,
		);
	} catch {
		toast.error(
			i18n.t('components.config.daemon.scheduler.messages.export.failed'),
		);
	}
	componentState.value = ComponentState.Ready;
}

async function getMessagings(): Promise<void> {
	try {
		messagings.value = await webappSchedulerService.getMessagingInstances();
	} catch {
		toast.error(
			i18n.t('components.config.daemon.scheduler.messages.listMessagings.failed'),
		);
	}
}

async function listTasks(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	const opts = new DaemonMessageOptions(
		null,
		30_000,
		i18n.t('components.config.daemon.scheduler.messages.list.failed'),
		() => {
			componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
			msgId.value = null;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		SchedulerService.listTasks(true, opts),
	);
}

function handleListTasks(rsp: DaemonApiResponse): void {
	if (rsp.data.status !== 0) {
		toast.error(
			i18n.t('components.config.daemon.scheduler.messages.list.failed'),
		);
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
		return;
	}
	tasks.value = rsp.data.rsp.tasks;
	componentState.value = ComponentState.Ready;
}

async function getTask(taskId: string): Promise<void> {
	componentState.value = ComponentState.Action;
	const opts = new DaemonMessageOptions(
		null,
		30_000,
		i18n.t('components.config.daemon.scheduler.messages.fetch.timeout'),
		() => {
			componentState.value = ComponentState.Ready;
			msgId.value = null;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		SchedulerService.getTask(taskId, opts),
	);
}

async function handleGetTask(rsp: DaemonApiResponse): Promise<void> {
	if (rsp.data.status !== 0) {
		toast.error(
			i18n.t('components.config.daemon.scheduler.messages.fetch.failed'),
		);
		componentState.value = ComponentState.Ready;
		return;
	}
	const taskId = rsp.data.rsp.taskId as string;
	const idx = tasks.value.findIndex((item: SchedulerRecord) => item.taskId === taskId);
	if (idx !== -1) {
		tasks.value[idx] = rsp.data.rsp as SchedulerRecord;
	}
	componentState.value = ComponentState.Ready;
}

async function startTask(taskId: string): Promise<void> {
	componentState.value = ComponentState.Action;
	const opts = new DaemonMessageOptions(
		null,
		30_000,
		i18n.t('components.config.daemon.scheduler.messages.start.timeout'),
		() => {
			componentState.value = ComponentState.Ready;
			msgId.value = null;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		SchedulerService.startTask(taskId, opts),
	);
}

function handleStartTask(rsp: DaemonApiResponse): void {
	if (rsp.data.status !== 0) {
		toast.error(
			i18n.t('components.config.daemon.scheduler.messages.start.failed'),
		);
		componentState.value = ComponentState.Ready;
		return;
	}
	const taskId = rsp.data.rsp.taskId as string;
	toast.success(
		i18n.t('components.config.daemon.scheduler.messages.start.success', { id: taskId }),
	);
	getTask(taskId);
}

async function stopTask(taskId: string): Promise<void> {
	componentState.value = ComponentState.Action;
	const opts = new DaemonMessageOptions(
		null,
		30_000,
		i18n.t('components.config.daemon.scheduler.messages.stop.timeout'),
		() => {
			componentState.value = ComponentState.Ready;
			msgId.value = null;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		SchedulerService.stopTask(taskId, opts),
	);
}

function handleStopTask(rsp: DaemonApiResponse): void {
	if (rsp.data.status !== 0) {
		toast.error(
			i18n.t('components.config.daemon.scheduler.messages.stop.failed'),
		);
		componentState.value = ComponentState.Ready;
		return;
	}
	const taskId = rsp.data.rsp.taskId as string;
	toast.success(
		i18n.t('components.config.daemon.scheduler.messages.stop.success', { id: taskId }),
	);
	getTask(taskId);
}

onMounted(() => {
	listTasks();
	getMessagings();
});
</script>
