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
					{{ $t('components.configuration.daemon.connections.mqtt.clouds.azure.title') }}
				</template>
				<TextInput
					v-model='config.connectionString'
					:label='$t("components.configuration.daemon.connections.mqtt.clouds.azure.connectionString")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.configuration.daemon.connections.mqtt.clouds.azure.validation.connectionStringMissing")),
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

import { type AzureService } from '@iqrf/iqrf-gateway-webapp-client/services/Cloud';
import { type AzureIotHubConfig } from '@iqrf/iqrf-gateway-webapp-client/types/Cloud';
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
const service: AzureService = useApiClient().getCloudServices().getAzureService();
const form: Ref<typeof VForm | null> = ref(null);
const defaultConfig: AzureIotHubConfig = {
	connectionString: '',
};
const config: Ref<AzureIotHubConfig> = ref({...defaultConfig});

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
