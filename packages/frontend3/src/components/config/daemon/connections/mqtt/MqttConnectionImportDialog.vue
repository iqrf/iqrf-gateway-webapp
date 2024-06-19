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
			<CardTitleActionBtn
				v-bind='props'
				:action='Action.Import'
				:tooltip='$t("components.configuration.daemon.connections.actions.import")'
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
					{{ $t('components.configuration.daemon.connections.mqtt.import.title') }}
				</template>
				<v-file-input
					v-model='files'
					accept='.json'
					:label='$t("components.configuration.daemon.connections.profileFile")'
					:rules='[
						(v: File|Blob|null) => ValidationRules.required(v, $t("components.configuration.daemon.connections.validation.profileFileMissing")),
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
import {
	IqrfGatewayDaemonComponentName,
	type IqrfGatewayDaemonMqttMessaging,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
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
	if (!isMqttConnectionProfile(obj)) {
		toast.error(
			i18n.t('components.configuration.daemon.connections.messages.profileFileInvalid'),
		);
		componentState.value = ComponentState.Ready;
		return;
	}
	componentState.value = ComponentState.Ready;
	emit('import', obj);
	close();
}

function isMqttConnectionProfile(obj: any): obj is IqrfGatewayDaemonMqttMessaging {
	return obj.component !== undefined && typeof obj.component === 'string' && obj.component === IqrfGatewayDaemonComponentName.IqrfMqttMessaging &&
		obj.instance !== undefined && typeof obj.instance === 'string' &&
		obj.BrokerAddr !== undefined && typeof obj.BrokerAddr === 'string' &&
		obj.ClientId !== undefined && typeof obj.ClientId === 'string' &&
		obj.ConnectTimeout !== undefined && typeof obj.ConnectTimeout === 'number' &&
		obj.EnableServerCertAuth !== undefined && typeof obj.EnableServerCertAuth === 'boolean' &&
		obj.EnabledCipherSuites !== undefined && typeof obj.EnabledCipherSuites === 'string' &&
		obj.EnabledSSL !== undefined && typeof obj.EnabledSSL === 'boolean' &&
		obj.KeepAliveInterval !== undefined && typeof obj.KeepAliveInterval === 'number' &&
		obj.KeyStore !== undefined && typeof obj.KeyStore === 'string' &&
		obj.MaxReconnect !== undefined && typeof obj.MaxReconnect === 'number' &&
		obj.MinReconnect !== undefined && typeof obj.MinReconnect === 'number' &&
		obj.Password !== undefined && typeof obj.Password === 'string' &&
		obj.Persistence !== undefined && typeof obj.Persistence === 'number' &&
		obj.PrivateKey !== undefined && typeof obj.PrivateKey === 'string' &&
		obj.PrivateKeyPassword !== undefined && typeof obj.PrivateKeyPassword === 'string' &&
		obj.Qos !== undefined && typeof obj.Qos === 'number' &&
		obj.TopicRequest !== undefined && typeof obj.TopicRequest === 'string' &&
		obj.TopicResponse !== undefined && typeof obj.TopicResponse === 'string' &&
		obj.TrustStore !== undefined && typeof obj.TrustStore === 'string' &&
		obj.User !== undefined && typeof obj.User === 'string' &&
		obj.acceptAsyncMsg !== undefined && typeof obj.acceptAsyncMsg === 'boolean';
}

function close(): void {
	files.value = [];
	show.value = false;
}

</script>
