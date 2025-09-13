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
		:disabled='[ComponentState.Reloading, ComponentState.Saving].includes(componentState)'
		@submit.prevent='onSubmit()'
	>
		<ICard>
			<template #title>
				{{ $t('pages.config.monit.title') }}
			</template>
			<template #titleActions>
				<ICardTitleActionBtn
					:action='Action.Reload'
					@click='getConfig()'
				/>
			</template>
			<v-skeleton-loader
				class='input-skeleton-loader'
				:loading='componentState === ComponentState.Loading'
				type='table-heading, table-row-divider@2, table-row, heading, text, heading@3'
			>
				<v-responsive>
					<section v-if='config'>
						<legend class='section-legend'>
							{{ $t('components.config.monit.checks.title') }}
						</legend>
						<DataTable
							class='mb-4'
							:items='config.checks'
							:headers='headers'
							:dense='true'
							:hover='true'
							:hide-pagination='true'
							:no-data-text='$t("components.config.monit.checks.noRecords")'
						>
							<template #item.enabled='{ item }'>
								<v-checkbox-btn
									v-model='item.enabled'
									class='float-right'
								/>
							</template>
						</DataTable>
						<legend class='section-legend'>
							{{ $t('components.config.monit.mmonit.title') }}
						</legend>
						<v-checkbox
							v-model='config.mmonit.enabled'
							:label='$t("components.config.monit.mmonit.enable")'
						/>
						<TextInput
							v-model='config.mmonit.server'
							:label='$t("components.config.monit.mmonit.server")'
							:prepend-inner-icon='mdiServerNetwork'
							:rules='[
								(v: string|null) => ValidationRules.required(v, $t("components.config.monit.validation.serverMissing")),
								(v: string) => mmonitServerValidation(v, $t("components.config.monit.validation.serverInvalid")),
							]'
							:disabled='!config.mmonit.enabled'
							required
						/>
						<TextInput
							v-model='config.mmonit.credentials.username'
							:label='$t("components.common.fields.username")'
							:prepend-inner-icon='mdiAccount'
							:rules='[
								(v: string|null) => ValidationRules.required(v, $t("components.common.validations.username.required")),
							]'
							:disabled='!config.mmonit.enabled'
							required
						/>
						<TextInput
							v-model='config.mmonit.credentials.password'
							:label='$t("components.common.fields.password")'
							:prepend-inner-icon='mdiKey'
							:rules='[
								(v: string|null) => ValidationRules.required(v, $t("components.common.validations.password.required")),
							]'
							:disabled='!config.mmonit.enabled'
							required
						/>
					</section>
				</v-responsive>
			</v-skeleton-loader>
			<template #actions>
				<ICardActionBtn
					:action='Action.Edit'
					:disabled='!isValid.value || [ComponentState.Loading, ComponentState.Reloading, ComponentState.Saving].includes(componentState)'
					type='submit'
				/>
			</template>
		</ICard>
	</v-form>
</template>

<script lang='ts' setup>
import { type MonitService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { type MonitConfig } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {
	Action, ICard,
	ICardActionBtn,
	ICardTitleActionBtn,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiAccount, mdiKey, mdiServerNetwork } from '@mdi/js';
import { onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';
import { z } from 'zod';

import DataTable from '@/components/layout/data-table/DataTable.vue';
import TextInput from '@/components/layout/form/TextInput.vue';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const service: MonitService = useApiClient().getConfigServices().getMonitService();
const form: Ref<VForm | null> = ref(null);
const config: Ref<MonitConfig | null> = ref(null);
const headers = [
	{ key: 'name', title: i18n.t('components.config.monit.checks.name') },
	{ key: 'enabled', title: i18n.t('common.states.enabled'), align: 'end' },
];

function mmonitServerValidation(value: string, error: string): boolean|string {
	const validator: z.ZodURL = z.url();
	try {
		validator.parse(value);
		const url: URL = new URL(value);
		if ((url.protocol === 'http:' || url.protocol === 'https:') && url.username === '' && url.password === '' && url.search === '' && url.hash === '') {
			return true;
		} else {
			return error;
		}
	} catch {
		return error;
	}
}

async function getConfig(): Promise<void> {
	if (componentState.value === ComponentState.Created) {
		componentState.value = ComponentState.Loading;
	} else {
		componentState.value = ComponentState.Reloading;
	}
	try {
		config.value = await service.getConfig();
	} catch {
		toast.error('TODO FETCH ERROR HANDLING');

	}
	componentState.value = ComponentState.Ready;
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || config.value === null) {
		return;
	}
	componentState.value = ComponentState.Saving;
	const params = { ...config.value };
	try {
		await service.updateConfig(params);
		await getConfig();
		toast.success(
			i18n.t('components.config.monit.messages.save.success'),
		);
	} catch {
		toast.error('TODO SAVE ERROR HANDLING');
	}
}

onMounted(() => {
	getConfig();
});
</script>
