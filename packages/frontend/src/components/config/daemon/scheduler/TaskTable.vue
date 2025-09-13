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
			<v-tooltip location='bottom'>
				<template #activator='{ props }'>
					<v-btn
						v-bind='props'
						color='white'
						size='large'
						:icon='mdiReload'
						@click='listTasks()'
					/>
				</template>
				{{ $t('components.config.daemon.scheduler.actions.reload') }}
			</v-tooltip>
			<TaskForm
				:action='Action.Add'
				:messagings='messagings'
				@saved='listTasks()'
			/>
			<TasksImportDialog @imported='listTasks()' />
			<v-tooltip location='bottom'>
				<template #activator='{ props }'>
					<v-btn
						v-bind='props'
						color='white'
						size='large'
						:icon='mdiExport'
						@click='exportTasks()'
					/>
				</template>
				{{ $t('components.config.daemon.scheduler.actions.export') }}
			</v-tooltip>
			<TasksDeleteDialog
				@deleted='listTasks()'
			/>
		</template>
		<DataTable
			:headers='headers'
			:items='tasks'
			:hover='true'
			:dense='true'
			:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
			no-data-text='components.config.daemon.scheduler.noTasks'
		>
			<template #item.time='{ item }'>
				{{ timeString(item.timeSpec) }}
			</template>
			<template #item.active='{ item }'>
				<v-skeleton-loader
					class='table-row-skeleton-loader'
					:loading='loadingTasks.includes(item.taskId)'
					type='text'
				>
					<v-responsive>
						<BooleanCheckMarker
							v-if='item.active !== undefined'
							:value='item.active'
						/>
					</v-responsive>
				</v-skeleton-loader>
			</template>
			<template #item.actions='{ item }'>
				<v-skeleton-loader
					:loading='loadingTasks.includes(item.taskId)'
					type='text'
				>
					<v-responsive>
						<span>
							<v-icon
								:icon='activateIcon(item.active)'
								size='large'
								color='primary'
								class='me-2'
								@click='item.active ? stopTask(item.taskId) : startTask(item.taskId)'
							/>
							<v-tooltip
								activator='parent'
								location='bottom'
							>
								{{ $t(`components.config.daemon.scheduler.actions.${item.active ? 'stop' : 'start'}`) }}
							</v-tooltip>
						</span>
						<span>
							<TaskForm
								:action='Action.Edit'
								:messagings='messagings'
								:scheduler-task='item'
								@saved='listTasks()'
							/>
							<v-tooltip
								activator='parent'
								location='bottom'
							>
								{{ $t('components.config.daemon.scheduler.actions.edit') }}
							</v-tooltip>
						</span>
						<TaskDeleteDialog
							:task-id='item.taskId'
							@deleted='listTasks()'
						/>
					</v-responsive>
				</v-skeleton-loader>
			</template>
		</DataTable>
	</ICard>
</template>

<script lang='ts' setup>
import { SchedulerMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { SchedulerService } from '@iqrf/iqrf-gateway-daemon-utils/services';
import { type ApiResponseManagementRsp, type TApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import {
	type SchedulerGetTaskResult,
	type SchedulerListDetailResult,
	type SchedulerStartTaskResult,
	type SchedulerStopTaskResult,
	type SchedulerTimeSpec,
} from '@iqrf/iqrf-gateway-daemon-utils/types/management';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { type IqrfGatewayDaemonSchedulerMessagings } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { FileDownloader } from '@iqrf/iqrf-gateway-webapp-client/utils';
import { Action, ICard } from '@iqrf/iqrf-vue-ui';
import { mdiExport, mdiPlay, mdiReload, mdiStop } from '@mdi/js';
import cronstrue from 'cronstrue';
import { DateTime, Duration } from 'luxon';
import { onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import BooleanCheckMarker from '@/components/BooleanCheckMarker.vue';
import TaskDeleteDialog from '@/components/config/daemon/scheduler/TaskDeleteDialog.vue';
import TaskForm from '@/components/config/daemon/scheduler/TaskForm.vue';
import TasksDeleteDialog from '@/components/config/daemon/scheduler/TasksDeleteDialog.vue';
import TasksImportDialog from '@/components/config/daemon/scheduler/TasksImportDialog.vue';
import DataTable from '@/components/layout/data-table/DataTable.vue';
import { useApiClient } from '@/services/ApiClient';
import { useDaemonStore } from '@/store/daemonSocket';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const daemonStore = useDaemonStore();
const webappSchedulerService: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const headers = [
	{ key: 'taskId', title: i18n.t('components.config.daemon.scheduler.taskId') },
	{ key: 'description', title: i18n.t('common.labels.description') },
	{ key: 'time', title: i18n.t('components.config.daemon.scheduler.time') },
	{ key: 'active', title: i18n.t('common.states.active') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end' },
];
const msgId: Ref<string | null> = ref(null);
const tasks: Ref<SchedulerGetTaskResult[]> = ref([]);
const loadingTasks: Ref<string[]> = ref([]);
const messagings: Ref<IqrfGatewayDaemonSchedulerMessagings | null> = ref(null);

daemonStore.$onAction(
	({ name, after }) => {
		if (name !== 'onMessage') {
			return;
		}
		after((rsp: TApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			daemonStore.removeMessage(msgId.value);
			switch (rsp.mType) {
				case SchedulerMessages.ListTasks:
					handleListTasks(rsp as ApiResponseManagementRsp<SchedulerListDetailResult>);
					break;
				case SchedulerMessages.GetTask:
					handleGetTask(rsp as ApiResponseManagementRsp<SchedulerGetTaskResult>);
					break;
				case SchedulerMessages.StartTask:
					handleStartTask(rsp as ApiResponseManagementRsp<SchedulerStartTaskResult>);
					break;
				case SchedulerMessages.StopTask:
					handleStopTask(rsp as ApiResponseManagementRsp<SchedulerStopTaskResult>);
					break;
				default:
				//
			}
		});
	},
);

function activateIcon(active: boolean): string {
	return active ? mdiStop : mdiPlay;
}

function timeString(timespec: SchedulerTimeSpec): string {
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
	componentState.value = ComponentState.Loading;
	try {
		const file = await webappSchedulerService.exportScheduler();
		FileDownloader.downloadFileResponse(
			file,
			`iqrf-gateway-scheduler_${new Date().toISOString()}.zip`,
		);
	} catch {
		toast.error('TODO EXPORT ERROR HANDLING');
	}
	componentState.value = ComponentState.Ready;
}

async function getMessagings(): Promise<void> {
	try {
		messagings.value = await webappSchedulerService.getSchedulerMessagings();
	} catch {
		//
	}
}

async function listTasks(): Promise<void> {
	if (componentState.value === ComponentState.Created) {
		componentState.value = ComponentState.Loading;
	} else {
		componentState.value = ComponentState.Reloading;
	}
	loadingTasks.value = [];
	const opts = new DaemonMessageOptions(
		30_000,
		null,
		() => {msgId.value = null;},
	);
	msgId.value = await daemonStore.sendMessage(
		SchedulerService.listTasks(
			{},
			{ details: true, clientId: SchedulerService.ClientID },
			opts,
		),
	);
}

function handleListTasks(rsp: ApiResponseManagementRsp<SchedulerListDetailResult>): void {
	if (rsp.data.status !== 0) {
		toast.error('TODO LIST ERROR HANDLING');
		return;
	}
	tasks.value = rsp.data.rsp.tasks as SchedulerGetTaskResult[];
	componentState.value = ComponentState.Ready;
}

async function getTask(taskId: string): Promise<void> {
	if (!loadingTasks.value.includes(taskId)) {
		loadingTasks.value.push(taskId);
	}
	const opts = new DaemonMessageOptions(
		30_000,
		null,
		() => {
			removeLoadingTask(taskId);
			msgId.value = null;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		SchedulerService.getTask(
			{},
			{ clientId: SchedulerService.ClientID, taskId: taskId },
			opts,
		),
	);
}

async function handleGetTask(rsp: ApiResponseManagementRsp<SchedulerGetTaskResult>): Promise<void> {
	if (rsp.data.status !== 0) {
		toast.error('TODO GET ERROR HANDLING');
		return;
	}
	const taskId = rsp.data.rsp.taskId as string;
	const idx = tasks.value.findIndex((item: SchedulerGetTaskResult) => item.taskId === taskId);
	if (idx !== -1) {
		tasks.value[idx] = rsp.data.rsp as SchedulerGetTaskResult;
		removeLoadingTask(taskId);
	}
}

async function startTask(taskId: string): Promise<void> {
	loadingTasks.value.push(taskId);
	const opts = new DaemonMessageOptions(
		30_000,
		null,
		() => {
			removeLoadingTask(taskId);
			msgId.value = null;},
	);
	msgId.value = await daemonStore.sendMessage(
		SchedulerService.startTask(
			{},
			{ clientId: SchedulerService.ClientID, taskId: taskId },
			opts,
		),
	);
}

function handleStartTask(rsp: ApiResponseManagementRsp<SchedulerStartTaskResult>): void {
	if (rsp.data.status !== 0) {
		toast.error('TODO START ERROR HANDLING');
		return;
	}
	const taskId = rsp.data.rsp.taskId as string;
	toast.success(
		i18n.t('components.config.daemon.scheduler.messages.start.success', { id: taskId }),
	);
	getTask(taskId);
}

async function stopTask(taskId: string): Promise<void> {
	loadingTasks.value.push(taskId);
	const opts = new DaemonMessageOptions(
		30_000,
		null,
		() => {
			removeLoadingTask(taskId);
			msgId.value = null;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		SchedulerService.stopTask(
			{},
			{ clientId: SchedulerService.ClientID, taskId: taskId },
			opts,
		),
	);
}

function handleStopTask(rsp: ApiResponseManagementRsp<SchedulerStopTaskResult>): void {
	if (rsp.data.status !== 0) {
		toast.error('TODO STOP ERROR HANDLING');
		return;
	}
	const taskId = rsp.data.rsp.taskId as string;
	toast.success(
		i18n.t('components.config.daemon.scheduler.messages.stop.success', { id: taskId }),
	);
	getTask(taskId);
}

function removeLoadingTask(taskId: string): void {
	const idx = loadingTasks.value.indexOf(taskId);
	if (idx === -1) {
		return;
	}
	loadingTasks.value.splice(idx, 1);
}

onMounted(() => {
	listTasks();
	getMessagings();
});
</script>
