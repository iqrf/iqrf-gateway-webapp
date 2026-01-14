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
			validate-on='eager'
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
				<INumberInput
					v-model='profile.button'
					:label='$t("components.config.controller.form.pins.buttonPin")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.config.controller.validation.buttonPin.required")),
						(v: number) => ValidationRules.integer(v, $t("components.config.controller.validation.buttonPin.integer")),
					]'
					:prepend-inner-icon='mdiRadioboxBlank'
					required
				/>
				<INumberInput
					v-model='profile.greenLed'
					:label='$t("components.config.controller.form.pins.greenLedPin")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.config.controller.validation.greenLedPin.required")),
						(v: number) => ValidationRules.integer(v, $t("components.config.controller.validation.greenLedPin.integer")),
					]'
					:prepend-inner-icon='mdiLedVariantOutline'
					required
				/>
				<INumberInput
					v-model='profile.redLed'
					:label='$t("components.config.controller.form.pins.redLedPin")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.config.controller.validation.redLedPin.required")),
						(v: number) => ValidationRules.integer(v, $t("components.config.controller.validation.redLedPin.integer")),
					]'
					:prepend-inner-icon='mdiLedVariantOn'
					required
				/>
				<v-checkbox
					v-model='watchdogPins'
					:label='$t("components.config.controller.form.pins.useWatchdogPins")'
					:hide-details='false'
				/>
				<INumberInput
					v-model='profile.sck'
					:label='$t("components.config.controller.form.pins.sckPin")'
					:rules='watchdogPins ?
						[
							(v: number|null) => ValidationRules.required(v, $t("components.config.controller.validation.sckPin.required")),
							(v: number) => ValidationRules.integer(v, $t("components.config.controller.validation.sckPin.integer")),
						] : []
					'
					:prepend-inner-icon='mdiChip'
					:disabled='!watchdogPins'
					:required='watchdogPins'
				/>
				<INumberInput
					v-model='profile.sda'
					:label='$t("components.config.controller.form.pins.sdaPin")'
					:rules='watchdogPins ?
						[
							(v: number|null) => ValidationRules.required(v, $t("components.config.controller.validation.sdaPin.required")),
							(v: number) => ValidationRules.integer(v, $t("components.config.controller.validation.sdaPin.integer")),
						] : []
					'
					:prepend-inner-icon='mdiChip'
					:disabled='!watchdogPins'
					:required='watchdogPins'
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
import { type IqrfGatewayControllerService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { type IqrfGatewayControllerMapping } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { MappingDeviceType } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
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
import { mdiChip, mdiLedVariantOn, mdiLedVariantOutline, mdiRadioboxBlank } from '@mdi/js';
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
		/// Action type
		action?: Action.Add | Action.Edit;
		/// Device profile to edit
		deviceProfile?: IqrfGatewayControllerMapping;
		/// Disable form
		disabled?: boolean;
	}>(),
	{
		action: Action.Add,
		deviceProfile: (): IqrfGatewayControllerMapping => ({
			button: 0,
			deviceType: MappingDeviceType.Adapter,
			greenLed: 0,
			name: '',
			redLed: 0,
			sck: -1,
			sda: -1,
		}),
		disabled: false,
	},
);
const emit = defineEmits<{
	saved: [];
}>();
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const service: IqrfGatewayControllerService = useApiClient().getConfigServices().getIqrfGatewayControllerService();
const show: Ref<boolean> = ref(false);
const form: TemplateRef<VForm> = useTemplateRef('form');
const defaultProfile: IqrfGatewayControllerMapping = {
	name: '',
	deviceType: MappingDeviceType.Board,
	button: 0,
	greenLed: 0,
	redLed: 0,
	sck: -1,
	sda: -1,
};
const profile: Ref<IqrfGatewayControllerMapping> = ref({ ...defaultProfile });
const watchdogPins: Ref<boolean> = ref(false);
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
		profile.value = { ...componentProps.deviceProfile };
		if ((componentProps.deviceProfile.sck !== undefined && componentProps.deviceProfile.sck !== -1) ||
			(componentProps.deviceProfile.sda !== undefined && componentProps.deviceProfile.sda !== -1)) {
			watchdogPins.value = true;
		} else {
			watchdogPins.value = false;
		}
		if (componentProps.deviceProfile.sck === undefined) {
			profile.value.sck = -1;
		}
		if (componentProps.deviceProfile.sda === undefined) {
			profile.value.sda = -1;
		}
	} else {
		profile.value = { ...defaultProfile };
		watchdogPins.value = false;
	}
});

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Action;
	const params = { ...profile.value };
	if (!watchdogPins.value) {
		delete params.sck;
		delete params.sda;
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
