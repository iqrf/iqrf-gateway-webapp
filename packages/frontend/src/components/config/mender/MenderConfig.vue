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
				{{ $t('pages.config.mender.title') }}
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
				:text='$t("components.config.mender.messages.fetch.failed")'
			/>
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
						<ITextInput
							v-for='index in config.client.config.Servers.keys()'
							:key='`MenderServerUrl${index}`'
							v-model='config.client.config.Servers[index]'
							:label='$t("components.config.mender.client.server")'
							:rules='[
								(v: string|null) => ValidationRules.required(v, $t("components.config.mender.validation.server.required")),
								(v: string) => ValidationRules.url(v, $t("components.config.mender.validation.server.url")),
							]'
							:prepend-inner-icon='mdiServerNetwork'
							required
						/>
						<ITextInput
							v-model='config.client.config.ServerCertificate'
							:label='$t("components.config.mender.client.cert")'
							:prepend-inner-icon='mdiFileCertificate'
						>
							<template #append>
								<MenderCertificateUploadDialog @path='(e: string) => config!.client.config.ServerCertificate = e' />
							</template>
						</ITextInput>
						<legend class='section-legend'>
							{{ $t('components.config.mender.inventory') }}
						</legend>
						<ITextInput
							v-model='config.client.config.TenantToken'
							:label='$t("components.config.mender.client.tenantToken")'
							:rules='[
								(v: string|null) => ValidationRules.required(v, $t("components.config.mender.validation.tenantToken.required")),
							]'
							:prepend-inner-icon='mdiKeyVariant'
							required
						/>
						<INumberInput
							v-model='config.client.config.InventoryPollIntervalSeconds'
							:label='$t("components.config.mender.client.inventoryPollInterval")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.config.mender.validation.inventoryPollInterval.required")),
								(v: number) => ValidationRules.integer(v, $t("components.config.mender.validation.inventoryPollInterval.integer")),
								(v: number) => ValidationRules.min(v, 0, $t("components.config.mender.validation.inventoryPollInterval.min")),
							]'
							:min='0'
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
						</INumberInput>
						<INumberInput
							v-model='config.client.config.RetryPollIntervalSeconds'
							:label='$t("components.config.mender.client.retryPollInterval")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.config.mender.validation.retryPollInterval.required")),
								(v: number) => ValidationRules.integer(v, $t("components.config.mender.validation.retryPollInterval.integer")),
								(v: number) => ValidationRules.min(v, 0, $t("components.config.mender.validation.retryPollInterval.min")),
							]'
							:min='0'
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
						</INumberInput>
						<INumberInput
							v-model='config.client.config.UpdatePollIntervalSeconds'
							:label='$t("components.config.mender.client.updatePollInterval")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.config.mender.validation.updatePollInterval.required")),
								(v: number) => ValidationRules.integer(v, $t("components.config.mender.validation.updatePollInterval.integer")),
								(v: number) => ValidationRules.min(v, 0, $t("components.config.mender.validation.updatePollInterval.min")),
							]'
							:min='0'
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
						</INumberInput>
						<legend class='section-legend'>
							{{ $t('components.config.mender.features') }}
						</legend>
						<v-checkbox
							v-model='config.connect.config.FileTransfer'
							:label='$t("components.config.mender.connect.fileTransfer")'
							hide-details
							density='compact'
						/>
						<v-checkbox
							v-model='config.connect.config.PortForward'
							:label='$t("components.config.mender.connect.portForward")'
							hide-details
							density='compact'
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
import { type MenderService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { type MenderConfig } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	INumberInput,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import {
	mdiFileCertificate,
	mdiKeyVariant,
	mdiServerNetwork,
	mdiTimerMarker,
	mdiTimerRefresh,
	mdiTimerSync,
} from '@mdi/js';
import humanizeDuration from 'humanize-duration';
import { onMounted, ref, type Ref, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';

import MenderCertificateUploadDialog from '@/components/config/mender/MenderCertificateUploadDialog.vue';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const service: MenderService = useApiClient().getConfigServices().getMenderService();
const form: Ref<VForm | null> = useTemplateRef('form');
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
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		config.value = await service.getConfig();
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error(
			i18n.t('components.config.mender.messages.fetch.failed'),
		);
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
	}
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || config.value === null) {
		return;
	}
	componentState.value = ComponentState.Action;
	try {
		const params = { ...config.value };
		await service.updateConfig(params);
		toast.success(
			i18n.t('components.config.mender.messages.save.success'),
		);
	} catch {
		toast.error(
			i18n.t('components.config.mender.messages.save.failed'),
		);
	}
	componentState.value = ComponentState.Ready;
}

onMounted(async (): Promise<void> => await getConfig());
</script>
