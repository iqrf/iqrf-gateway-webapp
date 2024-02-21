<template>
	<v-form
		ref='form'
		v-slot='{ isValid }'
		:disabled='[ComponentState.Reloading, ComponentState.Saving].includes(componentState)'
	>
		<Card>
			<template #title>
				{{ $t('pages.configuration.unattendedUpgrades.title') }}
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
				type='heading@3, text'
			>
				<v-responsive>
					<section v-if='config'>
						<TextInput
							v-model.number='config.packageListUpdateInterval'
							type='number'
							:label='$t("components.configuration.unattendedUpgrades.packageListUpdateInterval")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.configuration.unattendedUpgrades.validation.packageListUpdateIntervalMissing")),
								(v: number) => ValidationRules.integer(v, $t("components.configuration.unattendedUpgrades.validation.packageListUpdateIntervalInvalid")),
								(v: number) => ValidationRules.min(v, 0, $t("components.configuration.unattendedUpgrades.validation.packageListUpdateIntervalInvalid")),
							]'
							required
						>
							<template #append-inner>
								<v-chip
									label
									:color='intervalColor(config.packageListUpdateInterval)'
								>
									{{ $t('components.configuration.unattendedUpgrades.interval') }}
								</v-chip>
							</template>
						</TextInput>
						<TextInput
							v-model.number='config.packageUpdateInterval'
							type='number'
							:label='$t("components.configuration.unattendedUpgrades.packageUpdateInterval")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.configuration.unattendedUpgrades.validation.packageUpdateIntervalMissing")),
								(v: number) => ValidationRules.integer(v, $t("components.configuration.unattendedUpgrades.validation.packageUpdateIntervalInvalid")),
								(v: number) => ValidationRules.min(v, 0, $t("components.configuration.unattendedUpgrades.validation.packageUpdateIntervalInvalid")),
							]'
							required
						>
							<template #append-inner>
								<v-chip
									label
									:color='intervalColor(config.packageUpdateInterval)'
								>
									{{ $t('components.configuration.unattendedUpgrades.interval') }}
								</v-chip>
							</template>
						</TextInput>
						<TextInput
							v-model.number='config.packageRemovalInterval'
							type='number'
							:label='$t("components.configuration.unattendedUpgrades.packageRemovalInterval")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.configuration.unattendedUpgrades.validation.packageRemovalIntervalMissing")),
								(v: number) => ValidationRules.integer(v, $t("components.configuration.unattendedUpgrades.validation.packageRemovalIntervalInvalid")),
								(v: number) => ValidationRules.min(v, 0, $t("components.configuration.unattendedUpgrades.validation.packageRemovalIntervalInvalid")),
							]'
							required
						>
							<template #append-inner>
								<v-chip
									label
									:color='intervalColor(config.packageRemovalInterval)'
								>
									{{ $t('components.configuration.unattendedUpgrades.interval') }}
								</v-chip>
							</template>
						</TextInput>
						<v-checkbox
							v-model='config.rebootOnKernelUpdate'
							:label='$t("components.configuration.unattendedUpgrades.rebootOnKernelUpdate")'
							density='compact'
							hide-details
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
import { type AptService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { type AptConfig } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { mdiReload } from '@mdi/js';
import { type Ref, ref , onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import TextInput from '@/components/TextInput.vue';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const service: AptService = useApiClient().getConfigServices().getAptService();
const form: Ref<typeof VForm | null> = ref(null);
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
	componentState.value === ComponentState.Saving;
	const params = { ...config.value };
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
