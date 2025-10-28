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
		:disabled='[ComponentState.Reloading, ComponentState.Action].includes(componentState)'
		@submit.prevent='onSubmit()'
	>
		<ICard>
			<template #title>
				{{ $t('pages.config.monit.title') }}
			</template>
			<template #titleActions>
				<IActionBtn
					:action='Action.Reload'
					container-type='card-title'
					:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
					:disabled='componentState === ComponentState.Action'
					@click='getConfig()'
				/>
			</template>
			<v-alert
				v-if='componentState === ComponentState.FetchFailed'
				type='error'
				variant='tonal'
				:text='$t("components.config.monit.messages.fetch.failed")'
			/>
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
						<IDataTable
							class='mb-4'
							:items='config.checks'
							:headers='headers'
							:dense='true'
							:hover='true'
							:hide-pagination='true'
							disable-column-filtering
							disable-search
							:no-data-text='"components.config.monit.checks.noRecords"'
						>
							<template #item.enabled='{ item }'>
								<v-checkbox-btn
									v-model='item.enabled'
									class='float-right'
									:disabled='[ComponentState.Action, ComponentState.Reloading].includes(componentState)'
								/>
							</template>
						</IDataTable>
						<legend class='section-legend'>
							{{ $t('components.config.monit.mmonit.title') }}
						</legend>
						<v-checkbox
							v-model='config.mmonit.enabled'
							:label='$t("components.config.monit.mmonit.enable")'
							hide-details
						/>
						<ITextInput
							v-model='config.mmonit.server'
							:label='$t("components.config.monit.mmonit.server")'
							:prepend-inner-icon='mdiServerNetwork'
							:rules='config.mmonit.enabled ? [
								(v: string|null) => ValidationRules.required(v, $t("components.config.monit.validation.server.required")),
								(v: string) => mmonitServerValidation(v, $t("components.config.monit.validation.server.url")),
							] : []'
							:disabled='!config.mmonit.enabled'
							required
						/>
						<ITextInput
							v-model='config.mmonit.credentials.username'
							:label='$t("components.common.fields.username")'
							:prepend-inner-icon='mdiAccount'
							:rules='config.mmonit.enabled ? [
								(v: string|null) => ValidationRules.required(v, $t("components.common.validations.username.required")),
							]: []'
							:disabled='!config.mmonit.enabled'
							required
						/>
						<ITextInput
							v-model='config.mmonit.credentials.password'
							:label='$t("components.common.fields.password")'
							:prepend-inner-icon='mdiKey'
							:rules='config.mmonit.enabled ? [
								(v: string|null) => ValidationRules.required(v, $t("components.common.validations.password.required")),
							] : []'
							:disabled='!config.mmonit.enabled'
							required
						/>
					</section>
				</v-responsive>
			</v-skeleton-loader>
			<template #actions>
				<IActionBtn
					:action='Action.Save'
					:loading='componentState === ComponentState.Action'
					:disabled='!isValid.value || [ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
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
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IDataTable,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiAccount, mdiKey, mdiServerNetwork } from '@mdi/js';
import {
	computed,
	onMounted,
	ref,
	type Ref,
	type TemplateRef,
	useTemplateRef,
} from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';
import { z } from 'zod';

import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const service: MonitService = useApiClient().getConfigServices().getMonitService();
const form: TemplateRef<VForm> = useTemplateRef('form');
const config: Ref<MonitConfig | null> = ref(null);
const headers = computed(() => [
	{ key: 'name', title: i18n.t('components.config.monit.checks.name') },
	{ key: 'enabled', title: i18n.t('common.states.enabled'), align: 'end' },
]);

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
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		config.value = await service.getConfig();
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error(
			i18n.t('components.config.monit.messages.fetch.failed'),
		);
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
	}
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || config.value === null) {
		return;
	}
	componentState.value = ComponentState.Action;
	const params = { ...config.value };
	try {
		await service.updateConfig(params);
		toast.success(
			i18n.t('components.config.monit.messages.save.success'),
		);
	} catch {
		toast.error(
			i18n.t('components.config.monit.messages.save.failed'),
		);
	}
	componentState.value = ComponentState.Ready;
}

onMounted(() => {
	getConfig();
});
</script>
