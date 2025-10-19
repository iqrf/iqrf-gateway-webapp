<template>
	<v-form
		ref='form'
		v-slot='{ isValid }'
		:disabled='componentState === ComponentState.Action'
	>
		<ICard flat>
			<v-card-title>
				{{ $t('components.iqrfnet.network-manager.ota-upload.title') }}
			</v-card-title>
			<ISelectInput
				v-model='fileType'
				:label='$t("components.iqrfnet.network-manager.ota-upload.form.fileType")'
				:items='fileTypeOptions'
			/>
			<v-file-input
				v-model='file'
				accept='.hex,.iqrf'
				:label='$t("components.iqrfnet.network-manager.ota-upload.form.file")'
				:rules='[
					(v: File|null) => ValidationRules.required(v, $t("components.iqrfnet.network-manager.ota-upload.validation.file.required")),
					(v: File) => validateFile(v),
				]'
				prepend-icon=''
				show-size
				required
			/>
			<v-divider class='mb-4' />
			<ISelectInput
				v-model='uploadTarget'
				:label='$t("components.iqrfnet.network-manager.ota-upload.form.target")'
				:items='uploadTargetOptions'
				:description='uploadTarget === UploadTarget.Network ? $t("components.iqrfnet.network-manager.ota-upload.notes.network") : undefined'
			/>
			<INumberInput
				v-if='uploadTarget === UploadTarget.Node'
				v-model='otaParams.deviceAddr'
				:label='$t("components.iqrfnet.common.address")'
				:rules='[
					(v: number|null) => ValidationRules.required(v, $t("components.iqrfnet.common.validation.address.required")),
					(v: number) => ValidationRules.integer(v, $t("components.iqrfnet.common.validation.address.integer")),
					(v: number) => ValidationRules.between(v, 1, 239, $t("components.iqrfnet.common.validation.address.between")),
				]'
				:min='1'
				:max='239'
				required
			/>
			<INumberInput
				v-if='uploadTarget === UploadTarget.Network'
				v-model='otaParams.hwpId'
				:label='$t("components.iqrfnet.common.hwpid")'
				:rules='[
					(v: number|null) => ValidationRules.required(v, $t("components.iqrfnet.common.validation.hwpid.required")),
					(v: number) => ValidationRules.integer(v, $t("components.iqrfnet.common.validation.hwpid.integer")),
					(v: number) => ValidationRules.between(v, 0, 65535, $t("components.iqrfnet.common.validation.hwpid.between")),
				]'
				:min='0'
				:max='65535'
				required
				:description='$t("components.iqrfnet.network-manager.ota-upload.notes.hwpid")'
			/>
			<INumberInput
				v-model='otaParams.startMemAddr'
				:label='$t("components.iqrfnet.network-manager.ota-upload.form.eeepromAddress")'
				:rules='[
					(v: number|null) => ValidationRules.required(v, $t("components.iqrfnet.network-manager.ota-upload.validation.eeepromAddress.required")),
					(v: number) => ValidationRules.integer(v, $t("components.iqrfnet.network-manager.ota-upload.validation.eeepromAddress.integer")),
					(v: number) => ValidationRules.between(v, 768, 16383, $t("components.iqrfnet.network-manager.ota-upload.validation.eeepromAddress.between")),
				]'
				:min='768'
				:max='16383'
				required
			/>
			<v-checkbox
				v-model='otaParams.uploadEepromData'
				:label='$t("components.iqrfnet.network-manager.ota-upload.form.uploadEeprom")'
				hide-details
				density='compact'
			/>
			<v-checkbox
				v-model='otaParams.uploadEeepromData'
				:label='$t("components.iqrfnet.network-manager.ota-upload.form.uploadEeeprom")'
				hide-details
				density='compact'
			/>
			<v-divider class='my-2' />
			<div>
				{{ $t('components.iqrfnet.network-manager.ota-upload.form.uploadSteps.uploadEeeprom') }}<br>
				<IActionBtn
					class='mt-2'
					:action='Action.Upload'
					:loading='componentState === ComponentState.Action && currentAction === OtaUploadAction.Upload'
					:disabled='!isValid.value || (componentState === ComponentState.Action && currentAction !== OtaUploadAction.Upload)'
					:append-icon='checks.upload ? mdiCheck : undefined'
					@click='upload()'
				/>
			</div>
			<div class='my-2'>
				{{ $t('components.iqrfnet.network-manager.ota-upload.form.uploadSteps.verifyEeeprom') }}<br>
				<IActionBtn
					class='mt-2'
					:action='Action.Custom'
					:icon='mdiFileCheckOutline'
					:loading='componentState === ComponentState.Action && currentAction === OtaUploadAction.Verify'
					:disabled='!isValid.value || !checks.upload || (componentState === ComponentState.Action && currentAction !== OtaUploadAction.Verify)'
					:append-icon='checks.verify ? mdiCheck : undefined'
					:text='$t("components.iqrfnet.network-manager.ota-upload.actions.verify")'
					@click='executeStep(OtaUploadAction.Verify)'
				/>
			</div>
			<div>
				{{ $t('components.iqrfnet.network-manager.ota-upload.form.uploadSteps.loadFlash') }}<br>
				<IActionBtn
					class='mt-2'
					:action='Action.Custom'
					:icon='mdiImport'
					:loading='componentState === ComponentState.Action && currentAction === OtaUploadAction.Load'
					:disabled='!isValid.value || !checks.verify || (componentState === ComponentState.Action && currentAction !== OtaUploadAction.Load)'
					:append-icon='checks.load ? mdiCheck : undefined'
					:text='$t("components.iqrfnet.network-manager.ota-upload.actions.load")'
					@click='executeStep(OtaUploadAction.Load)'
				/>
			</div>
		</ICard>
	</v-form>
	<OtaUploadResultDialog
		ref='resultDialog'
		:results='results'
	/>
</template>

<script lang='ts' setup>
import { IqmeshServiceMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { OtaUploadService } from '@iqrf/iqrf-gateway-daemon-utils/services/iqmesh';
import { DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { IqmeshOtaUploadParams } from '@iqrf/iqrf-gateway-daemon-utils/types/iqmesh';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { FileFormat } from '@iqrf/iqrf-gateway-webapp-client/types/Iqrf';
import { Action, ComponentState, IActionBtn, ICard, INumberInput, ISelectInput, ValidationRules } from '@iqrf/iqrf-vue-ui';
import { mdiCheck, mdiFileCheckOutline, mdiImport } from '@mdi/js';
import { onBeforeUnmount, ref, Ref, toRaw, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import OtaUploadResultDialog from '@/components/iqrfnet/network-manager/OtaUploadResultDialog.vue';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';
import { useDaemonStore } from '@/store/daemonSocket';
import { OtaUploadAction, OtaUploadResult } from '@/types/DaemonApi/Iqmesh';

enum UploadTarget {
	Coordinator = 0,
	Node = 1,
	Network = 2,
}

const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const i18n = useI18n();
const daemonStore = useDaemonStore();
const form: Ref<VForm | null> = useTemplateRef('form');
const fileType: Ref<FileFormat> = ref(FileFormat.HEX);
const fileTypeOptions = [
	{
		title: i18n.t('components.iqrfnet.network-manager.ota-upload.form.fileTypes.hex'),
		value: FileFormat.HEX,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.ota-upload.form.fileTypes.iqrf'),
		value: FileFormat.IQRF,
	},
];
const file: Ref<File | null> = ref(null);
const uploadTarget: Ref<UploadTarget> = ref(UploadTarget.Coordinator);
const uploadTargetOptions = [
	{
		title: i18n.t('components.iqrfnet.network-manager.ota-upload.form.targets.coordinator'),
		value: UploadTarget.Coordinator,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.ota-upload.form.targets.node'),
		value: UploadTarget.Node,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.ota-upload.form.targets.network'),
		value: UploadTarget.Network,
	},
];
const otaParams: Ref<IqmeshOtaUploadParams> = ref({
	deviceAddr: 1,
	fileName: '',
	startMemAddr: 768,
	uploadEepromData: true,
	uploadEeepromData: true,
	hwpId: 65_535,
	loadingAction: OtaUploadAction.Upload,
});
const results: Ref<OtaUploadResult[]> = ref([]);
const resultDialog: Ref<InstanceType<typeof OtaUploadResultDialog>|null> = useTemplateRef('resultDialog');
const msgId: Ref<string | null> = ref(null);
const checks = ref({
	upload: false,
	verify: false,
	load: false,
});
const currentAction: Ref<OtaUploadAction> = ref(OtaUploadAction.Upload);
const service = useApiClient().getIqrfServices().getUpgradeService();

daemonStore.$onAction(({ name, after }) => {
	if (name === 'onMessage') {
		after((rsp: DaemonApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			daemonStore.removeMessage(msgId.value);
			componentState.value = ComponentState.Idle;
			if (rsp.mType === IqmeshServiceMessages.OtaUpload) {
				handleOtaUploadResponse(rsp);
			}
		});
	}
});

function validateFile(file: File): true | string {
	if (fileType.value === FileFormat.HEX && !file.name.endsWith('.hex')) {
		return i18n.t('components.iqrfnet.network-manager.ota-upload.validation.file.notHex');
	}
	if (fileType.value === FileFormat.IQRF && !file.name.endsWith('.iqrf')) {
		return i18n.t('components.iqrfnet.network-manager.ota-upload.validation.file.notIqrf');
	}
	return true;
}

function getAddress(): number {
	if (uploadTarget.value === UploadTarget.Coordinator) {
		return 0;
	} else if (uploadTarget.value === UploadTarget.Node) {
		return otaParams.value.deviceAddr;
	} else {
		return 255;
	}
}

async function upload(): Promise<void> {
	if (!validateForm(form.value) || file.value === null) {
		return;
	}
	currentAction.value = OtaUploadAction.Upload;
	componentState.value = ComponentState.Action;
	try {
		otaParams.value.fileName = (await service.uploadToFs(file.value, fileType.value)).fileName;
		executeStep(OtaUploadAction.Upload);
	} catch {
		toast.error(
			i18n.t('components.iqrfnet.network-manager.ota-upload.messages.uploadFs.failed'),
		);
	}
}

async function executeStep(action: OtaUploadAction): Promise<void> {
	componentState.value = ComponentState.Action;
	if (action === OtaUploadAction.Upload) {
		currentAction.value = OtaUploadAction.Upload;
		checks.value = {
			upload: false,
			verify: false,
			load: false,
		};
	} else if (action === OtaUploadAction.Verify) {
		currentAction.value = OtaUploadAction.Verify;
		checks.value.verify = checks.value.load = false;
	} else {
		currentAction.value = OtaUploadAction.Load;
		checks.value.load = false;
	}
	const params = structuredClone(toRaw(otaParams.value));
	if (fileType.value !== FileFormat.HEX) {
		delete params.uploadEepromData;
		delete params.uploadEeepromData;
	}
	params.loadingAction = action;
	params.deviceAddr = getAddress();
	const opts = new DaemonMessageOptions(
		null,
		null,
		null,
		() => {
			componentState.value = ComponentState.Idle;
			msgId.value = null;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		OtaUploadService.upload(
			{ repeat: 1, returnVerbose: true },
			params,
			opts,
		),
	);
}

function handleOtaUploadResponse(rsp: DaemonApiResponse): void {
	if (rsp.data.status > 1_000) {
		let message = '';
		switch (rsp.data.status) {
			case 1_001:
				message = i18n.t('components.iqrfnet.network-manager.ota-upload.messages.invalidRequest', { error: rsp.data.statusStr });
				break;
			case 1_004:
				message = i18n.t('components.iqrfnet.network-manager.ota-upload.messages.invalidFile', { error: rsp.data.statusStr });
				break;
			case 1_003:
			case 1_005:
				message = i18n.t('components.iqrfnet.network-manager.ota-upload.messages.invalidContent', { error: rsp.data.statusStr });
				break;
			case 1_006:
				message = i18n.t('components.iqrfnet.network-manager.ota-upload.messages.invalidMemory', { error: rsp.data.statusStr });
				break;
			case 1_007:
				message = i18n.t('components.iqrfnet.network-manager.ota-upload.messages.compatibilityError', { error: rsp.data.statusStr });
				break;
			case 1_008:
				message = i18n.t('components.iqrfnet.network-manager.ota-upload.messages.noDevicesError');
				break;
			case 1_009:
				message = i18n.t('components.iqrfnet.network-manager.ota-upload.messages.deviceOfflineError');
				break;
			case 1_010:
				message = i18n.t('components.iqrfnet.network-manager.ota-upload.messages.noHwpidMatch');
				break;
			default:
				message = i18n.t('components.iqrfnet.network-manager.ota-upload.messages.generalError', { error: rsp.data.statusStr });
		}
		toast.error(message);
	} else if ([UploadTarget.Coordinator, UploadTarget.Node].includes(uploadTarget.value)) {
		handleUnicastResponse(rsp);
	} else {
		handleBroadcastResponse(rsp);
	}
}

function handleUnicastResponse(rsp: DaemonApiResponse): void {
	const status = rsp.data.status;
	const address = rsp.data.rsp.deviceAddr;
	const translationParams = { address: address };
	switch (status) {
		case 0: {
			const action = rsp.data.rsp.loadingAction;
			if (action === OtaUploadAction.Upload) {
				checks.value.upload = true;
				toast.success(i18n.t(
					address === 0
						? 'components.iqrfnet.network-manager.ota-upload.messages.coordinator.uploadStepSuccess'
						: 'components.iqrfnet.network-manager.ota-upload.messages.node.uploadStepSuccess',
					translationParams,
				));
			} else {
				handleUnicastVerifyLoadResponse(action, rsp.data.rsp.verifyResult[0]);
			}
			break;
		}
		case -1:
			toast.error(i18n.t(
				address === 0
					? 'common.messages.offlineCoordinator'
					: 'common.messages.offlineDevice',
				translationParams,
			));
			break;
		case 8:
			toast.error(i18n.t('common.messages.noDevice', translationParams));
			break;
		default:
			toast.error(i18n.t(
				'components.iqrfnet.network-manager.ota-upload.messages.genericError',
			));
	}
}

function handleUnicastVerifyLoadResponse(action: OtaUploadAction, data: OtaUploadResult): void {
	if (data.result) {
		const translationParams = { address: data.address };
		if (action === OtaUploadAction.Verify) {
			checks.value.verify = true;
			toast.success(i18n.t(
				data.address === 0
					? 'components.iqrfnet.network-manager.ota-upload.messages.coordinator.verifyStepSuccess'
					: 'components.iqrfnet.network-manager.ota-upload.messages.node.verifyStepSuccess',
				translationParams,
			));
		} else {
			checks.value.load = true;
			toast.success(i18n.t(
				data.address === 0
					? 'components.iqrfnet.network-manager.ota-upload.messages.coordinator.loadStepSuccess'
					: 'components.iqrfnet.network-manager.ota-upload.messages.node.loadStepSuccess',
				translationParams,
			));
		}
	} else {
		toast.error(i18n.t(
			action === OtaUploadAction.Verify
				? 'components.iqrfnet.network-manager.ota-upload.messages.verifyStepFail'
				: 'components.iqrfnet.network-manager.ota-upload.messages.loadStepFail',
		));
	}

}

function handleBroadcastResponse(rsp: DaemonApiResponse): void {
	const action = rsp.data.rsp.loadingAction;
	if (action === OtaUploadAction.Upload) {
		checks.value.upload = true;
		toast.success(i18n.t(
			'components.iqrfnet.network-manager.ota-upload.messages.network.uploadStepSuccess',
		));
	} else {
		if (action === OtaUploadAction.Verify) {
			results.value = rsp.data.rsp.verifyResult;
			checks.value.verify = true;
		} else {
			results.value = rsp.data.rsp.loadResult;
			checks.value.load = true;
		}
		resultDialog.value?.open(action);
	}
}

onBeforeUnmount(() => {
	daemonStore.removeMessage(msgId.value);
});

</script>
