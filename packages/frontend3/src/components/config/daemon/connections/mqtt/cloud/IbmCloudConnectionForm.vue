<template>
	<v-dialog
		:model-value='show'
		scrollable
		persistent
		no-click-animation
		:width='width'
	>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			:disabled='componentState === ComponentState.Saving'
		>
			<Card>
				<template #title>
					{{ $t('components.configuration.daemon.connections.mqtt.clouds.ibm.title') }}
				</template>
				<TextInput
					v-model='config.organizationId'
					:label='$t("components.configuration.daemon.connections.mqtt.clouds.ibm.orgId")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.configuration.daemon.connections.mqtt.clouds.ibm.validation.orgIdMissing")),
					]'
					required
				/>
				<TextInput
					v-model='config.deviceType'
					:label='$t("components.configuration.daemon.connections.mqtt.clouds.ibm.deviceType")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.configuration.daemon.connections.mqtt.clouds.ibm.validation.deviceTypeMissing")),
					]'
					required
				/>
				<TextInput
					v-model='config.deviceId'
					:label='$t("components.configuration.daemon.connections.mqtt.clouds.ibm.deviceId")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.configuration.daemon.connections.mqtt.clouds.ibm.validation.deviceIdMissing")),
					]'
					required
				/>
				<TextInput
					v-model='config.token'
					:label='$t("components.configuration.daemon.connections.mqtt.clouds.ibm.token")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.configuration.daemon.connections.mqtt.clouds.ibm.validation.tokenMissing")),
					]'
					required
				/>
				<TextInput
					v-model='config.eventId'
					:label='$t("components.configuration.daemon.connections.mqtt.clouds.ibm.eventId")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.configuration.daemon.connections.mqtt.clouds.ibm.validation.eventIdMissing")),
					]'
					required
				/>
				<template #actions>
					<v-btn
						color='primary'
						variant='elevated'
						:disabled='!isValid.value || componentState === ComponentState.Saving'
						@click='onSubmit'
					>
						{{ $t('common.buttons.save') }}
					</v-btn>
					<v-spacer />
					<v-btn
						color='grey-darken-2'
						variant='elevated'
						:disabled='componentState === ComponentState.Saving'
						@click='close'
					>
						{{ $t('common.buttons.close') }}
					</v-btn>
				</template>
			</Card>
		</v-form>
	</v-dialog>
</template>

<script lang='ts' setup>

import { type IbmService } from '@iqrf/iqrf-gateway-webapp-client/services/Cloud';
import { type IbmCloudConfig } from '@iqrf/iqrf-gateway-webapp-client/types/Cloud';
import { type Ref, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import TextInput from '@/components/TextInput.vue';
import { getModalWidth } from '@/helpers/modal';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

defineProps({
	show: {
		type: Boolean,
		required: false,
		default: false,
	},
});
const emit = defineEmits(['saved', 'close']);
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const width = getModalWidth();
const i18n = useI18n();
const service: IbmService = useApiClient().getCloudServices().getIbmService();
const form: Ref<typeof VForm | null> = ref(null);
const defaultConfig: IbmCloudConfig = {
	deviceId: '',
	deviceType: '',
	eventId: '',
	organizationId: '',
	token: '',
};
const config: Ref<IbmCloudConfig> = ref({...defaultConfig});

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	const params = {...config.value};
	service.createMqttInstance(params)
		.then(() => {
			toast.success(
				i18n.t('components.configuration.daemon.connections.mqtt.clouds.messages.save.success'),
			);
			emit('saved');
			clear();
		})
		.catch(() => toast.error('TODO ERROR HANDLING'));
}

function clear(): void {
	config.value = {...defaultConfig};
}

function close(): void {
	emit('close');
	clear();
}
</script>
