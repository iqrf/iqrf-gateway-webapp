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
		:disabled='[ComponentState.Loading, ComponentState.Reloading, ComponentState.Action].includes(componentState)'
		@submit.prevent='onSubmit()'
	>
		<ICard>
			<template #title>
				{{ $t('pages.config.journal.title') }}
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
				:text='$t("components.config.journal.messages.fetch.failed")'
			/>
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
							hide-details
						/>
						<ISelectInput
							v-model='config.persistence'
							:label='$t("components.config.journal.storage")'
							:items='storageOptions'
							:prepend-inner-icon='mdiMemory'
						/>
						<INumberInput
							v-model='config.maxDiskSize'
							:label='$t("components.config.journal.maxSize")'
							:description='$t("components.config.journal.notes.systemDefault")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.config.journal.validation.maxSize.required")),
								(v: number) => ValidationRules.integer(v, $t("components.config.journal.validation.maxSize.integer")),
								(v: number) => ValidationRules.min(v, 0, $t("components.config.journal.validation.maxSize.min")),
							]'
							:min='0'
							required
							:prepend-inner-icon='mdiFilePercent'
						/>
						<INumberInput
							v-model='config.maxFiles'
							:label='$t("components.config.journal.maxFiles")'
							:description='$t("components.config.journal.notes.maxFiles")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.config.journal.validation.maxFiles.required")),
								(v: number) => ValidationRules.integer(v, $t("components.config.journal.validation.maxFiles.integer")),
								(v: number) => ValidationRules.min(v, 1, $t("components.config.journal.validation.maxFiles.min")),
							]'
							:min='1'
							required
							:prepend-inner-icon='mdiFileMultiple'
						/>
						<v-checkbox
							v-model='sizeRotation'
							:label='$t("components.config.journal.sizeRotation")'
							:hide-details='!sizeRotation'
						/>
						<INumberInput
							v-if='sizeRotation'
							v-model='config.sizeRotation.maxFileSize'
							:label='$t("components.config.journal.maxFileSize")'
							:description='$t("components.config.journal.notes.systemDefault")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.config.journal.validation.maxFileSize.required")),
								(v: number) => ValidationRules.integer(v, $t("components.config.journal.validation.maxFileSize.integer")),
								(v: number) => ValidationRules.min(v, 0, $t("components.config.journal.validation.maxFileSize.min")),
							]'
							:min='0'
							required
							:prepend-inner-icon='mdiFileSettings'
						/>
						<v-checkbox
							v-model='timeRotation'
							:label='$t("components.config.journal.timeRotation")'
							:hide-details='!timeRotation'
						/>
						<div v-if='timeRotation'>
							<ISelectInput
								v-model='config.timeRotation.unit'
								:label='$t("components.config.journal.timeUnit")'
								:items='unitOptions'
								:prepend-inner-icon='mdiClockTimeFour'
							/>
							<INumberInput
								v-model='config.timeRotation.count'
								:label='$t("components.config.journal.unitCount")'
								:rules='[
									(v: number|null) => ValidationRules.required(v, $t("components.config.journal.validation.unitCount.required")),
									(v: number) => ValidationRules.integer(v, $t("components.config.journal.validation.unitCount.integer")),
									(v: number) => ValidationRules.min(v, 1, $t("components.config.journal.validation.unitCount.min")),
								]'
								:min='1'
								required
								:prepend-inner-icon='mdiNumeric1Box'
							/>
						</div>
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
import { type JournalService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { type JournalConfig, JournalPersistence, JournalTimeUnit } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	INumberInput,
	ISelectInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import {
	mdiClockTimeFour,
	mdiFileMultiple,
	mdiFilePercent,
	mdiFileSettings,
	mdiMemory,
	mdiNumeric1Box,
} from '@mdi/js';
import { onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

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
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		config.value = await service.getConfig();
		if (config.value.sizeRotation.maxFileSize !== 0) {
			sizeRotation.value = true;
		}
		if (config.value.timeRotation.unit !== JournalTimeUnit.Months || config.value.timeRotation.count !== 1) {
			timeRotation.value = true;
		}
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error(
			i18n.t('components.config.journal.messages.fetch.failed'),
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
	if (!sizeRotation.value) {
		params.sizeRotation.maxFileSize = 0;
	}
	if (!timeRotation.value) {
		params.timeRotation.unit = JournalTimeUnit.Months;
		params.timeRotation.count = 1;
	}
	try {
		await service.updateConfig(params);
		toast.success(
			i18n.t('components.config.journal.messages.save.success'),
		);
	} catch {
		toast.error(
			i18n.t('components.config.journal.messages.save.failed'),
		);
	}
	componentState.value = ComponentState.Ready;
}

onMounted(() => {
	getConfig();
});
</script>
