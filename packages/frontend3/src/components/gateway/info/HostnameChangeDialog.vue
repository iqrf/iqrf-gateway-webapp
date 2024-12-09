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
	<ModalWindow
		v-model='show'
	>
		<template #activator='{ props }'>
			<v-btn
				color='primary'
				size='x-small'
				v-bind='props'
			>
				<v-icon :icon='mdiPencil' />
				{{ $t('components.common.actions.edit') }}
			</v-btn>
		</template>
		<Card>
			<template #title>
				{{ $t('components.gateway.information.hostnameChange.title') }}
			</template>
			<v-form ref='form'>
				<TextInput
					v-model='hostname'
					:label='$t("components.gateway.information.hostname")'
					:prepend-inner-icon='mdiTextShort'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.gateway.information.hostnameChange.validation.hostname")),
					]'
					required
				/>
				<v-checkbox
					v-model='setIdeHostname'
					:label='$t("components.gateway.information.hostnameChange.setIdeHostname")'
				/>
			</v-form>
			<template #actions>
				<CardActionBtn
					:action='Action.Edit'
					@click='onSubmit'
				/>
				<v-spacer />
				<CardActionBtn
					:action='Action.Cancel'
					@click='close'
				/>
			</template>
		</Card>
	</ModalWindow>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { type HostnameService } from '@iqrf/iqrf-gateway-webapp-client/services/Gateway';
import {
	type IqrfGatewayDaemonComponent,
	IqrfGatewayDaemonComponentName,
	type IqrfGatewayDaemonIdeCounterpart,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { mdiPencil, mdiTextShort } from '@mdi/js';
import { type PropType, ref, type Ref, watchEffect } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import TextInput from '@/components/layout/form/TextInput.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { Action } from '@/types/Action';

const emit = defineEmits(['saved']);
const componentProps = defineProps({
	currentHostname: {
		type: [String, null] as PropType<string | null>,
		required: true,
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
	Promise.all([
		updateIdeCounterpartConfig(),
		hostnameService.updateHostname(hostname.value),
	])
		.then(() => {
			toast.success(
				i18n.t('components.gateway.information.hostnameChange.messages.save.success'),
			);
			if (setIdeHostname.value) {
				toast.success(
					i18n.t('components.gateway.information.hostnameChange.messages.daemonRestart'),
				);
			}
			clear();
			close();
			emit('saved');
		})
		.catch(() => {
			toast.error('TODO ERROR HANDLING');
		});
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

function clear(): void {
	setIdeHostname.value = false;
}

function close(): void {
	show.value = false;
}

</script>
