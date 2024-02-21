<template>
	<v-form
		ref='form'
		v-slot='{ isValid }'
		:disabled='[ComponentState.Reloading, ComponentState.Saving].includes(componentState)'
	>
		<Card>
			<template #title>
				{{ $t('pages.configuration.daemon.interfaces.uart.title') }}
			</template>
			<template #titleActions>
				<v-tooltip
					location='bottom'
				>
					<template #activator='{ props }'>
						<v-btn
							v-bind='props'
							color='white'
							:icon='mdiReload'
							@click='getConfig'
						/>
					</template>
					{{ $t('common.buttons.reload') }}
				</v-tooltip>
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
							:label='$t("components.configuration.daemon.instance")'
							:rules='[
								(v: string|null) => ValidationRules.required(v, $t("components.configuration.daemon.validation.instanceMissing")),
							]'
							required
						/>
						<TextInput
							v-model='config.IqrfInterface'
							:label='$t("components.configuration.daemon.interfaces.interface")'
							:rules='[
								(v: string|null) => ValidationRules.required(v, $t("components.configuration.daemon.interfaces.validation.interfaceMissing")),
							]'
							required
						/>
						<v-checkbox
							v-model='config.uartReset'
							:label='$t("components.configuration.daemon.interfaces.uart.uartReset")'
							density='compact'
						/>
						<v-row :no-gutters='display.mobile.value'>
							<v-col
								cols='12'
								md='4'
							>
								<TextInput
									v-model.number='config.powerEnableGpioPin'
									type='number'
									:label='$t("components.configuration.daemon.interfaces.powerPin")'
									:rules='[
										(v: number|null) => ValidationRules.required(v, $t("components.configuration.daemon.interfaces.validation.powerPinMissing")),
										(v: number) => ValidationRules.integer(v, $t("components.configuration.daemon.interfaces.validation.powerPinInvalid")),
									]'
									required
								/>
							</v-col>
							<v-col
								cols='12'
								md='4'
							>
								<TextInput
									v-model.number='config.busEnableGpioPin'
									type='number'
									:label='$t("components.configuration.daemon.interfaces.busPin")'
									:rules='[
										(v: number|null) => ValidationRules.required(v, $t("components.configuration.daemon.interfaces.validation.busPinMissing")),
										(v: number) => ValidationRules.integer(v, $t("components.configuration.daemon.interfaces.validation.busPinInvalid")),
									]'
									required
								/>
							</v-col>
							<v-col
								cols='12'
								md='4'
							>
								<TextInput
									v-model.number='config.pgmSwitchGpioPin'
									type='number'
									:label='$t("components.configuration.daemon.interfaces.pgmPin")'
									:rules='[
										(v: number|null) => ValidationRules.required(v, $t("components.configuration.daemon.interfaces.validation.pgmPinMissing")),
										(v: number) => ValidationRules.integer(v, $t("components.configuration.daemon.interfaces.validation.pgmPinInvalid")),
									]'
									required
								/>
							</v-col>
						</v-row>
						<v-checkbox
							v-model='interfacePins'
							:label='$t("components.configuration.daemon.interfaces.interfacePins")'
							density='compact'
						/>
						<v-row :no-gutters='display.mobile.value'>
							<v-col
								cols='12'
								md='4'
							>
								<TextInput
									v-model.number='config.i2cEnableGpioPin'
									type='number'
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
							</v-col>
							<v-col
								cols='12'
								md='4'
							>
								<TextInput
									v-model.number='config.spiEnableGpioPin'
									type='number'
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
							</v-col>
							<v-col
								cols='12'
								md='4'
							>
								<TextInput
									v-model.number='config.uartEnableGpioPin'
									type='number'
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
							</v-col>
						</v-row>
						<SelectInput
							v-model='config.baudRate'
							:items='baudRateOptions'
							:label='$t("components.configuration.daemon.interfaces.uart.baudRate")'
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
							{{ $t('components.configuration.daemon.interfaces.profiles.uart') }}
						</v-btn>
					</template>
					<DeviceProfileTable
						:mapping-type='MappingType.UART'
						@apply='applyProfile'
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
							{{ $t('components.configuration.daemon.interfaces.uart.devices') }}
						</v-btn>
					</template>
					<InterfacePorts
						:interface-type='IqrfInterfaceType.UART'
						@apply='applyInterface'
					/>
				</v-menu>
			</span>
			<template #actions>
				<v-btn
					color='primary'
					variant='elevated'
					:disabled='!isValid.value || [ComponentState.Loading, ComponentState.Reloading, ComponentState.Saving].includes(componentState)'
					@click='onSubmit'
				>
					{{ $t('common.buttons.save') }}
				</v-btn>
			</template>
		</Card>
	</v-form>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	IqrfGatewayDaemonComponentName,
	type IqrfGatewayDaemonUart,
	type IqrfGatewayDaemonComponent,
	type IqrfGatewayDaemonMapping,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { MappingType } from '@iqrf/iqrf-gateway-webapp-client/types/Config/Mapping';
import { IqrfInterfaceType } from '@iqrf/iqrf-gateway-webapp-client/types/Iqrf';
import { mdiReload } from '@mdi/js';
import { computed, onMounted, type Ref, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { useDisplay } from 'vuetify';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import InterfacePorts from '@/components/config/daemon/interfaces/InterfacePorts.vue';
import DeviceProfileTable from '@/components/config/daemon/interfaces/profiles/DeviceProfileTable.vue';
import SelectInput from '@/components/SelectInput.vue';
import TextInput from '@/components/TextInput.vue';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const i18n = useI18n();
const display = useDisplay();
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const form: Ref<typeof VForm | null> = ref(null);
const config: Ref<IqrfGatewayDaemonUart | null> = ref(null);
let instance = '';
const interfacePins: Ref<boolean> = ref(false);
const showIntefaceMenu: Ref<boolean> = ref(false);
const showProfileMenu: Ref<boolean> = ref(false);
const baudRateOptions = computed(() => {
	const items: number[] = [1200, 2400, 4800, 9600, 19200, 38400, 57600, 115200, 230400];
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
	service.getComponent(IqrfGatewayDaemonComponentName.IqrfUart)
		.then((response: IqrfGatewayDaemonComponent<IqrfGatewayDaemonComponentName.IqrfUart>): void => {
			config.value = response.instances[0] ?? null;
			if (config.value !== null) {
				instance = config.value.instance;
				componentState.value = ComponentState.Ready;
			}
		})
		.catch(() => toast.error('TODO FETCH ERROR HANDLING'));
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
	service.updateInstance(IqrfGatewayDaemonComponentName.IqrfUart, instance, params)
		.then(() => {
			getConfig().then(() => {
				toast.success(
					i18n.t('components.configuration.daemon.interfaces.uart.messages.save.success'),
				);
			});
		})
		.catch(() => toast.error('TODO SAVE ERROR HANDLING'));
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
