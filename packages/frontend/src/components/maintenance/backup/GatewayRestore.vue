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
		@submit.prevent='onSubmit()'
	>
		<ICard>
			<template #title>
				{{ $t('components.maintenance.backup.restore.title') }}
			</template>
			<v-file-input
				v-model='archive'
				accept='.zip'
				:label='$t("components.maintenance.backup.restore.archive")'
				:rules='[
					(v: File|null) => ValidationRules.required(v, $t("components.maintenance.backup.restore.validation.archive.required")),
					(v: File) => ValidationRules.fileExtension(v, ["zip"], $t("components.maintenance.backup.restore.validation.archive.fileExtension")),
				]'
				:prepend-inner-icon='mdiFileOutline'
				:prepend-icon='undefined'
				show-size
				required
			/>
			<template #actions>
				<IActionBtn
					:action='Action.Upload'
					container-type='card'
					:loading='componentState === ComponentState.Action'
					:disabled='!isValid.value'
					:text='$t("common.buttons.restore")'
					type='submit'
				/>
			</template>
		</ICard>
	</v-form>
</template>

<script lang='ts' setup>
import { type BackupService } from '@iqrf/iqrf-gateway-webapp-client/services/Maintenance';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiFileOutline } from '@mdi/js';
import { ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const i18n = useI18n();
const service: BackupService = useApiClient().getMaintenanceServices().getBackupService();
const form: Ref<VForm | null> = ref(null);
const archive: Ref<File | null> = ref(null);

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || archive.value === null) {
		return;
	}
	const file = archive.value;
	componentState.value = ComponentState.Action;
	try {
		const rsp = await service.restore(file);
		toast.success(
			i18n.t(
				'components.maintenance.backup.restore.messages.save.success',
				{ time: rsp.timestamp.toJSDate().toLocaleTimeString() },
			),
		);
	} catch {
		toast.error(
			i18n.t('components.maintenance.backup.restore.messages.save.failed'),
		);
	}
	componentState.value = ComponentState.Idle;
}
</script>
