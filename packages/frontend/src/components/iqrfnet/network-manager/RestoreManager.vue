<template>
	<v-form
		ref='form'
		v-slot='{ isValid }'
		:disabled='componentState === ComponentState.Action'
	>
		<ICard flat>
			<v-card-title>
				{{ $t('components.iqrfnet.network-manager.restore.title') }}
			</v-card-title>
			<v-alert
				class='mb-4'
				type='info'
				variant='tonal'
				:text='$t("components.iqrfnet.network-manager.restore.note.accessPassword")'
			/>
			<v-file-input
				v-model='file'
				accept='.iqrfbkp'
				:label='$t("components.iqrfnet.network-manager.restore.file")'
				:rules='[
					(v: File|Blob|null) => ValidationRules.required(v, $t("components.iqrfnet.network-manager.restore.validation.file.required")),
				]'
				:prepend-inner-icon='mdiFileOutline'
				prepend-icon=''
				show-size
				required
			/>
			<v-checkbox
				v-model='restartCoordinator'
				:label='$t("components.iqrfnet.network-manager.restore.restartCoordinator")'
				:hint='$t("components.iqrfnet.network-manager.restore.note.restart")'
				persistent-hint
				density='compact'
			/>
			<template #actions>
				<IActionBtn
					:action='Action.Custom'
					:icon='mdiImport'
					:loading='componentState === ComponentState.Action'
					:disabled='!isValid.value'
					:text='$t("components.iqrfnet.network-manager.restore.actions.run")'
					@click='runRestore()'
				/>
			</template>
		</ICard>
	</v-form>
</template>

<script lang='ts' setup>
import { IqmeshServiceMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { BackupService } from '@iqrf/iqrf-gateway-daemon-utils/services/iqmesh';
import { DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { IqmeshRestoreParams } from '@iqrf/iqrf-gateway-daemon-utils/types/iqmesh';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { Action, ComponentState, IActionBtn, ICard, ValidationRules } from '@iqrf/iqrf-vue-ui';
import { mdiFileOutline, mdiImport } from '@mdi/js';
import { parse } from 'ini';
import { onBeforeUnmount, ref, Ref, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import { validateForm } from '@/helpers/validateForm';
import { useDaemonStore } from '@/store/daemonSocket';

interface RestoreData {
	Address: string;
	DataC?: string;
	DataN?: string;
	Device: string;
	Version: string;
}

const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const emit = defineEmits<{
  updateDevices: [];
}>();
const i18n = useI18n();
const daemonStore = useDaemonStore();
const msgId: Ref<string | null> = ref(null);
const form: Ref<VForm|null> = useTemplateRef('form');
const file: Ref<File | null> = ref(null);
const restartCoordinator: Ref<boolean> = ref(false);
const restoreData: Ref<RestoreData[]> = ref([]);

daemonStore.$onAction(({ name, after }) => {
	if (name === 'onMessage') {
		after((rsp: DaemonApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			daemonStore.removeMessage(msgId.value);
			componentState.value = ComponentState.Idle;
			if (rsp.mType === IqmeshServiceMessages.Restore) {
				handleRestore(rsp);
			}
		});
	}
});

async function runRestore(): Promise<void> {
	if (!await validateForm(form.value) || file.value === null) {
		return;
	}
	restoreData.value = [];
	componentState.value = ComponentState.Action;
	const ok = await parseContent(file.value);
	if (!ok) {
		componentState.value = ComponentState.Idle;
		return;
	}
	const idx = restoreData.value.findIndex((item: RestoreData) => item.DataC !== undefined);
	if (idx === -1) {
		componentState.value = ComponentState.Idle;
		toast.error(
			i18n.t('components.iqrfnet.network-manager.restore.validation.content.missingCoordinator'),
		);
		return;
	}
	const params: IqmeshRestoreParams = {
		deviceAddr: 0,
		restartCoordinator: restartCoordinator.value,
		data: restoreData.value[idx].DataC!,
	};
	const opts = new DaemonMessageOptions(null);
	msgId.value = await daemonStore.sendMessage(
		BackupService.restore(
			{ repeat: 1, returnVerbose: true },
			params,
			opts,
		),
	);
}

async function parseContent(file: File): Promise<boolean> {
	const content = await readContents(file);
	if (content === null) {
		toast.error(
			i18n.t('components.iqrfnet.network-manager.restore.messages.read.failed'),
		);
		return false;
	}
	const data = parse(content);
	if (!('Backup' in data)) {
		toast.error(
			i18n.t('components.iqrfnet.network-manager.restore.validation.content.invalid'),
		);
		return false;
	}
	delete data.Backup;
	const backupKeys = Object.keys(data);
	for (const key of backupKeys) {
		if (!new RegExp(/^[\da-f]{8}$/i).test(key)) {
			toast.error(
				i18n.t('components.iqrfnet.network-manager.restore.validation.content.invalid'),
			);
			return false;
		}
		if (!validateEntry(data[key], key)) {
			return false;
		}
	}
	restoreData.value = Object.keys(data).map(key => data[key]);
	return true;
}

async function readContents(file: File): Promise<string | null> {
	try {
		return await file.text();
	} catch {
		return null;
	}
}

function validateEntry(entry: RestoreData, key: string): boolean {
	if (!checkForProp(entry, 'Device', key)) {
		return false;
	}
	if (!checkForProp(entry, 'Version', key)) {
		return false;
	}
	if (!checkForProp(entry, 'Address', key)) {
		return false;
	}
	const device = entry.Device;
	if (device !== 'Coordinator' && device !== 'Node') { // Check device prop value
		toast.error(
			i18n.t(
				'components.iqrfnet.network-manager.restore.validation.content.wrongDevice',
				{ entry: key, device: device },
			),
		);
		return false;
	}
	const addr = Number.parseInt(entry.Address);
	if (addr < 0 || addr > 239) { // Check address prop range
		toast.error(
			i18n.t(
				'components.iqrfnet.network-manager.restore.validation.content.wrongAddr',
				{ entry: key, address: addr },
			),
		);
		return false;
	}
	if (device === 'Coordinator') {
		if (addr !== 0) { // Check invalid coordinator address
			toast.error(
				i18n.t(
					'components.iqrfnet.network-manager.restore.validation.content.wrongCoordinatorAddr',
					{ entry: key, address: addr },
				),
			);
			return false;
		}
		if (!entry.DataC) { // Check for missing coordinator data
			toast.error(
				i18n.t(
					'components.iqrfnet.network-manager.restore.validation.content.invalidCoordinatorDataC',
					{ entry: key },
				),
			);
			return false;
		}
		if (!new RegExp(/^[\da-f]+$/i).test(entry.DataC)) { // Check for invalid charset
			toast.error(
				i18n.t(
					'components.iqrfnet.network-manager.restore.validation.content.invalidDataContent',
					{ entry: key, device: 'C' },
				),
			);
			return false;
		}
		if (entry.DataN) { // Check for extra node data
			toast.error(
				i18n.t(
					'components.iqrfnet.network-manager.restore.validation.content.invalidCoordinatorDataN',
					{ entry: key },
				),
			);
			return false;
		}
	}
	if (device === 'Node') {
		if (addr === 0) { // Check invalid node address
			toast.error(
				i18n.t(
					'components.iqrfnet.network-manager.restore.validation.content.wrongNodeAddr',
					{ entry: key, address: addr },
				),
			);
			return false;
		}
		if (!entry.DataN) { // Check for missing node data
			toast.error(
				i18n.t(
					'components.iqrfnet.network-manager.restore.validation.content.invalidNodeDataN',
					{ entry: key },
				),
			);
			return false;
		}
		if (!new RegExp(/^[\da-f]+$/i).test(entry.DataN)) { // Check for invalid charset
			toast.error(
				i18n.t(
					'components.iqrfnet.network-manager.restore.validation.content.invalidDataContent',
					{ entry: key, device: 'N' },
				),
			);
			return false;
		}
		if (entry.DataC) { // Check for extra coordinator data
			toast.error(
				i18n.t(
					'components.iqrfnet.network-manager.restore.validation.content.invalidNodeDataC',
					{ entry: key },
				),
			);
			return false;
		}
	}
	return true;
}

function checkForProp(obj: object, property: string, key: string) {
	if (!(property in obj)) {
		toast.error(
			i18n.t('components.iqrfnet.network-manager.restore.validation.content.propMissing', { item: key, property: property }),
		);
		return false;
	}
	return true;
}

function handleRestore(rsp: DaemonApiResponse): void {
	if (rsp.data.status === 0) {
		toast.success(
			i18n.t('components.iqrfnet.network-manager.restore.messages.restore.success'),
		);
		emit('updateDevices');
		return;
	}
	let message = '';
	switch (rsp.data.status) {
		case -1:
			message = i18n.t('components.iqrfnet.network-manager.restore.messages.restore.offline');
			break;
		case 1_004:
			message = i18n.t('components.iqrfnet.network-manager.restore.messages.restore.invalidSize');
			break;
		case 1_005:
			message = i18n.t('components.iqrfnet.network-manager.restore.messages.restore.invalidChecksum');
			break;
		default:
			message = i18n.t('components.iqrfnet.network-manager.restore.messages.restore.failed');
	}
	toast.error(message);
}

onBeforeUnmount(() => {
	daemonStore.removeMessage(msgId.value);
});

</script>
