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
	<IModalWindow
		v-model='show'
		persistent
	>
		<template #activator='{ props }'>
			<IActionBtn
				v-if='action === Action.Add'
				v-bind='props'
				:action='action'
				container-type='card-title'
				:tooltip='$t("components.config.daemon.connections.actions.add")'
				:disabled='disabled'
			/>
			<IDataTableAction
				v-if='action === Action.Edit'
				v-bind='props'
				:action='action'
				:tooltip='$t("components.config.daemon.connections.actions.edit")'
				:disabled='disabled'
			/>
		</template>
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
				<ITextInput
					v-model='profile.BrokerAddr'
					:label='$t("components.config.daemon.connections.mqtt.broker")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.connections.mqtt.validation.broker.required")),
						(v: string) => ValidationRules.url(v, $t("components.config.daemon.connections.mqtt.validation.broker.url"), /^(tcp|ssl|mqtts?|wss?)$/),
					]'
					required
				>
					<template #append>
						<MqttUrlForm
							:card-title='$t("components.config.daemon.connections.mqtt.broker")'
							:url='profile.BrokerAddr'
							@edited='(value: string) => profile.BrokerAddr = value'
						/>
					</template>
				</ITextInput>
				<ITextInput
					v-model='profile.ClientId'
					:label='$t("components.config.daemon.connections.mqtt.clientId")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.connections.mqtt.validation.clientId.required")),
					]'
					required
				/>
				<ITextInput
					v-model='profile.TopicRequest'
					:label='$t("components.config.daemon.connections.mqtt.requestTopic")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.connections.mqtt.validation.requestTopic.required")),
					]'
					required
				/>
				<ITextInput
					v-model='profile.TopicResponse'
					:label='$t("components.config.daemon.connections.mqtt.responseTopic")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.connections.mqtt.validation.responseTopic.required")),
					]'
					required
				/>
				<ITextInput
					v-model='profile.User'
					:label='$t("common.labels.username")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("common.validation.username.required")),
					]'
					:prepend-inner-icon='mdiAccount'
					required
				/>
				<IPasswordInput
					v-model='profile.Password'
					:label='$t("common.labels.password")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("common.validation.password.required")),
					]'
					:required='profile.User.length > 0'
				/>
				<ISelectInput
					v-model='profile.Qos'
					:label='$t("components.config.daemon.connections.mqtt.qos")'
					:items='qosOptions'
					:hint='qosDescription'
					persistent-hint
				/>
				<ISelectInput
					v-model.number='profile.Persistence'
					:label='$t("components.config.daemon.connections.mqtt.persistence")'
					:items='persistenceOptions'
					:hint='persistenceDescription'
					persistent-hint
				/>
				<INumberInput
					v-model='profile.KeepAliveInterval'
					:label='$t("components.config.daemon.connections.mqtt.keepAlive")'
					:min='0'
					:rules='[
						(v: number) => ValidationRules.required(v, $t("components.config.daemon.connections.mqtt.validation.keepAlive.required")),
						(v: number) => ValidationRules.integer(v, $t("components.config.daemon.connections.mqtt.validation.keepAlive.integer")),
						(v: number) => ValidationRules.min(v, 0, $t("components.config.daemon.connections.mqtt.validation.keepAlive.min")),
					]'
					required
				/>
				<INumberInput
					v-model='profile.ConnectTimeout'
					:label='$t("components.config.daemon.connections.mqtt.timeout")'
					:min='0'
					:rules='[
						(v: number) => ValidationRules.required(v, $t("components.config.daemon.connections.mqtt.validation.timeout.required")),
						(v: number) => ValidationRules.integer(v, $t("components.config.daemon.connections.mqtt.validation.timeout.integer")),
						(v: number) => ValidationRules.min(v, 0, $t("components.config.daemon.connections.mqtt.validation.timeout.min")),
					]'
					required
				/>
				<INumberInput
					v-model='profile.MinReconnect'
					:label='$t("components.config.daemon.connections.mqtt.minReconnect")'
					:min='1'
					:max='profile.MaxReconnect'
					:rules='[
						(v: number) => ValidationRules.required(v, $t("components.config.daemon.connections.mqtt.validation.minReconnect.required")),
						(v: number) => ValidationRules.integer(v, $t("components.config.daemon.connections.mqtt.validation.minReconnect.integer")),
						(v: number) => ValidationRules.min(v, 1, $t("components.config.daemon.connections.mqtt.validation.minReconnect.min")),
						(v: number) => ValidationRules.max(v, profile.MaxReconnect, $t("components.config.daemon.connections.mqtt.validation.minReconnect.max")),
					]'
					required
				/>
				<INumberInput
					v-model='profile.MaxReconnect'
					:label='$t("components.config.daemon.connections.mqtt.maxReconnect")'
					:min='profile.MinReconnect'
					:rules='[
						(v: number) => ValidationRules.required(v, $t("components.config.daemon.connections.mqtt.validation.maxReconnect.required")),
						(v: number) => ValidationRules.integer(v, $t("components.config.daemon.connections.mqtt.validation.maxReconnect.integer")),
						(v: number) => ValidationRules.min(v, profile.MinReconnect, $t("components.config.daemon.connections.mqtt.validation.maxReconnect.min")),
					]'
					required
				/>
				<v-checkbox
					v-model='profile.acceptAsyncMsg'
					:label='$t("components.config.daemon.connections.acceptAsyncMessages")'
					density='compact'
					:hide-details='!hasTls'
				/>
				<span v-if='hasTls'>
					<ITextInput
						v-model='profile.TrustStore'
						:label='$t("components.config.daemon.connections.trustStore")'
					/>
					<ITextInput
						v-model='profile.KeyStore'
						:label='$t("components.config.daemon.connections.keyStore")'
					/>
					<ITextInput
						v-model='profile.PrivateKey'
						:label='$t("components.config.daemon.connections.privateKey")'
					/>
					<ITextInput
						v-model='profile.PrivateKeyPassword'
						:label='$t("components.config.daemon.connections.privateKeyPassword")'
					/>
					<ITextInput
						v-model='profile.EnabledCipherSuites'
						:label='$t("components.config.daemon.connections.cipherSuites")'
					/>
					<v-checkbox
						v-model='profile.EnableServerCertAuth'
						:label='$t("components.config.daemon.connections.serverCertAuth")'
					/>
				</span>
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
	type IqrfGatewayDaemonMqttMessaging,
	IqrfGatewayDaemonMqttMessagingPersistence,
	IqrfGatewayDaemonMqttMessagingQos,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IDataTableAction,
	IModalWindow,
	INumberInput,
	IPasswordInput,
	ISelectInput,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiAccount } from '@mdi/js';
import {
	computed, type PropType, ref, type Ref,
	type TemplateRef, useTemplateRef, watch,
} from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';

import MqttUrlForm from '@/components/MqttUrlForm.vue';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

const componentProps = defineProps({
	action: {
		type: String as PropType<Action>,
		default: Action.Add,
		required: false,
	},
	connectionProfile: {
		type: Object as PropType<IqrfGatewayDaemonMqttMessaging>,
		default: () => ({
			component: IqrfGatewayDaemonComponentName.IqrfMqttMessaging,
			instance: '',
			BrokerAddr: '',
			ClientId: '',
			TopicRequest: '',
			TopicResponse: '',
			User: '',
			Password: '',
			Persistence: IqrfGatewayDaemonMqttMessagingPersistence.FILESYSTEM,
			Qos: IqrfGatewayDaemonMqttMessagingQos.AT_LEAST_ONCE,
			KeepAliveInterval: 20,
			ConnectTimeout: 5,
			MinReconnect: 1,
			MaxReconnect: 64,
			TrustStore: '',
			KeyStore: '',
			PrivateKey: '',
			PrivateKeyPassword: '',
			EnabledCipherSuites: '',
			EnableServerCertAuth: false,
			acceptAsyncMsg: false,
		}),
		required: false,
	},
	disabled: {
		type: Boolean,
		required: false,
		default: false,
	},
});
const emit = defineEmits<{
	saved: [];
}>();
const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const i18n = useI18n();
const service: IqrfGatewayDaemonService = useApiClient()
	.getConfigServices()
	.getIqrfGatewayDaemonService();
const show: Ref<boolean> = ref(false);
let fromImport = false;
const form: TemplateRef<VForm> = useTemplateRef('form');
const defaultProfile: IqrfGatewayDaemonMqttMessaging = {
	component: IqrfGatewayDaemonComponentName.IqrfMqttMessaging,
	instance: '',
	BrokerAddr: '',
	ClientId: '',
	TopicRequest: '',
	TopicResponse: '',
	User: '',
	Password: '',
	Persistence: IqrfGatewayDaemonMqttMessagingPersistence.FILESYSTEM,
	Qos: IqrfGatewayDaemonMqttMessagingQos.AT_LEAST_ONCE,
	KeepAliveInterval: 20,
	ConnectTimeout: 5,
	MinReconnect: 1,
	MaxReconnect: 64,
	TrustStore: '',
	KeyStore: '',
	PrivateKey: '',
	PrivateKeyPassword: '',
	EnabledCipherSuites: '',
	EnableServerCertAuth: false,
	acceptAsyncMsg: false,
};
const profile: Ref<IqrfGatewayDaemonMqttMessaging> = ref({ ...defaultProfile });
let instance = '';
const hasTls = computed((): boolean => profile.value.BrokerAddr.startsWith('ssl://') ||
	profile.value.BrokerAddr.startsWith('mqtts://') ||
	profile.value.BrokerAddr.startsWith('wss://'));
const qosOptions = computed(() => [
	{
		title: i18n.t('components.config.daemon.connections.mqtt.qosLevels.atMostOnce'),
		value: IqrfGatewayDaemonMqttMessagingQos.AT_LEAST_ONCE,
	},
	{
		title: i18n.t('components.config.daemon.connections.mqtt.qosLevels.atLeastOnce'),
		value: IqrfGatewayDaemonMqttMessagingQos.AT_MOST_ONCE,
	},
	{
		title: i18n.t('components.config.daemon.connections.mqtt.qosLevels.exactlyOnce'),
		value: IqrfGatewayDaemonMqttMessagingQos.ONCE,
	},
]);
const qosDescription = computed(() => {
	if (profile.value.Qos === IqrfGatewayDaemonMqttMessagingQos.AT_MOST_ONCE) {
		return i18n.t('components.config.daemon.connections.mqtt.notes.qos.atMostOnce');
	} else if (profile.value.Qos === IqrfGatewayDaemonMqttMessagingQos.AT_LEAST_ONCE) {
		return i18n.t('components.config.daemon.connections.mqtt.notes.qos.atMostOnce');
	}
	return i18n.t('components.config.daemon.connections.mqtt.notes.qos.exactlyOnce');
});
const persistenceOptions = computed(() => [
	{
		title: i18n.t('components.config.daemon.connections.mqtt.persistenceLevels.memory'),
		value: IqrfGatewayDaemonMqttMessagingPersistence.MEMORY,
	},
	{
		title: i18n.t('components.config.daemon.connections.mqtt.persistenceLevels.filesystem'),
		value: IqrfGatewayDaemonMqttMessagingPersistence.FILESYSTEM,
	},
	{
		title: i18n.t('components.config.daemon.connections.mqtt.persistenceLevels.application'),
		value: IqrfGatewayDaemonMqttMessagingPersistence.APPLICATION,
	},
]);
const persistenceDescription = computed(() => {
	if (profile.value.Persistence === IqrfGatewayDaemonMqttMessagingPersistence.MEMORY) {
		return i18n.t('components.config.daemon.connections.mqtt.notes.persistence.memory');
	} else if (profile.value.Persistence === IqrfGatewayDaemonMqttMessagingPersistence.FILESYSTEM) {
		return i18n.t('components.config.daemon.connections.mqtt.notes.persistence.filesystem');
	}
	return i18n.t('components.config.daemon.connections.mqtt.notes.persistence.application');
});

const dialogTitle = computed(() => {
	if (componentProps.action === Action.Add) {
		return i18n.t('components.config.daemon.connections.actions.add');
	}
	return i18n.t('components.config.daemon.connections.actions.edit');
});

watch(show, (newVal: boolean): void => {
	if (!newVal || fromImport) {
		return;
	}
	if (componentProps.action === Action.Edit && componentProps.connectionProfile) {
		profile.value = { ...componentProps.connectionProfile };
		instance = componentProps.connectionProfile.instance;
	} else {
		profile.value = { ...defaultProfile };
		instance = defaultProfile.instance;
	}
});

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Action;
	const params = { ...profile.value };
	const translationParams = { name: instance };
	try {
		if (componentProps.action === Action.Add) {
			await service.createInstance(IqrfGatewayDaemonComponentName.IqrfMqttMessaging, params);
		} else {
			await service.updateInstance(IqrfGatewayDaemonComponentName.IqrfMqttMessaging, instance, params);
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

function importFromConfig(config: IqrfGatewayDaemonMqttMessaging): void {
	profile.value = { ...config };
	fromImport = true;
	show.value = true;
}

function close(): void {
	show.value = false;
	fromImport = false;
	profile.value = { ...defaultProfile };
}

defineExpose({
	importFromConfig,
});
</script>
