<template>
	<Card>
		<template #title>
			{{ $t('pages.configuration.daemon.scheduler.title') }}
		</template>
		<template #titleActions>
			<v-tooltip location='bottom'>
				<template #activator='{ props }'>
					<v-btn
						v-bind='props'
						color='white'
						size='large'
						:icon='mdiReload'
						@click='listTasks'
					/>
				</template>
				{{ $t('components.configuration.daemon.scheduler.actions.reload') }}
			</v-tooltip>
			<TaskForm
				:action='FormAction.Add'
				:messagings='messagings'
				@saved='listTasks'
			/>
			<TasksImportDialog @imported='listTasks' />
			<v-tooltip location='bottom'>
				<template #activator='{ props }'>
					<v-btn
						v-bind='props'
						color='white'
						size='large'
						:icon='mdiExport'
						@click='exportTasks'
					/>
				</template>
				{{ $t('components.configuration.daemon.scheduler.actions.export') }}
			</v-tooltip>
			<TasksDeleteDialog
				@deleted='listTasks'
			/>
		</template>
		<DataTable
			:headers='headers'
			:items='tasks'
			:hover='true'
			:dense='true'
			:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
			no-data-text='components.configuration.daemon.scheduler.noTasks'
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
								{{ $t(`components.configuration.daemon.scheduler.actions.${item.active ? 'stop' : 'start'}`) }}
							</v-tooltip>
						</span>
						<span>
							<TaskForm
								:action='FormAction.Edit'
								:messagings='messagings'
								:scheduler-task='item'
								@saved='listTasks'
							/>
							<v-tooltip
								activator='parent'
								location='bottom'
							>
								{{ $t('components.configuration.daemon.scheduler.actions.edit') }}
							</v-tooltip>
						</span>
						<TaskDeleteDialog
							:task-id='item.taskId'
							@deleted='listTasks'
						/>
					</v-responsive>
				</v-skeleton-loader>
			</template>
		</DataTable>
	</Card>
</template>

<script lang='ts' setup>
import { SchedulerMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { SchedulerService } from '@iqrf/iqrf-gateway-daemon-utils/services';
import { type DaemonApiResponse, type SchedulerRecord, type SchedulerRecordTimeSpec } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { type IqrfGatewayDaemonSchedulerMessagings } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { FileDownloader } from '@iqrf/iqrf-gateway-webapp-client/utils';
import { mdiCheckCircle, mdiCloseCircle, mdiExport, mdiPlay, mdiReload, mdiStop } from '@mdi/js';
import cronstrue from 'cronstrue';
import { DateTime, Duration } from 'luxon';
import { onMounted, type Ref, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import BooleanCheckMarker from '@/components/BooleanCheckMarker.vue';
import Card from '@/components/Card.vue';
import TaskDeleteDialog from '@/components/config/daemon/scheduler/TaskDeleteDialog.vue';
import TaskForm from '@/components/config/daemon/scheduler/TaskForm.vue';
import TasksDeleteDialog from '@/components/config/daemon/scheduler/TasksDeleteDialog.vue';
import TasksImportDialog from '@/components/config/daemon/scheduler/TasksImportDialog.vue';
import DataTable from '@/components/DataTable.vue';
import { FormAction } from '@/enums/controls';
import { useApiClient } from '@/services/ApiClient';
import { useDaemonStore } from '@/store/daemonSocket';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const daemonStore = useDaemonStore();
const webappSchedulerService: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const headers = [
	{key: 'taskId', title: i18n.t('components.configuration.daemon.scheduler.taskId')},
	{key: 'description', title: i18n.t('common.labels.description')},
	{key: 'time', title: i18n.t('components.configuration.daemon.scheduler.time')},
	{key: 'active', title: i18n.t('common.states.active')},
	{key: 'actions', title: i18n.t('common.columns.actions'), align: 'end'},
];
const msgId: Ref<string | null> = ref(null);
const tasks: Ref<SchedulerRecord[]> = ref([]);
const loadingTasks: Ref<string[]> = ref([]);
const messagings: Ref<IqrfGatewayDaemonSchedulerMessagings | null> = ref(null);

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

function activeIcon(active: boolean): string {
	return active ? mdiCheckCircle: mdiCloseCircle;
}

function activeIconColor(active: boolean): string {
	return active ? 'success' : 'error';
}

function activateIcon(active: boolean): string {
	return active ? mdiStop : mdiPlay;
}

function timeString(timespec: SchedulerRecordTimeSpec): string {
	if (timespec.exactTime) {
		return `At ${DateTime.fromISO(timespec.startTime).toLocaleString(DateTime.DATETIME_FULL)}`;
	}
	if (timespec.periodic) {
		const duration = Duration.fromMillis(timespec.period * 1000).normalize().rescale();
		return `Every ${duration.toHuman({listStyle: 'long'})}`;
	}
	return cronstrue.toString(timespec.cronTime);
}

function exportTasks(): void {
	componentState.value = ComponentState.Loading;
	webappSchedulerService.schedulerExport()
		.then((response: ArrayBuffer) => {
			const filename = `scheduler_${new Date().toISOString()}.zip`;
			FileDownloader.downloadFromData(response, 'application/zip', filename);
			componentState.value = ComponentState.Ready;
		})
		.catch(() => {
			toast.error('TODO EXPORT ERROR HANDLING');
			componentState.value = ComponentState.Ready;
		});
}

function getMessagings(): void {
	webappSchedulerService.schedulerMessagings()
		.then((response: IqrfGatewayDaemonSchedulerMessagings) => {
			messagings.value = response;
		});
}

function listTasks(): void {
	if (componentState.value === ComponentState.Created) {
		componentState.value = ComponentState.Loading;
	} else {
		componentState.value = ComponentState.Reloading;
	}
	loadingTasks.value = [];
	const options = new DaemonMessageOptions(
		null,
		30000,
		null,
		() => {msgId.value = null;},
	);
	daemonStore.sendMessage(
		SchedulerService.listTasks(true, options),
	).then((val: string) => msgId.value = val);
}

function handleListTasks(rsp: DaemonApiResponse): void {
	if (rsp.data.status !== 0) {
		toast.error(
			i18n.t('TODO LIST ERROR HANDLING'),
		);
		return;
	}
	tasks.value = rsp.data.rsp.tasks;
	componentState.value = ComponentState.Ready;
}

function getTask(taskId: string): void {
	if (!loadingTasks.value.includes(taskId)) {
		loadingTasks.value.push(taskId);
	}
	const options = new DaemonMessageOptions(
		null,
		30000,
		null,
		() => {msgId.value = null;},
	);
	daemonStore.sendMessage(
		SchedulerService.getTask(taskId, options),
	).then((val: string) => msgId.value = val);
}

function handleGetTask(rsp: DaemonApiResponse): void {
	if (rsp.data.status !== 0) {
		toast.error(
			i18n.t('TODO GET ERROR HANDLING'),
		);
		return;
	}
	const taskId = rsp.data.rsp.taskId;
	let idx = tasks.value.findIndex((item: SchedulerRecord) => item.taskId === taskId);
	if (idx !== -1) {
		tasks.value[idx] = rsp.data.rsp as SchedulerRecord;
		idx = loadingTasks.value.indexOf(taskId);
		if (idx !== -1) {
			loadingTasks.value.splice(idx, 1);
		}
	}
}

function startTask(taskId: string): void {
	loadingTasks.value.push(taskId);
	const options = new DaemonMessageOptions(
		null,
		30000,
		null,
		() => {msgId.value = null;},
	);
	daemonStore.sendMessage(
		SchedulerService.startTask(taskId, options),
	).then((val: string) => msgId.value = val);
}

function handleStartTask(rsp: DaemonApiResponse): void {
	if (rsp.data.status !== 0) {
		toast.error(
			i18n.t('TODO START ERROR HANDLING'),
		);
		return;
	}
	const taskId = rsp.data.rsp.taskId;
	toast.success(
		i18n.t('components.configuration.daemon.scheduler.messages.start.success', {id: taskId}),
	);
	getTask(taskId);
}

function stopTask(taskId: string): void {
	loadingTasks.value.push(taskId);
	const options = new DaemonMessageOptions(
		null,
		30000,
		null,
		() => {msgId.value = null;},
	);
	daemonStore.sendMessage(
		SchedulerService.stopTask(taskId, options),
	).then((val: string) => msgId.value = val);
}

function handleStopTask(rsp: DaemonApiResponse): void {
	if (rsp.data.status !== 0) {
		toast.error(
			i18n.t('TODO STOP ERROR HANDLING'),
		);
		return;
	}
	const taskId = rsp.data.rsp.taskId;
	toast.success(
		i18n.t('components.configuration.daemon.scheduler.messages.stop.success', {id: taskId}),
	);
	getTask(taskId);
}

onMounted(() => {
	listTasks();
	getMessagings();
});
</script>
