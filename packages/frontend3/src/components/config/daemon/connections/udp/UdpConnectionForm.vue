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
						(v: string|null) => ValidationRules.required(v, $t("components.configuration.daemon.connections.validation.profileMissing")),
					]'
					required
				/>
				<TextInput
					v-model.number='profile.LocalPort'
					type='number'
					:label='$t("components.configuration.daemon.connections.udp.localPort")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.configuration.daemon.connections.udp.validation.localPortMissing")),
						(v: number) => ValidationRules.integer(v, $t("components.configuration.daemon.connections.udp.validation.localPortInvalid")),
						(v: number) => ValidationRules.between(v, 1, 65535, $t("components.configuration.daemon.connections.udp.validation.localPortInvalid")),
					]'
					required
				/>
				<TextInput
					v-model.number='profile.RemotePort'
					type='number'
					:label='$t("components.configuration.daemon.connections.udp.remotePort")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.configuration.daemon.connections.udp.validation.remotePortMissing")),
						(v: number) => ValidationRules.integer(v, $t("components.configuration.daemon.connections.udp.validation.remotePortInvalid")),
						(v: number) => ValidationRules.between(v, 1, 65535, $t("components.configuration.daemon.connections.udp.validation.remotePortInvalid")),
					]'
					required
				/>
				<TextInput
					v-model.number='profile.deviceRecordExpiration'
					type='number'
					:label='$t("components.configuration.daemon.connections.udp.expiration")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.configuration.daemon.connections.udp.validation.expirationMissing")),
						(v: number) => ValidationRules.integer(v, $t("components.configuration.daemon.connections.udp.validation.expirationInvalid")),
						(v: number) => ValidationRules.min(v, 0, $t("components.configuration.daemon.connections.udp.validation.expirationInvalid")),
					]'
					required
				/>
				<template #actions>
					<v-btn
						color='primary'
						type='submit'
						variant='elevated'
						:disabled='!isValid.value || componentState === ComponentState.Saving'
					>
						{{ $t(`common.buttons.${action}`) }}
					</v-btn>
					<v-spacer />
					<v-btn
						color='grey-darken-2'
						variant='elevated'
						:disabled='componentState === ComponentState.Saving'
						@click='close'
					>
						{{ $t('common.buttons.cancel') }}
					</v-btn>
				</template>
			</Card>
		</v-form>
	</ModalWindow>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { IqrfGatewayDaemonComponentName, type IqrfGatewayDaemonUdpMessaging } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { mdiPencil, mdiPlus } from '@mdi/js';
import { computed, type PropType, type Ref, ref , watchEffect } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import ModalWindow from '@/components/ModalWindow.vue';
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
		type: Object as PropType<IqrfGatewayDaemonUdpMessaging>,
		default: () => ({
			component: IqrfGatewayDaemonComponentName.IqrfUdpMessaging,
			instance: '',
			LocalPort: 55000,
			RemotePort: 55300,
			deviceRecordExpiration: 300,
		}),
		required: false,
	},
});
const i18n = useI18n();
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const show: Ref<boolean> = ref(false);
const form: Ref<typeof VForm | null> = ref(null);
const defaultProfile: IqrfGatewayDaemonUdpMessaging = {
	component: IqrfGatewayDaemonComponentName.IqrfUdpMessaging,
	instance: '',
	LocalPort: 55000,
	RemotePort: 55300,
	deviceRecordExpiration: 300,
};
const profile: Ref<IqrfGatewayDaemonUdpMessaging> = ref({...defaultProfile});
let instance = '';
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
		profile.value = {...componentProps.connectionProfile};
		instance = componentProps.connectionProfile.instance;
	} else {
		profile.value = {...defaultProfile};
		instance = defaultProfile.instance;
	}
	componentState.value = ComponentState.Ready;
});

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Saving;
	const params = {...profile.value};
	if (componentProps.action === FormAction.Add) {
		service.createInstance(IqrfGatewayDaemonComponentName.IqrfUdpMessaging, params)
			.then(() => handleSuccess(instance))
			.catch(handleError);
	} else {
		service.updateInstance(IqrfGatewayDaemonComponentName.IqrfUdpMessaging, instance, params)
			.then(() => handleSuccess(instance))
			.catch(handleError);
	}
}

function handleSuccess(name: string): void {
	componentState.value = ComponentState.Ready;
	toast.success(
		i18n.t('components.configuration.daemon.connections.udp.messages.save.success', {name: name}),
	);
	close();
	emit('saved');
}

function handleError(): void {
	componentState.value = ComponentState.Ready;
	toast.error('TODO ERROR HANDLING');
}

function importFromConfig(config: IqrfGatewayDaemonUdpMessaging): void {
	profile.value = {...config};
	show.value = true;
}

defineExpose({
	importFromConfig,
});

function close(): void {
	show.value = false;
	profile.value = {...defaultProfile};
}
</script>
