<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

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
	<div>
		<h1>{{ $t('config.controller.title') }}</h1>
		<CCard>
			<CCardBody>
				<ValidationObserver v-slot='{invalid}'>
					<CForm @submit.prevent='save'>
						<div>
							<h3>{{ $t("config.controller.form.wsServers.title") }}</h3>
							<CRow>
								<CCol md='6'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='required|ws_addr'
										:custom-messages='{
											required: $t("config.controller.errors.missing.ws_api"),
											ws_addr: $t("config.controller.errors.invalid.ws_format"),
										}'
									>
										<CInput
											v-model='config.wsServers.api'
											:label='$t("config.controller.form.wsServers.api")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='errors.join(", ")'
										/>
									</ValidationProvider>
								</CCol>
								<CCol md='6'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='required|ws_addr'
										:custom-messages='{
											required: $t("config.controller.errors.missing.ws_monitor"),
											ws_addr: $t("config.controller.errors.invalid.ws_format"),
										}'
									>
										<CInput
											v-model='config.wsServers.monitor'
											:label='$t("config.controller.form.wsServers.monitor")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='errors.join(", ")'
										/>
									</ValidationProvider>
								</CCol>
							</CRow>
						</div><hr>
						<div>
							<h3>{{ $t("config.controller.form.logger.title") }}</h3>
							<CRow>
								<CCol md='6'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='required'
										:custom-messages='{
											required: $t("config.controller.errors.missing.l_file"),
										}'
									>
										<CInput
											v-model='config.logger.filePath'
											:label='$t("config.controller.form.logger.filePath")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='errors.join(", ")'
										/>
									</ValidationProvider>
								</CCol>
								<CCol md='6'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='required'
										:custom-messages='{
											required: $t("config.controller.errors.missing.l_severity"),
										}'
									>
										<CSelect
											:value.sync='config.logger.severity'
											:options='severityOptions'
											:label='$t("config.controller.form.logger.severity")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='errors.join(", ")'
											:placeholder='$t("config.controller.errors.missing.l_severity")'
										/>
									</ValidationProvider>
								</CCol>
							</CRow>
							<label>
								<strong>{{ $t('config.controller.form.logger.sink') }}</strong>
							</label>
							<CRow>
								<CCol md='2'>
									<CInputCheckbox
										:checked.sync='config.logger.sinks.file'
										:label='$t("config.controller.form.logger.sinks.file")'
									/>
								</CCol>
								<CCol md='2'>
									<CInputCheckbox
										:checked.sync='config.logger.sinks.syslog'
										:label='$t("config.controller.form.logger.sinks.syslog")'
									/>
								</CCol>
							</CRow>
						</div><hr>
						<div>
							<h3>{{ $t("config.controller.form.factoryReset.title") }}</h3>
							<CRow>
								<CCol md='2'>
									<CInputCheckbox
										:checked.sync='config.factoryReset.coordinator'
										:label='$t("forms.fields.coordinator")'
									/>
								</CCol>
								<CCol md='2'>
									<CInputCheckbox
										:checked.sync='config.factoryReset.daemon'
										:label='$t("config.controller.form.factoryReset.daemon")'
									/>
								</CCol>
								<CCol v-if='config.factoryReset.cloudProv !== undefined' md='2'>
									<CInputCheckbox
										:checked.sync='config.factoryReset.cloudProv'
										:label='$t("config.controller.form.factoryReset.cloudProv")'
									/>
								</CCol>
								<CCol v-if='config.factoryReset.iqaros !== undefined' md='2'>
									<CInputCheckbox
										:checked.sync='config.factoryReset.iqaros'
										:label='$t("config.controller.form.factoryReset.iqaros")'
									/>
								</CCol>
								<CCol md='2'>
									<CInputCheckbox
										:checked.sync='config.factoryReset.network'
										:label='$t("config.controller.form.factoryReset.network")'
									/>
								</CCol>
								<CCol md='2'>
									<CInputCheckbox
										:checked.sync='config.factoryReset.webapp'
										:label='$t("config.controller.form.factoryReset.webapp")'
									/>
								</CCol>
							</CRow>
						</div><hr>
						<div>
							<h3>{{ $t("config.controller.form.resetButton.title") }}</h3>
							<CSelect
								:value.sync='config.resetButton.api'
								:options='apiCallOptions'
								:label='$t("config.controller.form.resetButton.api")'
							/>
							<div v-if='config.resetButton.api === "discovery"'>
								<h3>{{ $t("config.controller.form.daemonApi.discovery.title") }}</h3>
								<CRow>
									<CCol md='6'>
										<ValidationProvider
											v-slot='{errors, touched, valid}'
											rules='integer|required|between:0,239'
											:custom-messages='{
												integer: $t("forms.errors.integer"),
												required: $t("iqrfnet.networkManager.discovery.errors.maxAddr"),
												between: $t("iqrfnet.networkManager.discovery.errors.maxAddr"),
											}'
										>
											<CInput
												v-model.number='config.daemonApi.discovery.maxAddr'
												type='number'
												min='0'
												max='239'
												:label='$t("iqrfnet.networkManager.discovery.form.maxAddr")'
												:is-valid='touched ? valid : null'
												:invalid-feedback='errors.join(", ")'
											/>
										</ValidationProvider>
									</CCol>
									<CCol md='6'>
										<ValidationProvider
											v-slot='{errors, touched, valid}'
											rules='integer|required|between:0,7'
											:custom-messages='{
												integer: $t("forms.errors.integer"),
												required: $t("iqrfnet.networkManager.discovery.errors.txPower"),
												between: $t("iqrfnet.networkManager.discovery.errors.txPower"),
											}'
										>
											<CInput
												v-model.number='config.daemonApi.discovery.txPower'
												type='number'
												min='0'
												max='7'
												:label='$t("iqrfnet.networkManager.discovery.form.txPower")'
												:is-valid='touched ? valid : null'
												:invalid-feedback='errors.join(", ")'
											/>
										</ValidationProvider>
									</CCol>
								</CRow>
								<CInputCheckbox
									:checked.sync='config.daemonApi.discovery.returnVerbose'
									:label='$t("forms.fields.verbose")'
								/>
							</div>
							<div v-if='config.resetButton.api === "autoNetwork"'>
								<h3>{{ $t("config.controller.form.daemonApi.autoNetwork.title") }}</h3>
								<CRow>
									<CCol md='6'>
										<ValidationProvider
											v-slot='{errors, touched, valid}'
											rules='required|integer|between:0,3'
											:custom-messages='{
												integer: $t("forms.errors.integer"),
												required: $t("iqrfnet.networkManager.autoNetwork.errors.actionRetries"),
												between: $t("iqrfnet.networkManager.autoNetwork.errors.actionRetries"),
											}'
										>
											<CInput
												v-model.number='config.daemonApi.autoNetwork.actionRetries'
												type='number'
												min='0'
												max='3'
												:label='$t("iqrfnet.networkManager.autoNetwork.form.params.actionRetries")'
												:is-valid='touched ? valid : null'
												:invalid-feedback='errors.join(", ")'
											/>
										</ValidationProvider>
									</CCol>
									<CCol md='6'>
										<ValidationProvider
											v-slot='{errors, touched, valid}'
											rules='integer|required|between:0,7'
											:custom-messages='{
												integer: $t("forms.errors.integer"),
												required: $t("iqrfnet.networkManager.discovery.errors.txPower"),
												between: $t("iqrfnet.networkManager.discovery.errors.txPower"),
											}'
										>
											<CInput
												v-model.number='config.daemonApi.autoNetwork.discoveryTxPower'
												type='number'
												min='0'
												max='7'
												:label='$t("iqrfnet.networkManager.autoNetwork.form.params.discoveryTxPower")'
												:is-valid='touched ? valid : null'
												:invalid-feedback='errors.join(", ")'
											/>
										</ValidationProvider>
									</CCol>
								</CRow>
								<CRow>
									<CCol md='6'>
										<CInputCheckbox
											:checked.sync='config.daemonApi.autoNetwork.discoveryBeforeStart'
											:label='$t("iqrfnet.networkManager.autoNetwork.form.params.discoveryBeforeStart")'
										/>
									</CCol>
									<CCol md='6'>
										<CInputCheckbox
											:checked.sync='config.daemonApi.autoNetwork.skipDiscoveryEachWave'
											:label='$t("iqrfnet.networkManager.autoNetwork.form.params.skipDiscoveryEachWave")'
										/>
									</CCol>
								</CRow>
								<h4>
									{{ $t("iqrfnet.networkManager.autoNetwork.form.stopConditions.title") }}
								</h4>
								<CRow>
									<CCol md='6'>
										<ValidationProvider
											v-slot='{errors, touched, valid}'
											rules='integer|required|between:1,127'
											:custom-messages='{
												integer: $t("forms.errors.integer"),
												required: $t("iqrfnet.networkManager.autoNetwork.errors.emptyWaves"),
												between: $t("iqrfnet.networkManager.autoNetwork.errors.emptyWaves"),
											}'
										>
											<CInput
												v-model.number='config.daemonApi.autoNetwork.stopConditions.emptyWaves'
												type='number'
												min='1'
												max='127'
												:label='$t("iqrfnet.networkManager.autoNetwork.form.stopConditions.emptyWaves")'
												:is-valid='touched ? valid : null'
												:invalid-feedback='errors.join(", ")'
											/>
										</ValidationProvider>
									</CCol>
									<CCol md='6'>
										<ValidationProvider
											v-slot='{errors, touched, valid}'
											rules='integer|required|between:1,127'
											:custom-messages='{
												integer: $t("forms.errors.integer"),
												required: $t("iqrfnet.networkManager.autoNetwork.errors.waves"),
												between: $t("iqrfnet.networkManager.autoNetwork.errors.waves"),
											}'
										>
											<CInput
												v-model.number='config.daemonApi.autoNetwork.stopConditions.waves'
												type='number'
												min='1'
												max='127'
												:label='$t("iqrfnet.networkManager.autoNetwork.form.stopConditions.waves")'
												:is-valid='touched ? valid : null'
												:invalid-feedback='errors.join(", ")'
											/>
										</ValidationProvider>
									</CCol>
								</CRow>
								<CRow>
									<CCol md='6'>
										<CInputCheckbox
											:checked.sync='config.daemonApi.autoNetwork.stopConditions.abortOnTooManyNodesFound'
											:label='$t("iqrfnet.networkManager.autoNetwork.form.params.abortOnTooManyNodesFound")'
										/>
									</CCol>
									<CCol md='6'>
										<CInputCheckbox
											:checked.sync='config.daemonApi.autoNetwork.returnVerbose'
											:label='$t("forms.fields.verbose")'
										/>
									</CCol>
								</CRow>
							</div>
						</div><hr>
						<div>
							<h3>{{ $t("config.controller.pins.title") }}</h3>
							<CRow>
								<CCol md='4'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='integer|required'
										:custom-messages='{
											integer: $t("config.controller.pins.errors.greenLed"),
											required: $t("config.controller.pins.errors.greenLed"),
										}'
									>
										<CInput
											v-model.number='config.statusLed.greenLed'
											type='number'
											:label='$t("config.controller.pins.form.greenLed")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='errors.join(", ")'
										/>
									</ValidationProvider>
								</CCol>
								<CCol md='4'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='integer|required'
										:custom-messages='{
											integer: $t("config.controller.pins.errors.redLed"),
											required: $t("config.controller.pins.errors.redLed"),
										}'
									>
										<CInput
											v-model.number='config.statusLed.redLed'
											type='number'
											:label='$t("config.controller.pins.form.redLed")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='errors.join(", ")'
										/>
									</ValidationProvider>
								</CCol>
								<CCol md='4'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='integer|required'
										:custom-messages='{
											integer: $t("config.controller.pins.errors.button"),
											required: $t("config.controller.pins.errors.button"),
										}'
									>
										<CInput
											v-model.number='config.resetButton.button'
											type='number'
											:label='$t("config.controller.pins.form.button")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='errors.join(", ")'
										/>
									</ValidationProvider>
								</CCol>
							</CRow>
							<CInputCheckbox
								:checked.sync='useI2cPins'
								:label='$t("config.controller.pins.form.useI2c")'
							/>
							<CRow>
								<CCol md='4'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='integer|required'
										:custom-messages='{
											integer: $t("config.controller.pins.errors.sck"),
											required: $t("config.controller.pins.errors.sck"),
										}'
									>
										<CInput
											v-model.number='config.powerOff.sck'
											type='number'
											:label='$t("config.controller.pins.form.sck")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='errors.join(", ")'
											:disabled='!useI2cPins'
										/>
									</ValidationProvider>
								</CCol>
								<CCol md='4'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='integer|required'
										:custom-messages='{
											integer: $t("config.controller.pins.errors.sda"),
											required: $t("config.controller.pins.errors.sda"),
										}'
									>
										<CInput
											v-model.number='config.powerOff.sda'
											type='number'
											:label='$t("config.controller.pins.form.sda")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='errors.join(", ")'
											:disabled='!useI2cPins'
										/>
									</ValidationProvider>
								</CCol>
							</CRow>
						</div>
						<CButton color='primary' type='submit' :disabled='invalid'>
							{{ $t('forms.save') }}
						</CButton>
					</CForm>
				</ValidationObserver>
			</CCardBody>
			<CCardFooter>
				<ControllerPinConfigs @update-pin-config='updatePinConfig' />
			</CCardFooter>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardFooter, CCardHeader, CForm, CInput, CInputCheckbox, CSelect} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import ControllerPinConfigs from '@/components/Config/Controller/ControllerPinConfigs.vue';

import {between, integer, required} from 'vee-validate/dist/rules';
import {controllerErrorToast, extendedErrorToast} from '@/helpers/errorToast';
import FeatureConfigService from '@/services/FeatureConfigService';
import ServiceService from '@/services/ServiceService';

import {AxiosError, AxiosResponse} from 'axios';
import {IController, IControllerPinConfig} from '@/interfaces/Config/Controller';
import {IOption} from '@/interfaces/Coreui';
import {NavigationGuardNext, Route} from 'vue-router';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardFooter,
		CCardHeader,
		CForm,
		CInput,
		CInputCheckbox,
		CSelect,
		ControllerPinConfigs,
		ValidationObserver,
		ValidationProvider
	},
	beforeRouteEnter(to: Route, from: Route, next: NavigationGuardNext): void {
		next((vm: Vue) => {
			if (!vm.$store.getters['features/isEnabled']('iqrfGatewayController')) {
				vm.$toast.error(
					vm.$t('config.controller.messages.disabled').toString()
				);
				vm.$router.push(from.path);
			}
		});
	},
	metaInfo: {
		title: 'config.controller.description',
	},
})

/**
 * IQRF Gateway Controller configuration component
 */
export default class ControllerConfig extends Vue {
	/**
	 * @constant {Array<IOption>} apiCallOptions Array of CoreUI api call select options
	 */
	private apiCallOptions: Array<IOption> = [
		{
			value: '',
			label: this.$t('config.controller.form.resetButton.noCall').toString()
		},
		{
			value: 'autoNetwork',
			label: this.$t('iqrfnet.networkManager.autoNetwork.title').toString()
		},
		{
			value: 'discovery',
			label: this.$t('iqrfnet.networkManager.discovery.title').toString()
		}
	];

	/**
	 * @constant {string} name Name of Controller service
	 */
	private name = 'controller';

	/**
	 * @constant {Array<IOption>} severityOptions Array of CoreUI logger severity select options
	 */
	private severityOptions: Array<IOption> = [
		{
			value: 'trace',
			label: this.$t('forms.fields.messageLevel.trace').toString()
		},
		{
			value: 'debug',
			label: this.$t('forms.fields.messageLevel.debug').toString()
		},
		{
			value: 'info',
			label: this.$t('forms.fields.messageLevel.info').toString()
		},
		{
			value: 'warning',
			label: this.$t('forms.fields.messageLevel.warning').toString()
		},
		{
			value: 'error',
			label: this.$t('forms.fields.messageLevel.error').toString()
		}
	];

	/**
	 * @var {IController} config IQRF Gateway Controller configuration
	 */
	private config: IController = {
		daemonApi: {
			autoNetwork: {
				actionRetries: 1,
				discoveryTxPower: 6,
				discoveryBeforeStart: true,
				skipDiscoveryEachWave: false,
				stopConditions: {
					abortOnTooManyNodesFound: false,
					emptyWaves: 2,
					waves: 2
				},
				returnVerbose: false,
			},
			discovery: {
				maxAddr: 0,
				txPower: 6,
				returnVerbose: false,
			},
		},
		factoryReset: {
			coordinator: false,
			daemon: false,
			network: false,
			webapp: false,
		},
		logger: {
			filePath: '/var/log/iqrf-gateway-controller.log',
			severity: 'info',
			sinks: {
				file: true,
				syslog: false,
			},
		},
		powerOff: {
			sck: 0,
			sda: 0,
		},
		resetButton: {
			api: '',
			button: 2,
		},
		statusLed: {
			greenLed: 0,
			redLed: 0,
		},
		wsServers: {
			api: 'ws://localhost:1338',
			monitor: 'ws://localhost:1438',
		},
	};

	/**
	 * @var {boolean} useI2cPins Controls whether I2C pin inputs are disabled
	 */
	private useI2cPins = false;

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		extend('ws_addr', (addr: string) => {
			const regex = /^ws:\/\/.+:([1-9]|[1-9][0-9]|[1-9][0-9]{2}|[1-9][0-9]{3}|[1-4][0-9][0-1][0-5][0-1])$/;
			return regex.test(addr);
		});
	}

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		this.getConfig();
	}

	/**
	 * Retrieves configuration of IQRF Gateway Controller
	 */
	private getConfig(): void {
		if (!this.$store.getters['spinner/isEnabled']) {
			this.$store.commit('spinner/SHOW');
		}
		this.$store.commit('spinner/SHOW');
		FeatureConfigService.getConfig(this.name)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.config = response.data;
				if (response.data.powerOff !== undefined) {
					this.useI2cPins = true;
				}
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.controller.messages.fetchFailed');
				this.$router.push('/');
			});
	}

	/**
	 * Updates the configuration of IQRF Gateway Controller
	 */
	private save(): void {
		const config: IController = JSON.parse(JSON.stringify(this.config));
		if (!this.useI2cPins) {
			delete config.powerOff;
		}
		this.$store.commit('spinner/SHOW');
		FeatureConfigService.saveConfig(this.name, config)
			.then(() => {
				this.restartController();
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'config.controller.messages.saveFailed'));
	}

	/**
	 * Restarts IQRF Controller service
	 */
	private restartController(): void {
		ServiceService.restart('iqrf-gateway-controller')
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('config.controller.messages.restartSuccess').toString()
				);
			})
			.catch((error: AxiosError) => controllerErrorToast(error, 'service.messages.restartFailed'));
	}

	/**
	 * Updates Controller pin configuration
	 * @param {IControllerPinConfig} profile Controller pin configuration profile
	 */
	private updatePinConfig(profile: IControllerPinConfig): void {
		this.config.statusLed = {
			greenLed: profile.greenLed,
			redLed: profile.redLed,
		};
		this.config.resetButton.button = profile.button;
		if (profile.sck !== undefined && profile.sda !== undefined) {
			if (this.config.powerOff === undefined) {
				this.config.powerOff = {
					sck: profile.sck,
					sda: profile.sda,
				};
			} else {
				this.config.powerOff.sck = profile.sck;
				this.config.powerOff.sda = profile.sda;
			}
			this.useI2cPins = true;
		} else {
			this.config.powerOff = {
				sck: 0,
				sda: 0,
			};
			this.useI2cPins = false;
		}
	}

}
</script>
