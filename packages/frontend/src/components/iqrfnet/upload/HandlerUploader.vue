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
		:disabled='componentState === ComponentState.Action'
	>
		<ICard>
			<template #title>
				{{ $t('components.iqrfnet.upload.dpa-handler.title') }}
			</template>
			<v-file-input
				v-model='handler'
				accept='.hex'
				:label='$t("components.iqrfnet.upload.dpa-handler.file")'
				:rules='[
					(v: File|Blob|null) => ValidationRules.required(v, $t("components.iqrfnet.upload.dpa-handler.validation.fileMissing")),
				]'
				:prepend-inner-icon='mdiFileOutline'
				prepend-icon=''
				show-size
				required
			/>
			<template #actions>
				<IModalWindow
					v-model='show'
					persistent
				>
					<template #activator='{ props }'>
						<IActionBtn
							v-bind='props'
							:action='Action.Upload'
							:loading='componentState === ComponentState.Action'
							:disabled='!isValid.value'
							@click='onSubmit()'
						/>
					</template>
					<ICard>
						<template #title>
							{{ $t('components.iqrfnet.upload.dpa-handler.dialog.title') }}
						</template>
						<div class='text-center'>
							{{ progressMessage }}
							<v-divider class='my-2' />
							<v-progress-linear
								:indeterminate='componentState === ComponentState.Action'
								model-value='100'
								:color='progressColor'
								rounded
								height='24'
							/>
						</div>
						<template #actions>
							<IActionBtn
								:action='Action.Close'
								:disabled='componentState === ComponentState.Action'
								@click='closeDialog()'
							/>
						</template>
					</ICard>
				</IModalWindow>
			</template>
		</ICard>
	</v-form>
</template>

<script lang='ts' setup>
import { type ServiceService } from '@iqrf/iqrf-gateway-webapp-client/services';
import { type UpgradeService } from '@iqrf/iqrf-gateway-webapp-client/services/Iqrf';
import { FileFormat, FileType } from '@iqrf/iqrf-gateway-webapp-client/types/Iqrf';
import { Action, ComponentState, IActionBtn, ICard, IModalWindow, ValidationRules } from '@iqrf/iqrf-vue-ui';
import { mdiFileOutline } from '@mdi/js';
import { computed, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const i18n = useI18n();
const form: Ref<VForm | null> = ref(null);
const show: Ref<boolean> = ref(false);
const handler: Ref<File | null> = ref(null);
const progressMessage: Ref<string> = ref('');
const serviceService: ServiceService = useApiClient().getServiceService();
const upgradeService: UpgradeService = useApiClient().getIqrfServices().getUpgradeService();

const progressColor = computed(() => {
	if (componentState.value === ComponentState.Action || ComponentState.Success) {
		return 'green';
	}
	return 'red';
});

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || handler.value === null) {
		return;
	}
	show.value = true;
	const file = handler.value;
	componentState.value = ComponentState.Action;
	let success = await stopDaemon();
	if (!success) {
		await handleError(
			i18n.t('components.iqrfnet.upload.dpa-handler.messages.stop.failed'),
		);
		return;
	}
	const path = await uploadFile(file);
	if (path === null) {
		await handleError(
			i18n.t('components.iqrfnet.upload.dpa-handler.messages.uploadFs.failed'),
		);
		return;
	}
	success = await runUploader(path);
	if (!success) {
		await handleError(
			i18n.t('components.iqrfnet.upload.dpa-handler.messages.uploadTr.failed'),
		);
		return;
	}
	success = await startDaemon();
	if (!success) {
		await handleError(
			i18n.t('components.iqrfnet.upload.dpa-handler.messages.start.failed'),
		);
		return;
	}
	toast.success(
		i18n.t('components.iqrfnet.upload.dpa-handler.messages.upload.success'),
	);
	progressMessage.value = i18n.t('components.iqrfnet.upload.dpa-handler.messages.upload.success');
	componentState.value = ComponentState.Success;
}

async function uploadFile(file: File): Promise<string|null> {
	progressMessage.value = i18n.t('components.iqrfnet.upload.dpa-handler.messages.uploadFs.action');
	try {
		return (await upgradeService.uploadToFs(file, FileFormat.HEX)).fileName;
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
	progressMessage.value = i18n.t('components.iqrfnet.upload.dpa-handler.messages.stop.action');
	try {
		await serviceService.stop('iqrf-gateway-daemon');
		return true;
	} catch {
		return false;
	}
}

async function startDaemon(): Promise<boolean> {
	progressMessage.value = i18n.t('components.iqrfnet.upload.dpa-handler.messages.start.action');
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
	await startDaemon();
	componentState.value = ComponentState.Error;
}

function closeDialog(): void {
	show.value = false;
	progressMessage.value = '';
	componentState.value = ComponentState.Idle;
}

</script>
