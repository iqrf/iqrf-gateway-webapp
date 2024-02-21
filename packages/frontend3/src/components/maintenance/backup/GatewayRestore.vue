<template>
	<v-form
		v-slot='{ isValid }'
		:disabled='componentState === ComponentState.Saving'
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
				<v-btn
					color='primary'
					variant='elevated'
					:disabled='!isValid.value || componentState === ComponentState.Saving'
					@click='onSubmit'
				>
					{{ $t('common.buttons.restore') }}
				</v-btn>
			</template>
		</Card>
	</v-form>
</template>

<script lang='ts' setup>
import { type BackupService } from '@iqrf/iqrf-gateway-webapp-client/services/MaintenanceService';
import { type PowerActionResponse } from '@iqrf/iqrf-gateway-webapp-client/types/Gateway/Power';
import { mdiFileOutline } from '@mdi/js';
import {
	type Ref,
	ref,
} from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Ready);
const i18n = useI18n();
const service: BackupService = useApiClient().getMaintenanceServices().getBackupService();
const form: Ref<typeof VForm | null> = ref(null);
const archive: Ref<File[] | null> = ref(null);

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || archive.value === null) {
		return;
	}
	const file = archive.value[0];
	componentState.value = ComponentState.Saving;
	service.restore(file)
		.then((response: PowerActionResponse) => {
			const time = new Date(response.timestamp * 1000).toLocaleTimeString();
			toast.success(
				i18n.t('components.maintenance.backup.restore.messages.save.success', { time: time }),
			);
			componentState.value = ComponentState.Ready;
		})
		.catch(() => toast.error('TODO ERROR HANDLING'));
}
</script>
