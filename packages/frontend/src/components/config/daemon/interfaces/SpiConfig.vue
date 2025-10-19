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
		:disabled='[ComponentState.Reloading, ComponentState.Action].includes(componentState)'
		@submit.prevent='onSubmit()'
	>
		<ICard>
			<template #title>
				{{ $t('pages.config.daemon.interfaces.spi.title') }}
			</template>
			<template #titleActions>
				<IActionBtn
					:action='Action.Reload'
					container-type='card-title'
					:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
					:disabled='componentState === ComponentState.Action'
					@click='getConfig()'
				/>
			</template>
			<v-alert
				v-if='componentState === ComponentState.FetchFailed'
				type='error'
				variant='tonal'
				:text='$t("components.config.daemon.interfaces.spi.messages.fetch.failed")'
			/>
			<v-skeleton-loader
				class='input-skeleton-loader'
				:loading='componentState === ComponentState.Loading'
				type='heading@2, text, heading, text, heading'
			>
				<v-responsive>
					<section v-if='config'>
						<ITextInput
							v-model='config.instance'
							:label='$t("components.config.daemon.instance")'
							:rules='[
								(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.validation.instance.required")),
							]'
							required
						/>
						<ITextInput
							v-model='config.IqrfInterface'
							:label='$t("components.config.daemon.interfaces.interface")'
							:rules='[
								(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.interfaces.validation.interface.required")),
							]'
							required
						/>
						<v-checkbox
							v-model='config.spiReset'
							:label='$t("components.config.daemon.interfaces.spi.spiReset")'
						/>
						<v-row :no-gutters='display.mobile.value'>
							<v-col
								cols='12'
								md='4'
							>
								<INumberInput
									v-model='config.powerEnableGpioPin'
									:label='$t("components.config.daemon.interfaces.powerPin")'
									:rules='[
										(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.interfaces.validation.powerPin.required")),
										(v: number) => ValidationRules.integer(v, $t("components.config.daemon.interfaces.validation.powerPin.integer")),
									]'
									required
								/>
							</v-col>
							<v-col
								cols='12'
								md='4'
							>
								<INumberInput
									v-model='config.busEnableGpioPin'
									:label='$t("components.config.daemon.interfaces.busPin")'
									:rules='[
										(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.interfaces.validation.busPin.required")),
										(v: number) => ValidationRules.integer(v, $t("components.config.daemon.interfaces.validation.busPin.integer")),
									]'
									required
								/>
							</v-col>
							<v-col
								cols='12'
								md='4'
							>
								<INumberInput
									v-model='config.pgmSwitchGpioPin'
									:label='$t("components.config.daemon.interfaces.pgmPin")'
									:rules='[
										(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.interfaces.validation.pgmPin.required")),
										(v: number) => ValidationRules.integer(v, $t("components.config.daemon.interfaces.validation.pgmPin.integer")),
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
								<INumberInput
									v-model='config.i2cEnableGpioPin'
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
							</v-col>
							<v-col
								cols='12'
								md='4'
							>
								<INumberInput
									v-model='config.spiEnableGpioPin'
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
							</v-col>
							<v-col
								cols='12'
								md='4'
							>
								<INumberInput
									v-model='config.uartEnableGpioPin'
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
							</v-col>
						</v-row>
						<span class='d-flex justify-center'>
							<v-menu
								v-model='showProfileMenu'
								location='top center'
								transition='slide-y-transition'
								:close-on-content-click='false'
								eager
							>
								<template #activator='{ props }'>
									<IActionBtn
										v-bind='props'
										:action='Action.Custom'
										color='primary'
										class='mr-1'
										:icon='mdiTuneVariant'
										:disabled='[ComponentState.Action, ComponentState.Reloading, ComponentState.FetchFailed].includes(componentState)'
										:text='$t("components.config.daemon.interfaces.profiles.spi")'
									/>
								</template>
								<DeviceProfileTable
									:mapping-type='MappingType.SPI'
									@apply='(p: IqrfGatewayDaemonMapping) => applyProfile(p)'
								/>
							</v-menu>
							<v-menu
								v-model='showInterfaceMenu'
								location='top center'
								transition='slide-y-transition'
								:close-on-content-click='false'
								eager
							>
								<template #activator='{ props }'>
									<IActionBtn
										v-bind='props'
										:action='Action.Custom'
										color='primary'
										:icon='mdiSerialPort'
										:disabled='[ComponentState.Action, ComponentState.Reloading, ComponentState.FetchFailed].includes(componentState)'
										:text='$t("components.config.daemon.interfaces.spi.devices.title")'
									/>
								</template>
								<InterfacePorts
									:interface-type='IqrfInterfaceType.SPI'
									@apply='(iface: string) => applyInterface(iface)'
								/>
							</v-menu>
						</span>
					</section>
				</v-responsive>
			</v-skeleton-loader>
			<template #actions>
				<IActionBtn
					:action='Action.Save'
					:loading='componentState === ComponentState.Action'
					:disabled='!isValid.value || [ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
					type='submit'
				/>
			</template>
		</ICard>
	</v-form>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	IqrfGatewayDaemonComponentName,
	type IqrfGatewayDaemonMapping,
	type IqrfGatewayDaemonSpi,
	MappingType,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { IqrfInterfaceType } from '@iqrf/iqrf-gateway-webapp-client/types/Iqrf';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	INumberInput,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiSerialPort, mdiTuneVariant } from '@mdi/js';
import { onMounted, ref, type Ref, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { useDisplay } from 'vuetify';
import { VForm } from 'vuetify/components';

import InterfacePorts from '@/components/config/daemon/interfaces/InterfacePorts.vue';
import DeviceProfileTable from '@/components/config/daemon/interfaces/profiles/DeviceProfileTable.vue';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

const i18n = useI18n();
const display = useDisplay();
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const service: IqrfGatewayDaemonService = useApiClient()
	.getConfigServices()
	.getIqrfGatewayDaemonService();
const form: Ref<VForm | null> = useTemplateRef('form');
const config: Ref<IqrfGatewayDaemonSpi | null> = ref(null);
let instance = '';
const interfacePins: Ref<boolean> = ref(false);
const showInterfaceMenu: Ref<boolean> = ref(false);
const showProfileMenu: Ref<boolean> = ref(false);

async function getConfig(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		config.value = (await service.getComponent(IqrfGatewayDaemonComponentName.IqrfSpi)).instances[0] ?? null;
		if (config.value === null) {
			throw new Error('Configuration instance missing.');
		}
		instance = config.value.instance;
		interfacePins.value = config.value.i2cEnableGpioPin !== undefined &&
			config.value.i2cEnableGpioPin !== -1 &&
			config.value.spiEnableGpioPin !== undefined &&
			config.value.spiEnableGpioPin !== -1;
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error(
			i18n.t('components.config.daemon.interfaces.spi.messages.fetch.failed'),
		);
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
	}
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || config.value === null) {
		return;
	}
	componentState.value = ComponentState.Action;
	const params = { ...config.value };
	if (!interfacePins.value) {
		delete params.i2cEnableGpioPin;
		delete params.spiEnableGpioPin;
		delete params.uartEnableGpioPin;
	}
	try {
		await service.updateInstance(IqrfGatewayDaemonComponentName.IqrfSpi, instance, params);
		toast.success(
			i18n.t('components.config.daemon.interfaces.spi.messages.save.success'),
		);
	} catch {
		toast.error(
			i18n.t('components.config.daemon.interfaces.spi.messages.save.failed'),
		);
	}
	componentState.value = ComponentState.Ready;
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
	};
	if (
		profile.i2cEnableGpioPin !== undefined && profile.i2cEnableGpioPin !== -1 &&
		profile.spiEnableGpioPin !== undefined && profile.spiEnableGpioPin !== -1
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
	showInterfaceMenu.value = false;
}

onMounted(() => {
	getConfig();
});
</script>
