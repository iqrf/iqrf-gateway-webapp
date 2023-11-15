<template>
	<v-form
		ref='form'
		v-slot='{ isValid }'
		:disabled='[ComponentState.Reloading, ComponentState.Saving].includes(componentState)'
	>
		<Card>
			<template #title>
				{{ $t('pages.configuration.mender.title') }}
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
				type='text, heading@2, text, heading@4, text@3'
			>
				<v-responsive>
					<section v-if='config'>
						<legend class='section-legend'>
							{{ $t('components.configuration.mender.connection') }}
						</legend>
						<TextInput
							v-model='config.client.ServerURL'
							:label='$t("components.configuration.mender.client.server")'
							:rules='[
								(v: string|null) => ValidationRules.required(v, $t("components.configuration.mender.validation.serverMissing")),
								(v: string) => ValidationRules.server(v, $t("components.configuration.mender.validation.serverInvalid")),
							]'
							required
						/>
						<TextInput
							v-model='config.client.ServerCertificate'
							:label='$t("components.configuration.mender.client.cert")'
						>
							<template #append>
								<MenderCertificateUploadDialog />
							</template>
						</TextInput>
						<legend class='section-legend'>
							{{ $t('components.configuration.mender.inventory') }}
						</legend>
						<TextInput
							v-model='config.client.TenantToken'
							:label='$t("components.configuration.mender.client.tenantToken")'
							:rules='[
								(v: string|null) => ValidationRules.required(v, $t("components.configuration.mender.validation.tenantTokenMissing")),
							]'
							required
						/>
						<TextInput
							v-model.number='config.client.InventoryPollIntervalSeconds'
							type='number'
							:label='$t("components.configuration.mender.client.inventoryPollInterval")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.configuration.mender.validation.inventoryPollIntervalMissing")),
								(v: number) => ValidationRules.integer(v, $t("components.configuration.mender.validation.inventoryPollIntervalInvalid")),
								(v: number) => ValidationRules.min(v, 0, $t("components.configuration.mender.validation.inventoryPollIntervalInvalid")),
							]'
							required
						>
							<template #append-inner>
								<v-chip
									label
									:color='intervalColor(config.client.InventoryPollIntervalSeconds)'
								>
									{{ intervalLabel(config.client.InventoryPollIntervalSeconds) }}
								</v-chip>
							</template>
						</TextInput>
						<TextInput
							v-model.number='config.client.RetryPollIntervalSeconds'
							type='number'
							:label='$t("components.configuration.mender.client.retryPollInterval")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.configuration.mender.validation.retryPollIntervalMissing")),
								(v: number) => ValidationRules.integer(v, $t("components.configuration.mender.validation.retryPollIntervalInvalid")),
								(v: number) => ValidationRules.min(v, 0, $t("components.configuration.mender.validation.retryPollIntervalInvalid")),
							]'
							required
						>
							<template #append-inner>
								<v-chip
									label
									:color='intervalColor(config.client.RetryPollIntervalSeconds)'
								>
									{{ intervalLabel(config.client.RetryPollIntervalSeconds) }}
								</v-chip>
							</template>
						</TextInput>
						<TextInput
							v-model.number='config.client.UpdatePollIntervalSeconds'
							type='number'
							:label='$t("components.configuration.mender.client.updatePollInterval")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.configuration.mender.validation.updatePollIntervalMissing")),
								(v: number) => ValidationRules.integer(v, $t("components.configuration.mender.validation.updatePollIntervalInvalid")),
								(v: number) => ValidationRules.min(v, 0, $t("components.configuration.mender.validation.updatePollIntervalInvalid")),
							]'
							required
						>
							<template #append-inner>
								<v-chip
									label
									:color='intervalColor(config.client.UpdatePollIntervalSeconds)'
								>
									{{ intervalLabel(config.client.UpdatePollIntervalSeconds) }}
								</v-chip>
							</template>
						</TextInput>
						<legend class='section-legend'>
							{{ $t('components.configuration.mender.features') }}
						</legend>
						<v-checkbox
							v-model='config.connect.FileTransfer'
							:label='$t("components.configuration.mender.connect.fileTransfer")'
							density='compact'
							hide-details
						/>
						<v-checkbox
							v-model='config.connect.PortForward'
							:label='$t("components.configuration.mender.connect.portForward")'
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
import { type MenderService } from '@iqrf/iqrf-gateway-webapp-client/services';
import { type MenderConfig } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { mdiReload } from '@mdi/js';
import { Duration } from 'luxon';
import {
	onMounted,
	type Ref,
	ref,
} from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import MenderCertificateUploadDialog from '@/components/config/mender/MenderCertificateUploadDialog.vue';
import TextInput from '@/components/TextInput.vue';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';


const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const service: MenderService = useApiClient().getMenderService();
const form: Ref<typeof VForm | null> = ref(null);
const config: Ref<MenderConfig | null> = ref(null);

function intervalColor(value: number|null): string {
	if (value === null || (typeof value === 'number' && value < 0)) {
		return 'error';
	}
	return 'info';
}

function intervalLabel(value: number|null): string {
	if (value === null || (typeof value === 'number' && value < 0)) {
		return 'N/A';
	}
	const duration = Duration.fromMillis(value * 1000).shiftTo('days', 'hours', 'minutes', 'seconds');
	return duration.toHuman({listStyle: 'long'});
}

async function getConfig(): Promise<void> {
	if (componentState.value === ComponentState.Created) {
		componentState.value = ComponentState.Loading;
	} else {
		componentState.value = ComponentState.Reloading;
	}
	service.getConfig()
		.then((response: MenderConfig) => {
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
	const params = {...config.value};
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
