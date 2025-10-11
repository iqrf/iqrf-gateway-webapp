<template>
	<v-form
		ref='form'
		v-slot='{ isValid }'
		:disabled='[ComponentState.Action, ComponentState.Loading].includes(componentState)'
		@submit.prevent='upload()'
	>
		<ICard class='mt-4'>
			<template #title>
				{{ $t('components.iqrfnet.upload.dpa-plugin.title') }}
			</template>
			<v-alert
				v-if='showAlert'
				variant='tonal'
				:type='alertType'
				:text='alertText'
			/>
			<v-skeleton-loader
				v-else
				class='input-skeleton-loader'
				:loading='componentState === ComponentState.Loading'
				type='heading'
			>
				<v-responsive>
					<ISelectInput
						v-model='version'
						:label='$t("components.iqrfnet.upload.dpa-plugin.form.version")'
						:rules='[
							(v: string|null) => ValidationRules.required(v, $t("components.iqrfnet.upload.dpa-plugin.validation.version.required")),
						]'
						:placeholder='$t("components.iqrfnet.upload.dpa-plugin.form.placeholder")'
						:items='versionOptions'
						required
					/>
				</v-responsive>
			</v-skeleton-loader>
			<template #actions>
				<IActionBtn
					:action='Action.Upload'
					:disabled='!isValid.value || versionOptions.length === 0'
					type='submit'
				/>
			</template>
		</ICard>
	</v-form>
	<SameVersionDialog
		ref='conflictDialog'
		:current='dpaVersionPretty'
		@upload='upload(true)'
	/>
	<DpaUpdateProgressDialog
		ref='progressDialog'
		:component-state='componentState'
		:message='progressMessage'
	/>
</template>

<script lang='ts' setup>
import { IqmeshServiceMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { EnumerationService } from '@iqrf/iqrf-gateway-daemon-utils/services/iqmesh';
import { DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { ServiceService } from '@iqrf/iqrf-gateway-webapp-client/services';
import { UpgradeService } from '@iqrf/iqrf-gateway-webapp-client/services/Iqrf';
import { DpaFileParams, FileType, IqrfInterfaces, RfModes } from '@iqrf/iqrf-gateway-webapp-client/types/Iqrf';
import { OsDpaService } from '@iqrf/iqrf-repository-client/services';
import { Action, ComponentState, IActionBtn, ICard, ISelectInput, ValidationRules } from '@iqrf/iqrf-vue-ui';
import { compare } from 'compare-versions';
import { computed, onBeforeMount, onMounted, ref, Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import DpaUpdateProgressDialog from '@/components/iqrfnet/upload/DpaUpdateProgressDialog.vue';
import SameVersionDialog from '@/components/iqrfnet/upload/SameVersionDialog.vue';
import { useApiClient } from '@/services/ApiClient';
import { useRepositoryClient } from '@/services/RepositoryClient';
import { useDaemonStore } from '@/store/daemonSocket';
import { DeviceEnumeration } from '@/types/DaemonApi/Iqmesh';
import { SelectItem } from '@/types/vuetify';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const daemonStore = useDaemonStore();
const osDpaService: Ref<OsDpaService | null> = ref(null);
const upgradeService: UpgradeService = useApiClient().getIqrfServices().getUpgradeService();
const serviceService: ServiceService = useApiClient().getServiceService();
const osBuild: Ref<string> = ref('');
const dpaVersion: Ref<string> = ref('');
const dpaVersionPretty: Ref<string> = ref('');
const trType: Ref<number> = ref(0);
const iqrfInterface: Ref<IqrfInterfaces> = ref(IqrfInterfaces.UART);
const msgId: Ref<string | null> = ref(null);
const form: Ref<VForm | null> = ref(null);
const conflictDialog: Ref<typeof SameVersionDialog | null> = ref(null);
const progressDialog: Ref<typeof DpaUpdateProgressDialog | null> = ref(null);
const version: Ref<string> = ref('');
const versionOptions: Ref<SelectItem[]> = ref([]);
const showAlert = computed(() => {
	return componentState.value === ComponentState.FetchFailed || (componentState.value !== ComponentState.Loading && versionOptions.value.length === 0);
});
const alertType = computed(() => {
	if (componentState.value === ComponentState.FetchFailed) {
		return 'error';
	}
	return 'success';
});
const alertText = computed(() => {
	if (componentState.value === ComponentState.FetchFailed) {
		return i18n.t('components.iqrfnet.upload.dpa-plugin.messages.dpaList.failed');
	}
	return i18n.t('components.iqrfnet.upload.dpa-plugin.messages.noUpgrades');
});
const progressMessage: Ref<string> = ref('');

daemonStore.$onAction(({ name, after }) => {
	if (name === 'onMessage') {
		after((rsp: DaemonApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			daemonStore.removeMessage(msgId.value);
			if (rsp.mType === IqmeshServiceMessages.Enumerate) {
				handleEnumerate(rsp);
			}
		});
	}
});

async function enumerate(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	const opts = new DaemonMessageOptions(
		null,
		60_000,
		i18n.t('components.iqrfnet.upload.dpa-plugin.messages.dpaList.failed'),
		() => {
			componentState.value = ComponentState.FetchFailed;
			msgId.value = null;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		EnumerationService.enumerate(
			{ repeat: 1, returnVerbose: true },
			{ deviceAddr: 0 },
			opts,
		),
	);
}

function handleEnumerate(rsp: DaemonApiResponse): void {
	if (rsp.data.status !== 0) {
		toast.error(
			i18n.t('components.iqrfnet.upload.dpa-plugin.messages.dpaList.failed'),
		);
		componentState.value = ComponentState.FetchFailed;
		return;
	}
	const data = rsp.data.rsp as DeviceEnumeration;
	osBuild.value = data.osRead.osBuild;
	dpaVersionPretty.value = data.peripheralEnumeration.dpaVer;
	dpaVersion.value = data.peripheralEnumeration.dpaVer.split('.').join('').padStart(4, '0');
	trType.value = data.osRead.trMcuType.value;
	iqrfInterface.value = data.osRead.flags.interfaceType === 'UART' ? IqrfInterfaces.UART : IqrfInterfaces.SPI;
	getDpaVersions();
}

async function getDpaVersions(): Promise<void> {
	if (osDpaService.value === null) {
		toast.error(
			i18n.t('components.iqrfnet.upload.dpa-plugin.messages.dpaList.failed'),
		);
		componentState.value = ComponentState.FetchFailed;
		return;
	}
	try {
		const fetched = [];
		const versions = await osDpaService.value.list({ osBuild: osBuild.value });
		for (const version of versions) {
			const dpaVerPretty = version.dpa.version;
			const dpaVer = dpaVerPretty.split('.').join('').padStart(4, '0');
			if (compare(dpaVer, '4.00', '<')) {
				fetched.push(
					{
						title: `${dpaVerPretty}-STD RF mode`,
						value: `${dpaVer}-STD`,
					},
					{
						title: `${dpaVerPretty}-LP RF mode`,
						value: `${dpaVer}-LP`,
					},
				);
			} else {
				fetched.push({
					title: dpaVerPretty,
					value: dpaVer,
				});
			}
		}
		fetched.sort().reverse();
		for (const item of fetched) {
			if (item.value === dpaVersion.value) {
				item.title = `${item.title} (Current)`;
			}
		}
		versionOptions.value = fetched;
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error(
			i18n.t('components.iqrfnet.upload.dpa-plugin.messages.dpaList.failed'),
		);
		componentState.value = ComponentState.FetchFailed;
	}
}

async function upload(force: boolean = false): Promise<void> {
	if (version.value.length === 0) {
		return;
	}
	if (!force && version.value === dpaVersion.value) {
		conflictDialog.value?.open();
		return;
	}
	componentState.value = ComponentState.Action;
	progressDialog.value?.open();
	const path = await downloadDpaFile();
	if (path === null) {
		await handleError(
			i18n.t('components.iqrfnet.upload.dpa-plugin.messages.uploadFs.failed'),
		);
		return;
	}
	let success = await stopDaemon();
	if (!success) {
		await handleError(
			i18n.t('components.iqrfnet.upload.messages.stop.failed'),
		);
		return;
	}
	success = await runUploader(path);
	if (!success) {
		await handleError(
			i18n.t('components.iqrfnet.upload.dpa-plugin.messages.uploadTr.failed'),
		);
		return;
	}
	success = await startDaemon();
	if (!success) {
		await handleError(
			i18n.t('components.iqrfnet.upload.messages.start.failed'),
		);
		return;
	}
	toast.success(
		i18n.t('components.iqrfnet.upload.dpa-plugin.messages.upload.success'),
	);
	componentState.value = ComponentState.Success;
}

async function downloadDpaFile(): Promise<string|null> {
	progressMessage.value = i18n.t('components.iqrfnet.upload.dpa-plugin.messages.uploadFs.action');
	try {
		let dpa = '';
		let rf = null;
		if (dpaVersion.value.endsWith('-STD')) {
			dpa = dpaVersion.value.split('-')[0];
			rf = RfModes.STD;
		} else if (dpaVersion.value.endsWith('-LP')) {
			dpa = dpaVersion.value.split('-')[0];
			rf = RfModes.LP;
		} else {
			dpa = dpaVersion.value;
		}
		const params: DpaFileParams = {
			dpa: dpa,
			interfaceType: iqrfInterface.value,
			osBuild: osBuild.value,
			trSeries: trType.value,
		};
		if (rf !== null) {
			params.rfMode = rf;
		}
		return (await upgradeService.getDpaFile(params)).fileName;
	} catch {
		return null;
	}
}

async function runUploader(path: string): Promise<boolean> {
	progressMessage.value = i18n.t('components.iqrfnet.upload.dpa-handler.messages.uploadTr.action');
	try {
		await upgradeService.uploadToTr(path, FileType.HEX);
		return true;
	} catch {
		return false;
	}
}

async function stopDaemon(): Promise<boolean> {
	progressMessage.value = i18n.t('components.iqrfnet.upload.messages.stop.action');
	try {
		await serviceService.stop('iqrf-gateway-daemon');
		return true;
	} catch {
		return false;
	}
}

async function startDaemon(afterSuccess: boolean = true): Promise<boolean> {
	if (afterSuccess) {
		progressMessage.value = i18n.t('components.iqrfnet.upload.messages.start.action');
	}
	try {
		await serviceService.start('iqrf-gateway-daemon');
		return true;
	} catch {
		return false;
	}
}

async function handleError(message: string): Promise<void> {
	progressMessage.value = message;
	toast.error(message);
	await startDaemon(false);
	componentState.value = ComponentState.Error;
}

onBeforeMount(async () => {
	try {
		osDpaService.value = (await useRepositoryClient()).getOsDpaService();
	} catch {
		//
	}
});

onMounted(() => {
	enumerate();
});
</script>
