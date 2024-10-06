<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
	<ModalWindow v-model='show'>
		<template #activator='{ props }'>
			<CardTitleActionBtn
				v-if='action === Action.Add'
				v-bind='props'
				:action='action'
				:tooltip='$t("components.config.daemon.connections.actions.add")'
			/>
			<DataTableAction
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
			:disabled='componentState === ComponentState.Saving'
			@submit.prevent='onSubmit'
		>
			<Card :action='action'>
				<template #title>
					{{ dialogTitle }}
				</template>
				<TextInput
					v-model='profile.instance'
					:label='$t("components.config.daemon.connections.profile")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.connections.validation.profileMissing")),
					]'
					required
				/>
				<TextInput
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
				</TextInput>
				<TextInput
					v-model='profile.ClientId'
					:label='$t("components.config.daemon.connections.mqtt.clientId")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.connections.mqtt.validation.clientIdMissing")),
					]'
					required
				/>
				<TextInput
					v-model='profile.TopicRequest'
					:label='$t("components.config.daemon.connections.mqtt.requestTopic")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.connections.mqtt.validation.requestTopicMissing")),
					]'
					required
				/>
				<TextInput
					v-model='profile.TopicResponse'
					:label='$t("components.config.daemon.connections.mqtt.responseTopic")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.connections.mqtt.validation.responseTopicMissing")),
					]'
					required
				/>
				<TextInput
					v-model='profile.User'
					:label='$t("common.labels.username")'
					:rules='[
						(v: string|null) => ValidationRules.requiredIf(v, profile.Password.length > 0, $t("common.validation.credentialsMissing")),
					]'
					:required='profile.Password.length > 0'
				/>
				<PasswordInput
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
					<TextInput
						v-model='profile.TrustStore'
						:label='$t("components.config.daemon.connections.trustStore")'
					/>
					<TextInput
						v-model='profile.KeyStore'
						:label='$t("components.config.daemon.connections.keyStore")'
					/>
					<TextInput
						v-model='profile.PrivateKey'
						:label='$t("components.config.daemon.connections.privateKey")'
					/>
					<TextInput
						v-model='profile.PrivateKeyPassword'
						:label='$t("components.config.daemon.connections.privateKeyPassword")'
					/>
					<TextInput
						v-model='profile.EnabledCipherSuites'
						:label='$t("components.config.daemon.connections.cipherSuites")'
					/>
					<v-checkbox
						v-model='profile.EnableServerCertAuth'
						:label='$t("components.config.daemon.connections.serverCertAuth")'
					/>
				</span>
				<template #actions>
					<CardActionBtn
						:action='action'
						:disabled='!isValid.value || componentState === ComponentState.Saving'
						type='submit'
					/>
					<v-spacer />
					<CardActionBtn
						:action='Action.Cancel'
						:disabled='componentState === ComponentState.Saving'
						@click='close'
					/>
				</template>
			</Card>
		</v-form>
	</ModalWindow>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	IqrfGatewayDaemonComponentName,
	IqrfGatewayDaemonMqttMessagingQos,
	type IqrfGatewayDaemonMqttMessaging,
	IqrfGatewayDaemonMqttMessagingPersistence,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { computed, type PropType, type Ref, ref , watchEffect } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import CardTitleActionBtn from '@/components/layout/card/CardTitleActionBtn.vue';
import DataTableAction from '@/components/layout/data-table/DataTableAction.vue';
import NumberInput from '@/components/layout/form/NumberInput.vue';
import PasswordInput from '@/components/layout/form/PasswordInput.vue';
import SelectInput from '@/components/layout/form/SelectInput.vue';
import TextInput from '@/components/layout/form/TextInput.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import MqttUrlForm from '@/components/MqttUrlForm.vue';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { Action } from '@/types/Action';
import { ComponentState } from '@/types/ComponentState';

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
	componentState.value = ComponentState.Saving;
	const params = { ...profile.value };
	if (componentProps.action === Action.Add) {
		service.createInstance(IqrfGatewayDaemonComponentName.IqrfMqttMessaging, params)
			.then(() => handleSuccess(instance))
			.catch(handleError);
	} else {
		service.updateInstance(IqrfGatewayDaemonComponentName.IqrfMqttMessaging, instance, params)
			.then(() => handleSuccess(instance))
			.catch(handleError);
	}
}

function handleSuccess(name: string): void {
	componentState.value = ComponentState.Ready;
	toast.success(
		i18n.t('components.config.daemon.connections.udp.messages.save.success', { name: name }),
	);
	close();
	emit('saved');
}

function handleError(): void {
	componentState.value = ComponentState.Ready;
	toast.error('TODO ERROR HANDLING');
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
