<template>
	<ModalWindow v-model='show'>
		<template #activator='{ props }'>
			<v-btn
				v-if='action === FormAction.Add'
				id='add-activator'
				v-bind='props'
				:color='iconColor'
				:icon='activatorIcon'
			/>
			<v-tooltip
				v-if='action === FormAction.Add'
				activator='#add-activator'
				location='bottom'
			>
				{{ $t('components.configuration.daemon.connections.actions.add') }}
			</v-tooltip>
			<v-icon
				v-if='action === FormAction.Edit'
				id='edit-activator'
				v-bind='props'
				:color='iconColor'
				:icon='activatorIcon'
				size='large'
				class='me-2'
			/>
			<v-tooltip
				v-if='action === FormAction.Edit'
				activator='#edit-activator'
				location='bottom'
			>
				{{ $t('components.configuration.daemon.connections.actions.edit') }}
			</v-tooltip>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			validate-on='input'
			:disabled='componentState === ComponentState.Saving'
			@submit.prevent='onSubmit'
		>
			<Card>
				<template #title>
					{{ dialogTitle }}
				</template>
				<TextInput
					v-model='profile.instance'
					:label='$t("components.configuration.daemon.connections.profile")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.configuration.daemon.connections.validation.profileMissing"))
					]'
					required
				/>
				<TextInput
					v-model='profile.BrokerAddr'
					:label='$t("components.configuration.daemon.connections.mqtt.broker")'
					readonly
					required
				>
					<template #append>
						<MqttUrlForm
							:card-title='$t("components.configuration.daemon.connections.mqtt.broker")'
							:url='profile.BrokerAddr'
							@edited='(value: string) => profile.BrokerAddr = value'
						/>
					</template>
				</TextInput>
				<TextInput
					v-model='profile.ClientId'
					:label='$t("components.configuration.daemon.connections.mqtt.clientId")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.configuration.daemon.connections.mqtt.validation.clientIdMissing"))
					]'
					required
				/>
				<TextInput
					v-model='profile.TopicRequest'
					:label='$t("components.configuration.daemon.connections.mqtt.requestTopic")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.configuration.daemon.connections.mqtt.validation.requestTopicMissing"))
					]'
					required
				/>
				<TextInput
					v-model='profile.TopicResponse'
					:label='$t("components.configuration.daemon.connections.mqtt.responseTopic")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.configuration.daemon.connections.mqtt.validation.responseTopicMissing"))
					]'
					required
				/>
				<TextInput
					v-model='profile.User'
					:label='$t("common.labels.username")'
					:rules='[
						(v: string|null) => ValidationRules.requiredIf(v, profile.Password.length > 0, $t("common.validation.credentialsMissing"))
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
					:label='$t("components.configuration.daemon.connections.mqtt.qos")'
					:items='qosOptions'
					:hint='qosDescription'
					persistent-hint
				/>
				<SelectInput
					v-model.number='profile.Persistence'
					:label='$t("components.configuration.daemon.connections.mqtt.persistence")'
					:items='persistenceOptions'
					:hint='persistenceDescription'
					persistent-hint
				/>
				<TextInput
					v-model.number='profile.KeepAliveInterval'
					type='number'
					:label='$t("components.configuration.daemon.connections.mqtt.keepAlive")'
					:rules='[
						(v: number) => ValidationRules.required(v, $t("components.configuration.daemon.connections.mqtt.validation.keepAliveMissing")),
						(v: number) => ValidationRules.integer(v, $t("components.configuration.daemon.connections.mqtt.validation.keepAliveInvalid")),
						(v: number) => ValidationRules.min(v, 0, $t("components.configuration.daemon.connections.mqtt.validation.keepAliveInvalid")),
					]'
					required
				/>
				<TextInput
					v-model.number='profile.ConnectTimeout'
					type='number'
					:label='$t("components.configuration.daemon.connections.mqtt.timeout")'
					:rules='[
						(v: number) => ValidationRules.required(v, $t("components.configuration.daemon.connections.mqtt.validation.timeoutMissing")),
						(v: number) => ValidationRules.integer(v, $t("components.configuration.daemon.connections.mqtt.validation.timeoutInvalid")),
						(v: number) => ValidationRules.min(v, 0, $t("components.configuration.daemon.connections.mqtt.validation.timeoutInvalid")),
					]'
					required
				/>
				<TextInput
					v-model.number='profile.MinReconnect'
					type='number'
					:label='$t("components.configuration.daemon.connections.mqtt.minReconnect")'
					:rules='[
						(v: number) => ValidationRules.required(v, $t("components.configuration.daemon.connections.mqtt.validation.minReconnectMissing")),
						(v: number) => ValidationRules.integer(v, $t("components.configuration.daemon.connections.mqtt.validation.minReconnectInvalid")),
						(v: number) => ValidationRules.min(v, 1, $t("components.configuration.daemon.connections.mqtt.validation.minReconnectInvalid")),
						(v: number) => ValidationRules.max(v, profile.MaxReconnect, $t("components.configuration.daemon.connections.mqtt.validation.minReconnectHigh")),
					]'
					required
				/>
				<TextInput
					v-model.number='profile.MaxReconnect'
					type='number'
					:label='$t("components.configuration.daemon.connections.mqtt.maxReconnect")'
					:rules='[
						(v: number) => ValidationRules.required(v, $t("components.configuration.daemon.connections.mqtt.validation.maxReconnectMissing")),
						(v: number) => ValidationRules.integer(v, $t("components.configuration.daemon.connections.mqtt.validation.maxReconnectInvalid")),
						(v: number) => ValidationRules.min(v, 1, $t("components.configuration.daemon.connections.mqtt.validation.maxReconnectInvalid")),
						(v: number) => ValidationRules.min(v, profile.MinReconnect, $t("components.configuration.daemon.connections.mqtt.validation.maxReconnectLow")),
					]'
					required
				/>
				<v-checkbox
					v-model='profile.acceptAsyncMsg'
					:label='$t("components.configuration.daemon.connections.acceptAsyncMessages")'
					density='compact'
					hide-details
				/>
				<v-checkbox
					v-model='profile.EnabledSSL'
					:label='$t("components.configuration.daemon.connections.enableTls")'
					density='compact'
					:hide-details='!profile.EnabledSSL'
				/>
				<span v-if='profile.EnabledSSL'>
					<TextInput
						v-model='profile.TrustStore'
						:label='$t("components.configuration.daemon.connections.trustStore")'
					/>
					<TextInput
						v-model='profile.KeyStore'
						:label='$t("components.configuration.daemon.connections.keyStore")'
					/>
					<TextInput
						v-model='profile.PrivateKey'
						:label='$t("components.configuration.daemon.connections.privateKey")'
					/>
					<TextInput
						v-model='profile.PrivateKeyPassword'
						:label='$t("components.configuration.daemon.connections.privateKeyPassword")'
					/>
					<TextInput
						v-model='profile.EnabledCipherSuites'
						:label='$t("components.configuration.daemon.connections.cipherSuites")'
					/>
					<v-checkbox
						v-model='profile.EnableServerCertAuth'
						:label='$t("components.configuration.daemon.connections.serverCertAuth")'
						density='compact'
						hide-details
					/>
				</span>
				<template #actions>
					<FormActionButton
						:action='action'
						:disabled='!isValid.value || componentState === ComponentState.Saving'
						type='submit'
					/>
					<v-spacer />
					<FormActionButton
						:action='FormAction.Cancel'
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
import { IqrfGatewayDaemonComponentName, IqrfGatewayDaemonMqttMessagingQos, type IqrfGatewayDaemonMqttMessaging, IqrfGatewayDaemonMqttMessagingPersistence } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { mdiPencil, mdiPlus } from '@mdi/js';
import { computed, type PropType, type Ref, ref , watchEffect } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import FormActionButton from '@/components/FormActionButton.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import MqttUrlForm from '@/components/MqttUrlForm.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import SelectInput from '@/components/SelectInput.vue';
import TextInput from '@/components/TextInput.vue';
import { FormAction } from '@/enums/controls';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const emit = defineEmits(['saved']);
const componentProps = defineProps({
	action: {
		type: String as PropType<FormAction>,
		default: FormAction.Add,
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
			EnabledSSL: false,
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
const form: Ref<typeof VForm | null> = ref(null);
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
	EnabledSSL: false,
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
const qosOptions = [
	{
		title: i18n.t('components.configuration.daemon.connections.mqtt.qosLevels.atMostOnce').toString(),
		value: IqrfGatewayDaemonMqttMessagingQos.AT_LEAST_ONCE,
	},
	{
		title: i18n.t('components.configuration.daemon.connections.mqtt.qosLevels.atLeastOnce').toString(),
		value: IqrfGatewayDaemonMqttMessagingQos.AT_MOST_ONCE,
	},
	{
		title: i18n.t('components.configuration.daemon.connections.mqtt.qosLevels.exactlyOnce').toString(),
		value: IqrfGatewayDaemonMqttMessagingQos.ONCE,
	},
];
const qosDescription = computed(() => {
	if (profile.value.Qos === IqrfGatewayDaemonMqttMessagingQos.AT_MOST_ONCE) {
		return i18n.t('components.configuration.daemon.connections.mqtt.messages.qos.atMostOnce').toString();
	} else if (profile.value.Qos === IqrfGatewayDaemonMqttMessagingQos.AT_LEAST_ONCE) {
		return i18n.t('components.configuration.daemon.connections.mqtt.messages.qos.atMostOnce').toString();
	}
	return i18n.t('components.configuration.daemon.connections.mqtt.messages.qos.exactlyOnce').toString();
});
const persistenceOptions = [
	{
		title: i18n.t('components.configuration.daemon.connections.mqtt.persistenceLevels.memory').toString(),
		value: IqrfGatewayDaemonMqttMessagingPersistence.MEMORY,
	},
	{
		title: i18n.t('components.configuration.daemon.connections.mqtt.persistenceLevels.filesystem').toString(),
		value: IqrfGatewayDaemonMqttMessagingPersistence.FILESYSTEM,
	},
	{
		title: i18n.t('components.configuration.daemon.connections.mqtt.persistenceLevels.application').toString(),
		value: IqrfGatewayDaemonMqttMessagingPersistence.APPLICATION,
	},
];
const persistenceDescription = computed(() => {
	if (profile.value.Persistence === 0) {
		return i18n.t('components.configuration.daemon.connections.mqtt.messages.persistence.memory').toString();
	} else if (profile.value.Persistence === 1) {
		return i18n.t('components.configuration.daemon.connections.mqtt.messages.persistence.filesystem').toString();
	}
	return i18n.t('components.configuration.daemon.connections.mqtt.messages.persistence.application').toString();
});

const iconColor = computed(() => {
	if (componentProps.action === FormAction.Add) {
		return 'white';
	}
	return 'info';
});
const activatorIcon = computed(() => {
	if (componentProps.action === FormAction.Add) {
		return mdiPlus;
	}
	return mdiPencil;
});
const dialogTitle = computed(() => {
	if (componentProps.action === FormAction.Add) {
		return i18n.t('components.configuration.daemon.connections.actions.add').toString();
	}
	return i18n.t('components.configuration.daemon.connections.actions.edit').toString();
});

watchEffect(async(): Promise<void> => {
	if (componentProps.action === FormAction.Edit && componentProps.connectionProfile) {
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
	if (componentProps.action === FormAction.Add) {
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
		i18n.t('components.configuration.daemon.connections.udp.messages.save.success', { name: name }),
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
