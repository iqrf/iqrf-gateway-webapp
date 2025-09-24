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
	<IModalWindow v-model='show'>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			:disabled='componentState === ComponentState.Action'
			@submit.prevent='onSubmit()'
		>
			<ICard :action='Action.Add'>
				<template #title>
					{{ $t('components.config.daemon.connections.mqtt.clouds.azure.title') }}
				</template>
				<ITextInput
					v-model='config.connectionString'
					:label='$t("components.config.daemon.connections.mqtt.clouds.azure.connectionString")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.connections.mqtt.clouds.azure.validation.connectionString.required")),
					]'
					:prepend-inner-icon='mdiConnection'
					required
				/>
				<template #actions>
					<IActionBtn
						:action='Action.Add'
						:loading='componentState === ComponentState.Action'
						:disabled='!isValid.value'
						type='submit'
					/>
					<v-spacer />
					<IActionBtn
						:action='Action.Close'
						:disabled='componentState === ComponentState.Action'
						@click='close()'
					/>
				</template>
			</ICard>
		</v-form>
	</IModalWindow>
</template>

<script lang='ts' setup>
import { type AzureService } from '@iqrf/iqrf-gateway-webapp-client/services/Cloud';
import { type AzureIotHubConfig } from '@iqrf/iqrf-gateway-webapp-client/types/Cloud';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IModalWindow,
	ITextInput,
	ValidationRules } from '@iqrf/iqrf-vue-ui';
import { mdiConnection } from '@mdi/js';
import { ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';

import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

const show = defineModel({
	required: true,
	type: Boolean,
});
const emit = defineEmits(['saved']);
const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const i18n = useI18n();
const service: AzureService = useApiClient().getCloudServices().getAzureService();
const form: Ref<VForm | null> = ref(null);
const defaultConfig: AzureIotHubConfig = {
	connectionString: '',
};
const config: Ref<AzureIotHubConfig> = ref({ ...defaultConfig });

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Action;
	const params = { ...config.value };
	try {
		await service.createMqttInstance(params);
		toast.success(
			i18n.t('components.config.daemon.connections.mqtt.clouds.messages.save.success'),
		);
		show.value = false;
		emit('saved');
		clear();
	} catch {
		toast.error(
			i18n.t('components.config.daemon.connections.mqtt.clouds.messages.save.failed'),
		);
	}
	componentState.value = ComponentState.Idle;
}

function clear(): void {
	config.value = { ...defaultConfig };
}

function close(): void {
	show.value = false;
	clear();
}
</script>
