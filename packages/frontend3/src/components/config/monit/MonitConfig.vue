<template>
	<v-form
		ref='form'
		v-slot='{ isValid }'
		:disabled='[ComponentState.Reloading, ComponentState.Saving].includes(componentState)'
	>
		<Card>
			<template #title>
				{{ $t('pages.configuration.monit.title') }}
			</template>
			<template #titleActions>
				<v-tooltip
					location='bottom'
				>
					<template #activator='{ props }'>
						<v-btn
							v-bind='props'
							color='white'
							:icon='mdiReload'
							@click='getConfig'
						/>
					</template>
					{{ $t('common.buttons.reload') }}
				</v-tooltip>
			</template>
			<v-skeleton-loader
				class='input-skeleton-loader'
				:loading='componentState === ComponentState.Loading'
				type='table-heading, table-row-divider@2, table-row, heading, text, heading@3'
			>
				<v-responsive>
					<section v-if='config'>
						<legend class='section-legend'>
							{{ $t('components.configuration.monit.checks.title') }}
						</legend>
						<DataTable
							class='mb-4'
							:items='config.checks'
							:headers='headers'
							:dense='true'
							:hover='true'
							:hide-pagination='true'
							:no-data-text='$t("components.configuration.monit.checks.noRecords")'
						>
							<template #item.enabled='{ item }'>
								<v-checkbox-btn
									v-model='item.enabled'
									class='float-right'
								/>
							</template>
						</DataTable>
						<legend class='section-legend'>
							{{ $t('components.configuration.monit.mmonit.title') }}
						</legend>
						<v-checkbox
							v-model='config.mmonit.enabled'
							:label='$t("components.configuration.monit.mmonit.enable")'
							density='compact'
						/>
						<TextInput
							v-model='config.mmonit.server'
							:label='$t("components.configuration.monit.mmonit.server")'
							:rules='[
								(v: string|null) => ValidationRules.required(v, $t("components.configuration.monit.validation.serverMissing")),
								(v: string) => mmonitServerValidation(v, $t("components.configuration.monit.validation.serverInvalid")),
							]'
							:disabled='!config.mmonit.enabled'
							required
						/>
						<TextInput
							v-model='config.mmonit.credentials.username'
							:label='$t("common.labels.username")'
							:rules='[
								(v: string|null) => ValidationRules.required(v, $t("common.validation.usernameMissing")),
							]'
							:disabled='!config.mmonit.enabled'
							required
						/>
						<TextInput
							v-model='config.mmonit.credentials.password'
							:label='$t("common.labels.password")'
							:rules='[
								(v: string|null) => ValidationRules.required(v, $t("common.validation.passwordMissing")),
							]'
							:disabled='!config.mmonit.enabled'
							required
						/>
					</section>
				</v-responsive>
			</v-skeleton-loader>
			<template #actions>
				<v-btn
					color='primary'
					variant='elevated'
					:disabled='!isValid.value || [ComponentState.Loading, ComponentState.Reloading, ComponentState.Saving].includes(componentState)'
					@click='onSubmit'
				>
					{{ $t('common.buttons.save') }}
				</v-btn>
			</template>
		</Card>
	</v-form>
</template>

<script lang='ts' setup>
import { type MonitService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { type MonitConfig } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { mdiReload } from '@mdi/js';
import {
	onMounted,
	type Ref,
	ref,
} from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';
import { z } from 'zod';

import Card from '@/components/Card.vue';
import DataTable from '@/components/DataTable.vue';
import TextInput from '@/components/TextInput.vue';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const service: MonitService = useApiClient().getConfigServices().getMonitService();
const form: Ref<typeof VForm | null> = ref(null);
const config: Ref<MonitConfig | null> = ref(null);
const headers = [
	{ key: 'name', title: i18n.t('components.configuration.monit.checks.name') },
	{ key: 'enabled', title: i18n.t('common.states.enabled'), align: 'end' },
];

function mmonitServerValidation(value: string, error: string): boolean|string {
	const validator: z.ZodString = z.string().url();
	try {
		validator.parse(value);
		const url: URL = new URL(value);
		if ((url.protocol === 'http:' || url.protocol === 'https:') && url.username === '' && url.password === '' && url.search === '' && url.hash === '') {
			return true;
		} else {
			return error;
		}
	} catch (e) {
		return error;
	}
}

async function getConfig(): Promise<void> {
	if (componentState.value === ComponentState.Created) {
		componentState.value = ComponentState.Loading;
	} else {
		componentState.value = ComponentState.Reloading;
	}
	service.getConfig()
		.then((response: MonitConfig) => {
			config.value = response;
			componentState.value = ComponentState.Ready;
		})
		.catch(() => toast.error('TODO FETCH ERROR HANDLING'));
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || config.value === null) {
		return;
	}
	componentState.value = ComponentState.Saving;
	const params = { ...config.value };
	service.editConfig(params)
		.then(() => {
			getConfig().then(() => {
				toast.success(
					i18n.t('components.configuration.monit.messages.save.success'),
				);
			});
		})
		.catch(() => toast.error('TODO SAVE ERROR HANDLING'));
}

onMounted(() => {
	getConfig();
});
</script>
