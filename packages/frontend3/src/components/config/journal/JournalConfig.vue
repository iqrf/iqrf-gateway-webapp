<template>
	<v-form
		ref='form'
		v-slot='{ isValid }'
		:disabled='[ComponentState.Reloading, ComponentState.Saving].includes(componentState)'
	>
		<Card>
			<template #title>
				{{ $t('pages.configuration.journal.title') }}
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
				type='text, heading@3, text@2'
			>
				<v-responsive>
					<section v-if='config'>
						<v-checkbox
							v-model='config.forwardToSyslog'
							:label='$t("components.configuration.journal.forwardToSyslog")'
							density='compact'
						/>
						<SelectInput
							v-model='config.persistence'
							:label='$t("components.configuration.journal.storage")'
							:items='storageOptions'
						/>
						<TextInput
							v-model.number='config.maxDiskSize'
							type='number'
							:label='$t("components.configuration.journal.maxSize")'
							:description='$t("components.configuration.journal.notes.systemDefault")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.configuration.journal.validation.maxSizeMissing")),
								(v: number) => ValidationRules.integer(v, $t("components.configuration.journal.validation.maxSizeInvalid")),
								(v: number) => ValidationRules.min(v, 0, $t("components.configuration.journal.validation.maxSizeInvalid")),
							]'
							required
						/>
						<TextInput
							v-model.number='config.maxFiles'
							type='number'
							:label='$t("components.configuration.journal.maxFiles")'
							:description='$t("components.configuration.journal.notes.maxFiles")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.configuration.journal.validation.maxFilesMissing")),
								(v: number) => ValidationRules.integer(v, $t("components.configuration.journal.validation.maxFilesInvalid")),
								(v: number) => ValidationRules.min(v, 1, $t("components.configuration.journal.validation.maxFilesInvalid")),
							]'
							required
						/>
						<v-checkbox
							v-model='sizeRotation'
							:label='$t("components.configuration.journal.sizeRotation")'
							density='compact'
							:hide-details='!sizeRotation'
						/>
						<TextInput
							v-if='sizeRotation'
							v-model.number='config.sizeRotation.maxFileSize'
							type='number'
							:label='$t("components.configuration.journal.maxFileSize")'
							:description='$t("components.configuration.journal.notes.systemDefault")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.configuration.journal.validation.maxFileSizeMissing")),
								(v: number) => ValidationRules.integer(v, $t("components.configuration.journal.validation.maxFileSizeInvalid")),
								(v: number) => ValidationRules.min(v, 0, $t("components.configuration.journal.validation.maxFileSizeInvalid")),
							]'
							required
						/>
						<v-checkbox
							v-model='timeRotation'
							:label='$t("components.configuration.journal.timeRotation")'
							density='compact'
							:hide-details='!timeRotation'
						/>
						<div v-if='timeRotation'>
							<SelectInput
								v-model='config.timeRotation.unit'
								:label='$t("components.configuration.journal.timeUnit")'
								:items='unitOptions'
							/>
							<TextInput
								v-model.number='config.timeRotation.count'
								type='number'
								:label='$t("components.configuration.journal.unitCount")'
								:rules='[
									(v: number|null) => ValidationRules.required(v, $t("components.configuration.journal.validation.unitCountMissing")),
									(v: number) => ValidationRules.integer(v, $t("components.configuration.journal.validation.unitCountInvalid")),
									(v: number) => ValidationRules.min(v, 1, $t("components.configuration.journal.validation.unitCountInvalid")),
								]'
								required
							/>
						</div>
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
import { type JournalService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { JournalTimeUnit, type JournalConfig, JournalPersistence } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { mdiReload } from '@mdi/js';
import { type Ref, ref , onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import SelectInput from '@/components/SelectInput.vue';
import TextInput from '@/components/TextInput.vue';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const service: JournalService = useApiClient().getConfigServices().getJournalService();
const form: Ref<typeof VForm | null> = ref(null);
const config: Ref<JournalConfig | null> = ref(null);
const storageOptions = [
	{
		title: i18n.t('components.configuration.journal.storages.volatile'),
		value: JournalPersistence.Volatile,
	},
	{
		title: i18n.t('components.configuration.journal.storages.persistent'),
		value: JournalPersistence.Persistent,
	},
];
const sizeRotation: Ref<boolean> = ref(false);
const timeRotation: Ref<boolean> = ref(false);
const unitOptions = [
	{
		title: i18n.t('components.configuration.journal.timeUnits.seconds'),
		value: JournalTimeUnit.Seconds,
	},
	{
		title: i18n.t('components.configuration.journal.timeUnits.minutes'),
		value: JournalTimeUnit.Minutes,
	},
	{
		title: i18n.t('components.configuration.journal.timeUnits.hours'),
		value: JournalTimeUnit.Hours,
	},
	{
		title: i18n.t('components.configuration.journal.timeUnits.days'),
		value: JournalTimeUnit.Days,
	},
	{
		title: i18n.t('components.configuration.journal.timeUnits.weeks'),
		value: JournalTimeUnit.Weeks,
	},
	{
		title: i18n.t('components.configuration.journal.timeUnits.months'),
		value: JournalTimeUnit.Months,
	},
	{
		title: i18n.t('components.configuration.journal.timeUnits.years'),
		value: JournalTimeUnit.Years,
	},
];

async function getConfig(): Promise<void> {
	if (componentState.value === ComponentState.Created) {
		componentState.value = ComponentState.Loading;
	} else {
		componentState.value = ComponentState.Reloading;
	}
	service.getConfig()
		.then((response: JournalConfig) => {
			config.value = response;
			if (config.value.sizeRotation.maxFileSize !== 0) {
				sizeRotation.value = true;
			}
			if (config.value.timeRotation.unit !== JournalTimeUnit.Months || config.value.timeRotation.count !== 1) {
				timeRotation.value = true;
			}
			componentState.value = ComponentState.Ready;
		})
		.catch(() => toast.error('TODO FETCH ERROR HANDLING'));
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || config.value === null) {
		return;
	}
	componentState.value === ComponentState.Saving;
	const params = { ...config.value };
	if (!sizeRotation.value) {
		params.sizeRotation.maxFileSize = 0;
	}
	if (!timeRotation.value) {
		params.timeRotation.unit = JournalTimeUnit.Months;
		params.timeRotation.count = 1;
	}
	service.editConfig(params)
		.then(() => {
			getConfig().then(() => {
				toast.success(
					i18n.t('components.configuration.journal.messages.save.success'),
				);
			});
		})
		.catch(() => toast.error('TODO SAVE ERROR HANDLING'));
}

onMounted(() => {
	getConfig();
});
</script>
