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
	<IModalWindow v-model='show'>
		<template #activator='{ props }'>
			<IActionBtn
				v-if='action === Action.Add'
				v-bind='props'
				:action='action'
				container-type='card-title'
				:tooltip='$t("components.config.daemon.connections.actions.add")'
			/>
			<IDataTableAction
				v-if='action === Action.Edit'
				v-bind='props'
				:action='action'
				:tooltip='$t("components.config.daemon.connections.actions.edit")'
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
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.connections.validation.profileMissing")),
					]'
					required
				/>
				<ITextInput
					v-model='profile.BrokerAddr'
					:label='$t("components.config.daemon.connections.mqtt.broker")'
					readonly
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
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.connections.mqtt.validation.clientIdMissing")),
					]'
					required
				/>
				<ITextInput
					v-model='profile.TopicRequest'
					:label='$t("components.config.daemon.connections.mqtt.requestTopic")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.connections.mqtt.validation.requestTopicMissing")),
					]'
					required
				/>
				<ITextInput
					v-model='profile.TopicResponse'
					:label='$t("components.config.daemon.connections.mqtt.responseTopic")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.connections.mqtt.validation.responseTopicMissing")),
					]'
					required
				/>
				<ITextInput
					v-model='profile.User'
					:label='$t("common.labels.username")'
					:rules='[
						(v: string|null) => ValidationRules.requiredIf(v, profile.Password.length > 0, $t("common.validation.credentialsMissing")),
					]'
					:required='profile.Password.length > 0'
				/>
				<IPasswordInput
					v-model='profile.Password'
					:label='$t("common.labels.password")'
					:rules='[
						(v: string|null) => ValidationRules.requiredIf(v, profile.User.length > 0, $t("common.validation.credentialsMissing")),
					]'
					:required='profile.User.length > 0'
				/>
				<SelectInput
					v-model='profile.Qos'
					:label='$t("components.config.daemon.connections.mqtt.qos")'
					:items='qosOptions'
					:hint='qosDescription'
					persistent-hint
				/>
				<SelectInput
					v-model.number='profile.Persistence'
					:label='$t("components.config.daemon.connections.mqtt.persistence")'
					:items='persistenceOptions'
					:hint='persistenceDescription'
					persistent-hint
				/>
				<NumberInput
					v-model.number='profile.KeepAliveInterval'
					:label='$t("components.config.daemon.connections.mqtt.keepAlive")'
					:min='0'
					:rules='[
						(v: number) => ValidationRules.required(v, $t("components.config.daemon.connections.mqtt.validation.keepAliveMissing")),
						(v: number) => ValidationRules.integer(v, $t("components.config.daemon.connections.mqtt.validation.keepAliveInvalid")),
						(v: number) => ValidationRules.min(v, 0, $t("components.config.daemon.connections.mqtt.validation.keepAliveInvalid")),
					]'
					required
				/>
				<NumberInput
					v-model.number='profile.ConnectTimeout'
					:label='$t("components.config.daemon.connections.mqtt.timeout")'
					:min='0'
					:rules='[
						(v: number) => ValidationRules.required(v, $t("components.config.daemon.connections.mqtt.validation.timeoutMissing")),
						(v: number) => ValidationRules.integer(v, $t("components.config.daemon.connections.mqtt.validation.timeoutInvalid")),
						(v: number) => ValidationRules.min(v, 0, $t("components.config.daemon.connections.mqtt.validation.timeoutInvalid")),
					]'
					required
				/>
				<NumberInput
					v-model.number='profile.MinReconnect'
					:label='$t("components.config.daemon.connections.mqtt.minReconnect")'
					:min='1'
					:max='profile.MaxReconnect'
					:rules='[
						(v: number) => ValidationRules.required(v, $t("components.config.daemon.connections.mqtt.validation.minReconnectMissing")),
						(v: number) => ValidationRules.integer(v, $t("components.config.daemon.connections.mqtt.validation.minReconnectInvalid")),
						(v: number) => ValidationRules.min(v, 1, $t("components.config.daemon.connections.mqtt.validation.minReconnectInvalid")),
						(v: number) => ValidationRules.max(v, profile.MaxReconnect, $t("components.config.daemon.connections.mqtt.validation.minReconnectHigh")),
					]'
					required
				/>
				<NumberInput
					v-model.number='profile.MaxReconnect'
					:label='$t("components.config.daemon.connections.mqtt.maxReconnect")'
					:min='1'
					:rules='[
						(v: number) => ValidationRules.required(v, $t("components.config.daemon.connections.mqtt.validation.maxReconnectMissing")),
						(v: number) => ValidationRules.integer(v, $t("components.config.daemon.connections.mqtt.validation.maxReconnectInvalid")),
						(v: number) => ValidationRules.min(v, 1, $t("components.config.daemon.connections.mqtt.validation.maxReconnectInvalid")),
						(v: number) => ValidationRules.min(v, profile.MinReconnect, $t("components.config.daemon.connections.mqtt.validation.maxReconnectLow")),
					]'
					required
				/>
				<v-checkbox
					v-model='profile.acceptAsyncMsg'
					:label='$t("components.config.daemon.connections.acceptAsyncMessages")'
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
						container-type='card'
						:disabled='!isValid.value || componentState === ComponentState.Action'
						type='submit'
					/>
					<v-spacer />
					<IActionBtn
						:action='Action.Cancel'
						container-type='card'
						:disabled='componentState === ComponentState.Action'
						@click='close()'
					/>
				</template>
			</ICard>
		</v-form>
	</IModalWindow>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
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
	IPasswordInput,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { computed, type PropType, ref, type Ref , watchEffect } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import NumberInput from '@/components/layout/form/NumberInput.vue';
import SelectInput from '@/components/layout/form/SelectInput.vue';
import MqttUrlForm from '@/components/MqttUrlForm.vue';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const emit = defineEmits(['saved']);
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
});
const i18n = useI18n();
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const show: Ref<boolean> = ref(false);
const form: Ref<VForm | null> = ref(null);
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
const hasTls = computed((): boolean =>
	profile.value.BrokerAddr.startsWith('ssl://') ||
	profile.value.BrokerAddr.startsWith('mqtts://') ||
	profile.value.BrokerAddr.startsWith('wss://'),
);
const qosOptions = [
	{
		title: i18n.t('components.config.daemon.connections.mqtt.qosLevels.atMostOnce').toString(),
		value: IqrfGatewayDaemonMqttMessagingQos.AT_LEAST_ONCE,
	},
	{
		title: i18n.t('components.config.daemon.connections.mqtt.qosLevels.atLeastOnce').toString(),
		value: IqrfGatewayDaemonMqttMessagingQos.AT_MOST_ONCE,
	},
	{
		title: i18n.t('components.config.daemon.connections.mqtt.qosLevels.exactlyOnce').toString(),
		value: IqrfGatewayDaemonMqttMessagingQos.ONCE,
	},
];
const qosDescription = computed(() => {
	if (profile.value.Qos === IqrfGatewayDaemonMqttMessagingQos.AT_MOST_ONCE) {
		return i18n.t('components.config.daemon.connections.mqtt.messages.qos.atMostOnce').toString();
	} else if (profile.value.Qos === IqrfGatewayDaemonMqttMessagingQos.AT_LEAST_ONCE) {
		return i18n.t('components.config.daemon.connections.mqtt.messages.qos.atMostOnce').toString();
	}
	return i18n.t('components.config.daemon.connections.mqtt.messages.qos.exactlyOnce').toString();
});
const persistenceOptions = [
	{
		title: i18n.t('components.config.daemon.connections.mqtt.persistenceLevels.memory').toString(),
		value: IqrfGatewayDaemonMqttMessagingPersistence.MEMORY,
	},
	{
		title: i18n.t('components.config.daemon.connections.mqtt.persistenceLevels.filesystem').toString(),
		value: IqrfGatewayDaemonMqttMessagingPersistence.FILESYSTEM,
	},
	{
		title: i18n.t('components.config.daemon.connections.mqtt.persistenceLevels.application').toString(),
		value: IqrfGatewayDaemonMqttMessagingPersistence.APPLICATION,
	},
];
const persistenceDescription = computed(() => {
	if (profile.value.Persistence === IqrfGatewayDaemonMqttMessagingPersistence.MEMORY) {
		return i18n.t('components.config.daemon.connections.mqtt.messages.persistence.memory').toString();
	} else if (profile.value.Persistence === IqrfGatewayDaemonMqttMessagingPersistence.FILESYSTEM) {
		return i18n.t('components.config.daemon.connections.mqtt.messages.persistence.filesystem').toString();
	}
	return i18n.t('components.config.daemon.connections.mqtt.messages.persistence.application').toString();
});

const dialogTitle = computed(() => {
	if (componentProps.action === Action.Add) {
		return i18n.t('components.config.daemon.connections.actions.add').toString();
	}
	return i18n.t('components.config.daemon.connections.actions.edit').toString();
});

watchEffect((): void => {
	if (componentProps.action === Action.Edit && componentProps.connectionProfile) {
		profile.value = { ...componentProps.connectionProfile };
		instance = componentProps.connectionProfile.instance;
	} else {
		profile.value = { ...defaultProfile };
		instance = defaultProfile.instance;
	}
	componentState.value = ComponentState.Ready;
});

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Action;
	const params = { ...profile.value };
	try {
		if (componentProps.action === Action.Add) {
			await service.createInstance(IqrfGatewayDaemonComponentName.IqrfMqttMessaging, params);
		} else {
			await service.updateInstance(IqrfGatewayDaemonComponentName.IqrfMqttMessaging, instance, params);
		}
		toast.success(
			i18n.t('components.config.daemon.connections.udp.messages.save.success', { name: instance }),
		);
		close();
		emit('saved');
	} catch {
		toast.error('TODO ERROR HANDLING');
	}
	componentState.value = ComponentState.Ready;
}

function importFromConfig(config: IqrfGatewayDaemonMqttMessaging): void {
	profile.value = { ...config };
	show.value = true;
}

defineExpose({
	importFromConfig,
});

function close(): void {
	show.value = false;
	profile.value = { ...defaultProfile };
}
</script>
