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
				<CardActionBtn
					color='primary'
					:disabled='!isValid.value || componentState === ComponentState.Saving'
					:text='$t("components.maintenance.mender.update.install")'
					@click='installArtifact'
				/>
				<CardActionBtn
					color='primary'
					:disabled='componentState === ComponentState.Saving'
					:text='$t("components.maintenance.mender.update.commit")'
					@click='commitUpdate'
				/>
				<CardActionBtn
					color='primary'
					:disabled='componentState === ComponentState.Saving'
					:text='$t("components.maintenance.mender.update.rollback")'
					@click='rollbackUpdate'
				/>
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

import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import MenderUpdateLog from '@/components/maintenance/mender-update/MenderUpdateLog.vue';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Ready);
const i18n = useI18n();
const service: MenderService = useApiClient().getMenderService();
const form: Ref<VForm | null> = ref(null);
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
