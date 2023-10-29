<template>
	<v-dialog
		v-model='show'
		scrollable
		persistent
		no-click-animation
		:width='width'
	>
		<template #activator='{ props }'>
			<v-btn
				v-if='action === FormAction.Add'
				v-bind='props'
				:color='iconColor'
				:icon='activatorIcon'
			/>
			<v-icon
				v-else
				v-bind='props'
				:color='iconColor'
				:icon='activatorIcon'
				size='large'
				class='me-2'
			/>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			validate-on='input'
			@submit.prevent='onSubmit'
		>
			<Card>
				<template #title>
					{{ dialogTitle }}
				</template>
				<TextInput
					v-model='profile.name'
					:label='$t("components.configuration.controller.profiles.name")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.configuration.controller.profiles.validation.nameMissing")),
					]'
					required
				/>
				<SelectInput
					v-model='profile.deviceType'
					:items='typeOptions'
					:label='$t("components.configuration.controller.profiles.type")'
					required
				/>
				<TextInput
					v-model.number='profile.button'
					type='number'
					:label='$t("components.configuration.controller.form.pins.button")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.configuration.controller.validation.buttonPin")),
						(v: number) => ValidationRules.integer(v, $t("components.configuration.controller.validation.buttonPin")),
					]'
					required
				/>
				<TextInput
					v-model.number='profile.greenLed'
					type='number'
					:label='$t("components.configuration.controller.form.pins.greenLed")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.configuration.controller.validation.greenLed")),
						(v: number) => ValidationRules.integer(v, $t("components.configuration.controller.validation.greenLed")),
					]'
					required
				/>
				<TextInput
					v-model.number='profile.redLed'
					type='number'
					:label='$t("components.configuration.controller.form.pins.redLed")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.configuration.controller.validation.redLed")),
						(v: number) => ValidationRules.integer(v, $t("components.configuration.controller.validation.redLed")),
					]'
					required
				/>
				<v-checkbox
					v-model='watchdogPins'
					:label='$t("components.configuration.controller.form.pins.useWatchdogPins")'
					density='compact'
				/>
				<TextInput
					v-model.number='profile.sck'
					type='number'
					:label='$t("components.configuration.controller.form.pins.sck")'
					:rules='watchdogPins ?
						[
							(v: number|null) => ValidationRules.required(v, $t("components.configuration.controller.validation.sck")),
							(v: number) => ValidationRules.integer(v, $t("components.configuration.controller.validation.sck")),
						] : []
					'
					:disabled='!watchdogPins'
					:required='watchdogPins'
				/>
				<TextInput
					v-model.number='profile.sda'
					type='number'
					:label='$t("components.configuration.controller.form.pins.sda")'
					:rules='watchdogPins ?
						[
							(v: number|null) => ValidationRules.required(v, $t("components.configuration.controller.validation.sda")),
							(v: number) => ValidationRules.integer(v, $t("components.configuration.controller.validation.sda")),
						] : []
					'
					:disabled='!watchdogPins'
					:required='watchdogPins'
				/>
				<template #actions>
					<v-btn
						color='primary'
						type='submit'
						variant='elevated'
						:disabled='!isValid.value'
					>
						{{ $t(`common.buttons.${action}`) }}
					</v-btn>
					<v-spacer />
					<v-btn
						color='grey-darken-2'
						variant='elevated'
						@click='close'
					>
						{{ $t('common.buttons.cancel') }}
					</v-btn>
				</template>
			</Card>
		</v-form>
	</v-dialog>
</template>

<script lang='ts' setup>
import { type IqrfGatewayControllerService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { type IqrfGatewayControllerMapping, IqrfGatewayControllerMappingDevice } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { mdiPencil, mdiPlus } from '@mdi/js';
import { computed, type PropType, type Ref, ref , watchEffect } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import SelectInput from '@/components/SelectInput.vue';
import TextInput from '@/components/TextInput.vue';
import { FormAction } from '@/enums/controls';
import { getModalWidth } from '@/helpers/modal';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';

const emit = defineEmits(['saved']);
const componentProps = defineProps({
	action: {
		type: String as PropType<FormAction>,
		default: FormAction.Add,
		required: false,
	},
	deviceProfile: {
		type: Object as PropType<IqrfGatewayControllerMapping>,
		default: (): IqrfGatewayControllerMapping => ({
			button: 0,
			deviceType: IqrfGatewayControllerMappingDevice.Adapter,
			greenLed: 0,
			name: '',
			redLed: 0,
			sck: -1,
			sda: -1,
		}),
		required: false,
	},
});
const i18n = useI18n();
const width = getModalWidth();
const service: IqrfGatewayControllerService = useApiClient().getConfigServices().getIqrfGatewayControllerService();
const show: Ref<boolean> = ref(false);
const form: Ref<typeof VForm | null> = ref(null);
const defaultProfile: IqrfGatewayControllerMapping = {
	name: '',
	deviceType: IqrfGatewayControllerMappingDevice.Board,
	button: 0,
	greenLed: 0,
	redLed: 0,
	sck: -1,
	sda: -1,
};
const profile: Ref<IqrfGatewayControllerMapping> = ref({...defaultProfile});
const watchdogPins: Ref<boolean> = ref(false);
const typeOptions = [
	{
		title: i18n.t('components.configuration.controller.profiles.types.adapter'),
		value: IqrfGatewayControllerMappingDevice.Adapter,
	},
	{
		title: i18n.t('components.configuration.controller.profiles.types.board'),
		value: IqrfGatewayControllerMappingDevice.Board,
	},
];
const iconColor = computed(() => {
	if (componentProps.action === FormAction.Add) {
		return 'white';
	}
	return 'info';
});
const activatorIcon = computed(() => {
	if (componentProps.action === FormAction.Add) {
		return mdiPlus;
	}
	return mdiPencil;
});
const dialogTitle = computed(() => {
	if (componentProps.action === FormAction.Add) {
		return i18n.t('components.configuration.controller.profiles.actions.add').toString();
	}
	return i18n.t('components.configuration.controller.profiles.actions.edit').toString();
});

watchEffect(async(): Promise<void> => {
	if (componentProps.action === FormAction.Edit && componentProps.deviceProfile) {
		profile.value = {...componentProps.deviceProfile};
		watchdogPins.value = (componentProps.deviceProfile.sck !== -1 && componentProps.deviceProfile.sda !== -1);
	} else {
		profile.value = {...defaultProfile};
		watchdogPins.value = (defaultProfile.sck !== -1 && defaultProfile.sda !== -1);
	}
});

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	const params = {...profile.value};
	if (!watchdogPins.value) {
		delete params.sck;
		delete params.sda;
	}
	if (componentProps.deviceProfile.id === undefined) {
		service.createMapping(params)
			.then(() => handleSuccess(params.name))
			.catch(handleError);
	} else {
		service.editMapping(componentProps.deviceProfile.id, params)
			.then(() => handleSuccess(params.name))
			.catch(handleError);
	}
}

function handleSuccess(name: string): void {
	toast.success(
		i18n.t('components.configuration.controller.profiles.messages.save.success', {name: name}),
	);
	close();
	emit('saved');
}

function handleError(): void {
	toast.error('TODO ERROR HANDLING');
}

function close(): void {
	show.value = false;
	profile.value = {...defaultProfile};
}
</script>
