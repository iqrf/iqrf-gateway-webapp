<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
		<v-card class='mb-5'>
			<v-card-text>
				<ValidationObserver v-slot='{invalid}'>
					<v-form>
						<fieldset>
							<h5>{{ $t("config.controller.form.wsServers.title") }}</h5>
							<v-row>
								<v-col cols='12' sm='6'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='required|ws_addr'
										:custom-messages='{
											required: $t("config.controller.errors.missing.ws_api"),
											ws_addr: $t("config.controller.errors.invalid.ws_format"),
										}'
									>
										<v-text-field
											v-model='config.wsServers.api'
											:label='$t("config.controller.form.wsServers.api")'
											:success='touched ? valid : null'
											:error-messages='errors'
										/>
									</ValidationProvider>
								</v-col>
								<v-col cols='12' sm='6'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='required|ws_addr'
										:custom-messages='{
											required: $t("config.controller.errors.missing.ws_monitor"),
											ws_addr: $t("config.controller.errors.invalid.ws_format"),
										}'
									>
										<v-text-field
											v-model='config.wsServers.monitor'
											:label='$t("config.controller.form.wsServers.monitor")'
											:success='touched ? valid : null'
											:error-messages='errors'
										/>
									</ValidationProvider>
								</v-col>
							</v-row>
						</fieldset>
						<fieldset>
							<h5>{{ $t("config.controller.form.logger.title") }}</h5>
							<v-row>
								<v-col cols='12' sm='6'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='required'
										:custom-messages='{
											required: $t("config.controller.errors.missing.l_file"),
										}'
									>
										<v-text-field
											v-model='config.logger.filePath'
											:label='$t("config.controller.form.logger.filePath")'
											:success='touched ? valid : null'
											:error-messages='errors'
										/>
									</ValidationProvider>
								</v-col>
								<v-col cols='12' sm='6'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='required'
										:custom-messages='{
											required: $t("config.controller.errors.missing.l_severity"),
										}'
									>
										<v-select
											v-model='config.logger.severity'
											:items='severityOptions'
											:label='$t("config.controller.form.logger.severity")'
											:success='touched ? valid : null'
											:error-messages='errors'
										/>
									</ValidationProvider>
								</v-col>
							</v-row>
							<v-row>
								<v-col cols='12' sm='4' lg='2'>
									<v-checkbox
										v-model='config.logger.sinks.file'
										:label='$t("config.controller.form.logger.sinks.file")'
									/>
								</v-col>
								<v-col cols='12' sm='4' lg='2'>
									<v-checkbox
										v-model='config.logger.sinks.syslog'
										:label='$t("config.controller.form.logger.sinks.syslog")'
									/>
								</v-col>
							</v-row>
						</fieldset>
						<fieldset>
							<h5>{{ $t("config.controller.form.factoryReset.title") }}</h5>
							<v-row>
								<v-col cols='12' sm='4' lg='2'>
									<v-checkbox
										v-model='config.factoryReset.coordinator'
										:label='$t("forms.fields.coordinator")'
									/>
								</v-col>
								<v-col cols='12' sm='4' lg='2'>
									<v-checkbox
										v-model='config.factoryReset.daemon'
										:label='$t("config.controller.form.factoryReset.daemon")'
									/>
								</v-col>
								<v-col
									v-if='config.factoryReset.cloudProv !== undefined'
									cols='12'
									sm='4'
									lg='2'
								>
									<v-checkbox
										:checked.sync='config.factoryReset.cloudProv'
										:label='$t("config.controller.form.factoryReset.cloudProv")'
									/>
								</v-col>
								<v-col
									v-if='config.factoryReset.iqaros !== undefined'
									cols='12'
									sm='4'
									lg='2'
								>
									<v-checkbox
										:checked.sync='config.factoryReset.iqaros'
										:label='$t("config.controller.form.factoryReset.iqaros")'
									/>
								</v-col>
								<v-col cols='12' sm='4' lg='2'>
									<v-checkbox
										v-model='config.factoryReset.network'
										:label='$t("config.controller.form.factoryReset.network")'
									/>
								</v-col>
								<v-col cols='12' sm='4' lg='2'>
									<v-checkbox
										v-model='config.factoryReset.webapp'
										:label='$t("config.controller.form.factoryReset.webapp")'
									/>
								</v-col>
							</v-row>
						</fieldset>
						<fieldset>
							<h5>{{ $t("config.controller.form.resetButton.title") }}</h5>
							<v-select
								v-model='config.resetButton.api'
								:items='apiCallOptions'
								:label='$t("config.controller.form.resetButton.api")'
							/>
							<div v-if='config.resetButton.api === "discovery"'>
								<h5>{{ $t("config.controller.form.daemonApi.discovery.title") }}</h5>
								<v-row>
									<v-col cols='12' sm='6'>
										<ValidationProvider
											v-slot='{errors, touched, valid}'
											rules='integer|required|between:0,239'
											:custom-messages='{
												integer: $t("forms.errors.integer"),
												required: $t("iqrfnet.networkManager.discovery.errors.maxAddr"),
												between: $t("iqrfnet.networkManager.discovery.errors.maxAddr"),
											}'
										>
											<v-text-field
												v-model.number='config.daemonApi.discovery.maxAddr'
												type='number'
												min='0'
												max='239'
												:label='$t("iqrfnet.networkManager.discovery.form.maxAddr")'
												:success='touched ? valid : null'
												:error-messages='errors'
											/>
										</ValidationProvider>
									</v-col>
									<v-col cols='12' sm='6'>
										<ValidationProvider
											v-slot='{errors, touched, valid}'
											rules='integer|required|between:0,7'
											:custom-messages='{
												integer: $t("forms.errors.integer"),
												required: $t("iqrfnet.networkManager.discovery.errors.txPower"),
												between: $t("iqrfnet.networkManager.discovery.errors.txPower"),
											}'
										>
											<v-text-field
												v-model.number='config.daemonApi.discovery.txPower'
												type='number'
												min='0'
												max='7'
												:label='$t("iqrfnet.networkManager.discovery.form.txPower")'
												:success='touched ? valid : null'
												:error-messages='errors'
											/>
										</ValidationProvider>
									</v-col>
								</v-row>
								<v-checkbox
									v-model='config.daemonApi.discovery.returnVerbose'
									:label='$t("forms.fields.verbose")'
								/>
							</div>
							<div v-if='config.resetButton.api === "autoNetwork"'>
								<h5>{{ $t("config.controller.form.daemonApi.autoNetwork.title") }}</h5>
								<v-row>
									<v-col cols='12' sm='6'>
										<ValidationProvider
											v-slot='{errors, touched, valid}'
											rules='required|integer|between:0,3'
											:custom-messages='{
												integer: $t("forms.errors.integer"),
												required: $t("iqrfnet.networkManager.autoNetwork.errors.actionRetries"),
												between: $t("iqrfnet.networkManager.autoNetwork.errors.actionRetries"),
											}'
										>
											<v-text-field
												v-model.number='config.daemonApi.autoNetwork.actionRetries'
												type='number'
												min='0'
												max='3'
												:label='$t("iqrfnet.networkManager.autoNetwork.form.params.actionRetries")'
												:success='touched ? valid : null'
												:error-messages='errors'
											/>
										</ValidationProvider>
									</v-col>
									<v-col cols='12' sm='6'>
										<ValidationProvider
											v-slot='{errors, touched, valid}'
											rules='integer|required|between:0,7'
											:custom-messages='{
												integer: $t("forms.errors.integer"),
												required: $t("iqrfnet.networkManager.discovery.errors.txPower"),
												between: $t("iqrfnet.networkManager.discovery.errors.txPower"),
											}'
										>
											<v-text-field
												v-model.number='config.daemonApi.autoNetwork.discoveryTxPower'
												type='number'
												min='0'
												max='7'
												:label='$t("iqrfnet.networkManager.autoNetwork.form.params.discoveryTxPower")'
												:success='touched ? valid : null'
												:error-messages='errors'
											/>
										</ValidationProvider>
									</v-col>
								</v-row>
								<v-row>
									<v-col cols='12' sm='6'>
										<v-checkbox
											v-model='config.daemonApi.autoNetwork.discoveryBeforeStart'
											:label='$t("iqrfnet.networkManager.autoNetwork.form.params.discoveryBeforeStart")'
										/>
									</v-col>
									<v-col cols='12' sm='6'>
										<v-checkbox
											v-model='config.daemonApi.autoNetwork.skipDiscoveryEachWave'
											:label='$t("iqrfnet.networkManager.autoNetwork.form.params.skipDiscoveryEachWave")'
										/>
									</v-col>
								</v-row>
								<h5>{{ $t("iqrfnet.networkManager.autoNetwork.form.stopConditions") }}</h5>
								<v-row>
									<v-col cols='12' sm='6'>
										<ValidationProvider
											v-slot='{errors, touched, valid}'
											rules='integer|required|between:1,127'
											:custom-messages='{
												integer: $t("forms.errors.integer"),
												required: $t("iqrfnet.networkManager.autoNetwork.errors.emptyWaves"),
												between: $t("iqrfnet.networkManager.autoNetwork.errors.emptyWaves"),
											}'
										>
											<v-text-field
												v-model.number='config.daemonApi.autoNetwork.stopConditions.emptyWaves'
												type='number'
												min='1'
												max='127'
												:label='$t("iqrfnet.networkManager.autoNetwork.form.stopConditions.emptyWaves")'
												:success='touched ? valid : null'
												:error-messages='errors'
											/>
										</ValidationProvider>
									</v-col>
									<v-col cols='12' sm='6'>
										<ValidationProvider
											v-slot='{errors, touched, valid}'
											rules='integer|required|between:1,127'
											:custom-messages='{
												integer: $t("forms.errors.integer"),
												required: $t("iqrfnet.networkManager.autoNetwork.errors.waves"),
												between: $t("iqrfnet.networkManager.autoNetwork.errors.waves"),
											}'
										>
											<v-text-field
												v-model.number='config.daemonApi.autoNetwork.stopConditions.waves'
												type='number'
												min='1'
												max='127'
												:label='$t("iqrfnet.networkManager.autoNetwork.form.stopConditions.waves")'
												:success='touched ? valid : null'
												:error-messages='errors'
											/>
										</ValidationProvider>
									</v-col>
								</v-row>
								<v-row>
									<v-col cols='12' sm='6'>
										<v-checkbox
											v-model='config.daemonApi.autoNetwork.stopConditions.abortOnTooManyNodesFound'
											:label='$t("iqrfnet.networkManager.autoNetwork.form.params.abortOnTooManyNodesFound")'
											dense
										/>
									</v-col>
									<v-col cols='12' sm='6'>
										<v-checkbox
											v-model='config.daemonApi.autoNetwork.returnVerbose'
											:label='$t("forms.fields.verbose")'
											dense
										/>
									</v-col>
								</v-row>
							</div>
						</fieldset>
						<fieldset>
							<h5>{{ $t("config.controller.pins.title") }}</h5>
							<v-row>
								<v-col cols='12' sm='4'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='integer|required'
										:custom-messages='{
											integer: $t("config.controller.pins.errors.greenLed"),
											required: $t("config.controller.pins.errors.greenLed"),
										}'
									>
										<v-text-field
											v-model.number='config.statusLed.greenLed'
											type='number'
											:label='$t("config.controller.pins.form.greenLed")'
											:success='touched ? valid : null'
											:error-messages='errors'
										/>
									</ValidationProvider>
								</v-col>
								<v-col cols='12' sm='4'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='integer|required'
										:custom-messages='{
											integer: $t("config.controller.pins.errors.redLed"),
											required: $t("config.controller.pins.errors.redLed"),
										}'
									>
										<v-text-field
											v-model.number='config.statusLed.redLed'
											type='number'
											:label='$t("config.controller.pins.form.redLed")'
											:success='touched ? valid : null'
											:error-messages='errors'
										/>
									</ValidationProvider>
								</v-col>
								<v-col cols='12' sm='4'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='integer|required'
										:custom-messages='{
											integer: $t("config.controller.pins.errors.button"),
											required: $t("config.controller.pins.errors.button"),
										}'
									>
										<v-text-field
											v-model.number='config.resetButton.button'
											type='number'
											:label='$t("config.controller.pins.form.button")'
											:success='touched ? valid : null'
											:error-messages='errors'
										/>
									</ValidationProvider>
								</v-col>
							</v-row>
							<v-checkbox
								v-model='useI2cPins'
								:label='$t("config.controller.pins.form.useI2c")'
							/>
							<v-row align='center'>
								<v-col cols='12' sm='4'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='integer|required'
										:custom-messages='{
											integer: $t("config.controller.pins.errors.sck"),
											required: $t("config.controller.pins.errors.sck"),
										}'
									>
										<v-text-field
											v-model.number='config.powerOff.sck'
											type='number'
											:label='$t("config.controller.pins.form.sck")'
											:success='touched ? valid : null'
											:error-messages='errors'
											:disabled='!useI2cPins'
										/>
									</ValidationProvider>
								</v-col>
								<v-col cols='12' sm='4'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='integer|required'
										:custom-messages='{
											integer: $t("config.controller.pins.errors.sda"),
											required: $t("config.controller.pins.errors.sda"),
										}'
									>
										<v-text-field
											v-model.number='config.powerOff.sda'
											type='number'
											:label='$t("config.controller.pins.form.sda")'
											:success='touched ? valid : null'
											:error-messages='errors'
											:disabled='!useI2cPins'
										/>
									</ValidationProvider>
								</v-col>
							</v-row>
						</fieldset>
						<v-btn
							class='mt-4'
							color='primary'
							:disabled='invalid'
							@click='save'
						>
							{{ $t('forms.save') }}
						</v-btn>
					</v-form>
				</ValidationObserver>
			</v-card-text>
		</v-card>
		<v-card>
			<v-card-text>
				<ControllerPinConfigs @update-pin-config='updatePinConfig' />
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import ControllerPinConfigs from '@/components/Config/Controller/ControllerPinConfigs.vue';

import {between, integer, required} from 'vee-validate/dist/rules';
import {controllerErrorToast, extendedErrorToast} from '@/helpers/errorToast';
import FeatureConfigService from '@/services/FeatureConfigService';

import {AxiosError, AxiosResponse} from 'axios';
import {IController, IControllerPinConfig} from '@/interfaces/Config/Controller';
import {NavigationGuardNext, Route} from 'vue-router';
import { ISelectItem } from '@/interfaces/Vuetify';
import {useApiClient} from '@/services/ApiClient';

@Component({
	components: {
		ControllerPinConfigs,
		ValidationObserver,
		ValidationProvider
	},
	beforeRouteEnter(_to: Route, from: Route, next: NavigationGuardNext): void {
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
	 * @constant {string} name Name of Controller service
	 */
	private readonly name = 'controller';

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
	 * @constant {Array<IOption>} apiCallOptions API call options
	 */
	private readonly apiCallOptions: Array<ISelectItem> = [
		{
			value: '',
			text: this.$t('config.controller.form.resetButton.noCall').toString(),
		},
		{
			value: 'autoNetwork',
			text: this.$t('iqrfnet.networkManager.autoNetwork.title').toString(),
		},
		{
			value: 'discovery',
			text: this.$t('iqrfnet.networkManager.discovery.title').toString(),
		}
	];

	/**
	 * Computes severity options
	 */
	get severityOptions(): Array<ISelectItem> {
		const severities = ['trace', 'debug', 'info', 'warning', 'error'];
		return severities.map((severity: string) => ({
			value: severity,
			text: this.$t(`forms.fields.messageLevel.${severity}`).toString()
		}));
	}

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
		useApiClient().getServiceService().restart('iqrf-gateway-controller')
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
