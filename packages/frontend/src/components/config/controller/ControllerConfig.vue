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
				{{ $t('pages.config.controller.title') }}
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
				:text='$t("components.config.controller.messages.fetch.failed")'
			/>
			<v-skeleton-loader
				class='input-skeleton-loader'
				:loading='componentState === ComponentState.Loading'
				type='text, heading, text, heading@2, text, heading@2, text, heading@3'
			>
				<v-responsive>
					<span v-if='configuration !== null'>
						<section>
							<legend class='section-legend'>
								{{ $t('components.config.controller.form.sections.websocket') }}
							</legend>
							<v-row :no-gutters='display.mobile.value'>
								<v-col
									cols='12'
									md='6'
								>
									<ITextInput
										v-model='configuration.wsServers.api'
										:label='$t("components.config.controller.form.websocket.api")'
										:prepend-inner-icon='mdiLinkVariant'
									>
										<template #append-inner>
											<WebsocketUrlForm
												:card-title='$t("components.config.controller.form.websocket.api")'
												:url='configuration.wsServers.api'
												@edited='(val: string) => configuration!.wsServers.api = val'
											/>
										</template>
									</ITextInput>
								</v-col>
								<v-col
									cols='12'
									md='6'
								>
									<ITextInput
										v-model='configuration.wsServers.monitor'
										:label='$t("components.config.controller.form.websocket.monitor")'
										:prepend-inner-icon='mdiLinkVariant'
									>
										<template #append-inner>
											<WebsocketUrlForm
												:card-title='$t("components.config.controller.form.websocket.monitor")'
												:url='configuration.wsServers.monitor'
												@edited='(val: string) => configuration!.wsServers.monitor = val'
											/>
										</template>
									</ITextInput>
								</v-col>
							</v-row>
							<legend class='section-legend'>
								{{ $t('components.config.controller.form.sections.logging') }}
							</legend>
							<v-row :no-gutters='display.mobile.value'>
								<v-col
									cols='12'
									md='6'
								>
									<ITextInput
										v-model='configuration.logger.filePath'
										:label='$t("components.config.controller.form.logging.path")'
										:prepend-inner-icon='mdiFileDocument'
										:rules='[
											(v: string|null) => ValidationRules.required(v, $t("components.config.controller.validation.logPath.required")),
										]'
										required
									/>
								</v-col>
								<v-col
									cols='12'
									md='6'
								>
									<ISelectInput
										v-model='configuration.logger.severity'
										:items='severityOptions'
										:label='$t("components.config.controller.form.logging.severity")'
										:prepend-inner-icon='mdiAlert'
									/>
								</v-col>
							</v-row>
							<v-table
								density='compact'
								class='mb-4'
							>
								<tbody>
									<tr>
										<td>
											{{ $t('components.config.controller.form.logging.sinks.file') }}
										</td>
										<td>
											<v-checkbox-btn
												v-model='configuration.logger.sinks.file'
												class='float-right'
												:disabled='[ComponentState.Reloading, ComponentState.Action].includes(componentState)'
											/>
										</td>
									</tr>
									<tr>
										<td>
											{{ $t('components.config.controller.form.logging.sinks.syslog') }}
										</td>
										<td>
											<v-checkbox-btn
												v-model='configuration.logger.sinks.syslog'
												class='float-right'
												:disabled='[ComponentState.Reloading, ComponentState.Action].includes(componentState)'
											/>
										</td>
									</tr>
								</tbody>
							</v-table>
							<legend class='section-legend'>
								{{ $t('components.config.controller.form.sections.factoryReset') }}
							</legend>
							<v-table
								class='mb-4'
								density='compact'
							>
								<tbody>
									<tr>
										<td>
											{{ $t('components.config.controller.form.factoryReset.coordinator') }}
										</td>
										<td>
											<v-checkbox-btn
												v-model='configuration.factoryReset.coordinator'
												class='float-right'
												:disabled='[ComponentState.Reloading, ComponentState.Action].includes(componentState)'
											/>
										</td>
									</tr>
									<tr>
										<td>
											{{ $t('components.gateway.information.version.iqrfGatewayDaemon') }}
										</td>
										<td>
											<v-checkbox-btn
												v-model='configuration.factoryReset.daemon'
												class='float-right'
												:disabled='[ComponentState.Reloading, ComponentState.Action].includes(componentState)'
											/>
										</td>
									</tr>
									<tr>
										<td>
											{{ $t('components.gateway.information.version.iqrfGatewayWebapp') }}
										</td>
										<td>
											<v-checkbox-btn
												v-model='configuration.factoryReset.webapp'
												class='float-right'
												:disabled='[ComponentState.Reloading, ComponentState.Action].includes(componentState)'
											/>
										</td>
									</tr>
									<tr v-if='configuration.factoryReset.iqaros !== undefined'>
										<td>
											{{ $t('components.config.controller.form.factoryReset.iqaros') }}
										</td>
										<td>
											<v-checkbox-btn
												v-model='configuration.factoryReset.iqaros'
												class='float-right'
												:disabled='[ComponentState.Reloading, ComponentState.Action].includes(componentState)'
											/>
										</td>
									</tr>
									<tr>
										<td>
											{{ $t('components.config.controller.form.factoryReset.network') }}
										</td>
										<td>
											<v-checkbox-btn
												v-model='configuration.factoryReset.network'
												class='float-right'
												:disabled='[ComponentState.Reloading, ComponentState.Action].includes(componentState)'
											/>
										</td>
									</tr>
								</tbody>
							</v-table>
							<legend class='section-legend'>
								{{ $t('components.config.controller.form.sections.button') }}
							</legend>
							<ISelectInput
								v-model='configuration.resetButton.api'
								:items='actionOptions'
								:label='$t("components.config.controller.form.button.action")'
								:prepend-inner-icon='mdiGestureTapHold'
							>
								<template #append>
									<ControllerAutonetworkForm
										v-if='configuration.resetButton.api === IqrfGatewayControllerAction.Autonetwork'
										:autonetwork-config='toRaw(configuration.daemonApi.autoNetwork)'
										@saved='onAutonetworkSave'
									/>
									<ControllerDiscoveryForm
										v-else-if='configuration.resetButton.api === IqrfGatewayControllerAction.Discovery'
										:discovery-config='toRaw(configuration.daemonApi.discovery)'
										@saved='onDiscoverySave'
									/>
								</template>
							</ISelectInput>
							<legend class='section-legend'>
								{{ $t('components.config.controller.form.sections.pins') }}
							</legend>
							<v-row :no-gutters='display.mobile.value'>
								<v-col
									cols='12'
									md='4'
								>
									<INumberInput
										v-model='configuration.resetButton.button'
										:label='$t("components.config.controller.form.pins.buttonPin")'
										:rules='[
											(v: number|null) => ValidationRules.required(v, $t("components.config.controller.validation.buttonPin.required")),
											(v: number) => ValidationRules.integer(v, $t("components.config.controller.validation.buttonPin.integer")),
										]'
										:prepend-inner-icon='mdiRadioboxBlank'
										required
									/>
								</v-col>
								<v-col
									cols='12'
									md='4'
								>
									<INumberInput
										v-model='configuration.statusLed.greenLed'
										:label='$t("components.config.controller.form.pins.greenLedPin")'
										:rules='[
											(v: number|null) => ValidationRules.required(v, $t("components.config.controller.validation.greenLedPin.required")),
											(v: number) => ValidationRules.integer(v, $t("components.config.controller.validation.greenLedPin.integer")),
										]'
										:prepend-inner-icon='mdiLedVariantOutline'
										required
									/>
								</v-col>
								<v-col
									cols='12'
									md='4'
								>
									<INumberInput
										v-model='configuration.statusLed.redLed'
										:label='$t("components.config.controller.form.pins.redLedPin")'
										:rules='[
											(v: number|null) => ValidationRules.required(v, $t("components.config.controller.validation.redLedPin.required")),
											(v: number) => ValidationRules.integer(v, $t("components.config.controller.validation.redLedPin.integer")),
										]'
										:prepend-inner-icon='mdiLedVariantOn'
										required
									/>
								</v-col>
							</v-row>
							<v-checkbox
								v-model='watchdogPins'
								:label='$t("components.config.controller.form.pins.useWatchdogPins")'
							/>
							<v-row :no-gutters='display.mobile.value'>
								<v-col
									cols='12'
									md='4'
								>
									<INumberInput
										v-model='configuration.powerOff.sck'
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
								</v-col>
								<v-col
									cols='12'
									md='4'
								>
									<INumberInput
										v-model='configuration.powerOff.sda'
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
								</v-col>
							</v-row>
						</section>
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
										{{ $t('components.config.profiles.title') }}
									</v-btn>
								</template>
								<DeviceProfilesTable
									@apply='(p: IqrfGatewayControllerMapping) => applyProfile(p)'
								/>
							</v-menu>
						</span>
					</span>
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
import {
	type IqrfGatewayControllerService,
} from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	IqrfGatewayControllerAction,
	type IqrfGatewayControllerApiAutonetworkConfig,
	type IqrfGatewayControllerApiDiscoveryConfig,
	type IqrfGatewayControllerConfig,
	IqrfGatewayControllerLoggingSeverity,
	type IqrfGatewayControllerMapping,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	INumberInput,
	ISelectInput,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import {
	mdiAlert,
	mdiChip,
	mdiFileDocument,
	mdiGestureTapHold,
	mdiLedVariantOn,
	mdiLedVariantOutline,
	mdiLinkVariant,
	mdiRadioboxBlank,
} from '@mdi/js';
import { onMounted, ref, type Ref, toRaw } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { useDisplay } from 'vuetify';
import { VForm } from 'vuetify/components';

import ControllerAutonetworkForm from '@/components/config/controller/ControllerAutonetworkForm.vue';
import ControllerDiscoveryForm from '@/components/config/controller/ControllerDiscoveryForm.vue';
import DeviceProfilesTable from '@/components/config/controller/profiles/DeviceProfilesTable.vue';
import WebsocketUrlForm from '@/components/config/WebsocketUrlForm.vue';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const display = useDisplay();
const i18n = useI18n();
const service: IqrfGatewayControllerService = useApiClient().getConfigServices().getIqrfGatewayControllerService();
const form: Ref<VForm | null> = ref(null);
const configuration: Ref<IqrfGatewayControllerConfig | null> = ref(null);
const severityOptions = [
	{
		title: i18n.t('common.labels.severity.trace'),
		value: IqrfGatewayControllerLoggingSeverity.Trace,
	},
	{
		title: i18n.t('common.labels.severity.debug'),
		value: IqrfGatewayControllerLoggingSeverity.Debug,
	},
	{
		title: i18n.t('common.labels.severity.info'),
		value: IqrfGatewayControllerLoggingSeverity.Info,
	},
	{
		title: i18n.t('common.labels.severity.warning'),
		value: IqrfGatewayControllerLoggingSeverity.Warning,
	},
	{
		title: i18n.t('common.labels.severity.error'),
		value: IqrfGatewayControllerLoggingSeverity.Error,
	},
];
const actionOptions = [
	{
		title: i18n.t('components.config.controller.form.button.actions.none'),
		value: IqrfGatewayControllerAction.None,
	},
	{
		title: i18n.t('components.config.controller.form.button.actions.autonetwork'),
		value: IqrfGatewayControllerAction.Autonetwork,
	},
	{
		title: i18n.t('components.config.controller.form.button.actions.discovery'),
		value: IqrfGatewayControllerAction.Discovery,
	},
];
const watchdogPins: Ref<boolean> = ref(false);
const showProfileMenu: Ref<boolean> = ref(false);

async function getConfig(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		configuration.value = await service.getConfig();
		watchdogPins.value = configuration.value.powerOff.sck !== -1 && configuration.value.powerOff.sck !== -1;
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error(
			i18n.t('components.config.controller.messages.fetch.failed'),
		);
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
	}
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	if (configuration.value === null) {
		return;
	}
	componentState.value = ComponentState.Action;
	const params = { ...configuration.value };
	if (!watchdogPins.value) {
		params.powerOff.sck = -1;
		params.powerOff.sda = -1;
	}
	try {
		await service.updateConfig(configuration.value);
		toast.success(
			i18n.t('components.config.controller.messages.save.success'),
		);
	} catch {
		toast.error(
			i18n.t('components.config.controller.messages.save.failed'),
		);
	}
	componentState.value = ComponentState.Ready;
}

function onAutonetworkSave(config: IqrfGatewayControllerApiAutonetworkConfig): void {
	if (configuration.value === null) {
		return;
	}
	configuration.value.daemonApi.autoNetwork = config;
}

function onDiscoverySave(config: IqrfGatewayControllerApiDiscoveryConfig): void {
	if (configuration.value === null) {
		return;
	}
	configuration.value.daemonApi.discovery = config;
}

function applyProfile(profile: IqrfGatewayControllerMapping): void {
	if (configuration.value === null) {
		return;
	}
	configuration.value.resetButton.button = profile.button;
	configuration.value.statusLed = { greenLed: profile.greenLed, redLed: profile.redLed };
	if (profile.sck !== undefined && profile.sck !== -1 && profile.sda !== undefined && profile.sda !== -1) {
		configuration.value.powerOff = { sck: profile.sck, sda: profile.sda };
		watchdogPins.value = true;
	} else {
		configuration.value.powerOff = { sck: -1, sda: -1 };
		watchdogPins.value = false;
	}
	showProfileMenu.value = false;
}

onMounted((): void => {
	getConfig();
});

</script>
