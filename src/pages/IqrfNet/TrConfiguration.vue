<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

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
		<h1>{{ $t('iqrfnet.trConfiguration.title') }}</h1>
		<v-card v-if='loaded !== null'>
			<v-card-text>
				<ValidationObserver v-slot='{invalid}'>
					<form>
						<v-row>
							<v-col>
								<v-select
									v-model='target'
									:items='targetOptions'
									:label='$t("iqrfnet.trConfiguration.form.target")'
								/>
								<ValidationProvider
									v-if='target === "node"'
									v-slot='{errors, touched, valid}'
									rules='integer|required|between:0,239'
									:custom-messages='{
										between: $t("iqrfnet.trConfiguration.form.messages.address"),
										integer: $t("iqrfnet.trConfiguration.form.messages.address"),
										required: $t("iqrfnet.trConfiguration.form.messages.address"),
									}'
								>
									<v-text-field
										v-model.number='address'
										type='number'
										min='0'
										max='239'
										:label='$t("forms.fields.address")'
										:success='touched ? valid : null'
										:error-messages='errors'
									/>
								</ValidationProvider>
								<div v-if='target === "network"'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='required|integer|between:0,65535'
										:custom-messages='{
											required: $t("iqrfnet.networkManager.otaUpload.errors.hwpid"),
											integer: $t("forms.errors.integer"),
											between: $t("iqrfnet.networkManager.otaUpload.errors.hwpid"),
										}'
									>
										<v-text-field
											v-model.number='hwpid'
											type='number'
											min='0'
											max='65535'
											:label='$t("iqrfnet.networkManager.otaUpload.form.hwpidFilter")'
											:success='touched ? valid : null'
											:error-messages='errors'
										>
											<template #append-outer>
												<ProductModal ref='productModal' @selected-product='setSelectedProduct' />
											</template>
										</v-text-field>
									</ValidationProvider>
									<em>
										{{ $t('iqrfnet.trConfiguration.messages.targetNote') }}<br>
										{{ $t('iqrfnet.networkManager.otaUpload.messages.hwpid') }}
									</em>
								</div>
								<v-btn
									v-if='target === "node"'
									color='primary'
									@click='enumerate'
								>
									{{ $t('forms.read') }}
								</v-btn>
							</v-col>
						</v-row>
						<v-row>
							<v-col md='6'>
								<fieldset>
									<legend>{{ $t('iqrfnet.trConfiguration.form.rf') }}</legend>
									<v-select
										v-model='config.rfBand'
										:label='$t("iqrfnet.trConfiguration.form.rfBand")'
										:items='[
											{value: "443", text: $t("iqrfnet.trConfiguration.form.rfBands.443")},
											{value: "868", text: $t("iqrfnet.trConfiguration.form.rfBands.868")},
											{value: "916", text: $t("iqrfnet.trConfiguration.form.rfBands.916")},
										]'
										:disabled='true'
									/>
									<ValidationProvider
										v-slot='{valid, touched, errors}'
										:rules='rfChannelRules.rule'
										:custom-messages='rfChannelValidatorMessages'
									>
										<v-text-field
											v-model.number='config.rfChannelA'
											:label='$t("iqrfnet.trConfiguration.form.rfChannelA")'
											:success='touched ? valid : null'
											:error-messages='errors'
											type='number'
											:max='rfChannelRules.max'
											:min='rfChannelRules.min'
										/>
									</ValidationProvider>
									<ValidationProvider
										v-slot='{valid, touched, errors}'
										:rules='rfChannelRules.rule'
										:custom-messages='rfChannelValidatorMessages'
									>
										<v-text-field
											v-model.number='config.rfChannelB'
											:label='$t("iqrfnet.trConfiguration.form.rfChannelB")'
											:success='touched ? valid : null'
											:error-messages='errors'
											type='number'
											:max='rfChannelRules.max'
											:min='rfChannelRules.min'
										/>
									</ValidationProvider>
									<ValidationProvider
										v-if='dpaVersion !== null && (compareVersions(dpaVersion, "3.03", "=") || compareVersions(dpaVersion, "3.04", "="))'
										v-slot='{valid, touched, errors}'
										:rules='rfChannelRules.rule'
										:custom-messages='rfChannelValidatorMessages'
									>
										<v-text-field
											v-model.number='config.rfSubChannelA'
											:label='$t("iqrfnet.trConfiguration.form.rfSubChannelA")'
											:success='touched ? valid : null'
											:error-messages='errors'
											type='number'
											:max='rfChannelRules.max'
											:min='rfChannelRules.min'
										/>
									</ValidationProvider>
									<ValidationProvider
										v-if='dpaVersion !== null && (compareVersions(dpaVersion, "3.03", "=") || compareVersions(dpaVersion, "3.04", "="))'
										v-slot='{valid, touched, errors}'
										:rules='rfChannelRules.rule'
										:custom-messages='rfChannelValidatorMessages'
									>
										<v-text-field
											v-model.number='config.rfSubChannelB'
											:label='$t("iqrfnet.trConfiguration.form.rfSubChannelB")'
											:success='touched ? valid : null'
											:error-messages='errors'
											type='number'
											:max='rfChannelRules.max'
											:min='rfChannelRules.min'
										/>
									</ValidationProvider>
									<ValidationProvider
										v-slot='{valid, touched, errors}'
										:rules='rfChannelRules.rule'
										:custom-messages='rfChannelValidatorMessages'
									>
										<v-text-field
											v-model.number='config.rfAltDsmChannel'
											:label='$t("iqrfnet.trConfiguration.form.rfAltDsmChannel")'
											:success='touched ? valid : null'
											:error-messages='errors'
											type='number'
											:max='rfChannelRules.max'
											:min='rfChannelRules.min'
										/>
									</ValidationProvider>
									<v-alert
										v-if='address === 0 && config.stdAndLpNetwork === false'
										type='warning'
									>
										{{ $t('iqrfnet.trConfiguration.form.messages.breakInteroperability') }}
									</v-alert>
									<v-select
										v-model='config.stdAndLpNetwork'
										:label='$t("iqrfnet.trConfiguration.form.networkType")'
										:items='networkTypeOptions'
										:disabled='address !== 0 || target === "network"'
									/>
									<ValidationProvider
										v-slot='{valid, touched, errors}'
										rules='integer|between:0,7|required'
										:custom-messages='{
											between: $t("iqrfnet.trConfiguration.form.messages.txPower"),
											integer: $t("iqrfnet.trConfiguration.form.messages.txPower"),
											required: $t("iqrfnet.trConfiguration.form.messages.txPower"),
										}'
									>
										<v-text-field
											v-model.number='config.txPower'
											type='number'
											max='7'
											min='0'
											:label='$t("iqrfnet.trConfiguration.form.txPower")'
											:success='touched ? valid : null'
											:error-messages='errors'
										/>
									</ValidationProvider>
									<ValidationProvider
										v-slot='{valid, touched, errors}'
										rules='integer|between:0,64|required'
										:custom-messages='{
											between: $t("iqrfnet.trConfiguration.form.messages.rxFilter"),
											integer: $t("iqrfnet.trConfiguration.form.messages.rxFilter"),
											required: $t("iqrfnet.trConfiguration.form.messages.rxFilter"),
										}'
									>
										<v-text-field
											v-model.number='config.rxFilter'
											type='number'
											max='64'
											min='0'
											:label='$t("iqrfnet.trConfiguration.form.rxFilter")'
											:success='touched ? valid : null'
											:error-messages='errors'
										/>
									</ValidationProvider>
									<ValidationProvider
										v-slot='{valid, touched, errors}'
										rules='integer|between:1,255|required'
										:custom-messages='{
											between: $t("iqrfnet.trConfiguration.form.messages.lpRxTimeout"),
											integer: $t("iqrfnet.trConfiguration.form.messages.lpRxTimeout"),
											required: $t("iqrfnet.trConfiguration.form.messages.lpRxTimeout"),
										}'
									>
										<v-text-field
											v-model.number='config.lpRxTimeout'
											type='number'
											min='1'
											max='255'
											:label='$t("iqrfnet.trConfiguration.form.lpRxTimeout")'
											:success='touched ? valid : null'
											:error-messages='errors'
											:disabled='isCoordinator'
										/>
									</ValidationProvider>
								</fieldset>
								<fieldset>
									<legend>{{ $t('iqrfnet.trConfiguration.form.rfPgm') }}</legend>
									<v-checkbox
										v-model='config.rfPgmEnableAfterReset'
										:label='$t("iqrfnet.trConfiguration.form.rfPgmEnableAfterReset")'
									/>
									<v-checkbox
										v-model='config.rfPgmTerminateAfter1Min'
										:label='$t("iqrfnet.trConfiguration.form.rfPgmTerminateAfter1Min")'
									/>
									<v-checkbox
										v-model='config.rfPgmTerminateMcuPin'
										:label='$t("iqrfnet.trConfiguration.form.rfPgmTerminateMcuPin")'
									/>
									<v-checkbox
										v-model='config.rfPgmDualChannel'
										:label='$t("iqrfnet.trConfiguration.form.rfPgmDualChannel")'
									/>
									<v-checkbox
										v-model='config.rfPgmLpMode'
										:label='$t("iqrfnet.trConfiguration.form.rfPgmLpMode")'
									/>
									<v-checkbox
										v-model='config.rfPgmIncorrectUpload'
										:label='$t("iqrfnet.trConfiguration.form.rfPgmIncorrectUpload")'
										:disabled='true'
									/>
								</fieldset>
								<fieldset>
									<legend>{{ $t('iqrfnet.trConfiguration.security.title') }}</legend>
									<v-checkbox
										v-model='securityPassword'
										:label='$t("iqrfnet.trConfiguration.security.form.accessPassword")'
									/>
									<ValidationProvider
										v-slot='{valid, touched, errors}'
										rules='security|maxlen:16'
										:custom-messages='{
											security: $t("iqrfnet.trConfiguration.security.errors.password"),
											maxlen: $t("iqrfnet.trConfiguration.security.errors.passwordLen"),
										}'
									>
										<v-text-field
											v-if='securityPassword'
											v-model='config.accessPassword'
											:type='passwordVisible ? "text" : "password"'
											:label='$t("iqrfnet.trConfiguration.security.form.value")'
											:success='touched ? valid : null'
											:error-messages='errors'
											:append-icon='passwordVisible ? "mdi-eye" : "mdi-eye-off"'
											@click:append='passwordVisible = !passwordVisible'
										/>
									</ValidationProvider>
									<v-checkbox
										v-model='securityKey'
										:label='$t("iqrfnet.trConfiguration.security.form.userKey")'
									/>
									<ValidationProvider
										v-slot='{valid, touched, errors}'
										rules='security|maxlen:16'
										:custom-messages='{
											security: $t("iqrfnet.trConfiguration.security.errors.key"),
											maxlen: $t("iqrfnet.trConfiguration.security.errors.keyLen"),
										}'
									>
										<v-text-field
											v-if='securityKey'
											v-model='config.securityUserKey'
											:type='keyVisible ? "text" : "password"'
											:label='$t("iqrfnet.trConfiguration.security.form.value")'
											:success='touched ? valid : null'
											:error-messages='errors'
											:append-icon='keyVisible ? "mdi-eye" : "mdi-eye-off"'
											@click:append='keyVisible = !keyVisible'
										/>
									</ValidationProvider>
									<p>
										<em v-if='securityPassword || securityKey'>
											{{ $t('iqrfnet.trConfiguration.security.messages.note') }}
										</em>
									</p>
								</fieldset>
							</v-col>
							<v-col md='6'>
								<fieldset>
									<legend>{{ $t('iqrfnet.trConfiguration.form.dpa.embeddedPeripherals') }}</legend>
									<v-checkbox
										v-model='config.embPers.coordinator'
										:label='$t("iqrfnet.trConfiguration.form.embPers.coordinator")'
										:disabled='unchangeablePeripherals.includes("coordinator") || address !== 0'
									/>
									<v-checkbox
										v-model='config.embPers.node'
										:label='$t("iqrfnet.trConfiguration.form.embPers.node")'
										:disabled='unchangeablePeripherals.includes("node") || address === 0'
									/>
									<v-checkbox
										v-model='config.embPers.os'
										:label='$t("iqrfnet.trConfiguration.form.embPers.os")'
										:disabled='unchangeablePeripherals.includes("os")'
									/>
									<v-checkbox
										v-model='config.embPers.eeprom'
										:label='$t("iqrfnet.trConfiguration.form.embPers.eeprom")'
									/>
									<v-checkbox
										v-model='config.embPers.eeeprom'
										:label='$t("iqrfnet.trConfiguration.form.embPers.eeeprom")'
									/>
									<v-checkbox
										v-model='config.embPers.ram'
										:label='$t("iqrfnet.trConfiguration.form.embPers.ram")'
									/>
									<v-checkbox
										v-model='config.embPers.ledr'
										:label='$t("iqrfnet.trConfiguration.form.embPers.ledr")'
									/>
									<v-checkbox
										v-model='config.embPers.ledg'
										:label='$t("iqrfnet.trConfiguration.form.embPers.ledg")'
									/>
									<v-checkbox
										v-model='config.embPers.spi'
										:label='$t("iqrfnet.trConfiguration.form.embPers.spi")'
										:disabled='isCoordinator || (dpaVersion !== null && compareVersions(dpaVersion, "4.14", ">"))'
									/>
									<v-checkbox
										v-model='config.embPers.io'
										:label='$t("iqrfnet.trConfiguration.form.embPers.io")'
									/>
									<v-checkbox
										v-model='config.embPers.thermometer'
										:label='$t("iqrfnet.trConfiguration.form.embPers.thermometer")'
									/>
									<v-checkbox
										v-model='config.embPers.uart'
										:label='$t("iqrfnet.trConfiguration.form.embPers.uart")'
										:disabled='isCoordinator'
									/>
								</fieldset>
								<fieldset>
									<legend>{{ $t('iqrfnet.trConfiguration.form.dpa.other') }}</legend>
									<v-checkbox
										v-model='config.customDpaHandler'
										:label='$t("iqrfnet.trConfiguration.form.customDpaHandler")'
										:disabled='!dpaHandlerDetected'
									/>
									<v-checkbox
										v-model='config.dpaPeerToPeer'
										:label='$t("iqrfnet.trConfiguration.form.dpaPeerToPeer")'
										:disabled='(isCoordinator) || (dpaVersion !== null && compareVersions(dpaVersion, "4.10", "<"))'
									/>
									<v-checkbox
										v-model='config.peerToPeer'
										:label='$t("iqrfnet.trConfiguration.form.peerToPeer")'
									/>
									<v-checkbox
										v-model='config.localFrcReception'
										:label='$t("iqrfnet.trConfiguration.form.localFrcReception")'
										:disabled='isCoordinator || (dpaVersion !== null && compareVersions(dpaVersion, "4.15", "<"))'
									/>
									<v-checkbox
										v-model='config.ioSetup'
										:label='$t("iqrfnet.trConfiguration.form.ioSetup")'
									/>
									<v-checkbox
										v-model='config.dpaAutoexec'
										:label='$t("iqrfnet.trConfiguration.form.dpaAutoexec")'
										:disabled='(dpaVersion !== null && compareVersions(dpaVersion, "4.14", ">"))'
									/>
									<v-checkbox
										v-model='config.routingOff'
										:label='$t("iqrfnet.trConfiguration.form.routingOff")'
										:disabled='isCoordinator'
									/>
									<v-checkbox
										v-model='config.neverSleep'
										:label='$t("iqrfnet.trConfiguration.form.neverSleep")'
										:disabled='isCoordinator || (dpaVersion !== null && compareVersions(dpaVersion, "3.03", "<"))'
									/>
									<v-checkbox
										v-model='config.nodeDpaInterface'
										:label='$t("iqrfnet.trConfiguration.form.nodeDpaInterface")'
										:disabled='(dpaVersion !== null && compareVersions(dpaVersion, "4.00", ">="))'
									/>
									<ValidationProvider
										v-slot='{valid, touched, errors}'
										rules='required'
										:custom-messages='{
											required: $t("iqrfnet.trConfiguration.form.messages.uartBaudRate"),
										}'
									>
										<v-select
											v-model='config.uartBaudrate'
											:label='$t(address === 0 ? "iqrfnet.trConfiguration.form.uartBaudRate" : "config.daemon.interfaces.iqrfUart.form.baudRate")'
											:success='touched ? valid : null'
											:error-messages='errors'
											:placeholder='$t(address === 0 ? "iqrfnet.trConfiguration.form.messages.uartBaudRate" : "config.daemon.interfaces.iqrfUart.errors.baudRate")'
											:items='uartBaudRates'
										/>
									</ValidationProvider>
									<v-divider />
									<em>
										{{ $t('iqrfnet.trConfiguration.form.notes.dpa3Higher') }}<br>
										{{ $t('iqrfnet.trConfiguration.form.notes.dpa4Lower') }}<br>
										{{ $t('iqrfnet.trConfiguration.form.notes.dpa410') }}<br>
										{{ $t('iqrfnet.trConfiguration.form.notes.dpa414') }}<br>
										{{ $t('iqrfnet.trConfiguration.form.notes.dpa415') }}<br>
										{{ $t('iqrfnet.trConfiguration.form.notes.coordinatorOnly') }}<br>
										{{ $t('iqrfnet.trConfiguration.form.notes.nodeOnly') }}<br>
										{{ $t('iqrfnet.trConfiguration.form.notes.uart') }}<br>
										{{ $t('iqrfnet.trConfiguration.form.notes.readOnly') }}
									</em>
								</fieldset>
							</v-col>
						</v-row>
						<v-btn
							color='primary'
							:disabled='invalid'
							@click='target === "node" ? handleSubmit(address) : handleSubmit(255)'
						>
							{{ $t('forms.write') }}
						</v-btn>
					</form>
				</ValidationObserver>
			</v-card-text>
		</v-card>
		<v-dialog
			v-model='dpaEnabledNotDetected'
			width='50%'
			persistent
			no-click-animation
		>
			<v-card>
				<v-card-title>{{ $t('iqrfnet.trConfiguration.messages.modalTitle') }}</v-card-title>
				<v-card-text>{{ $t('iqrfnet.trConfiguration.messages.modalPrompt') }}</v-card-text>
				<v-card-actions>
					<v-spacer />
					<v-btn
						@click='dpaEnabledNotDetected = false'
					>
						{{ $t('forms.cancel') }}
					</v-btn>
					<v-btn
						color='warning'
						@click='disableHandler'
					>
						{{ $t('service.actions.disable') }}
					</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import ProductModal from '@/components/IqrfNet/TrConfiguration/ProductDialog.vue';

import {
	between,
	integer,
	max,
	max_value,
	min_value,
	required
} from 'vee-validate/dist/rules';
import {NetworkTarget} from '@/enums/IqrfNet/network';

import compareVersions, {compare} from 'compare-versions';
import IqrfNetService from '@/services/IqrfNetService';
import OsService from '@/services/DaemonApi/OsService';

import {ITrConfiguration} from '@/interfaces/dpa';
import {IOption} from '@/interfaces/coreui';
import {IProduct} from '@/interfaces/repository';
import {MutationPayload} from 'vuex';
import {DaemonClientState} from '@/interfaces/wsClient';

@Component({
	components: {
		ProductModal,
		ValidationObserver,
		ValidationProvider,
	},
	methods: {
		compareVersions: compareVersions.compare,
	},
	metaInfo: {
		title: 'iqrfnet.trConfiguration.title',
	},
})

/**
 * Transceiver configuration page component
 */
export default class TrConfiguration extends Vue {
	/**
	 * @var {number} address Device address
	 */
	private address = 0;

	/**
	 * @var {number} hwpid HWPID to filter devices by
	 */
	private hwpid = 65535;

	/**
	 * @var {NetworkTarget} target Read and write TR conf target
	 */
	private target = NetworkTarget.NODE;

	///// TR config /////

	/**
	 * @var {ITrConfiguration} config Device transciever configuration object
	 */
	private config: ITrConfiguration = {
		rfBand: '868', // OS RF
		rfChannelA: 52,
		rfChannelB: 2,
		rfSubChannelA: 1,
		rfSubChannelB: 1,
		rfPgmEnableAfterReset: false, // OS RFPGM
		rfPgmTerminateAfter1Min: true,
		rfPgmTerminateMcuPin: true,
		rfPgmDualChannel: true,
		rfPgmLpMode: false,
		rfPgmIncorrectUpload: false,
		embPers: { // DPA embedded peripherals
			coordinator: false,
			eeprom: false,
			eeeprom: false,
			io: false,
			ledg: false,
			ledr: false,
			node: false,
			os: false,
			pwm: false,
			ram: false,
			spi: false,
			thermometer: false,
			uart: false,
		},
		stdAndLpNetwork: true, // DPA RF
		txPower: 7,
		rxFilter: 5,
		lpRxTimeout: 6,
		rfAltDsmChannel: 0,
		customDpaHandler: false, // DPA other
		dpaPeerToPeer: false,
		peerToPeer: false,
		dpaAutoexec: false,
		localFrcReception: false,
		ioSetup: false,
		routingOff: false,
		nodeDpaInterface: false,
		neverSleep: false,
		uartBaudrate: 9600,
		accessPassword: '', // Security
		securityUserKey: ''
	};

	////////////////////////

	/**
	 * @var {boolean} dpaEnabledNotDetected Indicates whether custom DPA handler is enabled but not detected
	 */
	private dpaEnabledNotDetected = false;

	/**
	 * @var {boolean} dpaHandlerDetected Indicates whether transceiver has a custom dpa handler implemented
	 */
	private dpaHandlerDetected = false;

	/**
	 * @var {string|null} dpaVersion Version of DPA used by transceiver
	 */
	private dpaVersion: string|null = null;

	/**
	 * @var {boolean} securityPassword Controls access password field availability
	 */
	private securityPassword = false;

	/**
	 * @var {boolean} passwordVisible Controls visibility of password field
	 */
	private passwordVisible = false;

	/**
	 * @var {boolean} securityKey Controls user key field availability
	 */
	private securityKey = false;

	/**
	 * @var {boolean} keyVisible Controls visibility of key field
	 */
	private keyVisible = false;

	/**
	 * @var {boolean|null} loaded Indicates whether configuration has been loaded
	 */
	private loaded: boolean|null = null;

	/**
	 * @var {string|null} msgId Daemon api message id
	 */
	private msgId: string|null = null;

	/**
	 * @var {number} timeout Async reset timeout
	 */
	private timeout = 0;

	/**
	 * @var {boolean} expectingReset Expecting coordinator reset
	 */
	private expectingReset = false;

	/**
	 * @var {Array<string>} message Message to display
	 */
	private message: Array<string> = [];

	/**
	 * @constant {Array<string>} unchangeablePeripherals Array of peripherals whose states cannot be changed
	 */
	private unchangeablePeripherals: Array<string> = [
		'coordinator',
		'node',
		'os'
	];

	/**
	 * @constant {Array<IOption>} targetOptions Array of CoreUI conf target options
	 */
	private targetOptions: Array<IOption> = [
		{
			value: NetworkTarget.NODE,
			text: this.$t('iqrfnet.trConfiguration.form.targets.device').toString(),
		},
		{
			value: NetworkTarget.NETWORK,
			text: this.$t('iqrfnet.trConfiguration.form.targets.network').toString(),
		},
	];

	/**
	 * @constant {Array<IOption>} networkTypeOptions Array of CoreUI network type options
	 */
	private networkTypeOptions: Array<IOption> = [
		{
			value: false,
			text: this.$t('iqrfnet.trConfiguration.form.networkTypes.std').toString(),
		},
		{
			value: true,
			text: this.$t('iqrfnet.trConfiguration.form.networkTypes.stdLp').toString(),
		}
	];

	/**
	 * Checks if the device is a coordinator
   */
	get isCoordinator(): boolean {
		return this.address === 0 && this.target !== NetworkTarget.NETWORK;
	}

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * Component unwatch function
	 */
	private unwatch: CallableFunction = () => {return;};

	/**
	 * Computes rules for validation of RF channel input field
	 * @returns {Record<string, string|number>|undefined} Dictionary of rules if rfBand in configuration is valid
	 */
	get rfChannelRules(): Record<string, string|number>|undefined {
		switch (this.config.rfBand) {
			case '433':
				return {rule:'integer|between:0,16|required', min: 0, max: 16};
			case '868':
				return {rule: 'integer|between:0,67|required', min: 0, max: 67};
			case '916':
				return {rule: 'integer|between:0,255|required', min: 0, max: 255};
		}
		return undefined;
	}

	/**
	 * Computes feedback messages in case rfBand field value is invalid
	 * @returns {Record<string, string>} Dictionary of messages for applied rules
	 */
	get rfChannelValidatorMessages(): Record<string, string> {
		let message = '';
		switch (this.config.rfBand) {
			case '433':
				message = this.$t('iqrfnet.trConfiguration.form.messages.rfChannel.433').toString();
				break;
			case '868':
				message = this.$t('iqrfnet.trConfiguration.form.messages.rfChannel.868').toString();
				break;
			case '916':
				message = this.$t('iqrfnet.trConfiguration.form.messages.rfChannel.916').toString();
				break;
		}
		return {
			between: message,
			integer: message,
			required: message
		};
	}

	/**
	 * Computes CoreUI select options for UART baudrate
	 * @returns {Array<IOption>} CoreUI select options
	 */
	get uartBaudRates(): Array<IOption> {
		const uartBaudRates = [1200, 2400, 4800, 9600, 19200, 38400, 57600, 115200, 230400];
		return uartBaudRates.map((uartBaudRate) => {
			return {
				value: uartBaudRate,
				text: this.$t(`iqrfnet.trConfiguration.form.uartBaudrates.${uartBaudRate}`).toString(),
			};
		});
	}

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('min', min_value);
		extend('max', max_value);
		extend('maxlen', max);
		extend('required', required);
		extend('security', (value: string) => {
			const re = /^[ -~]{0,16}/;
			return re.test(value);
		});
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type !== 'daemonClient/SOCKET_ONMESSAGE') {
				return;
			}
			if (this.expectingReset) {
				if (mutation.payload.mType === 'iqrfRaw' && mutation.payload.data.msgId === 'async') {
					if (mutation.payload.data.rsp.rData.startsWith('00.00.ff.3f')) {
						window.clearTimeout(this.timeout);
						this.expectingReset = false;
						this.$store.dispatch('spinner/hide');
						this.$toast.info(this.message.join(' '));
					}
				}
			}
			if (mutation.payload.data.msgId !== this.msgId) {
				return;
			}
			this.$store.dispatch('daemonClient/removeMessage', this.msgId);
			if (mutation.payload.mType === 'iqmeshNetwork_WriteTrConf') {
				this.$store.dispatch('spinner/hide');
				this.handleWriteResponse(mutation.payload.data);
			} else if (mutation.payload.mType === 'iqmeshNetwork_EnumerateDevice') {
				this.$store.dispatch('spinner/hide');
				this.handleEnumerationResponse(mutation.payload);
			} else if (mutation.payload.mType === 'iqrfEmbedOs_Read') {
				this.$store.dispatch('spinner/hide');
				this.handleOsReadResponse(mutation.payload.data);
			} else if (mutation.payload.mType === 'iqrfEmbedOs_Restart') {
				this.handleRestartResponse(mutation.payload.data);
			} else if (mutation.payload.mType === 'iqrfEmbedOs_WriteCfgByte') {
				this.handleWriteByteResponse(mutation.payload.data);
			} else if (mutation.payload.mType === 'messageError') {
				this.$store.dispatch('spinner/hide');
				this.$toast.error(
					this.$t('messageError', {error: mutation.payload.data.rsp.errorStr}).toString()
				);
			}
		});
	}

	/**
	 * Initializes daemon version for error handling and reads configuration
	 */
	mounted(): void {
		if (this.$store.getters['daemonClient/isConnected']) {
			this.readOs();
		} else {
			this.unwatch = this.$store.watch(
				(_state: DaemonClientState, getter) => getter['daemonClient/isConnected'],
				(newVal: boolean, oldVal: boolean) => {
					if (!oldVal && newVal) {
						this.readOs();
						this.unwatch();
					}
				}
			);
		}
	}

	/**
	 * Vue lifecycle hook beforeDestroy
	 */
	beforeDestroy(): void {
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		this.unwatch();
		this.unsubscribe();
	}

	/**
	 * Retrieves OS information
	 */
	private readOs(): void {
		this.$store.dispatch('spinner/show', {timeout: 30000});
		OsService.read(this.address, 30000, 'iqrfnet.trConfiguration.messages.osReadFailure', () => this.msgId = null)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles OS read request response
	 * @param response Daemon API response
	 */
	private handleOsReadResponse(response): void {
		if (response.status === 0) {
			const flags = response.rsp.result.flags & 8;
			if (flags === 8) {
				this.dpaEnabledNotDetected = true;
			} else {
				this.enumerate();
			}
		} else {
			this.$store.dispatch('spinner/hide');
			this.$toast.error(
				this.$t('iqrfnet.trConfiguration.messages.osReadFailure').toString()
			);
		}
	}

	/**
	 * Disables Custom DPA handler in TR configuration
	 */
	private disableHandler(): void {
		this.dpaEnabledNotDetected = false;
		this.$store.dispatch('spinner/show', {timeout: 30000});
		OsService.disableHandler(this.address, 30000, 'iqrfnet.trConfiguration.messages.restartFailure', () => this.msgId = null)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles WriteCfgByte request response
	 * @param response Daemon API response
	 */
	private handleWriteByteResponse(response): void {
		if (response.status === 0) {
			this.restartDevice(response.rsp.nAdr, 0);
		} else {
			this.$toast.error(
				this.$t('iqrfnet.trConfiguration.messages.disableFailure').toString()
			);
		}
	}

	/**
	 * Performs device enumeration
	 */
	private enumerate(): void {
		this.$store.dispatch('spinner/show', {timeout: 60000});
		IqrfNetService.enumerateDevice(this.address, 60000, 'iqrfnet.trConfiguration.messages.readFailure', () => this.msgId = null)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles Enumeration request response
	 * @param response Daemon API response
	 */
	private handleEnumerationResponse(response): void {
		if (response.data.status === 0) {
			this.parseResponse(response);
			return;
		}
		if (response.data.status === -1 || response.data.status === 1005) {
			this.$toast.error(
				this.$t('forms.messages.deviceOffline', {address: this.address}).toString()
			);
		} else if (response.data.status === 8 || response.data.status === 1001) {
			this.$toast.error(
				this.$t('forms.messages.noDevice', {address: this.address}).toString()
			);
		} else {
			this.$toast.error(
				this.$t('iqrfnet.trConfiguration.messages.readFailure').toString()
			);
		}
		this.loaded = false;
	}

	/**
	 * Parses device enumeration response
	 * @param response Daemon API response
	 */
	private parseResponse(response: any): void {
		const rsp = response.data.rsp;
		this.config = {...this.config, ...rsp.trConfiguration};
		this.dpaHandlerDetected = rsp.osRead.flags.dpaHandlerDetected;
		this.dpaVersion = rsp.peripheralEnumeration.dpaVer;
		this.$store.dispatch('spinner/hide');
		this.$toast.success(
			this.$t('iqrfnet.trConfiguration.messages.readSuccess').toString()
		);
		this.loaded = true;
	}

	/**
	 * Updates transceiver configuration object with embed peripherals configuration and then sends WriteTrConfiguration request
	 * @param {number} address Device address
	 */
	private handleSubmit(address: number): void {
		this.message = [];
		const config = this.filterConfigToSend(JSON.parse(JSON.stringify(this.config)), address);
		this.$store.dispatch('spinner/show', {timeout: 255000});
		IqrfNetService.writeTrConfiguration(address, this.hwpid, config, 255000, 'iqrfnet.trConfiguration.messages.writeFailure', () => this.msgId = null)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Filters configuration to be sent based on DPA and target device
	 */
	private filterConfigToSend(config: ITrConfiguration, address: number): ITrConfiguration {
		if (address === 0) { // filter configuration options not available for coordinator
			// filter RF
			delete config.lpRxTimeout;
			// filter peripherals
			delete config.embPers.node;
			delete config.embPers.spi;
			delete config.embPers.uart;
			// filter DPA other section
			delete config.dpaPeerToPeer;
			delete config.localFrcReception;
			delete config.routingOff;
			delete config.neverSleep;
		} else { // filter configuration options not available for nodes
			// filter RF
			delete config.stdAndLpNetwork;
			// filter peripherals
			delete config.embPers.coordinator;
		}
		if (this.$store.getters['user/getRole'] === 'normal') { // normal user cannot change these
			delete config.embPers.coordinator;
			delete config.embPers.node;
			delete config.embPers.os;
		}
		if (this.dpaVersion !== null) {
			const dpa = this.dpaVersion;
			if (!compare(dpa, '3.03', '=') && !compare(dpa, '3.04', '=')) {
				delete config.rfSubChannelA;
				delete config.rfSubChannelB;
			}
			if (compare(dpa, '3.03', '<')) {
				delete config.neverSleep;
			}
			if (compare(dpa, '4.00', '>=')) {
				delete config.nodeDpaInterface;
			}
			if (compare(dpa, '4.10', '<')) {
				delete config.dpaPeerToPeer;
			}
			if (compare(dpa, '4.14', '>')) {
				delete config.dpaAutoexec;
				delete config.embPers.spi;
			}
			if (compare(dpa, '4.15', '<')) {
				delete config.localFrcReception;
			}
		}
		if (!this.securityPassword) {
			delete config.accessPassword;
		}
		if (!this.securityKey) {
			delete config.securityUserKey;
		}
		delete config.rfPgmIncorrectUpload;
		delete config.rfBand;
		delete config.embPers.values;
		return config;
	}

	/**
	 * Handles WriteTrConfiguration request response
	 */
	private handleWriteResponse(response): void {
		const address = response.rsp.deviceAddr;
		if (response.status === 0) {
			if (this.target === NetworkTarget.NETWORK) {
				if (response.rsp.notRespondedNodes !== undefined) {
					this.message.push(this.$t(
						'iqrfnet.trConfiguration.messages.notResponded',
						{nodes: response.rsp.notRespondedNodes.join(', ')}
					).toString());
				}
				if (response.rsp.notMatchedNodes !== undefined) {
					this.message.push(this.$t(
						'iqrfnet.trConfiguration.messages.notMatched',
						{nodes: response.rsp.notMatchedNodes.join(', ')}
					).toString());
				}
			}
			if (response.rsp.restartNeeded) {
				this.restartDevice(address, (address === 255) ? this.hwpid : null);
			} else {
				if (this.target === NetworkTarget.NETWORK) {
					this.message.unshift(this.$t('iqrfnet.trConfiguration.messages.writeSuccess').toString());
					this.$toast.info(this.message.join(' '));
				} else {
					this.$toast.info(this.$t('iqrfnet.trConfiguration.messages.writeSuccess').toString());
				}
			}
			return;
		}

		if (response.status === -1) {
			this.$toast.error(
				this.$t('forms.messages.deviceOffline', {address: address}).toString()
			);
		} else if (response.status === 8) {
			this.$toast.error(
				this.$t('forms.messages.noDevice', {address: address}).toString()
			);
		} else {
			this.$toast.error(
				this.$t('iqrfnet.trConfiguration.messages.writeFailure').toString()
			);
		}
	}

	/**
	 * Performs device restart
	 * @param {number} address Device address
	 * @param {number|null} hwpid HWPID
	 */
	private restartDevice(address: number, hwpid: number|null = null): void {
		this.$store.dispatch('spinner/show', {timeout: 30000});
		OsService.restart(address, hwpid, 30000, 'iqrfnet.trConfiguration.messages.restartFailure', () => this.msgId = null)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles restart response
	 */
	private handleRestartResponse(response): void {
		if (response.status === 0) {
			this.message.unshift(this.$t('iqrfnet.trConfiguration.messages.restartSuccess').toString());
			if (response.rsp.nAdr === 0) {
				this.expectingReset = true;
				this.timeout = window.setTimeout(() => {
					this.expectingReset = false;
					this.$store.dispatch('spinner/hide');
					this.$toast.error('iqrfnet.trConfiguration.messages.restartFailure').toString();
				}, 3000);
			} else {
				this.$store.dispatch('spinner/hide');
				this.$toast.info(this.message.join(' '));
			}
		} else {
			this.$toast.error(
				this.$t('iqrfnet.trConfiguration.messages.restartFailure').toString()
			);
		}
	}

	/**
	 * Sets HWPID from selected product
	 * @param {IProduct} product Selected product
	 */
	private setSelectedProduct(product: IProduct): void {
		this.hwpid = product.hwpid;
	}
}
</script>
