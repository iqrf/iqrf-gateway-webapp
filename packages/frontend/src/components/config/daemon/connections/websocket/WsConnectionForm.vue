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
					v-model='messagingConfig.instance'
					:label='$t("components.config.daemon.connections.profile")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.connections.validation.profile.required")),
					]'
					required
				/>
				<INumberInput
					v-model='serviceConfig.WebsocketPort'
					:label='$t("common.labels.port")'
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
					v-model='messagingConfig.acceptAsyncMsg'
					:label='$t("components.config.daemon.connections.websocket.asyncMessages")'
					density='compact'
					hide-details
				/>
				<v-checkbox
					v-model='serviceConfig.acceptOnlyLocalhost'
					:label='$t("components.config.daemon.monitoring.onlyLocalhost")'
					density='compact'
					hide-details
				/>
				<v-checkbox
					v-model='serviceConfig.tlsEnabled'
					:label='$t("components.config.daemon.connections.websocket.tlsEnabled")'
					density='compact'
					:hide-details='!serviceConfig.tlsEnabled'
				/>
				<div v-if='serviceConfig.tlsEnabled'>
					<ISelectInput
						v-model='serviceConfig.tlsMode'
						:label='$t("components.config.daemon.connections.websocket.tlsMode")'
						:items='tlsModeOptions'
						:description='$t(`components.config.daemon.connections.websocket.notes.tlsModes.${serviceConfig.tlsMode}`)'
					/>
					<ITextInput
						v-model='serviceConfig.certificate'
						:label='$t("components.config.daemon.connections.websocket.certificate")'
						:rules='[
							(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.connections.websocket.validation.certificate.required")),
						]'
						required
					/>
					<ITextInput
						v-model='serviceConfig.privateKey'
						:label='$t("components.config.daemon.connections.websocket.privateKey")'
						:rules='[
							(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.connections.websocket.validation.privateKey.required")),
						]'
						required
					/>
				</div>
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
import { IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { IqrfGatewayDaemonComponentName, IqrfGatewayDaemonWsMessaging, ShapeWebsocketService } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IDataTableAction,
	IModalWindow,
	INumberInput,
	ISelectInput,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { computed, PropType, ref, Ref, toRaw, watch } from 'vue';
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

const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const i18n = useI18n();
const daemonService: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const emit = defineEmits(['saved']);
const componentProps = defineProps({
	action: {
		type: String as PropType<Action>,
		default: Action.Add,
		required: false,
	},
	messagingInstance: {
		type: Object as PropType<IqrfGatewayDaemonWsMessaging>,
		required: false,
		default: undefined,
	},
	serviceInstance: {
		type: Object as PropType<ShapeWebsocketService>,
		required: false,
		default: undefined,
	},
	disabled: {
		type: Boolean,
		required: false,
		default: false,
	},
});
const show: Ref<boolean> = ref(false);
let fromImport = false;
const form: Ref<VForm | null> = ref(null);
const defaultMessaging: IqrfGatewayDaemonWsMessaging = {
	component:  IqrfGatewayDaemonComponentName.IqrfWsMessaging,
	instance: '',
	acceptAsyncMsg: false,
	RequiredInterfaces: [],
};
const defaultService: ShapeWebsocketService = {
	component: IqrfGatewayDaemonComponentName.ShapeWebsocketService,
	instance: '',
	acceptOnlyLocalhost: false,
	WebsocketPort: 1_338,
	certificate: '',
	privateKey: '',
	tlsEnabled: false,
	tlsMode: TLSModes.Intermediate,
};
let previousInstance = '';
const messagingConfig: Ref<IqrfGatewayDaemonWsMessaging> = ref(structuredClone(defaultMessaging));
const serviceConfig: Ref<ShapeWebsocketService> = ref(structuredClone(defaultService));
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

const dialogTitle = computed(() => {
	if (componentProps.action === Action.Add) {
		return i18n.t('components.config.daemon.connections.actions.add').toString();
	}
	return i18n.t('components.config.daemon.connections.actions.edit').toString();
});

watch(show, (newVal: boolean): void => {
	if (!newVal || fromImport) {
		return;
	}
	if (componentProps.action === Action.Edit && componentProps.messagingInstance && componentProps.serviceInstance) {
		messagingConfig.value = structuredClone(componentProps.messagingInstance);
		serviceConfig.value = structuredClone(componentProps.serviceInstance);
		previousInstance = componentProps.messagingInstance.instance;
	} else {
		messagingConfig.value = structuredClone(defaultMessaging);
		serviceConfig.value = structuredClone(defaultService);
		previousInstance = defaultMessaging.instance;
	}
});

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Action;
	const messagingParams = structuredClone(toRaw(messagingConfig.value));
	const serviceParams = structuredClone(toRaw(serviceConfig.value));
	serviceParams.instance = messagingParams.instance;
	if (messagingParams.RequiredInterfaces.length === 0) {
		messagingParams.RequiredInterfaces.push({
			name: IqrfGatewayDaemonComponentName.ShapeWebsocketService,
			target: {
				instance: serviceParams.instance,
			},
		});
	} else {
		messagingParams.RequiredInterfaces[0].target.instance = serviceParams.instance;
	}
	try {
		if (componentProps.action === Action.Add) {
			await daemonService.createInstance(IqrfGatewayDaemonComponentName.ShapeWebsocketService, serviceParams);
			await daemonService.createInstance(IqrfGatewayDaemonComponentName.IqrfWsMessaging, messagingParams);
		} else {
			await daemonService.updateInstance(IqrfGatewayDaemonComponentName.ShapeWebsocketService, previousInstance, serviceParams);
			await daemonService.updateInstance(IqrfGatewayDaemonComponentName.IqrfWsMessaging, previousInstance, messagingParams);
		}
		toast.success(
			i18n.t('components.config.daemon.connections.websocket.messages.save.success', { name: previousInstance }),
		);
		close();
		emit('saved');
	} catch {
		toast.error(
			i18n.t('components.config.daemon.connections.websocket.messages.save.failed', { name: previousInstance }),
		);
	}
	componentState.value = ComponentState.Idle;
}

function importFromConfig(messaging: IqrfGatewayDaemonWsMessaging, service: ShapeWebsocketService): void {
	messagingConfig.value = structuredClone(messaging);
	serviceConfig.value = structuredClone(service);
	fromImport = true;
	show.value = true;
}

defineExpose({
	importFromConfig,
});

function close(): void {
	show.value = false;
	fromImport = false;
}
</script>
