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
		v-slot='{ isValid }'
		:disabled='componentState === ComponentState.Saving'
		@submit.prevent='onSubmit'
	>
		<Card>
			<template #title>
				{{ $t('components.maintenance.backup.restore.title') }}
			</template>
			<v-file-input
				v-model='archive'
				accept='.zip'
				:label='$t("components.maintenance.backup.restore.archive")'
				:rules='[
					(v: File|Blob|null) => ValidationRules.required(v, $t("components.maintenance.backup.restore.validation.archiveMissing")),
				]'
				:prepend-inner-icon='mdiFileOutline'
				:prepend-icon='null'
				show-size
				required
			/>
			<template #actions>
				<CardActionBtn
					:action='Action.Upload'
					:disabled='!isValid.value || componentState === ComponentState.Saving'
					:text='$t("common.buttons.restore")'
					type='submit'
				/>
			</template>
		</Card>
	</v-form>
</template>

<script lang='ts' setup>
import { type BackupService } from '@iqrf/iqrf-gateway-webapp-client/services/Maintenance';
import { type PowerActionResponse } from '@iqrf/iqrf-gateway-webapp-client/types/Gateway';
import { mdiFileOutline } from '@mdi/js';
import { type Ref, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { Action } from '@/types/Action';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Ready);
const i18n = useI18n();
const service: BackupService = useApiClient().getMaintenanceServices().getBackupService();
const form: Ref<VForm | null> = ref(null);
const archive: Ref<File[] | null> = ref(null);

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || archive.value === null) {
		return;
	}
	const file = archive.value[0];
	componentState.value = ComponentState.Saving;
	service.restore(file)
		.then((response: PowerActionResponse) => {
			const time = response.timestamp.toJSDate().toLocaleTimeString();
			toast.success(
				i18n.t('components.maintenance.backup.restore.messages.save.success', { time: time }),
			);
			componentState.value = ComponentState.Ready;
		})
		.catch(() => toast.error('TODO ERROR HANDLING'));
}
</script>
