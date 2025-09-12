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
		<v-form
			ref='form'
			v-slot='{ isValid }'
			:disabled='componentState === ComponentState.Saving'
		>
			<Card>
				<template #title>
					{{ $t('components.config.daemon.connections.mqtt.clouds.ibm.title') }}
				</template>
				<TextInput
					v-model='config.organizationId'
					:label='$t("components.config.daemon.connections.mqtt.clouds.ibm.orgId")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.connections.mqtt.clouds.ibm.validation.orgIdMissing")),
					]'
					required
				/>
				<TextInput
					v-model='config.deviceType'
					:label='$t("components.config.daemon.connections.mqtt.clouds.ibm.deviceType")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.connections.mqtt.clouds.ibm.validation.deviceTypeMissing")),
					]'
					required
				/>
				<TextInput
					v-model='config.deviceId'
					:label='$t("components.config.daemon.connections.mqtt.clouds.ibm.deviceId")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.connections.mqtt.clouds.ibm.validation.deviceIdMissing")),
					]'
					required
				/>
				<TextInput
					v-model='config.token'
					:label='$t("components.config.daemon.connections.mqtt.clouds.ibm.token")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.connections.mqtt.clouds.ibm.validation.tokenMissing")),
					]'
					required
				/>
				<TextInput
					v-model='config.eventId'
					:label='$t("components.config.daemon.connections.mqtt.clouds.ibm.eventId")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.connections.mqtt.clouds.ibm.validation.eventIdMissing")),
					]'
					required
				/>
				<template #actions>
					<v-btn
						color='primary'
						variant='elevated'
						:disabled='!isValid.value || componentState === ComponentState.Saving'
						@click='onSubmit()'
					>
						{{ $t('common.buttons.save') }}
					</v-btn>
					<v-spacer />
					<v-btn
						color='grey-darken-2'
						variant='elevated'
						:disabled='componentState === ComponentState.Saving'
						@click='close()'
					>
						{{ $t('common.buttons.close') }}
					</v-btn>
				</template>
			</Card>
		</v-form>
	</ModalWindow>
</template>

<script lang='ts' setup>
import { type IbmService } from '@iqrf/iqrf-gateway-webapp-client/services/Cloud';
import { type IbmCloudConfig } from '@iqrf/iqrf-gateway-webapp-client/types/Cloud';
import { ValidationRules } from '@iqrf/iqrf-vue-ui';
import { ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';

import Card from '@/components/layout/card/Card.vue';
import TextInput from '@/components/layout/form/TextInput.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const show = defineModel({
	required: true,
	type: Boolean,
});
const emit = defineEmits(['saved']);
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const service: IbmService = useApiClient().getCloudServices().getIbmService();
const form: Ref<VForm | null> = ref(null);
const defaultConfig: IbmCloudConfig = {
	deviceId: '',
	deviceType: '',
	eventId: '',
	organizationId: '',
	token: '',
};
const config: Ref<IbmCloudConfig> = ref({ ...defaultConfig });

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
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
		toast.error('TODO ERROR HANDLING');
	}
}

function clear(): void {
	config.value = { ...defaultConfig };
}

function close(): void {
	show.value = false;
	clear();
}
</script>
