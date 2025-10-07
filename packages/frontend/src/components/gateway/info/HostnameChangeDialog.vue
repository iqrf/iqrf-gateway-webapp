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
				v-bind='props'
				:action='Action.Custom'
				color='primary'
				size='small'
				:icon='mdiPencil'
				:text='$t("components.common.actions.edit")'
				:disabled='disabled'
			/>
		</template>
		<v-form
			v-slot='{ isValid }'
			ref='form'
			:disabled='componentState === ComponentState.Action'
		>
			<ICard>
				<template #title>
					{{ $t('components.gateway.information.hostname.dialog.title') }}
				</template>
				<ITextInput
					v-model='hostname'
					:label='$t("components.gateway.information.hostname.label")'
					:prepend-inner-icon='mdiTextShort'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.gateway.information.hostname.dialog.validation.hostname.required")),
					]'
					required
				/>
				<v-checkbox
					v-model='setIdeHostname'
					:label='$t("components.gateway.information.hostname.dialog.setIdeHostname")'
					hide-details
				/>
				<template #actions>
					<IActionBtn
						:action='Action.Edit'
						:disabled='!isValid.value || componentState === ComponentState.Action'
						@click='onSubmit()'
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
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { type HostnameService } from '@iqrf/iqrf-gateway-webapp-client/services/Gateway';
import {
	type IqrfGatewayDaemonComponent,
	IqrfGatewayDaemonComponentName,
	type IqrfGatewayDaemonIdeCounterpart,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { Action, ComponentState, IActionBtn, ICard, IModalWindow, ITextInput, ValidationRules } from '@iqrf/iqrf-vue-ui';
import { mdiPencil, mdiTextShort } from '@mdi/js';
import { type PropType, ref, type Ref, watchEffect } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const emit = defineEmits(['saved']);
const componentProps = defineProps({
	currentHostname: {
		type: [String, null] as PropType<string | null>,
		required: true,
	},
	disabled: {
		type: Boolean,
		required: false,
		default: false,
	},
});
const i18n = useI18n();
const hostnameService: HostnameService = useApiClient().getGatewayServices().getHostnameService();
const daemonConfigurationService: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const show: Ref<boolean> = ref(false);
const form: Ref<VForm | null> = ref(null);
const hostname: Ref<string> = ref('');
const setIdeHostname: Ref<boolean> = ref(false);
const ideComponentComponent = IqrfGatewayDaemonComponentName.IqrfIdeCounterpart;

watchEffect((): void => {
	if (componentProps.currentHostname === null) {
		hostname.value = '';
	} else {
		hostname.value = componentProps.currentHostname.split('.', 1)[0] ?? '';
	}
});

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Action;
	try {
		await Promise.all([
			updateIdeCounterpartConfig(),
			hostnameService.updateHostname(hostname.value),
		]);
		toast.success(
			i18n.t('components.gateway.information.hostname.dialog.messages.save.success'),
		);
		if (setIdeHostname.value) {
			toast.success(
				i18n.t('components.gateway.information.hostname.dialog.messages.daemonRestart'),
			);
		}
		close();
		emit('saved');
	} catch {
		toast.error(
			i18n.t('components.gateway.information.hostname.dialog.messages.save.failed'),
		);
	}
	componentState.value = ComponentState.Idle;
}

async function updateIdeCounterpartConfig(): Promise<void> {
	if (!setIdeHostname.value) {
		return;
	}
	const instance: IqrfGatewayDaemonIdeCounterpart|null = await daemonConfigurationService.getComponent(ideComponentComponent)
		.then((response: IqrfGatewayDaemonComponent<IqrfGatewayDaemonComponentName.IqrfIdeCounterpart>): IqrfGatewayDaemonIdeCounterpart|null =>
			response.instances[0] ?? null);
	if (instance === null) {
		return;
	}
	instance.gwIdentName = hostname.value;
	await daemonConfigurationService.updateInstance(ideComponentComponent, instance.instance, instance);
}

function close(): void {
	setIdeHostname.value = false;
	show.value = false;
}

</script>
