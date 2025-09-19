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
		<template #activator='{ props }'>
			<IActionBtn
				v-if='action === Action.Add'
				v-bind='props'
				:action='action'
				container-type='card-title'
			/>
			<IDataTableAction
				v-else
				v-bind='props'
				:action='action'
				:tooltip='$t("components.config.profiles.actions.edit")'
			/>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			validate-on='input'
			@submit.prevent='onSubmit()'
		>
			<ICard>
				<template #title>
					{{ dialogTitle }}
				</template>
				<ITextInput
					v-model='profile.name'
					:label='$t("components.config.profiles.name")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.profiles.validation.nameMissing")),
					]'
					required
				/>
				<SelectInput
					v-model='profile.deviceType'
					:items='typeOptions'
					:label='$t("components.config.profiles.deviceType")'
					required
				/>
				<ITextInput
					v-model='profile.IqrfInterface'
					:label='$t("components.config.daemon.interfaces.interface")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.interfaces.validation.interfaceMissing")),
					]'
					required
				/>
				<NumberInput
					v-model.number='profile.powerEnableGpioPin'
					:label='$t("components.config.daemon.interfaces.powerPin")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.interfaces.validation.powerPinMissing")),
						(v: number) => ValidationRules.integer(v, $t("components.config.daemon.interfaces.validation.powerPinInvalid")),
					]'
					required
				/>
				<NumberInput
					v-model.number='profile.busEnableGpioPin'
					:label='$t("components.config.daemon.interfaces.busPin")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.interfaces.validation.busPinMissing")),
						(v: number) => ValidationRules.integer(v, $t("components.config.daemon.interfaces.validation.busPinInvalid")),
					]'
					required
				/>
				<NumberInput
					v-model.number='profile.pgmSwitchGpioPin'
					:label='$t("components.config.daemon.interfaces.pgmPin")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.interfaces.validation.pgmPinMissing")),
						(v: number) => ValidationRules.integer(v, $t("components.config.daemon.interfaces.validation.pgmPinInvalid")),
					]'
					required
				/>
				<v-checkbox
					v-model='interfacePins'
					:label='$t("components.config.daemon.interfaces.interfacePins")'
					:hide-details='false'
				/>
				<NumberInput
					v-model.number='profile.i2cEnableGpioPin'
					:label='$t("components.config.daemon.interfaces.i2cPin")'
					:rules='interfacePins ?
						[
							(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.interfaces.validation.i2cPinMissing")),
							(v: number) => ValidationRules.integer(v, $t("components.config.daemon.interfaces.validation.i2cPinInvalid")),
						] : []
					'
					:disabled='!interfacePins'
					:required='interfacePins'
				/>
				<NumberInput
					v-model.number='profile.spiEnableGpioPin'
					:label='$t("components.config.daemon.interfaces.spiPin")'
					:rules='interfacePins ?
						[
							(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.interfaces.validation.spiPinMissing")),
							(v: number) => ValidationRules.integer(v, $t("components.config.daemon.interfaces.validation.spiPinInvalid")),
						] : []
					'
					:disabled='!interfacePins'
					:required='interfacePins'
				/>
				<NumberInput
					v-model.number='profile.uartEnableGpioPin'
					:label='$t("components.config.daemon.interfaces.uartPin")'
					:rules='interfacePins ?
						[
							(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.interfaces.validation.uartPinMissing")),
							(v: number) => ValidationRules.integer(v, $t("components.config.daemon.interfaces.validation.uartPinInvalid")),
						] : []
					'
					:disabled='!interfacePins'
					:required='interfacePins'
				/>
				<SelectInput
					v-if='profile.type === MappingType.UART'
					v-model='profile.baudRate'
					:items='baudRateOptions'
					:label='$t("components.config.daemon.interfaces.uart.baudRate")'
					required
				/>
				<template #actions>
					<IActionBtn
						:action='action'
						container-type='card'
						:disabled='!isValid.value'
						type='submit'
					/>
					<v-spacer />
					<IActionBtn
						:action='Action.Cancel'
						container-type='card'
						@click='close()'
					/>
				</template>
			</ICard>
		</v-form>
	</IModalWindow>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { type IqrfGatewayDaemonMapping } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { MappingDeviceType, MappingType } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {
	Action,
	IActionBtn,
	ICard,
	IDataTableAction,
	IModalWindow,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import {
	computed,
	type PropType,
	ref,
	type Ref,
} from 'vue';
import { watchEffect } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import NumberInput from '@/components/layout/form/NumberInput.vue';
import SelectInput from '@/components/layout/form/SelectInput.vue';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

const componentProps = defineProps({
	action: {
		type: String as PropType<Action>,
		default: Action.Add,
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
			baudRate: 9_600,
		}),
		required: false,
	},
});
const emit = defineEmits(['saved']);
const i18n = useI18n();
const show: Ref<boolean> = ref(false);
const form: Ref<VForm | null> = ref(null);
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
	baudRate: 9_600,
};
const profile: Ref<IqrfGatewayDaemonMapping> = ref({ ...defaultProfile });
const interfacePins: Ref<boolean> = ref(false);
const typeOptions = [
	{
		title: i18n.t('components.config.profiles.deviceTypes.adapter'),
		value: MappingDeviceType.Adapter,
	},
	{
		title: i18n.t('components.config.profiles.deviceTypes.board'),
		value: MappingDeviceType.Board,
	},
];
const baudRateOptions = computed(() => {
	const items: number[] = [1_200, 2_400, 4_800, 9_600, 19_200, 38_400, 57_600, 115_200, 230_400];
	return items.map((item: number) => ({
		title: `${item} Bd`,
		value: item,
	}));
});
const dialogTitle = computed(() => {
	if (componentProps.action === Action.Add) {
		return i18n.t('components.config.profiles.actions.add').toString();
	}
	return i18n.t('components.config.profiles.actions.edit').toString();
});

watchEffect((): void => {
	if (componentProps.action === Action.Edit && componentProps.deviceProfile) {
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
	try {
		if (componentProps.deviceProfile.id === undefined) {
			await service.createMapping(params);
		} else {
			await service.updateMapping(componentProps.deviceProfile.id, params);
		}
		toast.success(
			i18n.t('components.config.profiles.messages.save.success', { name: name }),
		);
		close();
		emit('saved');
	} catch {
		toast.error('TODO ERROR HANDLING');
	}
}

function close(): void {
	show.value = false;
}

</script>
