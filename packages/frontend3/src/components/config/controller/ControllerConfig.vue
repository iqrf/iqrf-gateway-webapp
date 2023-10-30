<template>
	<v-form
		ref='form'
		v-slot='{ isValid }'
		validate-on='input'
	>
		<Card>
			<template #title>
				{{ $t('pages.configuration.controller.title') }}
			</template>
			<span v-if='componentState !== ComponentState.Loading && configuration !== null'>
				<section>
					<legend>{{ $t('components.configuration.controller.form.sections.websocket') }}</legend>
					<v-row :no-gutters='display.mobile.value'>
						<v-col
							cols='12'
							md='6'
						>
							<TextInput
								v-model='configuration.wsServers.api'
								:label='$t("components.configuration.controller.form.websocket.api")'
								readonly
							>
								<template #append-inner>
									<WebsocketUrlForm
										:card-title='$t("components.configuration.controller.form.websocket.api")'
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
								:label='$t("components.configuration.controller.form.websocket.monitor")'
								readonly
							>
								<template #append-inner>
									<WebsocketUrlForm
										:card-title='$t("components.configuration.controller.form.websocket.monitor")'
										:url='configuration.wsServers.monitor'
										@edited='(val: string) => configuration!.wsServers.monitor = val'
									/>
								</template>
							</TextInput>
						</v-col>
					</v-row>
				</section>
				<section>
					<legend>{{ $t('components.configuration.controller.form.sections.logging') }}</legend>
					<v-row :no-gutters='display.mobile.value'>
						<v-col
							cols='12'
							md='6'
						>
							<TextInput
								v-model='configuration.logger.filePath'
								:label='$t("components.configuration.controller.form.logging.path")'
								:rules='[
									(v: string|null) => ValidationRules.required(v, $t("components.configuration.controller.validation.logPath")),
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
								:label='$t("components.configuration.controller.form.logging.severity")'
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
									{{ $t('components.configuration.controller.form.logging.sinks.file') }}
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
									{{ $t('components.configuration.controller.form.logging.sinks.syslog') }}
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
					<legend>{{ $t('components.configuration.controller.form.sections.factoryReset') }}</legend>
					<v-table
						class='mb-4'
						density='compact'
					>
						<tbody>
							<tr>
								<td>
									{{ $t('components.configuration.controller.form.factoryReset.coordinator') }}
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
									{{ $t('components.configuration.controller.form.factoryReset.iqaros') }}
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
									{{ $t('components.configuration.controller.form.factoryReset.network') }}
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
					<legend>{{ $t('components.configuration.controller.form.sections.button') }}</legend>
					<SelectInput
						v-model='configuration.resetButton.api'
						:items='actionOptions'
						:label='$t("components.configuration.controller.form.button.action")'
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
							<v-btn
								v-else
								color='primary'
								:disabled='true'
							>
								{{ $t('components.configuration.controller.form.button.configure') }}
							</v-btn>
						</template>
					</SelectInput>
				</section>
				<section>
					<legend>{{ $t('components.configuration.controller.form.sections.pins') }}</legend>
					<v-row :no-gutters='display.mobile.value'>
						<v-col
							cols='12'
							md='4'
						>
							<TextInput
								v-model.number='configuration.resetButton.button'
								type='number'
								:label='$t("components.configuration.controller.form.pins.button")'
								:rules='[
									(v: number|null) => ValidationRules.required(v, $t("components.configuration.controller.validation.buttonPin")),
									(v: number) => ValidationRules.integer(v, $t("components.configuration.controller.validation.buttonPin")),
								]'
								required
							/>
						</v-col>
						<v-col
							cols='12'
							md='4'
						>
							<TextInput
								v-model.number='configuration.statusLed.greenLed'
								type='number'
								:label='$t("components.configuration.controller.form.pins.greenLed")'
								:rules='[
									(v: number|null) => ValidationRules.required(v, $t("components.configuration.controller.validation.greenLed")),
									(v: number) => ValidationRules.integer(v, $t("components.configuration.controller.validation.greenLed")),
								]'
								required
							/>
						</v-col>
						<v-col
							cols='12'
							md='4'
						>
							<TextInput
								v-model.number='configuration.statusLed.redLed'
								type='number'
								:label='$t("components.configuration.controller.form.pins.redLed")'
								:rules='[
									(v: number|null) => ValidationRules.required(v, $t("components.configuration.controller.validation.redLed")),
									(v: number) => ValidationRules.integer(v, $t("components.configuration.controller.validation.redLed")),
								]'
								required
							/>
						</v-col>
					</v-row>
					<v-checkbox
						v-model='watchdogPins'
						:label='$t("components.configuration.controller.form.pins.useWatchdogPins")'
						density='compact'
					/>
					<v-row :no-gutters='display.mobile.value'>
						<v-col
							cols='12'
							md='4'
						>
							<TextInput
								v-model.number='configuration.powerOff.sck'
								type='number'
								:label='$t("components.configuration.controller.form.pins.sck")'
								:rules='watchdogPins ?
									[
										(v: number|null) => ValidationRules.required(v, $t("components.configuration.controller.validation.sck")),
										(v: number) => ValidationRules.integer(v, $t("components.configuration.controller.validation.sck")),
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
							<TextInput
								v-model.number='configuration.powerOff.sda'
								type='number'
								:label='$t("components.configuration.controller.form.pins.sda")'
								:rules='watchdogPins ?
									[
										(v: number|null) => ValidationRules.required(v, $t("components.configuration.controller.validation.sda")),
										(v: number) => ValidationRules.integer(v, $t("components.configuration.controller.validation.sda")),
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
								{{ $t('pages.configuration.controller.profiles.title') }}
							</v-btn>
						</template>
						<DeviceProfilesTable
							:show-apply='true'
							@apply='applyProfile'
						/>
					</v-menu>
				</span>
			</span>
			<template #actions>
				<v-btn
					color='primary'
					variant='elevated'
					:disabled='componentState !== ComponentState.Ready || !isValid.value'
					@click='onSubmit'
				>
					{{ $t('common.buttons.save') }}
				</v-btn>
			</template>
		</Card>
	</v-form>
</template>

<script lang='ts' setup>
import { type IqrfGatewayControllerService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	IqrfGatewayControllerAction,
	type IqrfGatewayControllerApiAutonetworkConfig,
	type IqrfGatewayControllerApiDiscoveryConfig,
	type IqrfGatewayControllerConfig,
	IqrfGatewayControllerLoggingSeverity,
	type IqrfGatewayControllerMapping,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { onMounted, type Ref, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { useDisplay } from 'vuetify';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import ControllerAutonetworkForm from '@/components/config/controller/ControllerAutonetworkForm.vue';
import ControllerDiscoveryForm from '@/components/config/controller/ControllerDiscoveryForm.vue';
import DeviceProfilesTable from '@/components/config/controller/profiles/DeviceProfilesTable.vue';
import WebsocketUrlForm from '@/components/config/WebsocketUrlForm.vue';
import SelectInput from '@/components/SelectInput.vue';
import TextInput from '@/components/TextInput.vue';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const display = useDisplay();
const i18n = useI18n();
const service: IqrfGatewayControllerService = useApiClient().getConfigServices().getIqrfGatewayControllerService();
const form: Ref<typeof VForm | null> = ref(null);
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
		title: i18n.t('components.configuration.controller.form.button.actions.none'),
		value: IqrfGatewayControllerAction.None,
	},
	{
		title: i18n.t('components.configuration.controller.form.button.actions.autonetwork'),
		value: IqrfGatewayControllerAction.Autonetwork,
	},
	{
		title: i18n.t('components.configuration.controller.form.button.actions.discovery'),
		value: IqrfGatewayControllerAction.Discovery,
	},
];
const watchdogPins: Ref<boolean> = ref(false);
const showProfileMenu: Ref<boolean> = ref(false);

async function getConfig(): Promise<void> {
	componentState.value = ComponentState.Loading;
	service.fetchConfig()
		.then((data: IqrfGatewayControllerConfig) => {
			configuration.value = data;
			watchdogPins.value = (data.powerOff.sck !== -1 && data.powerOff.sck !== -1);
			componentState.value = ComponentState.Ready;
		})
		.catch(() => {
			toast.error('TODO ERROR HANDLING');
			componentState.value = ComponentState.FetchFailed;
		});
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	if (configuration.value === null) {
		return;
	}
	const params = {...configuration.value};
	if (!watchdogPins.value) {
		params.powerOff.sck = -1;
		params.powerOff.sda = -1;
	}
	service.saveConfig(configuration.value)
		.then(() => {
			getConfig().then(() => {
				toast.success(
					i18n.t('components.configuration.controller.messages.save.success'),
				);
			});
		})
		.catch(() => toast.error('TODO ERROR HANDLING'));
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
	configuration.value.statusLed = {greenLed: profile.greenLed, redLed: profile.redLed};
	if (profile.sck !== undefined && profile.sck !== -1 && profile.sda !== undefined && profile.sda !== -1) {
		configuration.value.powerOff = {sck: profile.sck, sda: profile.sda};
		watchdogPins.value = true;
	} else {
		configuration.value.powerOff = {sck: -1, sda: -1};
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
