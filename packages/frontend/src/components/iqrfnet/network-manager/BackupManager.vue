<template>
	<v-form
		ref='form'
		v-slot='{ isValid }'
		:disabled='componentState === ComponentState.Action'
	>
		<ICard flat tile>
			<v-card-title>
				{{ $t('components.iqrfnet.network-manager.backup.title') }}
			</v-card-title>
			<ISelectInput
				v-model='targetType'
				:label='$t("components.iqrfnet.network-manager.backup.target")'
				:items='targetTypeOptions'
			/>
			<INumberInput
				v-if='targetType === TargetType.NODE'
				v-model='address'
				:label='$t("components.iqrfnet.common.address")'
				:rules='[
					(v: number|null) => ValidationRules.required(v, $t("components.iqrfnet.common.validation.address.required")),
					(v: number) => ValidationRules.integer(v, $t("components.iqrfnet.common.validation.address.integer")),
					(v: number) => ValidationRules.between(v, 1, 239, $t("components.iqrfnet.common.validation.address.between")),
				]'
				:min='1'
				:max='239'
				:required='targetType === TargetType.NODE'
			/>
			<template #actions>
				<IActionBtn
					:action='Action.Custom'
					:icon='mdiExport'
					:loading='componentState === ComponentState.Action'
					:disabled='!isValid.value'
					:text='$t("components.iqrfnet.network-manager.backup.actions.backup")'
					@click='runBackup()'
				/>
			</template>
		</ICard>
	</v-form>
	<BackupLog
		ref='backupLog'
		:messages='messages'
		:progress='progress'
		:component-state='componentState'
		:data-len='deviceData.length'
		@generate-backup='generateAndDownloadBackupFile()'
	/>
</template>

<script lang='ts' setup>
import { IqmeshServiceMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { BackupService } from '@iqrf/iqrf-gateway-daemon-utils/services/iqmesh';
import { DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { IqmeshBackupParams } from '@iqrf/iqrf-gateway-daemon-utils/types/iqmesh';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { Action, ComponentState, IActionBtn, ICard, INumberInput, ISelectInput, ValidationRules } from '@iqrf/iqrf-vue-ui';
import { mdiExport } from '@mdi/js';
import saveAs from 'file-saver';
import { computed, onBeforeUnmount, onMounted, ref, type Ref, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';

import BackupLog from '@/components/iqrfnet/network-manager/BackupLog.vue';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';
import { useDaemonStore } from '@/store/daemonSocket';

enum TargetType {
	COORDINATOR = 0,
	NODE = 1,
	NETWORK = 2,
}

interface BackupData {
	data?: string;
	deviceAddr: number;
	dpaVer?: number;
	mid?: number;
	online: boolean;
}

const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const i18n = useI18n();
const daemonStore = useDaemonStore();
const msgId: Ref<string | null> = ref(null);
const form: Ref<VForm | null> = useTemplateRef('form');
const backupLog: Ref<InstanceType<typeof BackupLog> | null> = useTemplateRef('backupLog');
const targetType: Ref<TargetType> = ref(TargetType.COORDINATOR);
const targetTypeOptions = computed(() => [
	{
		title: i18n.t('components.iqrfnet.common.peripherals.coordinator'),
		value: TargetType.COORDINATOR,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.backup.targets.node'),
		value: TargetType.NODE,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.backup.targets.network'),
		value: TargetType.NETWORK,
	},
]);
const address: Ref<number> = ref(1);
const deviceData: Ref<BackupData[]> = ref([]);
const failedDevices: Ref<number[]> = ref([]);
const messages: Ref<string[]> = ref([]);
const progress: Ref<number> = ref(0);
const webappVersion: Ref<string> = ref('unknown');

daemonStore.$onAction(({ name, after }) => {
	if (name === 'onMessage') {
		after((rsp: DaemonApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			daemonStore.removeMessage(msgId.value);
			if (rsp.mType === IqmeshServiceMessages.Backup) {
				handleBackup(rsp);
			}
		});
	}
});

async function getWebappVersion(): Promise<void> {
	try {
		webappVersion.value = (await useApiClient().getVersionService().getWebapp()).version;
	} catch {
		//
	}
}

async function runBackup(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	progress.value = 0;
	messages.value = [];
	deviceData.value = [];
	failedDevices.value = [];
	componentState.value = ComponentState.Action;
	const addr = targetType.value === TargetType.COORDINATOR ? 0 : address.value;
	const params: IqmeshBackupParams = {
		deviceAddr: addr,
	};
	if (targetType.value === TargetType.NETWORK) {
		params.wholeNetwork = true;
	}
	const opts = new DaemonMessageOptions(null);
	backupLog.value?.open();
	if (targetType.value === TargetType.COORDINATOR) {
		messages.value.push(
			i18n.t('components.iqrfnet.network-manager.backup.messages.coordinator.start'),
		);
	} else if (targetType.value === TargetType.NODE) {
		messages.value.push(
			i18n.t('components.iqrfnet.network-manager.backup.messages.node.start', { address: address.value }),
		);
	} else {
		messages.value.push(
			i18n.t('components.iqrfnet.network-manager.backup.messages.network.start'),
		);
	}
	msgId.value = await daemonStore.sendMessage(
		BackupService.backup(
			{ repeat: 1, returnVerbose: true },
			params,
			opts,
		),
	);
}

function handleBackup(rsp: DaemonApiResponse): void {
	if (rsp.data.status === 0) {
		processDeviceData(rsp.data.rsp.devices[0]);
		progress.value = rsp.data.rsp.progress;
		if (rsp.data.rsp.progress === 100) {
			finalizeBackup();
		}
		return;
	}

	if (targetType.value === TargetType.COORDINATOR) {
		daemonStore.removeMessage(msgId.value);
		componentState.value = ComponentState.Idle;
		messages.value.push(
			i18n.t('components.iqrfnet.network-manager.backup.messages.coordinator.failed'),
		);
		toast.error(
			i18n.t('components.iqrfnet.network-manager.backup.messages.coordinator.failed'),
		);
	} else if (targetType.value === TargetType.NODE) {
		daemonStore.removeMessage(msgId.value);
		componentState.value = ComponentState.Idle;
		messages.value.push(
			i18n.t('components.iqrfnet.network-manager.backup.messages.node.failed', { address: address.value }),
		);
		toast.error(
			i18n.t('components.iqrfnet.network-manager.backup.messages.node.failed', { address: address.value }),
		);

	} else if (rsp.data.status === -1 || (rsp.data.statusStr && rsp.data.statusStr.includes('ERROR_TIMEOUT'))) {
		if (rsp.data.rsp === undefined) {
			messages.value.push(
				i18n.t('components.iqrfnet.network-manager.backup.messages.network.failed'),
			);
			toast.error(
				i18n.t('components.iqrfnet.network-manager.backup.messages.network.failed'),
			);
			finalizeBackup();
			return;
		}
		processDeviceData(rsp.data.rsp.devices[0]);
		progress.value = rsp.data.rsp.progress;
		if (rsp.data.rsp.progress === 100) {
			finalizeBackup();
		}
	}
}

function processDeviceData(rsp: BackupData): void {
	if (!rsp.online) {
		if (rsp.deviceAddr === 0) {
			messages.value.push(
				i18n.t('components.iqrfnet.network-manager.backup.messages.coordinator.failed'),
			);
		} else {
			messages.value.push(
				i18n.t('components.iqrfnet.network-manager.backup.messages.node.failed', { address: rsp.deviceAddr }),
			);
		}
		failedDevices.value.push(rsp.deviceAddr);
	} else {
		if (rsp.deviceAddr === 0) {
			messages.value.push(
				i18n.t('components.iqrfnet.network-manager.backup.messages.coordinator.success'),
			);
		} else {
			messages.value.push(
				i18n.t('components.iqrfnet.network-manager.backup.messages.node.success', { address: rsp.deviceAddr }),
			);
		}
		deviceData.value.push(rsp);
	}
}

function finalizeBackup(): void {
	daemonStore.removeMessage(msgId.value);
	componentState.value = ComponentState.Idle;
	if (targetType.value === TargetType.COORDINATOR) {
		messages.value.push(
			i18n.t('components.iqrfnet.network-manager.backup.messages.coordinator.finish'),
		);
	} else if (targetType.value === TargetType.NODE) {
		messages.value.push(
			i18n.t('components.iqrfnet.network-manager.backup.messages.node.finish', { address: address.value }),
		);
	} else {
		messages.value.push(
			i18n.t('components.iqrfnet.network-manager.backup.messages.network.finish'),
		);
	}
	if (failedDevices.value.length > 0) {
		messages.value.push(
			i18n.t('components.iqrfnet.network-manager.backup.messages.failedNodes', { nodes: failedDevices.value.join(', ') }),
		);
	}
}

function generateAndDownloadBackupFile(): void {
	if (deviceData.value.length === 0) {
		toast.error(
			i18n.t('components.iqrfnet.network-manager.backup.messages.noData'),
		);
		return;
	}
	const created = new Date()
		.toLocaleString('en-GB')
		.replaceAll('/', '.')
		.replaceAll(',', '');
	let fileContent = `[Backup]\nCreated=${created} IQRF GW Webapp: ${webappVersion.value}\n\n`;
	let fileName: string;
	if (targetType.value === TargetType.COORDINATOR) {
		fileName = 'Coordinator_';
		fileContent += `${coordinatorBackup()}\n`;
	} else if (targetType.value === TargetType.NODE) {
		fileName = 'Node_';
		fileContent += `${nodeBackup(0)}\n`;
	} else {
		fileName = 'Network_';
		fileContent += networkBackup();
	}
	const timeStamp = new Date()
		.toISOString()
		.slice(2, 10)
		.replaceAll('-', '');
	fileName += `${deviceData.value[0].mid!.toString(16).toUpperCase()}_${timeStamp}.iqrfbkp`;
	const blob = new Blob([fileContent], { type: 'text/plain;charset=utf-8' });
	saveAs(blob, fileName);
}

function coordinatorBackup(): string {
	const device = deviceData.value[0];
	let message = `[${device.mid!.toString(16).toUpperCase()}]\n`;
	message += `Device=Coordinator\nVersion=${getDpaVersion(device.dpaVer!)}\n`;
	message += `DataC=${device.data!.toUpperCase()}\nAddress=${device.deviceAddr}\n`;
	return message;
}

function nodeBackup(index: number): string {
	const device = deviceData.value[index];
	let message = `[${device.mid!.toString(16).toUpperCase()}]\n`;
	message += `Device=Node\nVersion=${getDpaVersion(device.dpaVer!)}\n`;
	message += `DataN=${device.data!.toUpperCase()}\nAddress=${device.deviceAddr}\n`;
	return message;
}

function networkBackup(): string {
	let message = `${coordinatorBackup()}\n`;
	for (let i = 1; i < deviceData.value.length; ++i) {
		message += `${nodeBackup(i)}\n`;
	}
	return message;
}

function getDpaVersion(version: number): string {
	const major = version >> 8;
	const minor = version & 0xFF;
	return `${major.toString()}.${minor.toString(16).padStart(2, '0')}`;
}

onMounted(() => {
	getWebappVersion();
});

onBeforeUnmount(() => {
	daemonStore.removeMessage(msgId.value);
});

</script>
