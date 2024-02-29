<template>
	<v-form
		ref='form'
		v-slot='{ isValid }'
		:disabled='componentState === ComponentState.Saving'
	>
		<Card>
			<template #title>
				{{ $t('pages.maintenance.mender.title') }}
			</template>
			<v-file-input
				v-model='artifacts'
				accept='.mender'
				:label='$t("components.maintenance.mender.update.artifact")'
				:rules='[
					(v: File|Blob|null) => ValidationRules.required(v, $t("components.maintenance.mender.update.validations.artifact.required")),
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
					@click='installArtifact'
				>
					{{ $t('components.maintenance.mender.update.install') }}
				</v-btn>
				<v-btn
					color='primary'
					variant='elevated'
					:disabled='componentState === ComponentState.Saving'
					@click='commitUpdate'
				>
					{{ $t('components.maintenance.mender.update.commit') }}
				</v-btn>
				<v-btn
					color='primary'
					variant='elevated'
					:disabled='componentState === ComponentState.Saving'
					@click='rollbackUpdate'
				>
					{{ $t('components.maintenance.mender.update.rollback') }}
				</v-btn>
				<MenderUpdateLog :log='log' />
			</template>
		</Card>
	</v-form>
</template>

<script lang='ts' setup>
import { type MenderService } from '@iqrf/iqrf-gateway-webapp-client/services';
import { mdiFileOutline } from '@mdi/js';
import { type AxiosError } from 'axios';
import { type Ref, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import MenderUpdateLog from '@/components/maintenance/mender-update/MenderUpdateLog.vue';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Ready);
const i18n = useI18n();
const service: MenderService = useApiClient().getMenderService();
const form: Ref<typeof VForm | null> = ref(null);
const artifacts: Ref<File[]> = ref([]);
const log: Ref<string|null> = ref(null);

async function installArtifact(): Promise<void> {
	if (!await validateForm(form.value) || artifacts.value.length === 0) {
		return;
	}
	componentState.value = ComponentState.Saving;
	const artifactFile = artifacts.value[0];
	service.install(artifactFile)
		.then((data: string) => {
			updateLog(data);
			componentState.value = ComponentState.Ready;
			toast.success(
				i18n.t('components.maintenance.mender.update.message.install.success'),
			);
		})
		.catch((error: AxiosError) => {
			const message = (error.response?.data as Record<string, string>).message ?? null;
			updateLog(message);
			componentState.value = ComponentState.Ready;
			toast.error('TODO INSTALL ERROR HANDLING');
		});
}

function commitUpdate(): void {
	componentState.value = ComponentState.Saving;
	service.commit()
		.then((data: string) => {
			updateLog(data);
			componentState.value = ComponentState.Ready;
			toast.success(
				i18n.t('components.maintenance.mender.update.message.commit.success'),
			);
		})
		.catch((error: AxiosError) => {
			const message = (error.response?.data as Record<string, string>).message ?? null;
			updateLog(message);
			componentState.value = ComponentState.Ready;
			toast.error('TODO COMMIT ERROR HANDLING');
		});
}

function rollbackUpdate(): void {
	componentState.value = ComponentState.Saving;
	service.rollback()
		.then((data: string) => {
			updateLog(data);
			componentState.value = ComponentState.Ready;
			toast.success(
				i18n.t('components.maintenance.mender.update.message.rollback.success'),
			);
		})
		.catch((error: AxiosError) => {
			const message = (error.response?.data as Record<string, string>).message ?? null;
			updateLog(message);
			componentState.value = ComponentState.Ready;
			toast.error('TODO ROLLBACK ERROR HANDLING');
		});
}

function updateLog(data: string|null): void {
	if (data === null) {
		return;
	}
	if (log.value === null) {
		log.value = data;
	} else {
		log.value += '\n' + data;
	}
}

</script>
