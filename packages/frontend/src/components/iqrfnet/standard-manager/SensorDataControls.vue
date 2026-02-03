<template>
	<ICard>
		<template #title>
			{{ $t('components.iqrfnet.standard-manager.sensor-data.title') }}
		</template>
		<template #titleActions>
			<IActionBtn
				:action='Action.Reload'
				container-type='card-title'
				:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
				:disabled='componentState === ComponentState.Action || dataReadingInProgress'
				:tooltip='$t("components.iqrfnet.standard-manager.sensor-data.actions.status")'
				@click='getStatus()'
			/>
		</template>
		<v-alert
			:title='$t("components.iqrfnet.standard-manager.sensor-data.status.title")'
			:color='statusColor'
			:icon='statusIcon'
			:text='statusMessage'
			variant='tonal'
		/>
		<template #actions>
			<IActionBtn
				color='info'
				:icon='mdiPlayCircleOutline'
				:loading='componentState === ComponentState.Action && workerAction === WorkerAction.Invoke'
				:disabled='!status || !status.running || dataReadingInProgress || [ComponentState.Loading, ComponentState.Reloading].includes(componentState) || (componentState === ComponentState.Action && workerAction !== WorkerAction.Invoke)'
				:text='$t("components.iqrfnet.standard-manager.sensor-data.actions.invoke")'
				@click='invoke()'
			/>
			<IActionBtn
				color='primary'
				:icon='mdiPlay'
				:loading='componentState === ComponentState.Action && workerAction === WorkerAction.Start'
				:disabled='!status || status.running || dataReadingInProgress || [ComponentState.Loading, ComponentState.Reloading].includes(componentState) || (componentState === ComponentState.Action && workerAction !== WorkerAction.Start)'
				:text='$t("components.iqrfnet.standard-manager.sensor-data.actions.start")'
				@click='start()'
			/>
			<IActionBtn
				color='grey-darken-2'
				:icon='mdiStop'
				:loading='componentState === ComponentState.Action && workerAction === WorkerAction.Stop'
				:disabled='!status || !status.running || dataReadingInProgress || [ComponentState.Loading, ComponentState.Reloading].includes(componentState) || (componentState === ComponentState.Action && workerAction !== WorkerAction.Stop)'
				:text='$t("components.iqrfnet.standard-manager.sensor-data.actions.stop")'
				@click='stop()'
			/>
		</template>
	</ICard>
</template>

<script lang='ts' setup>
import { SensorDataMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { SensorDataService } from '@iqrf/iqrf-gateway-daemon-utils/services';
import { DaemonApiResponse, SensorDataStatus } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { Action, ComponentState, IActionBtn, ICard } from '@iqrf/iqrf-vue-ui';
import { mdiAlertCircleOutline, mdiClockOutline, mdiPauseCircleOutline, mdiPlay, mdiPlayCircleOutline, mdiStop } from '@mdi/js';
import { storeToRefs } from 'pinia';
import { computed, onBeforeUnmount, onMounted, ref, Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { DaemonApiSendError } from '@/errors/DaemonApiSendError';
import { useDaemonStore } from '@/store/daemonSocket';
import { useMonitorStore } from '@/store/monitorSocket';

enum WorkerAction {
	Invoke = 0,
	Start = 0,
	Stop = 0,
}

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const daemonStore = useDaemonStore();
const monitorStore = useMonitorStore();
const { dataReadingInProgress } = storeToRefs(monitorStore);
const status: Ref<SensorDataStatus | null> = ref(null);
const workerAction: Ref<WorkerAction | null> = ref(null);
const msgId: Ref<string | null> = ref(null);

daemonStore.$onAction(({ name, after }) => {
	if (name === 'onMessage') {
		after((rsp: DaemonApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			daemonStore.removeMessage(msgId.value);
			switch (rsp.mType) {
				case SensorDataMessages.Status:
					handleGetStatus(rsp);
					break;
				case SensorDataMessages.Invoke:
					handleInvoke(rsp);
					break;
				case SensorDataMessages.Start:
					handleStart(rsp);
					break;
				case SensorDataMessages.Stop:
					handleStop(rsp);
					break;
			}
		});
	}
});

const statusColor = computed(() => {
	if (status.value === null) {
		return 'warning';
	}
	if (!status.value.running) {
		return 'grey';
	}
	if (!dataReadingInProgress.value) {
		return 'primary';
	}
	return 'info';
});

const statusIcon = computed(() => {
	if (status.value === null) {
		return mdiAlertCircleOutline;
	}
	if (!status.value.running) {
		return mdiPauseCircleOutline;
	}
	if (!dataReadingInProgress.value) {
		return mdiClockOutline;
	}
	return mdiPlayCircleOutline;
});

const statusMessage = computed(() => {
	if (status.value === null) {
		return i18n.t('components.iqrfnet.standard-manager.sensor-data.status.unknown');
	}
	if (!status.value.running) {
		return i18n.t('components.iqrfnet.standard-manager.sensor-data.status.inactive');
	}
	if (!dataReadingInProgress.value) {
		return i18n.t('components.iqrfnet.standard-manager.sensor-data.status.idle');
	}
	return i18n.t('components.iqrfnet.standard-manager.sensor-data.status.reading');
});

async function getStatus(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	const opts = new DaemonMessageOptions(
		null,
		10_000,
		i18n.t('components.iqrfnet.standard-manager.sensor-data.messages.status.timeout'),
	);
	try {
		msgId.value = await daemonStore.sendMessage(
			SensorDataService.status(opts),
		);
	} catch (error) {
		if (error instanceof DaemonApiSendError) {
			console.error(error);
		}
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
	}
}

function handleGetStatus(rsp: DaemonApiResponse): void {
	if (rsp.data.status !== 0) {
		toast.error(
			i18n.t('components.iqrfnet.standard-manager.sensor-data.messages.status.failed'),
		);
		componentState.value = ComponentState.FetchFailed;
		return;
	}
	componentState.value = ComponentState.Ready;
	status.value = rsp.data.rsp as SensorDataStatus;
}

async function invoke(): Promise<void> {
	componentState.value = ComponentState.Action;
	const opts = new DaemonMessageOptions(
		null,
		10_000,
		i18n.t('components.iqrfnet.standard-manager.sensor-data.messages.invoke.timeout'),
	);
	try {
		msgId.value = await daemonStore.sendMessage(
			SensorDataService.invoke(opts),
		);
	} catch (error) {
		if (error instanceof DaemonApiSendError) {
			console.error(error);
			toast.error(error.message);
		}
		componentState.value = ComponentState.Ready;
	}
}

function handleInvoke(rsp: DaemonApiResponse): void {
	if (rsp.data.status !== 0) {
		componentState.value = ComponentState.Ready;
		toast.error(
			i18n.t('components.iqrfnet.standard-manager.sensor-data.messages.invoke.failed'),
		);
		return;
	}
	toast.success(
		i18n.t('components.iqrfnet.standard-manager.sensor-data.messages.invoke.success'),
	);
	getStatus();
}

async function start(): Promise<void> {
	componentState.value = ComponentState.Action;
	const opts = new DaemonMessageOptions(
		null,
		10_000,
		i18n.t('components.iqrfnet.standard-manager.sensor-data.messages.start.timeout'),
	);
	try {
		msgId.value = await daemonStore.sendMessage(
			SensorDataService.start(opts),
		);
	} catch (error) {
		if (error instanceof DaemonApiSendError) {
			console.error(error);
			toast.error(error.message);
		}
		componentState.value = ComponentState.Ready;
	}
}

function handleStart(rsp: DaemonApiResponse): void {
	if (rsp.data.status !== 0) {
		componentState.value = ComponentState.Ready;
		toast.error(
			i18n.t('components.iqrfnet.standard-manager.sensor-data.messages.start.failed'),
		);
		return;
	}
	toast.success(
		i18n.t('components.iqrfnet.standard-manager.sensor-data.messages.start.success'),
	);
	getStatus();
}

async function stop(): Promise<void> {
	componentState.value = ComponentState.Action;
	const opts = new DaemonMessageOptions(
		null,
		10_000,
		i18n.t('components.iqrfnet.standard-manager.sensor-data.messages.stop.timeout'),
	);
	try {
		msgId.value = await daemonStore.sendMessage(
			SensorDataService.stop(opts),
		);
	} catch (error) {
		if (error instanceof DaemonApiSendError) {
			console.error(error);
			toast.error(error.message);
		}
		componentState.value = ComponentState.Ready;
	}
}

function handleStop(rsp: DaemonApiResponse): void {
	if (rsp.data.status !== 0) {
		componentState.value = ComponentState.Ready;
		toast.error(
			i18n.t('components.iqrfnet.standard-manager.sensor-data.messages.stop.failed'),
		);
		return;
	}
	toast.success(
		i18n.t('components.iqrfnet.standard-manager.sensor-data.messages.stop.success'),
	);
	getStatus();
}

onMounted(() => {
	getStatus();
});

onBeforeUnmount(() => {
	daemonStore.removeMessage(msgId.value);
});

</script>
