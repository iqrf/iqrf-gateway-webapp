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
	<IModalWindow
		v-model='show'
		persistent
	>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			validate-on='input'
			:disabled='componentState === ComponentState.Action'
			@submit.prevent='onSubmit()'
		>
			<ICard :action='action'>
				<template #title>
					{{ dialogTitle }}
				</template>
				<ITextInput
					v-model='profile.instance'
					:label='$t("components.config.daemon.connections.profile")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.connections.validation.profile.required")),
					]'
					required
				/>
				<WebSocketTransportModeSelect
					v-model='profile.transportMode'
				/>
				<INumberInput
					v-model='profile.authTimeout'
					:label='$t("components.config.daemon.connections.ws.authTimeout")'
					:min='10'
					:max='60'
					:rules='[
						(v: number|null) => ValidationRules.required(
							v,
							$t("components.config.daemon.connections.ws.validation.authTimeout.required"),
						),
						(v: number) => ValidationRules.integer(
							v,
							$t("components.config.daemon.connections.ws.validation.authTimeout.integer"),
						),
						(v: number) => ValidationRules.between(
							v,
							10,
							60,
							$t("components.config.daemon.connections.ws.validation.authTimeout.required"),
						),
					]'
					required
				/>
				<INumberInput
					v-model='profile.maxClients'
					:label='$t("components.config.daemon.connections.ws.maxClients")'
					:min='0'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.connections.ws.validation.maxClients.required")),
						(v: number) => ValidationRules.integer(v, $t("components.config.daemon.connections.ws.validation.maxClients.integer")),
						(v: number) => ValidationRules.min(v, 0, $t("components.config.daemon.connections.ws.validation.maxClients.required")),
					]'
					required
				/>
				<v-checkbox
					v-model='profile.acceptOnlyLocalhost'
					:label='$t("components.config.daemon.connections.ws.localhostOnly")'
					hide-details
					density='compact'
				/>
				<INumberInput
					v-model='profile.port'
					:label='$t("components.config.daemon.connections.ws.plainPort")'
					:min='1'
					:max='65535'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("common.validation.port.required")),
						(v: number) => ValidationRules.integer(v, $t("common.validation.port.integer")),
						(v: number) => ValidationRules.between(v, 1, 65535, $t("common.validation.port.between")),
					]'
					:disabled='profile.transportMode === IqrfGatewayDaemonWsTransportModes.Tls'
					required
				/>
				<INumberInput
					v-model='profile.tlsPort'
					:label='$t("components.config.daemon.connections.ws.tlsPort")'
					:min='1'
					:max='65535'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("common.validation.port.required")),
						(v: number) => ValidationRules.integer(v, $t("common.validation.port.integer")),
						(v: number) => ValidationRules.between(v, 1, 65535, $t("common.validation.port.between")),
					]'
					:disabled='profile.transportMode === IqrfGatewayDaemonWsTransportModes.Plain'
					required
				/>
				<WebSocketTlsModeSelect
					v-model='profile.tlsMode'
					:disabled='profile.transportMode === IqrfGatewayDaemonWsTransportModes.Plain'
				/>
				<ITextInput
					v-model='profile.cert'
					:label='$t("components.config.daemon.connections.ws.certificate")'
					:rules='
						profile.transportMode !== IqrfGatewayDaemonWsTransportModes.Plain ?
							[
								(v: string|null) => ValidationRules.required(
									v,
									$t("components.config.daemon.connections.ws.validation.certificate.required"),
								),
							] : []
					'
					:disabled='profile.transportMode === IqrfGatewayDaemonWsTransportModes.Plain'
					:required='profile.transportMode !== IqrfGatewayDaemonWsTransportModes.Plain'
				/>
				<ITextInput
					v-model='profile.privKey'
					:label='$t("components.config.daemon.connections.ws.privateKey")'
					:rules='
						profile.transportMode !== IqrfGatewayDaemonWsTransportModes.Plain ?
							[
								(v: string|null) => ValidationRules.required(
									v,
									$t("components.config.daemon.connections.ws.validation.privateKey.required"),
								),
							] : []
					'
					:disabled='profile.transportMode === IqrfGatewayDaemonWsTransportModes.Plain'
					:required='profile.transportMode !== IqrfGatewayDaemonWsTransportModes.Plain'
				/>
				<v-checkbox
					v-model='profile.acceptAsyncMsg'
					:label='$t("components.config.daemon.connections.ws.asyncMessages")'
					hide-details
					density='compact'
				/>
				<template #actions>
					<IActionBtn
						:action='action'
						:loading='componentState === ComponentState.Action'
						:disabled='!isValid.value'
						type='submit'
					/>
					<v-spacer />
					<IActionBtn
						:action='Action.Cancel'
						:disabled='componentState === ComponentState.Action'
						@click='close()'
					/>
				</template>
			</ICard>
		</v-form>
	</IModalWindow>
</template>

<script lang='ts' setup>
import {
	type IqrfGatewayDaemonService,
} from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	IqrfGatewayDaemonComponentName,
	type IqrfGatewayDaemonWsMessaging,
	IqrfGatewayDaemonWsTlsModes,
	IqrfGatewayDaemonWsTransportModes,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IModalWindow,
	INumberInput,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import {
	computed,
	ref,
	useTemplateRef,
} from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';

import WebSocketTlsModeSelect
	from '@/components/config/daemon/connections/websocket/WebSocketTlsModeSelect.vue';
import WebSocketTransportModeSelect
	from '@/components/config/daemon/connections/websocket/WebSocketTransportModeSelect.vue';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

const emit = defineEmits<{
	saved: [];
}>();
const componentState = ref<ComponentState>(ComponentState.Idle);
const i18n = useI18n();
const service: IqrfGatewayDaemonService = useApiClient()
	.getConfigServices()
	.getIqrfGatewayDaemonService();
const show = ref<boolean>(false);
const form = useTemplateRef<VForm>('form');
const defaultProfile: IqrfGatewayDaemonWsMessaging = {
	component: IqrfGatewayDaemonComponentName.IqrfWsMessaging,
	instance: '',
	port: 1_338,
	acceptAsyncMsg: false,
	acceptOnlyLocalhost: false,
	transportMode: IqrfGatewayDaemonWsTransportModes.Both,
	tlsMode: IqrfGatewayDaemonWsTlsModes.Modern,
	tlsPort: 8_338,
	cert: '',
	privKey: '',
	authTimeout: 60,
	maxClients: 50,
};
const profile = ref<IqrfGatewayDaemonWsMessaging>({ ...defaultProfile });
const action = ref<Action>(Action.Add);
let instance = '';

const dialogTitle = computed(() => {
	if (action.value === Action.Add) {
		return i18n.t('components.config.daemon.connections.actions.add');
	}
	return i18n.t('components.config.daemon.connections.actions.edit');
});

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Action;
	const params = { ...profile.value };
	const translationParams = { name: action.value === Action.Add ? params.instance : instance };
	try {
		if (action.value === Action.Add) {
			await service.createInstance(IqrfGatewayDaemonComponentName.IqrfWsMessaging, params);
		} else {
			await service.updateInstance(IqrfGatewayDaemonComponentName.IqrfWsMessaging, instance, params);
		}
		toast.success(
			i18n.t('components.config.daemon.connections.mqtt.messages.save.success', translationParams),
		);
		close();
		emit('saved');
	} catch {
		toast.error(
			i18n.t('components.config.daemon.connections.mqtt.messages.save.failed', translationParams),
		);
	}
	componentState.value = ComponentState.Idle;
}

function open(config: IqrfGatewayDaemonWsMessaging | null = null, openAction: Action): void {
	action.value = openAction;
	if (config) {
		profile.value = { ...config };
		instance = config.instance;
	} else {
		profile.value = { ...defaultProfile };
	}
	show.value = true;
}

function close(): void {
	show.value = false;
	profile.value = { ...defaultProfile };
	instance = '';
}

defineExpose({
	open,
});
</script>
