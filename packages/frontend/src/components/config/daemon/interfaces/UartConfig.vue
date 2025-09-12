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
	<v-form
		ref='form'
		v-slot='{ isValid }'
		:disabled='[ComponentState.Reloading, ComponentState.Saving].includes(componentState)'
		@submit.prevent='onSubmit()'
	>
		<Card>
			<template #title>
				{{ $t('pages.config.daemon.interfaces.uart.title') }}
			</template>
			<template #titleActions>
				<CardTitleActionBtn
					:action='Action.Reload'
					@click='getConfig()'
				/>
			</template>
			<v-skeleton-loader
				class='input-skeleton-loader'
				:loading='componentState === ComponentState.Loading'
				type='heading@2, text, heading, text, heading@2'
			>
				<v-responsive>
					<section v-if='config'>
						<TextInput
							v-model='config.instance'
							:label='$t("components.config.daemon.instance")'
							:rules='[
								(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.validation.instanceMissing")),
							]'
							required
						/>
						<TextInput
							v-model='config.IqrfInterface'
							:label='$t("components.config.daemon.interfaces.interface")'
							:rules='[
								(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.interfaces.validation.interfaceMissing")),
							]'
							required
						/>
						<v-checkbox
							v-model='config.uartReset'
							:label='$t("components.config.daemon.interfaces.uart.uartReset")'
						/>
						<v-row :no-gutters='display.mobile.value'>
							<v-col
								cols='12'
								md='4'
							>
								<NumberInput
									v-model.number='config.powerEnableGpioPin'
									:label='$t("components.config.daemon.interfaces.powerPin")'
									:rules='[
										(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.interfaces.validation.powerPinMissing")),
										(v: number) => ValidationRules.integer(v, $t("components.config.daemon.interfaces.validation.powerPinInvalid")),
									]'
									required
								/>
							</v-col>
							<v-col
								cols='12'
								md='4'
							>
								<NumberInput
									v-model.number='config.busEnableGpioPin'
									:label='$t("components.config.daemon.interfaces.busPin")'
									:rules='[
										(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.interfaces.validation.busPinMissing")),
										(v: number) => ValidationRules.integer(v, $t("components.config.daemon.interfaces.validation.busPinInvalid")),
									]'
									required
								/>
							</v-col>
							<v-col
								cols='12'
								md='4'
							>
								<NumberInput
									v-model.number='config.pgmSwitchGpioPin'
									:label='$t("components.config.daemon.interfaces.pgmPin")'
									:rules='[
										(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.interfaces.validation.pgmPinMissing")),
										(v: number) => ValidationRules.integer(v, $t("components.config.daemon.interfaces.validation.pgmPinInvalid")),
									]'
									required
								/>
							</v-col>
						</v-row>
						<v-checkbox
							v-model='interfacePins'
							:label='$t("components.config.daemon.interfaces.interfacePins")'
						/>
						<v-row :no-gutters='display.mobile.value'>
							<v-col
								cols='12'
								md='4'
							>
								<NumberInput
									v-model.number='config.i2cEnableGpioPin'
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
							</v-col>
							<v-col
								cols='12'
								md='4'
							>
								<NumberInput
									v-model.number='config.spiEnableGpioPin'
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
							</v-col>
							<v-col
								cols='12'
								md='4'
							>
								<NumberInput
									v-model.number='config.uartEnableGpioPin'
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
							</v-col>
						</v-row>
						<SelectInput
							v-model='config.baudRate'
							:items='baudRateOptions'
							:label='$t("components.config.daemon.interfaces.uart.baudRate")'
							required
						/>
					</section>
				</v-responsive>
			</v-skeleton-loader>
			<span class='d-flex justify-space-around'>
				<v-menu
					v-model='showProfileMenu'
					location='top center'
					transition='slide-y-transition'
					:close-on-content-click='false'
					eager
				>
					<template #activator='{ props }'>
						<v-btn
							v-bind='props'
							color='primary'
						>
							{{ $t('components.config.daemon.interfaces.profiles.uart') }}
						</v-btn>
					</template>
					<DeviceProfileTable
						:mapping-type='MappingType.UART'
						@apply='(p: IqrfGatewayDaemonMapping) => applyProfile(p)'
					/>
				</v-menu>
				<v-menu
					v-model='showIntefaceMenu'
					location='top center'
					transition='slide-y-transition'
					:close-on-content-click='false'
					eager
				>
					<template #activator='{ props }'>
						<v-btn
							v-bind='props'
							color='primary'
						>
							{{ $t('components.config.daemon.interfaces.uart.devices') }}
						</v-btn>
					</template>
					<InterfacePorts
						:interface-type='IqrfInterfaceType.UART'
						@apply='(iface: string) => applyInterface(iface)'
					/>
				</v-menu>
			</span>
			<template #actions>
				<CardActionBtn
					:action='Action.Edit'
					:disabled='!isValid.value || [ComponentState.Loading, ComponentState.Reloading, ComponentState.Saving].includes(componentState)'
					type='submit'
				/>
			</template>
		</Card>
	</v-form>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	IqrfGatewayDaemonComponentName,
	type IqrfGatewayDaemonMapping,
	type IqrfGatewayDaemonUart,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { MappingType } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { IqrfInterfaceType } from '@iqrf/iqrf-gateway-webapp-client/types/Iqrf';
import { ValidationRules } from '@iqrf/iqrf-vue-ui';
import { computed, onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { useDisplay } from 'vuetify';
import { VForm } from 'vuetify/components';

import InterfacePorts from '@/components/config/daemon/interfaces/InterfacePorts.vue';
import DeviceProfileTable from '@/components/config/daemon/interfaces/profiles/DeviceProfileTable.vue';
import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import CardTitleActionBtn from '@/components/layout/card/CardTitleActionBtn.vue';
import NumberInput from '@/components/layout/form/NumberInput.vue';
import SelectInput from '@/components/layout/form/SelectInput.vue';
import TextInput from '@/components/layout/form/TextInput.vue';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';
import { Action } from '@/types/Action';
import { ComponentState } from '@/types/ComponentState';

const i18n = useI18n();
const display = useDisplay();
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const form: Ref<VForm | null> = ref(null);
const config: Ref<IqrfGatewayDaemonUart | null> = ref(null);
let instance = '';
const interfacePins: Ref<boolean> = ref(false);
const showIntefaceMenu: Ref<boolean> = ref(false);
const showProfileMenu: Ref<boolean> = ref(false);
const baudRateOptions = computed(() => {
	const items: number[] = [1_200, 2_400, 4_800, 9_600, 19_200, 38_400, 57_600, 115_200, 230_400];
	return items.map((item: number) => ({
		title: `${item} Bd`,
		value: item,
	}));
});

async function getConfig(): Promise<void> {
	if (componentState.value === ComponentState.Created) {
		componentState.value = ComponentState.Loading;
	} else {
		componentState.value = ComponentState.Reloading;
	}
	try {
		config.value = (await service.getComponent(IqrfGatewayDaemonComponentName.IqrfUart)).instances[0] ?? null;
		if (config.value !== null) {
			instance = config.value.instance;
		}
	} catch {
		toast.error('TODO FETCH ERROR HANDLING');
	}
	componentState.value = ComponentState.Ready;
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || config.value === null) {
		return;
	}
	componentState.value = ComponentState.Saving;
	const params = { ...config.value };
	if (!interfacePins.value) {
		delete params.i2cEnableGpioPin;
		delete params.spiEnableGpioPin;
		delete params.uartEnableGpioPin;
	}
	try {
		await service.updateInstance(IqrfGatewayDaemonComponentName.IqrfUart, instance, params);
		await getConfig();
		toast.success(
			i18n.t('components.config.daemon.interfaces.uart.messages.save.success'),
		);
	} catch {
		toast.error('TODO SAVE ERROR HANDLING');
	}
}

function applyProfile(profile: IqrfGatewayDaemonMapping): void {
	if (config.value === null) {
		return;
	}
	config.value = {
		...config.value,
		IqrfInterface: profile.IqrfInterface,
		busEnableGpioPin: profile.busEnableGpioPin,
		powerEnableGpioPin: profile.powerEnableGpioPin,
		pgmSwitchGpioPin: profile.pgmSwitchGpioPin,
		baudRate: profile.baudRate!,
	};
	if (profile.i2cEnableGpioPin !== undefined &&
		profile.i2cEnableGpioPin !== -1 &&
		profile.spiEnableGpioPin !== undefined &&
		profile.spiEnableGpioPin !== -1
	) {
		config.value.i2cEnableGpioPin = profile.i2cEnableGpioPin;
		config.value.spiEnableGpioPin = profile.spiEnableGpioPin;
		config.value.uartEnableGpioPin = profile.uartEnableGpioPin;
		interfacePins.value = true;
	} else {
		config.value.i2cEnableGpioPin = config.value.spiEnableGpioPin = config.value.uartEnableGpioPin = 0;
		interfacePins.value = false;
	}
	showProfileMenu.value = false;
}

function applyInterface(iface: string): void {
	if (config.value === null) {
		return;
	}
	config.value.IqrfInterface = iface;
	showIntefaceMenu.value = false;
}

onMounted(() => {
	getConfig();
});
</script>
