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
		<Card>
			<template #title>
				{{ $t('pages.config.journal.title') }}
			</template>
			<template #titleActions>
				<CardTitleActionBtn
					:action='Action.Reload'
					@click='getConfig()'
				/>
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
							:label='$t("components.config.journal.forwardToSyslog")'
						/>
						<SelectInput
							v-model='config.persistence'
							:label='$t("components.config.journal.storage")'
							:items='storageOptions'
						/>
						<NumberInput
							v-model.number='config.maxDiskSize'
							:label='$t("components.config.journal.maxSize")'
							:description='$t("components.config.journal.notes.systemDefault")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.config.journal.validation.maxSizeMissing")),
								(v: number) => ValidationRules.integer(v, $t("components.config.journal.validation.maxSizeInvalid")),
								(v: number) => ValidationRules.min(v, 0, $t("components.config.journal.validation.maxSizeInvalid")),
							]'
							required
						/>
						<NumberInput
							v-model.number='config.maxFiles'
							:label='$t("components.config.journal.maxFiles")'
							:description='$t("components.config.journal.notes.maxFiles")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.config.journal.validation.maxFilesMissing")),
								(v: number) => ValidationRules.integer(v, $t("components.config.journal.validation.maxFilesInvalid")),
								(v: number) => ValidationRules.min(v, 1, $t("components.config.journal.validation.maxFilesInvalid")),
							]'
							required
						/>
						<v-checkbox
							v-model='sizeRotation'
							:label='$t("components.config.journal.sizeRotation")'
							:hide-details='!sizeRotation'
						/>
						<NumberInput
							v-if='sizeRotation'
							v-model.number='config.sizeRotation.maxFileSize'
							:label='$t("components.config.journal.maxFileSize")'
							:description='$t("components.config.journal.notes.systemDefault")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.config.journal.validation.maxFileSizeMissing")),
								(v: number) => ValidationRules.integer(v, $t("components.config.journal.validation.maxFileSizeInvalid")),
								(v: number) => ValidationRules.min(v, 0, $t("components.config.journal.validation.maxFileSizeInvalid")),
							]'
							required
						/>
						<v-checkbox
							v-model='timeRotation'
							:label='$t("components.config.journal.timeRotation")'
							:hide-details='!timeRotation'
						/>
						<div v-if='timeRotation'>
							<SelectInput
								v-model='config.timeRotation.unit'
								:label='$t("components.config.journal.timeUnit")'
								:items='unitOptions'
							/>
							<NumberInput
								v-model.number='config.timeRotation.count'
								:label='$t("components.config.journal.unitCount")'
								:rules='[
									(v: number|null) => ValidationRules.required(v, $t("components.config.journal.validation.unitCountMissing")),
									(v: number) => ValidationRules.integer(v, $t("components.config.journal.validation.unitCountInvalid")),
									(v: number) => ValidationRules.min(v, 1, $t("components.config.journal.validation.unitCountInvalid")),
								]'
								required
							/>
						</div>
					</section>
				</v-responsive>
			</v-skeleton-loader>
			<template #actions>
				<CardActionBtn
					:action='Action.Edit'
					:disabled='!isValid.value || [ComponentState.Loading, ComponentState.Reloading, ComponentState.Saving].includes(componentState)'
					type='submit'
				/>
			</template>
		</Card>
	</v-form>
</template>

<script lang='ts' setup>
import { type JournalService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { type JournalConfig, JournalPersistence, JournalTimeUnit } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { onMounted, ref , type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import CardTitleActionBtn from '@/components/layout/card/CardTitleActionBtn.vue';
import NumberInput from '@/components/layout/form/NumberInput.vue';
import SelectInput from '@/components/layout/form/SelectInput.vue';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { Action } from '@/types/Action';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const service: JournalService = useApiClient().getConfigServices().getJournalService();
const form: Ref<VForm | null> = ref(null);
const config: Ref<JournalConfig | null> = ref(null);
const storageOptions = [
	{
		title: i18n.t('components.config.journal.storages.volatile'),
		value: JournalPersistence.Volatile,
	},
	{
		title: i18n.t('components.config.journal.storages.persistent'),
		value: JournalPersistence.Persistent,
	},
];
const sizeRotation: Ref<boolean> = ref(false);
const timeRotation: Ref<boolean> = ref(false);
const unitOptions = [
	{
		title: i18n.t('components.config.journal.timeUnits.seconds'),
		value: JournalTimeUnit.Seconds,
	},
	{
		title: i18n.t('components.config.journal.timeUnits.minutes'),
		value: JournalTimeUnit.Minutes,
	},
	{
		title: i18n.t('components.config.journal.timeUnits.hours'),
		value: JournalTimeUnit.Hours,
	},
	{
		title: i18n.t('components.config.journal.timeUnits.days'),
		value: JournalTimeUnit.Days,
	},
	{
		title: i18n.t('components.config.journal.timeUnits.weeks'),
		value: JournalTimeUnit.Weeks,
	},
	{
		title: i18n.t('components.config.journal.timeUnits.months'),
		value: JournalTimeUnit.Months,
	},
	{
		title: i18n.t('components.config.journal.timeUnits.years'),
		value: JournalTimeUnit.Years,
	},
];

async function getConfig(): Promise<void> {
	if (componentState.value === ComponentState.Created) {
		componentState.value = ComponentState.Loading;
	} else {
		componentState.value = ComponentState.Reloading;
	}
	try {
		config.value = await service.getConfig();
		if (config.value.sizeRotation.maxFileSize !== 0) {
			sizeRotation.value = true;
		}
		if (config.value.timeRotation.unit !== JournalTimeUnit.Months || config.value.timeRotation.count !== 1) {
			timeRotation.value = true;
		}
	} catch {
		toast.error('TODO FETCH ERROR HANDLING');
	}
	componentState.value = ComponentState.Ready;
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
	try {
		await service.updateConfig(params);
		await getConfig();
		toast.success(
			i18n.t('components.config.journal.messages.save.success'),
		);
	} catch {

		toast.error('TODO SAVE ERROR HANDLING');
	}
}

onMounted(() => {
	getConfig();
});
</script>
