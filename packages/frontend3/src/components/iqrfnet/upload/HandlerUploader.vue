<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
		:disabled='componentState === ComponentState.Saving'
	>
		<Card>
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
				:prepend-icon='null'
				show-size
				required
			/>
			<template #actions>
				<v-btn
					color='primary'
					variant='elevated'
					:disabled='!isValid.value'
					@click='onSubmit'
				>
					{{ $t('common.buttons.save') }}
				</v-btn>
			</template>
		</Card>
	</v-form>
</template>

<script lang='ts' setup>
import { type ServiceService } from '@iqrf/iqrf-gateway-webapp-client/services';
import { type UpgradeService } from '@iqrf/iqrf-gateway-webapp-client/services/Iqrf/UpgradeService';
import { FileFormat, FileType, type FileUploadResult } from '@iqrf/iqrf-gateway-webapp-client/types/Iqrf/Upgrade';
import { mdiFileOutline } from '@mdi/js';
import { type Ref, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import Card from '@/components/layout/card/Card.vue';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Ready);
const i18n = useI18n();
const form: Ref<typeof VForm | null> = ref(null);
const handler: Ref<File[] | null> = ref(null);
const serviceService: ServiceService = useApiClient().getServiceService();
const upgradeService: UpgradeService = useApiClient().getIqrfServices().getUpgradeService();

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || handler.value === null) {
		return;
	}
	const file = handler.value[0];
	componentState.value = ComponentState.Saving;
	let success = await stopDaemon();
	if (!success) {
		handleError('TODO DAEMON STOP ERROR HANDLING');
		return;
	}
	const path = await uploadFile(file);
	if (path === null) {
		handleError('TODO UPLOAD TO FS ERROR HANDLING');
		return;
	}
	success = await runUploader(path);
	if (!success) {
		handleError('TODO UPLOAD TO TR ERROR HANDLING');
		return;
	}
	success = await startDaemon();
	if (!success) {
		handleError('TODO DAEMON START ERROR HANDLING');
		return;
	}
	toast.success(
		i18n.t('components.iqrfnet.upload.dpa-handler.messages.success'),
	);
	componentState.value = ComponentState.Ready;
}

async function uploadFile(file: File): Promise<string | null> {
	return upgradeService.uploadToFs(file, FileFormat.HEX)
		.then((result: FileUploadResult) => Promise.resolve(result.fileName))
		.catch(() => Promise.resolve(null));
}

async function runUploader(path: string): Promise<boolean> {
	return upgradeService.uploadToTr(path, FileType.HEX)
		.then(() => Promise.resolve(true))
		.catch(() => Promise.resolve(false));
}

async function stopDaemon(): Promise<boolean> {
	return serviceService.stop('iqrf-gateway-daemon')
		.then(() => Promise.resolve(true))
		.catch(() => Promise.resolve(false));
}

async function startDaemon(): Promise<boolean> {
	return serviceService.start('iqrf-gateway-daemon')
		.then(() => Promise.resolve(true))
		.catch(() => Promise.resolve(false));
}

async function handleError(message: string): Promise<void> {
	toast.error(message);
	await startDaemon();
	componentState.value = ComponentState.Ready;
}

</script>
