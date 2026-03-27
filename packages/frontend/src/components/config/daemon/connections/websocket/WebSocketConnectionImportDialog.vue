<!--
Copyright 2017-2026 IQRF Tech s.r.o.
Copyright 2019-2026 MICRORISC s.r.o.

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
				:action='Action.Import'
				container-type='card-title'
				:tooltip='$t("components.config.daemon.connections.actions.import")'
				:disabled='disabled'
			/>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			:disabled='componentState === ComponentState.Action'
			@submit.prevent='onSubmit()'
		>
			<ICard>
				<template #title>
					{{ $t('components.config.daemon.connections.ws.import.title') }}
				</template>
				<v-file-input
					v-model='file'
					accept='.json'
					:label='$t("components.config.daemon.connections.profileFile")'
					:rules='[
						(v: File|null) => ValidationRules.required(
							v,
							$t("components.config.daemon.connections.validation.profileFile.required"),
						),
						(v: File) => ValidationRules.fileExtension(
							v,
							["json"],
							$t("components.config.daemon.connections.validation.profileFile.extension"),
						),
					]'
					:prepend-inner-icon='mdiFileOutline'
					prepend-icon=''
					show-size
					required
				/>
				<template #actions>
					<IActionBtn
						:action='Action.Import'
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
import {
	IqrfGatewayDaemonComponentName,
	type IqrfGatewayDaemonWsMessaging,
	IqrfGatewayDaemonWsTlsModes,
	IqrfGatewayDaemonWsTransportModes,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IModalWindow,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiFileOutline } from '@mdi/js';
import { ref, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import { validateForm } from '@/helpers/validateForm';

withDefaults(
	defineProps<{
		disabled?: boolean;
	}>(),
	{
		disabled: false,
	},
);
const emit = defineEmits<{
	import: [messaging: IqrfGatewayDaemonWsMessaging];
}>();
const componentState = ref<ComponentState>(ComponentState.Idle);
const i18n = useI18n();
const show = ref<boolean>(false);
const form = useTemplateRef<VForm>('form');
const file = ref<File | null>(null);

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || file.value === null) {
		return;
	}
	componentState.value = ComponentState.Action;
	const content = await file.value.text();
	let obj;
	try {
		obj = JSON.parse(content);
	} catch {
		toast.error(
			i18n.t('components.config.daemon.connections.validation.profileFile.json'),
		);
		componentState.value = ComponentState.Idle;
		return;
	}
	if (!isWsConnectionProfile(obj)) {
		toast.error(
			i18n.t('components.config.daemon.connections.messages.profileFileInvalid'),
		);
		componentState.value = ComponentState.Idle;
		return;
	}
	componentState.value = ComponentState.Idle;
	emit('import', obj);
	close();
}

function isWsConnectionProfile(obj: any): obj is IqrfGatewayDaemonWsMessaging {
	return obj.component !== undefined && typeof obj.component === 'string' && obj.component === IqrfGatewayDaemonComponentName.IqrfWsMessaging &&
		obj.instance !== undefined && typeof obj.instance === 'string' &&
		obj.port !== undefined && typeof obj.port === 'number' &&
		obj.acceptAsyncMsg !== undefined && typeof obj.acceptAsyncMsg === 'boolean' &&
		obj.acceptOnlyLocalhost !== undefined && typeof obj.acceptOnlyLocalhost === 'boolean' &&
		obj.transportMode !== undefined && Object.values(IqrfGatewayDaemonWsTransportModes).includes(obj.transportMode) &&
		obj.tlsMode !== undefined && Object.values(IqrfGatewayDaemonWsTlsModes).includes(obj.tlsMode) &&
		obj.tlsPort !== undefined && typeof obj.tlsPort === 'number' &&
		obj.cert !== undefined && typeof obj.cert === 'string' &&
		obj.privKey !== undefined && typeof obj.privKey === 'string' &&
		obj.authTimeout !== undefined && typeof obj.authTimeout === 'number' &&
		obj.maxClients !== undefined && typeof obj.maxClients === 'number';
}

function close(): void {
	file.value = null;
	show.value = false;
}

</script>
