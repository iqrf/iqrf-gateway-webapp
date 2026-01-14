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
					{{ $t('components.config.daemon.connections.actions.import') }}
				</template>
				<v-file-input
					v-model='file'
					accept='.json'
					:label='$t("components.config.daemon.connections.profileFile")'
					:rules='[
						(v: File|null) => ValidationRules.required(v, $t("components.config.daemon.connections.validation.profileFile.required")),
						(v: File) => ValidationRules.fileExtension(v, ["json"], $t("components.config.daemon.connections.validation.profileFile.extension")),
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
	type ShapeWebsocketService,
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
import { ref, type Ref, type TemplateRef, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import { validateForm } from '@/helpers/validateForm';

withDefaults(
	defineProps<{
		/// Import dialog activator button disabled state
		disabled?: boolean;
	}>(),
	{
		disabled: false,
	},
);
const emit = defineEmits<{
	import: [messaging: IqrfGatewayDaemonWsMessaging, service: ShapeWebsocketService];
}>();
const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const i18n = useI18n();
const show: Ref<boolean> = ref(false);
const form: TemplateRef<VForm> = useTemplateRef('form');
const file: Ref<File | null> = ref(null);

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
	if (obj.messaging === undefined || !isWsMessaging(obj.messaging) || obj.service === undefined || !isWsService(obj.service)) {
		toast.error(
			i18n.t('components.config.daemon.connections.messages.profileFileInvalid'),
		);
		componentState.value = ComponentState.Idle;
		return;
	}
	componentState.value = ComponentState.Idle;
	emit('import', obj.messaging, obj.service);
	close();
}

function isWsMessaging(obj: any): obj is IqrfGatewayDaemonWsMessaging {
	return obj !== undefined && obj !== null && typeof obj === 'object' &&
		obj.component !== undefined && typeof obj.component === 'string' && obj.component === IqrfGatewayDaemonComponentName.IqrfWsMessaging &&
		obj.instance !== undefined && typeof obj.instance === 'string' &&
		obj.acceptAsyncMsg !== undefined && typeof obj.acceptAsyncMsg === 'boolean' &&
		obj.RequiredInterfaces !== undefined && Array.isArray(obj.RequiredInterfaces) && obj.RequiredInterfaces.length === 1 && typeof obj.RequiredInterfaces[0] === 'object' &&
		obj.RequiredInterfaces[0].name !== undefined && typeof obj.RequiredInterfaces[0].name === 'string' && obj.RequiredInterfaces[0].name === 'shape::IWebsocketService' &&
		obj.RequiredInterfaces[0].target !== undefined && typeof obj.RequiredInterfaces[0].target === 'object' &&
		obj.RequiredInterfaces[0].target.instance !== undefined && typeof obj.RequiredInterfaces[0].target.instance === 'string';
}

function isWsService(obj: any): obj is ShapeWebsocketService {
	return obj !== undefined && obj !== null && typeof obj === 'object' &&
		obj.component !== undefined && typeof obj.component === 'string' && obj.component === IqrfGatewayDaemonComponentName.ShapeWebsocketService &&
		obj.instance !== undefined && typeof obj.instance === 'string' &&
		obj.WebsocketPort !== undefined && typeof obj.WebsocketPort === 'number' &&
		obj.acceptOnlyLocalhost !== undefined && typeof obj.acceptOnlyLocalhost === 'boolean' &&
		(obj.certificate === undefined || typeof obj.certificate === 'string') &&
		(obj.privateKey === undefined || typeof obj.privateKey === 'string') &&
		(obj.tlsEnabled === undefined || typeof obj.tlsEnabled === 'boolean') &&
		(obj.tlsMode === undefined || typeof obj.tlsMode === 'string');
}

function close(): void {
	file.value = null;
	show.value = false;
}

</script>
