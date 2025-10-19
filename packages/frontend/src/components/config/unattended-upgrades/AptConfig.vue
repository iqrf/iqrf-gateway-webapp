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
				{{ $t('pages.config.unattendedUpgrades.title') }}
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
				:text='$t("components.config.unattendedUpgrades.messages.fetch.failed")'
			/>
			<v-skeleton-loader
				class='input-skeleton-loader'
				:loading='componentState === ComponentState.Loading'
				type='heading@3, text'
			>
				<v-responsive>
					<section v-if='config'>
						<INumberInput
							v-model='config.packageListUpdateInterval'
							:label='$t("components.config.unattendedUpgrades.packageListUpdateInterval")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.config.unattendedUpgrades.validation.packageListUpdateInterval.required")),
								(v: number) => ValidationRules.integer(v, $t("components.config.unattendedUpgrades.validation.packageListUpdateInterval.integer")),
								(v: number) => ValidationRules.min(v, 0, $t("components.config.unattendedUpgrades.validation.packageListUpdateInterval.min")),
							]'
							:min='0'
							required
							:prepend-inner-icon='mdiTimerRefresh'
						>
							<template #append-inner>
								<v-chip
									label
									:color='intervalColor(config.packageListUpdateInterval)'
								>
									{{ $t('components.config.unattendedUpgrades.interval') }}
								</v-chip>
							</template>
						</INumberInput>
						<INumberInput
							v-model='config.packageUpdateInterval'
							:label='$t("components.config.unattendedUpgrades.packageUpdateInterval")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.config.unattendedUpgrades.validation.packageUpdateInterval.required")),
								(v: number) => ValidationRules.integer(v, $t("components.config.unattendedUpgrades.validation.packageUpdateInterval.integer")),
								(v: number) => ValidationRules.min(v, 0, $t("components.config.unattendedUpgrades.validation.packageUpdateInterval.min")),
							]'
							:min='0'
							required
							:prepend-inner-icon='mdiTimerRefresh'
						>
							<template #append-inner>
								<v-chip
									label
									:color='intervalColor(config.packageUpdateInterval)'
								>
									{{ $t('components.config.unattendedUpgrades.interval') }}
								</v-chip>
							</template>
						</INumberInput>
						<INumberInput
							v-model='config.packageRemovalInterval'
							:label='$t("components.config.unattendedUpgrades.packageRemovalInterval")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.config.unattendedUpgrades.validation.packageRemovalInterval.required")),
								(v: number) => ValidationRules.integer(v, $t("components.config.unattendedUpgrades.validation.packageRemovalInterval.integer")),
								(v: number) => ValidationRules.min(v, 0, $t("components.config.unattendedUpgrades.validation.packageRemovalInterval.min")),
							]'
							:min='0'
							required
							:prepend-inner-icon='mdiTimerRefresh'
						>
							<template #append-inner>
								<v-chip
									label
									:color='intervalColor(config.packageRemovalInterval)'
								>
									{{ $t('components.config.unattendedUpgrades.interval') }}
								</v-chip>
							</template>
						</INumberInput>
						<v-checkbox
							v-model='config.rebootOnKernelUpdate'
							:label='$t("components.config.unattendedUpgrades.rebootOnKernelUpdate")'
							hide-details
						/>
					</section>
				</v-responsive>
			</v-skeleton-loader>
			<template #actions>
				<IActionBtn
					:action='Action.Save'
					:disabled='!isValid.value || [ComponentState.Loading, ComponentState.Reloading, ComponentState.Action].includes(componentState)'
					type='submit'
				/>
			</template>
		</ICard>
	</v-form>
</template>

<script lang='ts' setup>
import { type AptService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { type AptConfig } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	INumberInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiTimerRefresh } from '@mdi/js';
import { onMounted, ref, type Ref, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const service: AptService = useApiClient().getConfigServices().getAptService();
const form: Ref<VForm | null> = useTemplateRef('form');
const config: Ref<AptConfig | null> = ref(null);

function intervalColor(value: number|string|null): string {
	if (value === null || (typeof value === 'string' && value.length === 0) || (typeof value === 'number' && value < 0)) {
		return 'error';
	}
	return 'info';
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
			i18n.t('components.config.unattendedUpgrades.messages.fetch.failed'),
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
			i18n.t('components.config.unattendedUpgrades.messages.save.success'),
		);
	} catch {
		toast.error(
			i18n.t('components.config.unattendedUpgrades.messages.save.failed'),
		);
	}
	componentState.value = ComponentState.Ready;
}

onMounted(() => {
	getConfig();
});
</script>
