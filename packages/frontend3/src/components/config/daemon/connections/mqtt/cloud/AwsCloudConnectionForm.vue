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
					{{ $t('components.configuration.daemon.connections.mqtt.clouds.aws.title') }}
				</template>
				<TextInput
					v-model='config.endpoint'
					:label='$t("components.configuration.daemon.connections.mqtt.clouds.aws.endpoint")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.configuration.daemon.connections.mqtt.clouds.aws.validation.endpointMissing")),
					]'
					required
				/>
				<v-file-input
					v-model='config.certificate'
					accept='.pem'
					:label='$t("components.configuration.daemon.connections.mqtt.clouds.aws.certificate")'
					:rules='[
						(v: File|Blob|null) => ValidationRules.required(v, $t("components.configuration.daemon.connections.mqtt.clouds.aws.validation.certificateMissing")),
					]'
					:prepend-inner-icon='mdiFileOutline'
					:prepend-icon='null'
					show-size
					required
				/>
				<v-file-input
					v-model='config.privateKey'
					accept='.pem,.key'
					:label='$t("components.configuration.daemon.connections.mqtt.clouds.aws.privateKey")'
					:rules='[
						(v: File|Blob|null) => ValidationRules.required(v, $t("components.configuration.daemon.connections.mqtt.clouds.aws.validation.privateKeyMissing")),
					]'
					:prepend-inner-icon='mdiFileOutline'
					:prepend-icon='null'
					show-size
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

import { type AwsService } from '@iqrf/iqrf-gateway-webapp-client/services/Cloud';
import { type AwsMqttConfig } from '@iqrf/iqrf-gateway-webapp-client/types/Cloud';
import { mdiFileOutline } from '@mdi/js';
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
const service: AwsService = useApiClient().getCloudServices().getAwsService();
const form: Ref<typeof VForm | null> = ref(null);
const defaultConfig: AwsMqttConfig = {
	endpoint: '',
	certificate: null,
	privateKey: null,
};
const config: Ref<AwsMqttConfig> = ref({...defaultConfig});

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	const params = {
		...config.value,
		certificate: (config.value.certificate as unknown as File[]|Blob[])[0],
		privateKey: (config.value.privateKey as unknown as File[]|Blob[])[0],
	};
	console.warn(params);
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