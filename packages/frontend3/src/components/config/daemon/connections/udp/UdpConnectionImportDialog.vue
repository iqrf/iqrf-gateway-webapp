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
	<ModalWindow v-model='show'>
		<template #activator='{ props }'>
			<CardTitleActionBtn
				v-bind='props'
				:action='Action.Import'
				:tooltip='$t("components.config.daemon.connections.actions.import")'
			/>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			:disabled='componentState === ComponentState.Saving'
			@submit.prevent='onSubmit'
		>
			<Card>
				<template #title>
					{{ $t('components.config.daemon.connections.udp.import.title') }}
				</template>
				<v-file-input
					v-model='files'
					accept='.json'
					:label='$t("components.config.daemon.connections.profileFile")'
					:rules='[
						(v: File|Blob|null) => ValidationRules.required(v, $t("components.config.daemon.connections.validation.profileFileMissing")),
					]'
					:prepend-inner-icon='mdiFileOutline'
					:prepend-icon='undefined'
					show-size
					required
				/>
				<template #actions>
					<CardActionBtn
						:action='Action.Import'
						:disabled='!isValid.value || componentState === ComponentState.Saving'
						type='submit'
					/>
					<v-spacer />
					<CardActionBtn
						:action='Action.Cancel'
						@click='close'
					/>
				</template>
			</Card>
		</v-form>
	</ModalWindow>
</template>

<script lang='ts' setup>
import { IqrfGatewayDaemonComponentName, type IqrfGatewayDaemonUdpMessaging } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { mdiFileOutline } from '@mdi/js';
import { ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import CardTitleActionBtn from '@/components/layout/card/CardTitleActionBtn.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { Action } from '@/types/Action';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Ready);
const emit = defineEmits(['import']);
const i18n = useI18n();
const show: Ref<boolean> = ref(false);
const form: Ref<VForm | null> = ref(null);
const files: Ref<File[]> = ref([]);

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || files.value.length === 0) {
		return;
	}
	componentState.value = ComponentState.Saving;
	const file = files.value[0];
	const content = await file.text();
	const obj = JSON.parse(content) as unknown;
	if (!isUdpConnectionProfile(obj)) {
		toast.error(
			i18n.t('components.config.daemon.connections.messages.profileFileInvalid'),
		);
		componentState.value = ComponentState.Ready;
		return;
	}
	componentState.value = ComponentState.Ready;
	emit('import', obj);
	close();
}

function isUdpConnectionProfile(obj: any): obj is IqrfGatewayDaemonUdpMessaging {
	return obj.component !== undefined && typeof obj.component === 'string' && obj.component === IqrfGatewayDaemonComponentName.IqrfUdpMessaging &&
		obj.instance !== undefined && typeof obj.instance === 'string' &&
		obj.LocalPort !== undefined && typeof obj.LocalPort === 'number' &&
		obj.RemotePort !== undefined && typeof obj.RemotePort === 'number' &&
		obj.deviceRecordExpiration !== undefined && typeof obj.deviceRecordExpiration === 'number';
}

function close(): void {
	files.value = [];
	show.value = false;
}

</script>
