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
			/>
			<IDataTableAction
				v-if='action === Action.Edit'
				v-bind='props'
				:action='Action.Edit'
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
				<NumberInput
					v-model.number='profile.LocalPort'
					:label='$t("components.config.daemon.connections.udp.localPort")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.connections.udp.validation.localPortMissing")),
						(v: number) => ValidationRules.integer(v, $t("components.config.daemon.connections.udp.validation.localPortInvalid")),
						(v: number) => ValidationRules.between(v, 1, 65535, $t("components.config.daemon.connections.udp.validation.localPortInvalid")),
					]'
					required
				/>
				<NumberInput
					v-model.number='profile.RemotePort'
					:label='$t("components.config.daemon.connections.udp.remotePort")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.connections.udp.validation.remotePortMissing")),
						(v: number) => ValidationRules.integer(v, $t("components.config.daemon.connections.udp.validation.remotePortInvalid")),
						(v: number) => ValidationRules.between(v, 1, 65535, $t("components.config.daemon.connections.udp.validation.remotePortInvalid")),
					]'
					required
				/>
				<NumberInput
					v-model.number='profile.deviceRecordExpiration'
					:label='$t("components.config.daemon.connections.udp.expiration")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.connections.udp.validation.expirationMissing")),
						(v: number) => ValidationRules.integer(v, $t("components.config.daemon.connections.udp.validation.expirationInvalid")),
						(v: number) => ValidationRules.min(v, 0, $t("components.config.daemon.connections.udp.validation.expirationInvalid")),
					]'
					required
				/>
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
import { IqrfGatewayDaemonComponentName, type IqrfGatewayDaemonUdpMessaging } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IDataTableAction,
	IModalWindow,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import {
	computed, type PropType, ref, type Ref,
	type TemplateRef, useTemplateRef, watchEffect,
} from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import NumberInput from '@/components/layout/form/NumberInput.vue';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

const componentProps = defineProps({
	action: {
		type: String as PropType<Action>,
		default: Action.Add,
		required: false,
	},
	connectionProfile: {
		type: Object as PropType<IqrfGatewayDaemonUdpMessaging>,
		default: () => ({
			component: IqrfGatewayDaemonComponentName.IqrfUdpMessaging,
			instance: '',
			LocalPort: 55_000,
			RemotePort: 55_300,
			deviceRecordExpiration: 300,
		}),
		required: false,
	},
});
const emit = defineEmits<{
	saved: [];
}>();
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const show: Ref<boolean> = ref(false);
const form: TemplateRef<VForm> = useTemplateRef('form');
const defaultProfile: IqrfGatewayDaemonUdpMessaging = {
	component: IqrfGatewayDaemonComponentName.IqrfUdpMessaging,
	instance: '',
	LocalPort: 55_000,
	RemotePort: 55_300,
	deviceRecordExpiration: 300,
};
const profile: Ref<IqrfGatewayDaemonUdpMessaging> = ref({ ...defaultProfile });
let instance = '';
const dialogTitle = computed(() => {
	if (componentProps.action === Action.Add) {
		return i18n.t('components.config.daemon.connections.actions.add');
	}
	return i18n.t('components.config.daemon.connections.actions.edit');
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
			await service.createInstance(IqrfGatewayDaemonComponentName.IqrfUdpMessaging, params);
		} else {
			await service.updateInstance(IqrfGatewayDaemonComponentName.IqrfUdpMessaging, instance, params);
		}
		toast.success(
			i18n.t('components.config.daemon.connections.udp.messages.save.success', { name: name }),
		);
		close();
		emit('saved');
	} catch {
		toast.error('TODO ERROR HANDLING');
	}
	componentState.value = ComponentState.Ready;
}

function importFromConfig(config: IqrfGatewayDaemonUdpMessaging): void {
	profile.value = { ...config };
	show.value = true;
}

function close(): void {
	show.value = false;
	profile.value = { ...defaultProfile };
}

defineExpose({
	importFromConfig,
});
</script>
