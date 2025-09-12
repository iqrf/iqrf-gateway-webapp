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
		@submit.prevent='onSubmit()'
	>
		<Card>
			<template #title>
				{{ $t('pages.config.controller.title') }}
			</template>
			<span v-if='componentState !== ComponentState.Loading && configuration !== null'>
				<section>
					<legend>{{ $t('components.config.controller.form.sections.websocket') }}</legend>
					<v-row :no-gutters='display.mobile.value'>
						<v-col
							cols='12'
							md='6'
						>
							<TextInput
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
							</TextInput>
						</v-col>
						<v-col
							cols='12'
							md='6'
						>
							<TextInput
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
							</TextInput>
						</v-col>
					</v-row>
				</section>
				<section>
					<legend>{{ $t('components.config.controller.form.sections.logging') }}</legend>
					<v-row :no-gutters='display.mobile.value'>
						<v-col
							cols='12'
							md='6'
						>
							<TextInput
								v-model='configuration.logger.filePath'
								:label='$t("components.config.controller.form.logging.path")'
								:prepend-inner-icon='mdiFileDocument'
								:rules='[
									(v: string|null) => ValidationRules.required(v, $t("components.config.controller.validation.logPath")),
								]'
								required
							/>
						</v-col>
						<v-col
							cols='12'
							md='6'
						>
							<SelectInput
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
									/>
								</td>
							</tr>
						</tbody>
					</v-table>
				</section>
				<section>
					<legend>{{ $t('components.config.controller.form.sections.factoryReset') }}</legend>
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
									/>
								</td>
							</tr>
						</tbody>
					</v-table>
				</section>
				<section>
					<legend>{{ $t('components.config.controller.form.sections.button') }}</legend>
					<SelectInput
						v-model='configuration.resetButton.api'
						:items='actionOptions'
						:label='$t("components.config.controller.form.button.action")'
						:prepend-inner-icon='mdiGestureTapHold'
					>
						<template #append>
							<ControllerAutonetworkForm
								v-if='configuration.resetButton.api === IqrfGatewayControllerAction.Autonetwork'
								:autonetwork-config='configuration.daemonApi.autoNetwork'
								@saved='onAutonetworkSave'
							/>
							<ControllerDiscoveryForm
								v-else-if='configuration.resetButton.api === IqrfGatewayControllerAction.Discovery'
								:discovery-config='configuration.daemonApi.discovery'
								@saved='onDiscoverySave'
							/>
							<ControllerActionConfigureBtn
								v-else
								:disabled='true'
							/>
						</template>
					</SelectInput>
				</section>
				<section>
					<legend>{{ $t('components.config.controller.form.sections.pins') }}</legend>
					<v-row :no-gutters='display.mobile.value'>
						<v-col
							cols='12'
							md='4'
						>
							<NumberInput
								v-model.number='configuration.resetButton.button'
								:label='$t("components.config.controller.form.pins.button")'
								:rules='[
									(v: number|null) => ValidationRules.required(v, $t("components.config.controller.validation.buttonPin")),
									(v: number) => ValidationRules.integer(v, $t("components.config.controller.validation.buttonPin")),
								]'
								required
							/>
						</v-col>
						<v-col
							cols='12'
							md='4'
						>
							<NumberInput
								v-model.number='configuration.statusLed.greenLed'
								:label='$t("components.config.controller.form.pins.greenLed")'
								:rules='[
									(v: number|null) => ValidationRules.required(v, $t("components.config.controller.validation.greenLed")),
									(v: number) => ValidationRules.integer(v, $t("components.config.controller.validation.greenLed")),
								]'
								required
							/>
						</v-col>
						<v-col
							cols='12'
							md='4'
						>
							<NumberInput
								v-model.number='configuration.statusLed.redLed'
								:label='$t("components.config.controller.form.pins.redLed")'
								:rules='[
									(v: number|null) => ValidationRules.required(v, $t("components.config.controller.validation.redLed")),
									(v: number) => ValidationRules.integer(v, $t("components.config.controller.validation.redLed")),
								]'
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
							<NumberInput
								v-model.number='configuration.powerOff.sck'
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
						</v-col>
						<v-col
							cols='12'
							md='4'
						>
							<NumberInput
								v-model.number='configuration.powerOff.sda'
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
			<template #actions>
				<CardActionBtn
					:action='Action.Edit'
					:disabled='componentState !== ComponentState.Ready || !isValid.value'
					type='submit'
				/>
			</template>
		</Card>
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
import { ValidationRules } from '@iqrf/iqrf-vue-ui';
import {
	mdiAlert,
	mdiFileDocument,
	mdiGestureTapHold,
	mdiLinkVariant,
} from '@mdi/js';
import { onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { useDisplay } from 'vuetify';
import { VForm } from 'vuetify/components';

import ControllerActionConfigureBtn from '@/components/config/controller/ControllerActionConfigureBtn.vue';
import ControllerAutonetworkForm from '@/components/config/controller/ControllerAutonetworkForm.vue';
import ControllerDiscoveryForm from '@/components/config/controller/ControllerDiscoveryForm.vue';
import DeviceProfilesTable from '@/components/config/controller/profiles/DeviceProfilesTable.vue';
import WebsocketUrlForm from '@/components/config/WebsocketUrlForm.vue';
import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import NumberInput from '@/components/layout/form/NumberInput.vue';
import SelectInput from '@/components/layout/form/SelectInput.vue';
import TextInput from '@/components/layout/form/TextInput.vue';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';
import { Action } from '@/types/Action';
import { ComponentState } from '@/types/ComponentState';

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
	componentState.value = ComponentState.Loading;
	try {
		configuration.value = await service.getConfig();
		watchdogPins.value = configuration.value.powerOff.sck !== -1 && configuration.value.powerOff.sck !== -1;
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error('TODO ERROR HANDLING');
		componentState.value = ComponentState.FetchFailed;
	}
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	if (configuration.value === null) {
		return;
	}
	const params = { ...configuration.value };
	if (!watchdogPins.value) {
		params.powerOff.sck = -1;
		params.powerOff.sda = -1;
	}
	try {
		await service.updateConfig(configuration.value);
		await getConfig();
		toast.success(
			i18n.t('components.config.controller.messages.save.success'),
		);
	} catch {
		toast.error('TODO ERROR HANDLING');
	}
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

<style lang='scss' scoped>
legend {
	font-size: large;
	font-weight: 300;
	padding-bottom: 1em;
}

</style>
