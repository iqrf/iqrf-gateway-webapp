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
	<IModalWindow
		v-model='show'
		persistent
	>
		<template #activator='{ props }'>
			<IActionBtn
				v-if='action === Action.Add'
				v-bind='props'
				:action='action'
				container-type='card-title'
				:tooltip='$t("components.config.profiles.actions.add")'
				:disabled='disabled'
			/>
			<IDataTableAction
				v-else
				v-bind='props'
				:action='action'
				:tooltip='$t("components.config.profiles.actions.edit")'
				:disabled='disabled'
			/>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			validate-on='input'
			:disabled='componentState === ComponentState.Action'
			@submit.prevent='onSubmit()'
		>
			<ICard :action='action'>
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
				<ISelectInput
					v-model='profile.deviceType'
					:items='typeOptions'
					:label='$t("components.config.profiles.deviceType")'
					required
				/>
				<ITextInput
					v-model='profile.IqrfInterface'
					:label='$t("components.config.daemon.interfaces.interface")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.interfaces.validation.interface.required")),
					]'
					required
				/>
				<INumberInput
					v-model='profile.powerEnableGpioPin'
					:label='$t("components.config.daemon.interfaces.powerPin")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.interfaces.validation.powerPin.required")),
						(v: number) => ValidationRules.integer(v, $t("components.config.daemon.interfaces.validation.powerPin.integer")),
					]'
					required
				/>
				<INumberInput
					v-model='profile.busEnableGpioPin'
					:label='$t("components.config.daemon.interfaces.busPin")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.interfaces.validation.busPin.required")),
						(v: number) => ValidationRules.integer(v, $t("components.config.daemon.interfaces.validation.busPin.integer")),
					]'
					required
				/>
				<INumberInput
					v-model='profile.pgmSwitchGpioPin'
					:label='$t("components.config.daemon.interfaces.pgmPin")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.interfaces.validation.pgmPin.required")),
						(v: number) => ValidationRules.integer(v, $t("components.config.daemon.interfaces.validation.pgmPin.integer")),
					]'
					required
				/>
				<v-checkbox
					v-model='interfacePins'
					:label='$t("components.config.daemon.interfaces.interfacePins")'
					hide-details
				/>
				<INumberInput
					v-model='profile.i2cEnableGpioPin'
					:label='$t("components.config.daemon.interfaces.i2cPin")'
					:rules='interfacePins ?
						[
							(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.interfaces.validation.i2cPin.required")),
							(v: number) => ValidationRules.integer(v, $t("components.config.daemon.interfaces.validation.i2cPin.integer")),
						] : []
					'
					:disabled='!interfacePins'
					:required='interfacePins'
				/>
				<INumberInput
					v-model='profile.spiEnableGpioPin'
					:label='$t("components.config.daemon.interfaces.spiPin")'
					:rules='interfacePins ?
						[
							(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.interfaces.validation.spiPin.required")),
							(v: number) => ValidationRules.integer(v, $t("components.config.daemon.interfaces.validation.spiPin.integer")),
						] : []
					'
					:disabled='!interfacePins'
					:required='interfacePins'
				/>
				<INumberInput
					v-model='profile.uartEnableGpioPin'
					:label='$t("components.config.daemon.interfaces.uartPin")'
					:rules='interfacePins ?
						[
							(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.interfaces.validation.uartPin.required")),
							(v: number) => ValidationRules.integer(v, $t("components.config.daemon.interfaces.validation.uartPin.integer")),
						] : []
					'
					:disabled='!interfacePins'
					:required='interfacePins'
				/>
				<ISelectInput
					v-if='profile.type === MappingType.UART'
					v-model='profile.baudRate'
					:items='baudRateOptions'
					:label='$t("components.config.daemon.interfaces.uart.baudRate")'
					required
				/>
				<template #actions>
					<IActionBtn
						:action='action'
						:loading='componentState === ComponentState.Action'
						:disabled='!isValid.value'
						type='submit'
					/>
					<v-spacer />
					<IActionBtn
						:action='Action.Cancel'
						:disabled='componentState === ComponentState.Action'
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
	ComponentState,
	IActionBtn,
	ICard,
	IDataTableAction,
	IModalWindow,
	INumberInput,
	ISelectInput,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import {
	computed, ref, type Ref,
	type TemplateRef, useTemplateRef, watch,
} from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

const componentProps = withDefaults(
	defineProps<{
		/// Action to perform (add/edit)
		action?: Action.Add | Action.Edit;
		/// Mapping type (SPI/I2C/UART)
		mappingType: MappingType;
		/// Device profile to edit
		deviceProfile?: IqrfGatewayDaemonMapping;
		/// Disable form activator button
		disabled?: boolean;
	}>(),
	{
		action: Action.Add,
		deviceProfile: (): IqrfGatewayDaemonMapping => ({
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
		disabled: false,
	},
);
const emit = defineEmits<{
	saved: [];
}>();
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const show: Ref<boolean> = ref(false);
const form: TemplateRef<VForm> = useTemplateRef('form');
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
const typeOptions = computed(() => [
	{
		title: i18n.t('components.config.profiles.deviceTypes.adapter'),
		value: MappingDeviceType.Adapter,
	},
	{
		title: i18n.t('components.config.profiles.deviceTypes.board'),
		value: MappingDeviceType.Board,
	},
]);
const baudRateOptions = computed(() => {
	const items: number[] = [1_200, 2_400, 4_800, 9_600, 19_200, 38_400, 57_600, 115_200, 230_400];
	return items.map((item: number) => ({
		title: `${item} Bd`,
		value: item,
	}));
});
const dialogTitle = computed(() => {
	if (componentProps.action === Action.Add) {
		return i18n.t('components.config.profiles.actions.add');
	}
	return i18n.t('components.config.profiles.actions.edit');
});

watch(show, (newVal: boolean): void => {
	if (!newVal) {
		return;
	}
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
	componentState.value = ComponentState.Action;
	const params = { ...profile.value };
	if (!interfacePins.value) {
		delete params.i2cEnableGpioPin;
		delete params.spiEnableGpioPin;
		delete params.uartEnableGpioPin;
	}
	if (params.type !== MappingType.UART) {
		delete params.baudRate;
	}
	const translationParams = { name: params.name };
	try {
		if (componentProps.deviceProfile.id === undefined) {
			await service.createMapping(params);
		} else {
			await service.updateMapping(componentProps.deviceProfile.id, params);
		}
		toast.success(
			i18n.t('components.config.profiles.messages.save.success', translationParams),
		);
		close();
		emit('saved');
	} catch {
		toast.error(
			i18n.t('components.config.profiles.messages.save.failed', translationParams),
		);
	}
	componentState.value = ComponentState.Ready;
}

function close(): void {
	show.value = false;
}

</script>
