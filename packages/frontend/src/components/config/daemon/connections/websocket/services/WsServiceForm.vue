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
				:tooltip='$t("components.config.daemon.connections.websocket.service.actions.add")'
				:disabled='disabled'
			/>
			<IDataTableAction
				v-if='action === Action.Edit'
				v-bind='props'
				:action='action'
				:tooltip='$t("components.config.daemon.connections.websocket.service.actions.edit")'
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
					v-model='serviceConfig.instance'
					:label='$t("components.config.daemon.connections.websocket.service.name")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.connections.websocket.service.validation.name.required")),
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
					v-model='serviceConfig.acceptOnlyLocalhost'
					:label='$t("components.config.daemon.connections.websocket.acceptOnlyLocalhost")'
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
					<WsTlsModeInput
						v-model='serviceConfig.tlsMode'
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
import {
	IqrfGatewayDaemonComponentName,
	type ShapeWebsocketService,
	ShapeWebsocketTlsMode,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IDataTableAction,
	IModalWindow,
	INumberInput,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import {
	computed,
	PropType,
	ref,
	Ref,
	TemplateRef,
	toRaw,
	useTemplateRef,
	watch,
} from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import WsTlsModeInput
	from '@/components/config/daemon/connections/websocket/WsTlsModeInput.vue';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

const componentProps = defineProps({
	action: {
		type: String as PropType<Action>,
		default: Action.Add,
		required: false,
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
const emit = defineEmits<{
	saved: [];
}>();
const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const i18n = useI18n();
const daemonService: IqrfGatewayDaemonService = useApiClient()
	.getConfigServices()
	.getIqrfGatewayDaemonService();
const show: Ref<boolean> = ref(false);
let fromImport = false;
const form: TemplateRef<VForm> = useTemplateRef('form');
const defaultService: ShapeWebsocketService = {
	component: IqrfGatewayDaemonComponentName.ShapeWebsocketService,
	instance: '',
	acceptOnlyLocalhost: false,
	WebsocketPort: 1_338,
	certificate: '',
	privateKey: '',
	tlsEnabled: false,
	tlsMode: ShapeWebsocketTlsMode.Intermediate,
};
let previousInstance = '';
const serviceConfig: Ref<ShapeWebsocketService> = ref(structuredClone(defaultService));

const dialogTitle = computed(() => {
	if (componentProps.action === Action.Add) {
		return i18n.t('components.config.daemon.connections.websocket.service.actions.add').toString();
	}
	return i18n.t('components.config.daemon.connections.websocket.service.actions.edit').toString();
});

watch(show, (newVal: boolean): void => {
	if (!newVal || fromImport) {
		return;
	}
	if (componentProps.action === Action.Edit && componentProps.serviceInstance) {
		serviceConfig.value = structuredClone(componentProps.serviceInstance);
		previousInstance = componentProps.serviceInstance.instance;
	} else {
		serviceConfig.value = structuredClone(defaultService);
		previousInstance = defaultService.instance;
	}
});

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Action;
	const serviceParams = structuredClone(toRaw(serviceConfig.value));
	const translationParams = { name: componentProps.action === Action.Add ? serviceParams.instance : previousInstance };
	try {
		if (componentProps.action === Action.Add) {
			await daemonService.createInstance(IqrfGatewayDaemonComponentName.ShapeWebsocketService, serviceParams);
		} else {
			await daemonService.updateInstance(IqrfGatewayDaemonComponentName.ShapeWebsocketService, previousInstance, serviceParams);
		}
		toast.success(
			i18n.t('components.config.daemon.connections.websocket.service.messages.save.success', translationParams),
		);
		close();
		emit('saved');
	} catch {
		toast.error(
			i18n.t('components.config.daemon.connections.websocket.service.messages.save.failed', translationParams),
		);
	}
	componentState.value = ComponentState.Idle;
}

function importFromConfig(service: ShapeWebsocketService): void {
	serviceConfig.value = structuredClone(service);
	fromImport = true;
	show.value = true;
}

function close(): void {
	show.value = false;
	fromImport = false;
}

defineExpose({
	importFromConfig,
});
</script>
