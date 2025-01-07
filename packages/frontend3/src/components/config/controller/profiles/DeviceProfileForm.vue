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
		<template #activator='{ props }'>
			<CardTitleActionBtn
				v-if='action === Action.Add'
				v-bind='props'
				:action='action'
			/>
			<DataTableAction
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
			@submit.prevent='onSubmit'
		>
			<Card :action='action'>
				<template #title>
					{{ dialogTitle }}
				</template>
				<TextInput
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
				<NumberInput
					v-model.number='profile.button'
					:label='$t("components.config.controller.form.pins.button")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.config.controller.validation.buttonPin")),
						(v: number) => ValidationRules.integer(v, $t("components.config.controller.validation.buttonPin")),
					]'
					required
				/>
				<NumberInput
					v-model.number='profile.greenLed'
					:label='$t("components.config.controller.form.pins.greenLed")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.config.controller.validation.greenLed")),
						(v: number) => ValidationRules.integer(v, $t("components.config.controller.validation.greenLed")),
					]'
					required
				/>
				<NumberInput
					v-model.number='profile.redLed'
					:label='$t("components.config.controller.form.pins.redLed")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.config.controller.validation.redLed")),
						(v: number) => ValidationRules.integer(v, $t("components.config.controller.validation.redLed")),
					]'
					required
				/>
				<v-checkbox
					v-model='watchdogPins'
					:label='$t("components.config.controller.form.pins.useWatchdogPins")'
					:hide-details='false'
				/>
				<NumberInput
					v-model.number='profile.sck'
					:label='$t("components.config.controller.form.pins.sck")'
					:rules='watchdogPins ?
						[
							(v: number|null) => ValidationRules.required(v, $t("components.config.controller.validation.sck")),
							(v: number) => ValidationRules.integer(v, $t("components.config.controller.validation.sck")),
						] : []
					'
					:disabled='!watchdogPins'
					:required='watchdogPins'
				/>
				<NumberInput
					v-model.number='profile.sda'
					:label='$t("components.config.controller.form.pins.sda")'
					:rules='watchdogPins ?
						[
							(v: number|null) => ValidationRules.required(v, $t("components.config.controller.validation.sda")),
							(v: number) => ValidationRules.integer(v, $t("components.config.controller.validation.sda")),
						] : []
					'
					:disabled='!watchdogPins'
					:required='watchdogPins'
				/>
				<template #actions>
					<CardActionBtn
						:action='action'
						:disabled='!isValid.value'
						type='submit'
					/>
					<v-spacer />
					<CardActionBtn :action='Action.Cancel' @click='close' />
				</template>
			</Card>
		</v-form>
	</ModalWindow>
</template>

<script lang='ts' setup>
import { type IqrfGatewayControllerService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { type IqrfGatewayControllerMapping } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { MappingDeviceType } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { computed, type PropType, ref, type Ref, watchEffect } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import CardTitleActionBtn from '@/components/layout/card/CardTitleActionBtn.vue';
import DataTableAction from '@/components/layout/data-table/DataTableAction.vue';
import NumberInput from '@/components/layout/form/NumberInput.vue';
import SelectInput from '@/components/layout/form/SelectInput.vue';
import TextInput from '@/components/layout/form/TextInput.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { Action } from '@/types/Action';

const emit = defineEmits(['saved']);
const componentProps = defineProps({
	action: {
		type: String as PropType<Action>,
		default: Action.Add,
		required: false,
	},
	deviceProfile: {
		type: Object as PropType<IqrfGatewayControllerMapping>,
		default: (): IqrfGatewayControllerMapping => ({
			button: 0,
			deviceType: MappingDeviceType.Adapter,
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
const service: IqrfGatewayControllerService = useApiClient().getConfigServices().getIqrfGatewayControllerService();
const show: Ref<boolean> = ref(false);
const form: Ref<VForm | null> = ref(null);
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
const dialogTitle = computed(() => {
	if (componentProps.action === Action.Add) {
		return i18n.t('components.config.profiles.actions.add').toString();
	}
	return i18n.t('components.config.profiles.actions.edit').toString();
});

watchEffect((): void => {
	if (componentProps.action === Action.Edit && componentProps.deviceProfile) {
		profile.value = { ...componentProps.deviceProfile };
		watchdogPins.value = componentProps.deviceProfile.sck !== -1 && componentProps.deviceProfile.sda !== -1;
	} else {
		profile.value = { ...defaultProfile };
		watchdogPins.value = defaultProfile.sck !== -1 && defaultProfile.sda !== -1;
	}
});

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	const params = { ...profile.value };
	if (!watchdogPins.value) {
		delete params.sck;
		delete params.sda;
	}
	if (componentProps.deviceProfile.id === undefined) {
		service.createMapping(params)
			.then(() => handleSuccess(params.name))
			.catch(handleError);
	} else {
		service.updateMapping(componentProps.deviceProfile.id, params)
			.then(() => handleSuccess(params.name))
			.catch(handleError);
	}
}

function handleSuccess(name: string): void {
	toast.success(
		i18n.t('components.config.profiles.messages.save.success', { name: name }),
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
