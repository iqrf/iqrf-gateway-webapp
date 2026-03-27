<!--
Copyright 2017-2026 IQRF Tech s.r.o.
Copyright 2019-2026 MICRORISC s.r.o.

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
				{{ $t('pages.config.daemon.monitoring.title') }}
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
				:text='$t("components.config.daemon.monitoring.messages.fetch.failed")'
			/>
			<v-skeleton-loader
				class='input-skeleton-loader'
				:loading='componentState === ComponentState.Loading'
				type='heading@2, list-item@2'
			>
				<v-responsive>
					<section v-if='config'>
						<INumberInput
							v-model='config.reportPeriod'
							:label='$t("components.config.daemon.monitoring.reportPeriod")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.monitoring.validation.reportPeriod.required")),
								(v: number) => ValidationRules.integer(v, $t("components.config.daemon.monitoring.validation.reportPeriod.integer")),
								(v: number) => ValidationRules.min(v, 1, $t("components.config.daemon.monitoring.validation.reportPeriod.minimum")),
							]'
							:min='1'
							required
						/>
						<ISelectInput
							v-model='config.transportMode'
							:label='$t("components.config.daemon.connections.ws.transportMode")'
							:items='transportModeOptions'
						/>
						<v-checkbox
							v-model='config.acceptOnlyLocalhost'
							:label='$t("components.config.daemon.connections.ws.localhostOnly")'
							hide-details
							density='compact'
						/>
						<INumberInput
							v-model='config.port'
							:label='$t("components.config.daemon.connections.ws.plainPort")'
							:min='1'
							:max='65535'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("common.validation.port.required")),
								(v: number) => ValidationRules.integer(v, $t("common.validation.port.integer")),
								(v: number) => ValidationRules.between(v, 1, 65535, $t("common.validation.port.between")),
							]'
							:disabled='config.transportMode === IqrfGatewayDaemonWsTransportModes.Tls'
							required
						/>
						<INumberInput
							v-model='config.tlsPort'
							:label='$t("components.config.daemon.connections.ws.tlsPort")'
							:min='1'
							:max='65535'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("common.validation.port.required")),
								(v: number) => ValidationRules.integer(v, $t("common.validation.port.integer")),
								(v: number) => ValidationRules.between(v, 1, 65535, $t("common.validation.port.between")),
							]'
							:disabled='config.transportMode === IqrfGatewayDaemonWsTransportModes.Plain'
							required
						/>
						<ISelectInput
							v-model='config.tlsMode'
							:label='$t("components.config.daemon.connections.ws.tlsMode")'
							:items='tlsModeOptions'
							:hint='getWebSocketTlsModeDescription(config.tlsMode)'
							persistent-hint
							:disabled='config.transportMode === IqrfGatewayDaemonWsTransportModes.Plain'
						/>
						<ITextInput
							v-model='config.cert'
							:label='$t("components.config.daemon.connections.ws.certificate")'
							:rules='
								config.transportMode !== IqrfGatewayDaemonWsTransportModes.Plain ?
									[
										(v: string|null) => ValidationRules.required(
											v,
											$t("components.config.daemon.connections.ws.validation.certificate.required"),
										),
									] : []
							'
							:disabled='config.transportMode === IqrfGatewayDaemonWsTransportModes.Plain'
							:required='config.transportMode !== IqrfGatewayDaemonWsTransportModes.Plain'
						/>
						<ITextInput
							v-model='config.privKey'
							:label='$t("components.config.daemon.connections.ws.privateKey")'
							:rules='
								config.transportMode !== IqrfGatewayDaemonWsTransportModes.Plain ?
									[
										(v: string|null) => ValidationRules.required(
											v,
											$t("components.config.daemon.connections.ws.validation.privateKey.required"),
										),
									] : []
							'
							:disabled='config.transportMode === IqrfGatewayDaemonWsTransportModes.Plain'
							:required='config.transportMode !== IqrfGatewayDaemonWsTransportModes.Plain'
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
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	IqrfGatewayDaemonComponentName,
	type IqrfGatewayDaemonMonitor,
	IqrfGatewayDaemonWsTransportModes,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	INumberInput,
	ISelectInput,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import {
	onMounted,
	ref,
	useTemplateRef,
} from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import { getWebSocketTlsModeDescription, getWebSocketTlsModeOptions, getWebSocketTransportModeOptions } from '@/common/daemon';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

const i18n = useI18n();
const componentState = ref<ComponentState>(ComponentState.Created);
const service: IqrfGatewayDaemonService = useApiClient()
	.getConfigServices()
	.getIqrfGatewayDaemonService();
const form = useTemplateRef<VForm>('form');
const config = ref<IqrfGatewayDaemonMonitor | null>(null);
const tlsModeOptions = getWebSocketTlsModeOptions();
const transportModeOptions = getWebSocketTransportModeOptions();

async function getConfig(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		const data = await service.getComponent(IqrfGatewayDaemonComponentName.IqrfMonitor);
		if (data.instances.length === 0) {
			throw new Error('Configuration instance missing.');
		}
		config.value = data.instances[0];
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error(
			i18n.t('components.config.daemon.monitoring.messages.fetch.failed'),
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
		await service.updateInstance(IqrfGatewayDaemonComponentName.IqrfMonitor, params.instance, params);
		toast.success(
			i18n.t('components.config.daemon.monitoring.messages.save.success'),
		);
	} catch {
		toast.error(
			i18n.t('components.config.daemon.monitoring.messages.save.failed'),
		);
	}
	componentState.value = ComponentState.Ready;
}

onMounted(() => {
	getConfig();
});
</script>
