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
		@submit.prevent='onSubmit'
	>
		<Card>
			<template #title>
				{{ $t('pages.config.unattendedUpgrades.title') }}
			</template>
			<template #titleActions>
				<CardTitleActionBtn
					:action='Action.Reload'
					@click='getConfig'
				/>
			</template>
			<v-skeleton-loader
				class='input-skeleton-loader'
				:loading='componentState === ComponentState.Loading'
				type='heading@3, text'
			>
				<v-responsive>
					<section v-if='config'>
						<NumberInput
							v-model.number='config.packageListUpdateInterval'
							:label='$t("components.config.unattendedUpgrades.packageListUpdateInterval")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.config.unattendedUpgrades.validation.packageListUpdateIntervalMissing")),
								(v: number) => ValidationRules.integer(v, $t("components.config.unattendedUpgrades.validation.packageListUpdateIntervalInvalid")),
								(v: number) => ValidationRules.min(v, 0, $t("components.config.unattendedUpgrades.validation.packageListUpdateIntervalInvalid")),
							]'
							required
						>
							<template #append-inner>
								<v-chip
									label
									:color='intervalColor(config.packageListUpdateInterval)'
								>
									{{ $t('components.config.unattendedUpgrades.interval') }}
								</v-chip>
							</template>
						</NumberInput>
						<NumberInput
							v-model.number='config.packageUpdateInterval'
							:label='$t("components.config.unattendedUpgrades.packageUpdateInterval")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.config.unattendedUpgrades.validation.packageUpdateIntervalMissing")),
								(v: number) => ValidationRules.integer(v, $t("components.config.unattendedUpgrades.validation.packageUpdateIntervalInvalid")),
								(v: number) => ValidationRules.min(v, 0, $t("components.config.unattendedUpgrades.validation.packageUpdateIntervalInvalid")),
							]'
							required
						>
							<template #append-inner>
								<v-chip
									label
									:color='intervalColor(config.packageUpdateInterval)'
								>
									{{ $t('components.config.unattendedUpgrades.interval') }}
								</v-chip>
							</template>
						</NumberInput>
						<NumberInput
							v-model.number='config.packageRemovalInterval'
							:label='$t("components.config.unattendedUpgrades.packageRemovalInterval")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.config.unattendedUpgrades.validation.packageRemovalIntervalMissing")),
								(v: number) => ValidationRules.integer(v, $t("components.config.unattendedUpgrades.validation.packageRemovalIntervalInvalid")),
								(v: number) => ValidationRules.min(v, 0, $t("components.config.unattendedUpgrades.validation.packageRemovalIntervalInvalid")),
							]'
							required
						>
							<template #append-inner>
								<v-chip
									label
									:color='intervalColor(config.packageRemovalInterval)'
								>
									{{ $t('components.config.unattendedUpgrades.interval') }}
								</v-chip>
							</template>
						</NumberInput>
						<v-checkbox
							v-model='config.rebootOnKernelUpdate'
							:label='$t("components.config.unattendedUpgrades.rebootOnKernelUpdate")'
						/>
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
import { type AptService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { type AptConfig } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { onMounted, ref , type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import CardTitleActionBtn from '@/components/layout/card/CardTitleActionBtn.vue';
import NumberInput from '@/components/layout/form/NumberInput.vue';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { Action } from '@/types/Action';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const service: AptService = useApiClient().getConfigServices().getAptService();
const form: Ref<VForm | null> = ref(null);
const config: Ref<AptConfig | null> = ref(null);

function intervalColor(value: number|string|null): string {
	if (value === null || (typeof value === 'string' && value.length === 0) || (typeof value === 'number' && value < 0)) {
		return 'error';
	}
	return 'info';
}

async function getConfig(): Promise<void> {
	if (componentState.value === ComponentState.Created) {
		componentState.value = ComponentState.Loading;
	} else {
		componentState.value = ComponentState.Reloading;
	}
	service.getConfig()
		.then((response: AptConfig) => {
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
	service.updateConfig(params)
		.then(() => {
			getConfig().then(() => {
				toast.success(
					i18n.t('components.config.journal.messages.save.success'),
				);
			});
		})
		.catch(() => toast.error('TODO SAVE ERROR HANDLING'));
}

onMounted(() => {
	getConfig();
});
</script>
