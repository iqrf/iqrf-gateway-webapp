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
				{{ $t('pages.config.mender.title') }}
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
				type='text, heading@2, text, heading@4, text@3'
			>
				<v-responsive>
					<section v-if='config'>
						<legend class='section-legend'>
							{{ $t('components.config.mender.connection') }}
						</legend>
						<TextInput
							v-for='index in config.client.config.Servers.keys()'
							:key='`MenderServerUrl${ index}`'
							v-model='config.client.config.Servers[index]'
							:label='$t("components.config.mender.client.server")'
							:rules='[
								(v: string|null) => ValidationRules.required(v, $t("components.config.mender.validation.serverMissing")),
								(v: string) => ValidationRules.url(v, $t("components.config.mender.validation.serverInvalid")),
							]'
							:prepend-inner-icon='mdiServerNetwork'
							required
						/>
						<TextInput
							v-model='config.client.config.ServerCertificate'
							:label='$t("components.config.mender.client.cert")'
							:prepend-inner-icon='mdiFileCertificate'
						>
							<template #append>
								<MenderCertificateUploadDialog />
							</template>
						</TextInput>
						<legend class='section-legend'>
							{{ $t('components.config.mender.inventory') }}
						</legend>
						<TextInput
							v-model='config.client.config.TenantToken'
							:label='$t("components.config.mender.client.tenantToken")'
							:rules='[
								(v: string|null) => ValidationRules.required(v, $t("components.config.mender.validation.tenantTokenMissing")),
							]'
							:prepend-inner-icon='mdiKeyVariant'
							required
						/>
						<NumberInput
							v-model.number='config.client.config.InventoryPollIntervalSeconds'
							:label='$t("components.config.mender.client.inventoryPollInterval")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.config.mender.validation.inventoryPollIntervalMissing")),
								(v: number) => ValidationRules.integer(v, $t("components.config.mender.validation.inventoryPollIntervalInvalid")),
								(v: number) => ValidationRules.min(v, 0, $t("components.config.mender.validation.inventoryPollIntervalInvalid")),
							]'
							:prepend-inner-icon='mdiTimerMarker'
							required
						>
							<template #append-inner>
								<v-chip
									label
									:color='intervalColor(config.client.config.InventoryPollIntervalSeconds)'
								>
									{{ intervalLabel(config.client.config.InventoryPollIntervalSeconds) }}
								</v-chip>
							</template>
						</NumberInput>
						<NumberInput
							v-model.number='config.client.config.RetryPollIntervalSeconds'
							:label='$t("components.config.mender.client.retryPollInterval")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.config.mender.validation.retryPollIntervalMissing")),
								(v: number) => ValidationRules.integer(v, $t("components.config.mender.validation.retryPollIntervalInvalid")),
								(v: number) => ValidationRules.min(v, 0, $t("components.config.mender.validation.retryPollIntervalInvalid")),
							]'
							:prepend-inner-icon='mdiTimerRefresh'
							required
						>
							<template #append-inner>
								<v-chip
									label
									:color='intervalColor(config.client.config.RetryPollIntervalSeconds)'
								>
									{{ intervalLabel(config.client.config.RetryPollIntervalSeconds) }}
								</v-chip>
							</template>
						</NumberInput>
						<NumberInput
							v-model.number='config.client.config.UpdatePollIntervalSeconds'
							:label='$t("components.config.mender.client.updatePollInterval")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.config.mender.validation.updatePollIntervalMissing")),
								(v: number) => ValidationRules.integer(v, $t("components.config.mender.validation.updatePollIntervalInvalid")),
								(v: number) => ValidationRules.min(v, 0, $t("components.config.mender.validation.updatePollIntervalInvalid")),
							]'
							:prepend-inner-icon='mdiTimerSync'
							required
						>
							<template #append-inner>
								<v-chip
									label
									:color='intervalColor(config.client.config.UpdatePollIntervalSeconds)'
								>
									{{ intervalLabel(config.client.config.UpdatePollIntervalSeconds) }}
								</v-chip>
							</template>
						</NumberInput>
						<legend class='section-legend'>
							{{ $t('components.config.mender.features') }}
						</legend>
						<v-checkbox
							v-model='config.connect.config.FileTransfer'
							:label='$t("components.config.mender.connect.fileTransfer")'
						/>
						<v-checkbox
							v-model='config.connect.config.PortForward'
							:label='$t("components.config.mender.connect.portForward")'
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
import { type MenderService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { type MenderConfig } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {
	mdiFileCertificate,
	mdiKeyVariant,
	mdiServerNetwork,
	mdiTimerMarker,
	mdiTimerRefresh,
	mdiTimerSync,
} from '@mdi/js';
import humanizeDuration from 'humanize-duration';
import { onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';

import MenderCertificateUploadDialog from '@/components/config/mender/MenderCertificateUploadDialog.vue';
import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import CardTitleActionBtn from '@/components/layout/card/CardTitleActionBtn.vue';
import NumberInput from '@/components/layout/form/NumberInput.vue';
import TextInput from '@/components/layout/form/TextInput.vue';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { Action } from '@/types/Action';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const service: MenderService = useApiClient().getConfigServices().getMenderService();
const form: Ref<VForm | null> = ref(null);
const config: Ref<MenderConfig | null> = ref(null);

function intervalColor(value: number|null): string {
	if (value === null || value < 0) {
		return 'error';
	}
	return 'info';
}

function intervalLabel(value: number|null): string {
	if (value === null || value < 0) {
		return 'N/A';
	}
	return humanizeDuration(value * 1_000, { language: i18n.locale.value });
}

async function getConfig(): Promise<void> {
	if (componentState.value === ComponentState.Created) {
		componentState.value = ComponentState.Loading;
	} else {
		componentState.value = ComponentState.Reloading;
	}
	try {
		config.value = await service.getConfig();
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error('TODO FETCH ERROR HANDLING');
	}
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || config.value === null) {
		return;
	}
	componentState.value = ComponentState.Saving;
	try {
		const params = { ...config.value };
		await service.updateConfig(params);
		await getConfig();
		toast.success(
			i18n.t('components.config.monit.messages.save.success'),
		);
	} catch {
		toast.error('TODO SAVE ERROR HANDLING');
	}
}

onMounted(async (): Promise<void> => await getConfig());
</script>
