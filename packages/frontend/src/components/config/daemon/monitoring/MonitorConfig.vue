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
					<section v-if='monitorConfig && websocketConfig'>
						<INumberInput
							v-model='monitorConfig.reportPeriod'
							:label='$t("components.config.daemon.monitoring.reportPeriod")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.monitoring.validation.reportPeriod.required")),
								(v: number) => ValidationRules.integer(v, $t("components.config.daemon.monitoring.validation.reportPeriod.integer")),
								(v: number) => ValidationRules.min(v, 1, $t("components.config.daemon.monitoring.validation.reportPeriod.minimum")),
							]'
							:min='1'
							required
						/>
						<INumberInput
							v-model='websocketConfig.WebsocketPort'
							:label='$t("components.config.daemon.monitoring.port")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("common.validation.port.required")),
								(v: number) => ValidationRules.integer(v, $t("common.validation.port.integer")),
								(v: number) => ValidationRules.between(v, 1, 65535, $t("common.validation.port.between")),
							]'
							:min='1'
							:max='65535'
							required
						/>
						<v-checkbox
							v-model='websocketConfig.acceptOnlyLocalhost'
							:label='$t("components.config.daemon.monitoring.onlyLocalhost")'
							density='compact'
							hide-details
						/>
						<v-checkbox
							v-model='websocketConfig.tlsEnabled'
							:label='$t("components.config.daemon.connections.websocket.tlsEnabled")'
							density='compact'
							:hide-details='!websocketConfig.tlsEnabled'
						/>
						<div v-if='websocketConfig.tlsEnabled'>
							<ISelectInput
								v-model='websocketConfig.tlsMode'
								:label='$t("components.config.daemon.connections.websocket.tlsMode")'
								:items='tlsModeOptions'
								:description='$t(`components.config.daemon.connections.websocket.notes.tlsModes.${websocketConfig.tlsMode}`)'
							/>
							<ITextInput
								v-model='websocketConfig.certificate'
								:label='$t("components.config.daemon.connections.websocket.certificate")'
								:rules='[
									(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.connections.websocket.validation.certificate.required")),
								]'
								required
							/>
							<ITextInput
								v-model='websocketConfig.privateKey'
								:label='$t("components.config.daemon.connections.websocket.privateKey")'
								:rules='[
									(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.connections.websocket.validation.privateKey.required")),
								]'
								required
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
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	IqrfGatewayDaemonComponentName,
	type IqrfGatewayDaemonMonitor,
	type ShapeWebsocketService,
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
import { onMounted, ref, type Ref, toRaw } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

enum TLSModes {
	Intermediate = 'intermediate',
	Modern = 'modern',
	Old = 'old',
}

const i18n = useI18n();
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const form: Ref<VForm | null> = ref(null);
const monitorConfig: Ref<IqrfGatewayDaemonMonitor | null> = ref(null);
const websocketConfig: Ref<ShapeWebsocketService | null> = ref(null);
const tlsModeOptions = [
	{
		title: i18n.t('components.config.daemon.connections.websocket.tlsModes.intermediate'),
		value: TLSModes.Intermediate,
	},
	{
		title: i18n.t('components.config.daemon.connections.websocket.tlsModes.modern'),
		value: TLSModes.Modern,
	},
	{
		title: i18n.t('components.config.daemon.connections.websocket.tlsModes.old'),
		value: TLSModes.Old,
	},
];

async function getConfig(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		monitorConfig.value = (await service.getComponent(IqrfGatewayDaemonComponentName.IqrfMonitor)).instances[0] ?? null;
		if (monitorConfig.value === null || monitorConfig.value.RequiredInterfaces.length === 0) {
			throw new Error('Configuration instance missing.');
		}
		const data = await service.getInstance(
			IqrfGatewayDaemonComponentName.ShapeWebsocketService,
			monitorConfig.value.RequiredInterfaces[0].target.instance,
		);
		websocketConfig.value = {
			certificate: '',
			privateKey: '',
			tlsEnabled: false,
			tlsMode: TLSModes.Intermediate,
			...data,
		};
		if (websocketConfig.value === null) {
			throw new Error('Configuration instance missing.');
		}
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error(
			i18n.t('components.config.daemon.monitoring.messages.fetch.failed'),
		);
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
	}
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || monitorConfig.value === null || websocketConfig.value === null) {
		return;
	}
	componentState.value = ComponentState.Action;
	const monitorParams = structuredClone(toRaw(monitorConfig.value));
	const websocketParams = structuredClone(toRaw(websocketConfig.value));
	try {
		await service.updateInstance(IqrfGatewayDaemonComponentName.IqrfMonitor, monitorParams.instance, monitorParams);
		await service.updateInstance(IqrfGatewayDaemonComponentName.ShapeWebsocketService, websocketParams.instance, websocketParams);
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
