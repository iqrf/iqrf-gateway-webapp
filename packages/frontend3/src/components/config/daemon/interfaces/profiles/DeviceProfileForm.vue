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
	<ModalWindow v-model='show'>
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
					:label='$t("components.configuration.profiles.name")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.configuration.profiles.validation.nameMissing")),
					]'
					required
				/>
				<SelectInput
					v-model='profile.deviceType'
					:items='typeOptions'
					:label='$t("components.configuration.profiles.deviceType")'
					required
				/>
				<TextInput
					v-model='profile.IqrfInterface'
					:label='$t("components.configuration.daemon.interfaces.interface")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.configuration.daemon.interfaces.validation.interfaceMissing")),
					]'
					required
				/>
				<NumberInput
					v-model.number='profile.powerEnableGpioPin'
					:label='$t("components.configuration.daemon.interfaces.powerPin")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.configuration.daemon.interfaces.validation.powerPinMissing")),
						(v: number) => ValidationRules.integer(v, $t("components.configuration.daemon.interfaces.validation.powerPinInvalid")),
					]'
					required
				/>
				<NumberInput
					v-model.number='profile.busEnableGpioPin'
					:label='$t("components.configuration.daemon.interfaces.busPin")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.configuration.daemon.interfaces.validation.busPinMissing")),
						(v: number) => ValidationRules.integer(v, $t("components.configuration.daemon.interfaces.validation.busPinInvalid")),
					]'
					required
				/>
				<NumberInput
					v-model.number='profile.pgmSwitchGpioPin'
					:label='$t("components.configuration.daemon.interfaces.pgmPin")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.configuration.daemon.interfaces.validation.pgmPinMissing")),
						(v: number) => ValidationRules.integer(v, $t("components.configuration.daemon.interfaces.validation.pgmPinInvalid")),
					]'
					required
				/>
				<v-checkbox
					v-model='interfacePins'
					:label='$t("components.configuration.daemon.interfaces.interfacePins")'
					density='compact'
				/>
				<NumberInput
					v-model.number='profile.i2cEnableGpioPin'
					:label='$t("components.configuration.daemon.interfaces.i2cPin")'
					:rules='interfacePins ?
						[
							(v: number|null) => ValidationRules.required(v, $t("components.configuration.daemon.interfaces.validation.i2cPinMissing")),
							(v: number) => ValidationRules.integer(v, $t("components.configuration.daemon.interfaces.validation.i2cPinInvalid")),
						] : []
					'
					:disabled='!interfacePins'
					:required='interfacePins'
				/>
				<NumberInput
					v-model.number='profile.spiEnableGpioPin'
					:label='$t("components.configuration.daemon.interfaces.spiPin")'
					:rules='interfacePins ?
						[
							(v: number|null) => ValidationRules.required(v, $t("components.configuration.daemon.interfaces.validation.spiPinMissing")),
							(v: number) => ValidationRules.integer(v, $t("components.configuration.daemon.interfaces.validation.spiPinInvalid")),
						] : []
					'
					:disabled='!interfacePins'
					:required='interfacePins'
				/>
				<NumberInput
					v-model.number='profile.uartEnableGpioPin'
					:label='$t("components.configuration.daemon.interfaces.uartPin")'
					:rules='interfacePins ?
						[
							(v: number|null) => ValidationRules.required(v, $t("components.configuration.daemon.interfaces.validation.uartPinMissing")),
							(v: number) => ValidationRules.integer(v, $t("components.configuration.daemon.interfaces.validation.uartPinInvalid")),
						] : []
					'
					:disabled='!interfacePins'
					:required='interfacePins'
				/>
				<SelectInput
					v-if='profile.type === MappingType.UART'
					v-model='profile.baudRate'
					:items='baudRateOptions'
					:label='$t("components.configuration.daemon.interfaces.uart.baudRate")'
					required
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
	</ModalWindow>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { type IqrfGatewayDaemonMapping } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { MappingDeviceType, MappingType } from '@iqrf/iqrf-gateway-webapp-client/types/Config/Mapping';
import { mdiPencil, mdiPlus } from '@mdi/js';
import {
	computed,
	type Ref,
	ref,
	type PropType,
} from 'vue';
import { watchEffect } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import NumberInput from '@/components/layout/form/NumberInput.vue';
import SelectInput from '@/components/layout/form/SelectInput.vue';
import TextInput from '@/components/layout/form/TextInput.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import { FormAction } from '@/enums/controls';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';

const componentProps = defineProps({
	action: {
		type: String as PropType<FormAction>,
		default: FormAction.Add,
		required: false,
	},
	mappingType: {
		type: String as PropType<MappingType>,
		required: true,
	},
	deviceProfile: {
		type: Object as PropType<IqrfGatewayDaemonMapping>,
		default: (): IqrfGatewayDaemonMapping => ({
			busEnableGpioPin: 0,
			deviceType: MappingDeviceType.Board,
			IqrfInterface: '',
			name: '',
			pgmSwitchGpioPin: 0,
			powerEnableGpioPin: 0,
			type: MappingType.SPI,
			i2cEnableGpioPin: -1,
			spiEnableGpioPin: -1,
			uartEnableGpioPin: -1,
			baudRate: 9600,
		}),
		required: false,
	},
});
const emit = defineEmits(['saved']);
const i18n = useI18n();
const show: Ref<boolean> = ref(false);
const form: Ref<typeof VForm | null> = ref(null);
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const defaultProfile: IqrfGatewayDaemonMapping = {
	busEnableGpioPin: 0,
	deviceType: MappingDeviceType.Board,
	IqrfInterface: '',
	name: '',
	pgmSwitchGpioPin: 0,
	powerEnableGpioPin: 0,
	type: MappingType.SPI,
	i2cEnableGpioPin: -1,
	spiEnableGpioPin: -1,
	uartEnableGpioPin: -1,
	baudRate: 9600,
};
const profile: Ref<IqrfGatewayDaemonMapping> = ref({ ...defaultProfile });
const interfacePins: Ref<boolean> = ref(false);
const typeOptions = [
	{
		title: i18n.t('components.configuration.profiles.deviceTypes.adapter'),
		value: MappingDeviceType.Adapter,
	},
	{
		title: i18n.t('components.configuration.profiles.deviceTypes.board'),
		value: MappingDeviceType.Board,
	},
];
const baudRateOptions = computed(() => {
	const items: number[] = [1200, 2400, 4800, 9600, 19200, 38400, 57600, 115200, 230400];
	return items.map((item: number) => ({
		title: `${item} Bd`,
		value: item,
	}));
});
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
		return i18n.t('components.configuration.profiles.actions.add').toString();
	}
	return i18n.t('components.configuration.profiles.actions.edit').toString();
});

watchEffect(async (): Promise<void> => {
	if (componentProps.action === FormAction.Edit && componentProps.deviceProfile) {
		profile.value = {
			...componentProps.deviceProfile,
			type: componentProps.mappingType,
		};
		interfacePins.value = !(
			(componentProps.deviceProfile.i2cEnableGpioPin === undefined &&
			componentProps.deviceProfile.spiEnableGpioPin === undefined &&
			componentProps.deviceProfile.uartEnableGpioPin === undefined) ||
			(componentProps.deviceProfile.i2cEnableGpioPin === -1 &&
			componentProps.deviceProfile.spiEnableGpioPin === -1 &&
			componentProps.deviceProfile.uartEnableGpioPin === -1)
		);
	} else {
		profile.value = {
			...defaultProfile,
			type: componentProps.mappingType,
		};
		interfacePins.value = !(
			(defaultProfile.i2cEnableGpioPin === undefined &&
			defaultProfile.spiEnableGpioPin === undefined &&
			defaultProfile.uartEnableGpioPin === undefined) ||
			(defaultProfile.i2cEnableGpioPin === -1 &&
			defaultProfile.spiEnableGpioPin === -1 &&
			defaultProfile.uartEnableGpioPin === -1)
		);
	}
});

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	const params = { ...profile.value };
	if (!interfacePins.value) {
		delete params.i2cEnableGpioPin;
		delete params.spiEnableGpioPin;
		delete params.uartEnableGpioPin;
	}
	if (params.type !== MappingType.UART) {
		delete params.baudRate;
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
		i18n.t('components.configuration.profiles.messages.save.success', { name: name }),
	);
	close();
	emit('saved');
}

function handleError(): void {
	toast.error('TODO ERROR HANDLING');
}

function close(): void {
	show.value = false;
}

</script>
