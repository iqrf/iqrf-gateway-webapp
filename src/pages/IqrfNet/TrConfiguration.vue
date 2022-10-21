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
		<CCard v-if='loaded !== null'>
			<CCardBody>
				<ValidationObserver v-slot='{invalid}'>
					<CForm>
						<CRow>
							<CCol>
								<CSelect
									:value.sync='target'
									:options='targetOptions'
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
									<CInput
										v-model.number='address'
										type='number'
										min='0'
										max='239'
										:label='$t("forms.fields.address")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
									/>
								</ValidationProvider>
								<div v-if='target === "network"'>
									<CButton
										style='float: right;'
										color='primary'
										size='sm'
										@click='showProductModal'
									>
										{{ $t('iqrfnet.product.browse') }}
									</CButton>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='required|integer|between:0,65535'
										:custom-messages='{
											required: $t("iqrfnet.networkManager.otaUpload.errors.hwpid"),
											integer: $t("forms.errors.integer"),
											between: $t("iqrfnet.networkManager.otaUpload.errors.hwpid"),
										}'
									>
										<CInput
											v-model.number='hwpid'
											type='number'
											min='0'
											max='65535'
											:label='$t("iqrfnet.networkManager.otaUpload.form.hwpidFilter")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='errors.join(", ")'
										/>
									</ValidationProvider>
									<em>
										{{ $t('iqrfnet.trConfiguration.messages.targetNote') }}<br>
										{{ $t('iqrfnet.networkManager.otaUpload.messages.hwpid') }}
									</em>
								</div>
								<CButton
									v-if='target === "node"'
									color='primary'
									@click='enumerate'
								>
									{{ $t('forms.read') }}
								</CButton>
							</CCol>
						</CRow><hr>
						<CRow>
							<CCol md='6'>
								<h2>{{ $t('iqrfnet.trConfiguration.form.rf') }}</h2>
								<CSelect
									v-model='config.rfBand'
									:label='$t("iqrfnet.trConfiguration.form.rfBand")'
									:options='[
										{value: "443", label: $t("iqrfnet.trConfiguration.form.rfBands.443")},
										{value: "868", label: $t("iqrfnet.trConfiguration.form.rfBands.868")},
										{value: "916", label: $t("iqrfnet.trConfiguration.form.rfBands.916")},
									]'
									disabled='true'
								/>
								<ValidationProvider
									v-slot='{valid, touched, errors}'
									:rules='rfChannelRules.rule'
									:custom-messages='rfChannelValidatorMessages'
								>
									<CInput
										v-model.number='config.rfChannelA'
										:label='$t("iqrfnet.trConfiguration.form.rfChannelA")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
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
									<CInput
										v-model.number='config.rfChannelB'
										:label='$t("iqrfnet.trConfiguration.form.rfChannelB")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
										type='number'
										:max='rfChannelRules.max'
										:min='rfChannelRules.min'
									/>
								</ValidationProvider>
								<ValidationProvider
									v-if='compareDpaVersion("3.03", "=") || compareDpaVersion("3.04", "=")'
									v-slot='{valid, touched, errors}'
									:rules='rfChannelRules.rule'
									:custom-messages='rfChannelValidatorMessages'
								>
									<CInput
										v-model.number='config.rfSubChannelA'
										:label='$t("iqrfnet.trConfiguration.form.rfSubChannelA")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
										type='number'
										:max='rfChannelRules.max'
										:min='rfChannelRules.min'
									/>
								</ValidationProvider>
								<ValidationProvider
									v-if='compareDpaVersion("3.03", "=") || compareDpaVersion("3.04", "=")'
									v-slot='{valid, touched, errors}'
									:rules='rfChannelRules.rule'
									:custom-messages='rfChannelValidatorMessages'
								>
									<CInput
										v-model.number='config.rfSubChannelB'
										:label='$t("iqrfnet.trConfiguration.form.rfSubChannelB")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
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
									<CInput
										v-model.number='config.rfAltDsmChannel'
										:label='$t("iqrfnet.trConfiguration.form.rfAltDsmChannel")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
										type='number'
										:max='rfChannelRules.max'
										:min='rfChannelRules.min'
									/>
								</ValidationProvider>
								<CAlert
									v-if='address === 0 && config.stdAndLpNetwork === false'
									color='warning'
								>
									{{ $t('iqrfnet.trConfiguration.form.messages.breakInteroperability') }}
								</CAlert>
								<CSelect
									:label='$t("iqrfnet.trConfiguration.form.networkType")'
									:value.sync='config.stdAndLpNetwork'
									:options='networkTypeOptions'
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
									<CInput
										v-model.number='config.txPower'
										type='number'
										max='7'
										min='0'
										:label='$t("iqrfnet.trConfiguration.form.txPower")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
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
									<CInput
										v-model.number='config.rxFilter'
										type='number'
										max='64'
										min='0'
										:label='$t("iqrfnet.trConfiguration.form.rxFilter")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
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
									<CInput
										v-model.number='config.lpRxTimeout'
										type='number'
										min='1'
										max='255'
										:label='$t("iqrfnet.trConfiguration.form.lpRxTimeout")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
										:disabled='isCoordinator'
									/>
								</ValidationProvider>
								<h2>{{ $t('iqrfnet.trConfiguration.form.rfPgm') }}</h2>
								<CInputCheckbox
									:checked.sync='config.rfPgmEnableAfterReset'
									:label='$t("iqrfnet.trConfiguration.form.rfPgmEnableAfterReset")'
								/>
								<CInputCheckbox
									:checked.sync='config.rfPgmTerminateAfter1Min'
									:label='$t("iqrfnet.trConfiguration.form.rfPgmTerminateAfter1Min")'
								/>
								<CInputCheckbox
									:checked.sync='config.rfPgmTerminateMcuPin'
									:label='$t("iqrfnet.trConfiguration.form.rfPgmTerminateMcuPin")'
								/>
								<CInputCheckbox
									:checked.sync='config.rfPgmDualChannel'
									:label='$t("iqrfnet.trConfiguration.form.rfPgmDualChannel")'
								/>
								<CInputCheckbox
									:checked.sync='config.rfPgmLpMode'
									:label='$t("iqrfnet.trConfiguration.form.rfPgmLpMode")'
								/>
								<CInputCheckbox
									:checked.sync='config.rfPgmIncorrectUpload'
									:label='$t("iqrfnet.trConfiguration.form.rfPgmIncorrectUpload")'
									disabled='true'
								/>
								<h2>{{ $t('iqrfnet.trConfiguration.security.title') }}</h2>
								<CInputCheckbox
									:checked.sync='securityPassword'
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
									<CInput
										v-if='securityPassword'
										v-model='config.accessPassword'
										:type='passwordVisible ? "text" : "password"'
										:label='$t("iqrfnet.trConfiguration.security.form.value")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
									>
										<template #append-content>
											<span @click='passwordVisible = !passwordVisible'>
												<FontAwesomeIcon
													:icon='(passwordVisible ? ["far", "eye-slash"] : ["far", "eye"])'
												/>
											</span>
										</template>
									</CInput>
								</ValidationProvider>
								<CInputCheckbox
									:checked.sync='securityKey'
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
									<CInput
										v-if='securityKey'
										v-model='config.securityUserKey'
										:type='keyVisible ? "text" : "password"'
										:label='$t("iqrfnet.trConfiguration.security.form.value")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
									>
										<template #append-content>
											<span @click='keyVisible = !keyVisible'>
												<FontAwesomeIcon
													:icon='(keyVisible ? ["far", "eye-slash"] : ["far", "eye"])'
												/>
											</span>
										</template>
									</CInput>
								</ValidationProvider>
								<p>
									<em v-if='securityPassword || securityKey'>
										{{ $t('iqrfnet.trConfiguration.security.messages.note') }}
									</em>
								</p>
							</CCol>
							<CCol md='6'>
								<h2>{{ $t('iqrfnet.trConfiguration.form.dpa.embeddedPeripherals') }}</h2>
								<CInputCheckbox
									:checked.sync='config.embPers.coordinator'
									:label='$t("iqrfnet.trConfiguration.form.embPers.coordinator")'
									:disabled='unchangeablePeripherals.includes("coordinator") || address !== 0'
								/>
								<CInputCheckbox
									:checked.sync='config.embPers.node'
									:label='$t("iqrfnet.trConfiguration.form.embPers.node")'
									:disabled='unchangeablePeripherals.includes("node") || address === 0'
								/>
								<CInputCheckbox
									:checked.sync='config.embPers.os'
									:label='$t("iqrfnet.trConfiguration.form.embPers.os")'
									:disabled='unchangeablePeripherals.includes("os")'
								/>
								<CInputCheckbox
									:checked.sync='config.embPers.eeprom'
									:label='$t("iqrfnet.trConfiguration.form.embPers.eeprom")'
								/>
								<CInputCheckbox
									:checked.sync='config.embPers.eeeprom'
									:label='$t("iqrfnet.trConfiguration.form.embPers.eeeprom")'
								/>
								<CInputCheckbox
									:checked.sync='config.embPers.ram'
									:label='$t("iqrfnet.trConfiguration.form.embPers.ram")'
								/>
								<CInputCheckbox
									:checked.sync='config.embPers.ledr'
									:label='$t("iqrfnet.trConfiguration.form.embPers.ledr")'
								/>
								<CInputCheckbox
									:checked.sync='config.embPers.ledg'
									:label='$t("iqrfnet.trConfiguration.form.embPers.ledg")'
								/>
								<CInputCheckbox
									:checked.sync='config.embPers.spi'
									:label='$t("iqrfnet.trConfiguration.form.embPers.spi")'
									:disabled='isCoordinator || compareDpaVersion("4.14", ">")'
								/>
								<CInputCheckbox
									:checked.sync='config.embPers.io'
									:label='$t("iqrfnet.trConfiguration.form.embPers.io")'
								/>
								<CInputCheckbox
									:checked.sync='config.embPers.thermometer'
									:label='$t("iqrfnet.trConfiguration.form.embPers.thermometer")'
								/>
								<CInputCheckbox
									:checked.sync='config.embPers.uart'
									:label='$t("iqrfnet.trConfiguration.form.embPers.uart")'
									:disabled='isCoordinator'
								/>
								<h2>{{ $t('iqrfnet.trConfiguration.form.dpa.other') }}</h2>
								<CInputCheckbox
									:checked.sync='config.customDpaHandler'
									:label='$t("iqrfnet.trConfiguration.form.customDpaHandler")'
									:disabled='!dpaHandlerDetected'
								/>
								<CInputCheckbox
									:checked.sync='config.dpaPeerToPeer'
									:label='$t("iqrfnet.trConfiguration.form.dpaPeerToPeer")'
									:disabled='isCoordinator || compareDpaVersion("4.10", "<")'
								/>
								<CInputCheckbox
									:checked.sync='config.peerToPeer'
									:label='$t("iqrfnet.trConfiguration.form.peerToPeer")'
								/>
								<CInputCheckbox
									:checked.sync='config.localFrcReception'
									:label='$t("iqrfnet.trConfiguration.form.localFrcReception")'
									:disabled='isCoordinator || compareDpaVersion("4.15", "<")'
								/>
								<CInputCheckbox
									:checked.sync='config.ioSetup'
									:label='$t("iqrfnet.trConfiguration.form.ioSetup")'
								/>
								<CInputCheckbox
									:checked.sync='config.dpaAutoexec'
									:label='$t("iqrfnet.trConfiguration.form.dpaAutoexec")'
									:disabled='compareDpaVersion("4.14", ">")'
								/>
								<CInputCheckbox
									:checked.sync='config.routingOff'
									:label='$t("iqrfnet.trConfiguration.form.routingOff")'
									:disabled='isCoordinator'
								/>
								<CInputCheckbox
									:checked.sync='config.neverSleep'
									:label='$t("iqrfnet.trConfiguration.form.neverSleep")'
									:disabled='isCoordinator || compareDpaVersion("3.03", "<")'
								/>
								<CInputCheckbox
									:checked.sync='config.nodeDpaInterface'
									:label='$t("iqrfnet.trConfiguration.form.nodeDpaInterface")'
									:disabled='compareDpaVersion("4.00", ">=")'
								/>
								<ValidationProvider
									v-slot='{valid, touched, errors}'
									rules='required'
									:custom-messages='{
										required: $t("iqrfnet.trConfiguration.form.messages.uartBaudRate"),
									}'
								>
									<CSelect
										:value.sync='config.uartBaudrate'
										:label='$t(address === 0 ? "iqrfnet.trConfiguration.form.uartBaudRate" : "config.daemon.interfaces.iqrfUart.form.baudRate")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
										:placeholder='$t(address === 0 ? "iqrfnet.trConfiguration.form.messages.uartBaudRate" : "config.daemon.interfaces.iqrfUart.errors.baudRate")'
										:options='uartBaudRates'
									/>
								</ValidationProvider><hr>
								<div class='form-group'>
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
								</div>
							</CCol>
						</CRow>
						<CButton
							color='primary'
							:disabled='invalid'
							@click='target === "node" ? handleSubmit(address) : handleSubmit(255)'
						>
							{{ $t('forms.write') }}
						</CButton>
					</CForm>
				</ValidationObserver>
			</CCardBody>
		</CCard>
		<ProductModal ref='productModal' @selected-product='setSelectedProduct' />
		<CModal
			:show.sync='dpaEnabledNotDetected'
			color='warning'
			size='lg'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('iqrfnet.trConfiguration.messages.modalTitle') }}
				</h5>
			</template>
			{{ $t('iqrfnet.trConfiguration.messages.modalPrompt') }}
			<template #footer>
				<CButton
					color='warning'
					@click='disableHandler'
				>
					{{ $t('service.actions.disable') }}
				</CButton> <CButton
					color='secondary'
					@click='dpaEnabledNotDetected = false'
				>
					{{ $t('forms.cancel') }}
				</CButton>
			</template>
		</CModal>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {
	CAlert,
	CButton,
	CCard,
	CCardBody,
	CCardHeader,
	CCol,
	CForm,
	CInput,
	CInputCheckbox,
	CModal,
	CRow,
	CSelect,
} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';
import ProductModal from '@/components/IqrfNet/TrConfiguration/ProductModal.vue';

import {
	between,
	integer,
	max,
	max_value,
	min_value,
	required
} from 'vee-validate/dist/rules';
import {NetworkTarget} from '@/enums/IqrfNet/network';

import {compare, CompareOperator} from 'compare-versions';
import IqrfNetService from '@/services/IqrfNetService';
import OsService from '@/services/DaemonApi/OsService';

import {ITrConfiguration} from '@/interfaces/DaemonApi/Dpa';
import {IOption} from '@/interfaces/Coreui';
import {IProduct} from '@/interfaces/Repository';
import {MutationPayload} from 'vuex';
import {DaemonClientState} from '@/interfaces/wsClient';

@Component({
	components: {
		CAlert,
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CCol,
		CForm,
		CInput,
		CInputCheckbox,
		CModal,
		CRow,
		CSelect,
		FontAwesomeIcon,
		ProductModal,
		ValidationObserver,
		ValidationProvider,
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
	 * @var {ITrConfiguration} config Transceiver configuration object
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
			label: this.$t('iqrfnet.trConfiguration.form.targets.device').toString(),
		},
		{
			value: NetworkTarget.NETWORK,
			label: this.$t('iqrfnet.trConfiguration.form.targets.network').toString(),
		},
	];

	/**
	 * @constant {Array<IOption>} networkTypeOptions Array of CoreUI network type options
	 */
	private networkTypeOptions: Array<IOption> = [
		{
			value: false,
			label: this.$t('iqrfnet.trConfiguration.form.networkTypes.std').toString(),
		},
		{
			value: true,
			label: this.$t('iqrfnet.trConfiguration.form.networkTypes.stdLp').toString(),
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
				label: this.$t(`iqrfnet.trConfiguration.form.uartBaudrates.${uartBaudRate}`).toString(),
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
	 * Compare DPA current version with supported version
	 * @param version DPA version
	 * @param operator Comparison operator
	 */
	private compareDpaVersion(version: string, operator: CompareOperator): boolean {
		return this.dpaVersion !== null && compare(this.dpaVersion, version, operator);
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
		switch (response.data.status) {
			case 0:
				this.parseResponse(response);
				return;
			case -1:
			case 1005:
				this.$toast.error(
					this.$t('forms.messages.deviceOffline', {address: this.address}).toString()
				);
				break;
			case 8:
			case 1001:
				this.$toast.error(
					this.$t('forms.messages.noDevice', {address: this.address}).toString()
				);
				break;
			default:
				this.$toast.error(
					this.$t('iqrfnet.trConfiguration.messages.readFailure').toString()
				);
				break;
		}
		this.loaded = false;
	}

	/**
	 * Parses device enumeration response
	 * @param response Daemon API response
	 */
	private parseResponse(response): void {
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
			if (!this.compareDpaVersion('3.03', '=') &&
					!this.compareDpaVersion('3.04', '=')) {
				delete config.rfSubChannelA;
				delete config.rfSubChannelB;
			}
			if (this.compareDpaVersion('3.03', '<')) {
				delete config.neverSleep;
			}
			if (this.compareDpaVersion('4.00', '>=')) {
				delete config.nodeDpaInterface;
			}
			if (this.compareDpaVersion('4.10', '<')) {
				delete config.dpaPeerToPeer;
			}
			if (this.compareDpaVersion('4.14', '>')) {
				delete config.dpaAutoexec;
				delete config.embPers.spi;
			}
			if (this.compareDpaVersion('4.15', '<')) {
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
		switch (response.status) {
			case 0:
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
					return;
				}
				if (this.target === NetworkTarget.NETWORK) {
					this.message.unshift(
						this.$t('iqrfnet.trConfiguration.messages.writeSuccess').toString()
					);
					this.$toast.info(this.message.join(' '));
				} else {
					this.$toast.info(
						this.$t('iqrfnet.trConfiguration.messages.writeSuccess').toString()
					);
				}
				return;
			case -1:
				this.$toast.error(
					this.$t('forms.messages.deviceOffline', {address: address}).toString()
				);
				break;
			case 8:
				this.$toast.error(
					this.$t('forms.messages.noDevice', {address: address}).toString()
				);
				break;
			default:
				this.$toast.error(
					this.$t('iqrfnet.trConfiguration.messages.writeFailure').toString()
				);
				break;
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
		if (response.status !== 0) {
			this.$toast.error(
				this.$t('iqrfnet.trConfiguration.messages.restartFailure').toString()
			);
			return;
		}
		this.message.unshift(this.$t('iqrfnet.trConfiguration.messages.restartSuccess').toString());
		if (response.rsp.nAdr === 0) {
			this.expectingReset = true;
			this.timeout = window.setTimeout(() => {
				this.expectingReset = false;
				this.$store.dispatch('spinner/hide');
				this.$toast.error(this.$t('iqrfnet.trConfiguration.messages.restartFailure').toString());
			}, 3000);
		} else {
			this.$store.dispatch('spinner/hide');
			this.$toast.info(this.message.join(' '));
		}
	}

	/**
	 * Renders product modal
	 */
	private showProductModal(): void {
		(this.$refs.productModal as ProductModal).showModal();
	}

	/**
	 * Sets HWPID from selected product
	 * @param {IProduct} product Selected product
	 */
	private setSelectedProduct(product: IProduct): void {
		this.hwpid = product.hwpid;
		(this.$refs.productModal as ProductModal).hideModal();
	}
}

</script>
