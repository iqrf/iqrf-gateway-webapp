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
				:tooltip='$t("components.config.daemon.connections.websocket.messaging.actions.add")'
				:disabled='disabled'
			/>
			<IDataTableAction
				v-if='action === Action.Edit'
				v-bind='props'
				:action='action'
				:tooltip='$t("components.config.daemon.connections.websocket.messaging.actions.edit")'
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
					:label='$t("components.config.daemon.connections.websocket.messaging.name")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.connections.websocket.messaging.validation.name.required")),
					]'
					required
				/>
				<v-checkbox
					v-model='messagingConfig.acceptAsyncMsg'
					:label='$t("components.config.daemon.connections.websocket.asyncMessages")'
					density='compact'
				/>
				<ISelectInput
					v-model='messagingConfig.RequiredInterfaces[0].target.instance'
					:label='$t("components.config.daemon.connections.websocket.messaging.service")'
					:items='serviceInstances'
					required
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
import { IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { IqrfGatewayDaemonComponentName, IqrfGatewayDaemonWsMessaging } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IDataTableAction,
	IModalWindow,
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

const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const i18n = useI18n();
const daemonService: IqrfGatewayDaemonService = useApiClient()
	.getConfigServices()
	.getIqrfGatewayDaemonService();
const emit = defineEmits<{
	saved: [];
}>();
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
	serviceInstances: {
		type: Array as PropType<string[]>,
		required: false,
		default: () => [],
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
	RequiredInterfaces: [
		{
			name: 'shape::IWebsocketService',
			target: {
				instance: '',
			},
		},
	],
};
let previousInstance = '';
const messagingConfig: Ref<IqrfGatewayDaemonWsMessaging> = ref(structuredClone(defaultMessaging));

const dialogTitle = computed(() => {
	if (componentProps.action === Action.Add) {
		return i18n.t('components.config.daemon.connections.websocket.messaging.actions.add').toString();
	}
	return i18n.t('components.config.daemon.connections.websocket.messaging.actions.edit').toString();
});

watch(show, (newVal: boolean): void => {
	if (!newVal || fromImport) {
		return;
	}
	if (componentProps.action === Action.Edit && componentProps.messagingInstance) {
		messagingConfig.value = structuredClone(componentProps.messagingInstance);
		previousInstance = componentProps.messagingInstance.instance;
	} else {
		messagingConfig.value = structuredClone(defaultMessaging);
		previousInstance = defaultMessaging.instance;
	}
});

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Action;
	const messagingParams = structuredClone(toRaw(messagingConfig.value));
	const translationParams = { name: componentProps.action === Action.Add ? messagingParams.instance : previousInstance };
	try {
		if (componentProps.action === Action.Add) {
			await daemonService.createInstance(IqrfGatewayDaemonComponentName.IqrfWsMessaging, messagingParams);
		} else {
			await daemonService.updateInstance(IqrfGatewayDaemonComponentName.IqrfWsMessaging, previousInstance, messagingParams);
		}
		toast.success(
			i18n.t('components.config.daemon.connections.websocket.messaging.messages.save.success', translationParams),
		);
		close();
		emit('saved');
	} catch {
		toast.error(
			i18n.t('components.config.daemon.connections.websocket.messaging.messages.save.failed', translationParams),
		);
	}
	componentState.value = ComponentState.Idle;
}

function importFromConfig(messaging: IqrfGatewayDaemonWsMessaging): void {
	messagingConfig.value = structuredClone(messaging);
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
