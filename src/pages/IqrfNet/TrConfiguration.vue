<template>
	<div>
		<h1>{{ $t('iqrfnet.trConfiguration.title') }}</h1>
		<AddressChanger :current-address='address' :loaded='loaded' @reload-configuration='enumerate' />
		<CCard v-if='config !== null'>
			<CCardHeader>{{ $t('iqrfnet.trConfiguration.title') }}</CCardHeader>
			<CCardBody>
				<ValidationObserver v-slot='{ invalid }'>
					<CForm @submit.prevent='handleSubmit'>
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
									v-slot='{ valid, touched, errors }'
									:rules='rfChannelRules.rule'
									:custom-messages='rfChannelValidatorMessages'
								>
									<CInput
										v-model.number='config.rfChannelA'
										:label='$t("iqrfnet.trConfiguration.form.rfChannelA")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
										type='number'
										:max='rfChannelRules.max'
										:min='rfChannelRules.min'
									/>
								</ValidationProvider>
								<ValidationProvider
									v-slot='{ valid, touched, errors }'
									:rules='rfChannelRules.rule'
									:custom-messages='rfChannelValidatorMessages'
								>
									<CInput
										v-model.number='config.rfChannelB'
										:label='$t("iqrfnet.trConfiguration.form.rfChannelB")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
										type='number'
										:max='rfChannelRules.max'
										:min='rfChannelRules.min'
									/>
								</ValidationProvider>
								<ValidationProvider
									v-if='config.rfSubChannelA !== undefined'
									v-slot='{ valid, touched, errors }'
									:rules='rfChannelRules.rule'
									:custom-messages='rfChannelValidatorMessages'
								>
									<CInput
										v-model.number='config.rfSubChannelA'
										:label='$t("iqrfnet.trConfiguration.form.rfSubChannelA")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
										type='number'
										:max='rfChannelRules.max'
										:min='rfChannelRules.min'
									/>
								</ValidationProvider>
								<ValidationProvider
									v-if='config.rfSubChannelB !== undefined'
									v-slot='{ valid, touched, errors }'
									:rules='rfChannelRules.rule'
									:custom-messages='rfChannelValidatorMessages'
								>
									<CInput
										v-model.number='config.rfSubChannelB'
										:label='$t("iqrfnet.trConfiguration.form.rfSubChannelB")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
										type='number'
										:max='rfChannelRules.max'
										:min='rfChannelRules.min'
									/>
								</ValidationProvider>
								<ValidationProvider
									v-if='config.rfAltDsmChannel !== undefined'
									v-slot='{ valid, touched, errors }'
									:rules='rfChannelRules.rule'
									:custom-messages='rfChannelValidatorMessages'
								>
									<CInput
										v-model.number='config.rfAltDsmChannel'
										:label='$t("iqrfnet.trConfiguration.form.rfAltDsmChannel")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
										type='number'
										:max='rfChannelRules.max'
										:min='rfChannelRules.min'
									/>
								</ValidationProvider>
								<CAlert v-if='address === 0 && config.stdAndLpNetwork === false' color='warning'>
									{{ $t('iqrfnet.trConfiguration.form.messages.breakInteroperability') }}
								</CAlert>
								<CSelect
									v-if='address === 0 && config.stdAndLpNetwork !== undefined'
									:label='$t("iqrfnet.trConfiguration.form.networkType")'
									:value.sync='config.stdAndLpNetwork'
									:options='[
										{
											value: false, label: $t("iqrfnet.trConfiguration.form.networkTypes.std")
										},
										{
											value: true, label: $t("iqrfnet.trConfiguration.form.networkTypes.stdLp")
										}
									]'
								/>
								<ValidationProvider
									v-slot='{ valid, touched, errors }'
									rules='integer|between:0,7|required'
									:custom-messages='{
										between: "iqrfnet.trConfiguration.form.messages.txPower",
										integer: "iqrfnet.trConfiguration.form.messages.txPower",
										required: "iqrfnet.trConfiguration.form.messages.txPower",
									}'
								>
									<CInput
										v-model.number='config.txPower'
										:label='$t("iqrfnet.trConfiguration.form.txPower")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
										type='number'
										max='7'
										min='0'
									/>
								</ValidationProvider>
								<ValidationProvider
									v-slot='{ valid, touched, errors }'
									rules='integer|between:0,64|required'
									:custom-messages='{
										between: "iqrfnet.trConfiguration.form.messages.rxFilter",
										integer: "iqrfnet.trConfiguration.form.messages.rxFilter",
										required: "iqrfnet.trConfiguration.form.messages.rxFilter",
									}'
								>
									<CInput
										v-model.number='config.rxFilter'
										:label='$t("iqrfnet.trConfiguration.form.rxFilter")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
										type='number'
										max='64'
										min='0'
									/>
								</ValidationProvider>
								<ValidationProvider
									v-slot='{ valid, touched, errors }'
									rules='integer|between:1,255|required'
									:custom-messages='{
										between: "iqrfnet.trConfiguration.form.messages.lpRxTimeout",
										integer: "iqrfnet.trConfiguration.form.messages.lpRxTimeout",
										required: "iqrfnet.trConfiguration.form.messages.lpRxTimeout",
									}'
								>
									<CInput
										v-model.number='config.lpRxTimeout'
										:label='$t("iqrfnet.trConfiguration.form.lpRxTimeout")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
										type='number'
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
							</CCol>
							<CCol md='6'>
								<h2>{{ $t('iqrfnet.trConfiguration.form.dpa.embeddedPeripherals') }}</h2>
								<CInputCheckbox
									v-for='peripheral of peripherals'
									:key='peripheral.name'
									:checked.sync='peripheral.enabled'
									:disabled='unchangeablePeripherals.includes(peripheral.name)'
									:label='$t("iqrfnet.trConfiguration.form.embPers." + peripheral.name)'
								/>
								<h2>{{ $t('iqrfnet.trConfiguration.form.dpa.other') }}</h2>
								<CInputCheckbox
									:checked.sync='config.customDpaHandler'
									:label='$t("iqrfnet.trConfiguration.form.customDpaHandler")'
									:disabled='!dpaHandlerDetected'
								/>
								<CInputCheckbox
									v-if='config.dpaPeerToPeer !== undefined'
									:checked.sync='config.dpaPeerToPeer'
									:label='$t("iqrfnet.trConfiguration.form.dpaPeerToPeer")'
								/>
								<CInputCheckbox
									:checked.sync='config.peerToPeer'
									:label='$t("iqrfnet.trConfiguration.form.peerToPeer")'
								/>
								<CInputCheckbox
									v-if='config.localFrcReception !== undefined && address !== 0'
									:checked.sync='config.localFrcReception'
									:label='$t("iqrfnet.trConfiguration.form.localFrcReception")'
								/>
								<CInputCheckbox
									:checked.sync='config.ioSetup'
									:label='$t("iqrfnet.trConfiguration.form.ioSetup")'
								/>
								<CInputCheckbox
									:checked.sync='config.dpaAutoexec'
									:label='$t("iqrfnet.trConfiguration.form.dpaAutoexec")'
								/>
								<CInputCheckbox
									:checked.sync='config.routingOff'
									:label='$t("iqrfnet.trConfiguration.form.routingOff")'
								/>
								<CInputCheckbox
									v-if='config.neverSleep !== undefined'
									:checked.sync='config.neverSleep'
									:label='$t("iqrfnet.trConfiguration.form.neverSleep")'
								/>
								<ValidationProvider
									v-slot='{ valid, touched, errors }'
									rules='required'
									:custom-messages='{
										required: "iqrfnet.trConfiguration.form.messages.uartBaudrate",
									}'
								>
									<CSelect
										:value.sync='config.uartBaudrate'
										:label='$t(address === 0 ? "iqrfnet.trConfiguration.form.uartBaudRate" : "config.daemon.interfaces.iqrfUart.form.baudRate")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
										:placeholder='$t(address === 0 ? "iqrfnet.trConfiguration.form.messages.uartBaudrate": "config.daemon.interfaces.iqrfUart.errors.baudRate")'
										:options='uartBaudRates'
									/>
								</ValidationProvider>
								<CInputCheckbox
									v-if='config.nodeDpaInterface !== undefined'
									:checked.sync='config.nodeDpaInterface'
									:label='$t("iqrfnet.trConfiguration.form.nodeDpaInterface")'
								/>
							</CCol>
						</CRow>
						<CButton color='primary' type='submit' :disabled='invalid'>
							{{ $t('forms.write') }}
						</CButton>
					</CForm>
				</ValidationObserver>
			</CCardBody>
		</CCard>
		<SecurityForm v-if='config !== null' :address='address' />
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
					color='secondary'
					@click='dpaEnabledNotDetected = false'
				>
					{{ $t('forms.cancel') }}
				</CButton>
				<CButton
					color='warning'
					@click='disableHandler'
				>
					{{ $t('service.actions.disable') }}
				</CButton>
			</template>
		</CModal>
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue, Watch} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput, CModal} from '@coreui/vue/src';
import {
	between,
	integer,
	min_value,
	max_value,
	required,
} from 'vee-validate/dist/rules';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import AddressChanger from '../../components/IqrfNet/AddressChanger.vue';
import SecurityForm from '../../components/IqrfNet/SecurityForm.vue';
import IqrfNetService from '../../services/IqrfNetService';
import OsService from '../../services/DaemonApi/OsService';
import {IOption} from '../../interfaces/coreui';
import {WebSocketClientState} from '../../store/modules/webSocketClient.module';
import {MutationPayload} from 'vuex';
import {Dictionary} from 'vue-router/types/router';
import {IEmbedPers, IEmbedPersEnabled, ITrConfiguration} from '../../interfaces/dpa';
import {versionHigherEqual} from '../../helpers/versionChecker';

@Component({
	components: {
		AddressChanger,
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		CModal,
		SecurityForm,
		ValidationObserver,
		ValidationProvider,
	}
})

/**
 * Transciever configuration page component
 */
export default class TrConfiguration extends Vue {
	/**
	 * @var {ITrConfiguration|null} config Device transciever configuration object
	 */
	private config: ITrConfiguration|null = null

	/**
	 * @var {boolean} dpaEnabledNotDetected Indicates whether custom DPA handler is enabled but not detected
	 */
	private dpaEnabledNotDetected = false

	/**
	 * @var {boolean} dpaHandlerDetected Indicates whether transciever has a custom dpa handler implemented
	 */
	private dpaHandlerDetected = false

	/**
	 * @var {string|null} dpaVersion Version of DPA used by transciever
	 */
	private dpaVersion: string|null = null

	/**
	 * @var {boolean} loaded Indicates whether configuration has been loaded
	 */
	private loaded = false

	/**
	 * @var {string|null} msgId Daemon api message id
	 */
	private msgId: string|null = null

	/**
	 * @var {Array<IEmbedPersEnabled} peripherals Array of embedded peripherals and their states
	 */
	private peripherals: Array<IEmbedPersEnabled> = []

	/**
	 * @constant {Array<string>} unchangeablePeripherals Array of peripherals whose states cannot be changed
	 */
	private unchangeablePeripherals: Array<string> = [
		'coordinator',
		'node',
		'os'
	]

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;}

	/**
	 * Component unwatch function
	 */
	private unwatch: CallableFunction = () => {return;}

	/**
	 * @var {boolean} daemon236 Indicates that Daemon version is 2.3.6 or higher
	 */
	private daemon236 = false

	/**
	 * @property {number} address Device address
	 */
	@Prop({required: true}) address!: number

	/**
	 * Device address watcher for transciever configuration retrieval
	 */
	@Watch('address')
	getAddress(): void {
		this.loaded = false;
		this.config = null;
		this.peripherals = [];
		if (this.$store.getters.isSocketConnected) {
			this.$store.dispatch('spinner/show', {timeout: 60000});
			IqrfNetService.enumerateDevice(this.address, 60000, 'iqrfnet.trConfiguration.messages.read.failure', () => this.msgId = null)
				.then((msgId: string) => this.msgId = msgId);
		}
	}
	
	/**
	 * Computes rules for validation of RF channel input field
	 * @returns {Dictionary<string|number>|undefined} Dictionary of rules if rfBand in configuration is valid
	 */
	get rfChannelRules(): Dictionary<string|number>|undefined {
		if (this.config === null) {
			return undefined;
		}
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
	 * @returns {Dictionary<string>} Dictionary of messages for applied rules
	 */
	get rfChannelValidatorMessages(): Dictionary<string> {
		let message = '';
		if (this.config !== null) {
			switch (this.config.rfBand) {
				case '433':
					message = 'iqrfnet.trConfiguration.form.messages.rfChannel.433';
					break;
				case '868':
					message = 'iqrfnet.trConfiguration.form.messages.rfChannel.868';
					break;
				case '916':
					message = 'iqrfnet.trConfiguration.form.messages.rfChannel.916';
					break;
			}
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
				label: this.$t('iqrfnet.trConfiguration.form.uartBaudrates.' + uartBaudRate).toString(),
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
		extend('required', required);
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type !== 'SOCKET_ONMESSAGE') {
				return;
			}
			if (mutation.payload.data.msgId !== this.msgId) {
				return;
			}
			this.$store.dispatch('removeMessage', this.msgId);
			if (mutation.payload.mType === 'iqmeshNetwork_WriteTrConf') {
				this.$store.dispatch('spinner/hide');
				this.handleWriteResponse(mutation.payload);
			} else if (mutation.payload.mType === 'iqmeshNetwork_EnumerateDevice') {
				this.$store.dispatch('spinner/hide');
				this.handleEnumerationResponse(mutation.payload);
			} else if (mutation.payload.mType === 'iqrfEmbedOs_Read') {
				this.$store.dispatch('spinner/hide');
				this.handleOsReadResponse(mutation.payload.data);
			} else if (mutation.payload.mType === 'iqrfEmbedOs_Reset') {
				this.$store.dispatch('spinner/hide');
				this.handleResetResponse(mutation.payload);
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
		this.daemon236 = versionHigherEqual('2.3.6');
		if (this.$store.getters.isSocketConnected) {
			this.readOs();
		} else {
			this.unwatch = this.$store.watch(
				(state: WebSocketClientState, getter: any) => getter.isSocketConnected,
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
		this.$store.dispatch('removeMessage', this.msgId);
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
			this.restartDevice();
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
	}

	/**
	 * Parses device enumeration response
	 * @param response Daemon API response
	 */
	private parseResponse(response: any): void {
		let rsp = response.data.rsp;
		this.config = rsp.trConfiguration;
		this.dpaHandlerDetected = rsp.osRead.flags.dpaHandlerDetected;
		this.dpaVersion = rsp.peripheralEnumeration.dpaVer;
		this.setEmbeddedPeripherals();
		this.$store.dispatch('spinner/hide');
		this.$toast.success(
			this.$t('iqrfnet.trConfiguration.messages.readSuccess').toString()
		);
		this.loaded = true;
	}

	/**
	 * Updates transciever configuration object with embed peripherals configuration and then sends WriteTrConfiguration request
	 */
	private handleSubmit(): void {
		let config = JSON.parse(JSON.stringify(this.config));
		config.embPers = this.getEmbeddedPeripherals();
		this.$store.dispatch('spinner/show', {timeout: 60000});
		IqrfNetService.writeTrConfiguration(this.address, config, 60000, 'iqrfnet.trConfiguration.messages.writeFailure', () => this.msgId = null)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles WriteTrConfiguration request response
	 */
	private handleWriteResponse(response): void {
		if (response.data.status === 0) {
			if (response.data.rsp.restartNeeded) {
				this.resetDevice();
			} else {
				this.$toast.success(
					this.$t('iqrfnet.trConfiguration.messages.writeSuccess').toString()
				);
			}
			return;
		}

		if (this.daemon236) { // unified status codes
			if (response.data.status === -1) {
				this.$toast.error(
					this.$t('forms.messages.deviceOffline', {address: this.address}).toString()
				);
			} else if (response.data.status === 8) {
				this.$toast.error(
					this.$t('forms.messages.noDevice', {address: this.address}).toString()
				);
			} else {
				this.$toast.error(
					this.$t('iqrfnet.trConfiguration.messages.writeFailure').toString()
				);
			}
			return;
		}
		
		if (response.data.status === 1007) { // pre-unified status codes
			if (response.data.statusStr.includes('ERROR_TIMEOUT')) {
				this.$toast.error(
					this.$t('forms.messages.deviceOffline', {address: this.address}).toString()
				);
			} else if (response.data.statusStr.includes('ERROR_NADR')) {
				this.$toast.error(
					this.$t('forms.messages.noDevice', {address: this.address}).toString()
				);
			} else {
				this.$toast.error(
					this.$t('iqrfnet.trConfiguration.messages.writeFailure').toString()
				);
			}
			return;
		}
		this.$toast.error(
			this.$t('iqrfnet.trConfiguration.messages.writeFailure').toString()
		);
	}

	/**
	 * Performs device reset
	 */
	private resetDevice(): void {
		this.$store.dispatch('spinner/show', {timeout: 30000});
		OsService.reset(this.address, 30000, 'iqrfnet.trConfiguration.messages.restartFailure', () => this.msgId = null)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Performs device restart
	 */
	private restartDevice(): void {
		this.$store.dispatch('spinner/show', {timeout: 30000});
		OsService.restart(this.address, 30000, 'iqrfnet.trConfiguration.messages.restartFailure', () => this.msgId = null)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles EmbedOs_Reset request response
	 */
	private handleResetResponse(response): void {
		if (response.data.status === 0) {
			this.$toast.success(
				this.$t('iqrfnet.trConfiguration.messages.restartSuccess').toString()
			);
		} else {
			this.$toast.error(
				this.$t('iqrfnet.trConfiguration.messages.restartFailure').toString()
			);
		}
	}

	/**
	 * Handles EmbedOs_Restart request response
	 */
	private handleRestartResponse(response): void {
		if (response.status === 0) {
			this.enumerate();
		} else {
			this.$toast.error(
				this.$t('iqrfnet.trConfiguration.messages.restartFailure').toString()
			);
		}
	}

	/**
	 * Populates array of embedded peripherals with information from ReadTrConfiguration request
	 */
	private setEmbeddedPeripherals(): void {
		if (this.config === null) {
			return;
		}
		let peripherals = JSON.parse(JSON.stringify(this.config.embPers));
		this.peripherals = [];
		for (let peripheral in peripherals) {
			if (!Object.prototype.hasOwnProperty.call(peripherals, peripheral)) { // peripheral information exists
				continue;
			}
			if (typeof this.config.embPers[peripheral] !== 'boolean') { // invalid value in configuration
				continue;
			}
			if (this.unchangeablePeripherals.includes(peripheral) &&
					this.$store.getters['user/getRole'] === 'normal') {
				continue;
			}
			this.peripherals.push({
				name: peripheral,
				enabled: this.config.embPers[peripheral],
			});
		}
	}

	/**
	 * Converts new configuration of embedded peripherals from array to object accepted by daemon
	 * @returns {IEmbedPers} Dictionary of embedded peripherals
	 */
	private getEmbeddedPeripherals(): IEmbedPers {
		let peripherals = {};
		for (let peripheral of this.peripherals) {
			peripherals[peripheral.name] = peripheral.enabled;
		}
		return (peripherals as IEmbedPers);
	}
}
</script>
