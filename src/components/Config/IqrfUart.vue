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
	<CCard>
		<CCardHeader>
			{{ $t('config.daemon.interfaces.iqrfUart.title') }}
		</CCardHeader>
		<CCardBody>
			<CElementCover
				v-if='loadFailed'
				style='z-index: 1;'
				:opacity='0.85'
			>
				{{ $t('config.daemon.messages.failedElement') }}
			</CElementCover>
			<ValidationObserver v-slot='{invalid}'>
				<CForm @submit.prevent='saveConfig'>
					<fieldset :disabled='loadFailed'>
						<ValidationProvider
							v-if='powerUser'
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{required: "config.daemon.interfaces.iqrfUart.errors.instance"}'
						>
							<CFormInput
								v-model='componentInstance'
								:label='$t("forms.fields.instanceName")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{required: "config.daemon.interfaces.iqrfUart.errors.iqrfInterface"}'
						>
							<CFormInput
								v-model='IqrfInterface'
								:label='$t("config.daemon.interfaces.iqrfUart.form.iqrfInterface")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{valid, touched, errors}'
							rules='required'
							:custom-messages='{
								required: "config.daemon.interfaces.iqrfUart.errors.baudRate",
							}'
						>
							<CFormSelect
								:value.sync='baudRate'
								:label='$t("config.daemon.interfaces.iqrfUart.form.baudRate")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
								:placeholder='$t("config.daemon.interfaces.iqrfUart.errors.baudRate")'
								:options='baudRates'
							/>
						</ValidationProvider>
						<CFormCheck
							:checked.sync='uartReset'
							:label='$t("config.daemon.interfaces.iqrfUart.form.uartReset")'
						/>
						<CRow>
							<CCol :md='(i2cEnableGpioPin !== null || spiEnableGpioPin !== null || uartEnableGpioPin !== null) ? 6 : 12'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required|integer'
									:custom-messages='{
										integer: "config.daemon.interfaces.interfaceMapping.errors.powerPin",
										required: "config.daemon.interfaces.interfaceMapping.errors.powerPin",
									}'
								>
									<CFormInput
										v-model.number='powerEnableGpioPin'
										type='number'
										:label='$t("config.daemon.interfaces.interfaceMapping.form.powerPin")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
									/>
								</ValidationProvider>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required|integer'
									:custom-messages='{
										integer: "config.daemon.interfaces.interfaceMapping.errors.busPin",
										required: "config.daemon.interfaces.interfaceMapping.errors.busPin",
									}'
								>
									<CFormInput
										v-model.number='busEnableGpioPin'
										type='number'
										:label='$t("config.daemon.interfaces.interfaceMapping.form.busPin")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
									/>
								</ValidationProvider>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required|integer'
									:custom-messages='{
										integer: "config.daemon.interfaces.interfaceMapping.errors.pgmPin",
										required: "cconfig.daemon.interfaces.interfaceMapping.errors.pgmPin",
									}'
								>
									<CFormInput
										v-model.number='pgmSwitchGpioPin'
										type='number'
										:label='$t("config.daemon.interfaces.interfaceMapping.form.pgmPin")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
									/>
								</ValidationProvider>
							</CCol>
							<CCol
								v-if='i2cEnableGpioPin !== null || spiEnableGpioPin !== null || uartEnableGpioPin !== null'
								md='6'
							>
								<CFormInput
									v-if='i2cEnableGpioPin !== null'
									v-model.number='i2cEnableGpioPin'
									type='number'
									:label='$t("config.daemon.interfaces.interfaceMapping.form.i2cPin")'
									:disabled='true'
								/>
								<CFormInput
									v-if='spiEnableGpioPin !== null'
									v-model.number='spiEnableGpioPin'
									type='number'
									:label='$t("config.daemon.interfaces.interfaceMapping.form.spiPin")'
									:disabled='true'
								/>
								<CFormInput
									v-if='uartEnableGpioPin !== null'
									v-model.number='uartEnableGpioPin'
									type='number'
									:label='$t("config.daemon.interfaces.interfaceMapping.form.uartPin")'
									:disabled='true'
								/>
								<div v-if='i2cEnableGpioPin !== null || spiEnableGpioPin !== null || uartEnableGpioPin !== null'>
									<i>{{ $t('config.daemon.interfaces.interfaceMapping.form.gwOnly') }}</i>
								</div><br v-if='i2cEnableGpioPin !== null || spiEnableGpioPin !== null || uartEnableGpioPin !== null'>
							</CCol>
						</CRow>
						<CButton type='submit' color='primary' :disabled='invalid'>
							{{ $t('forms.save') }}
						</CButton>
					</fieldset>
				</CForm>
			</ValidationObserver>
		</CCardBody>
		<CCardFooter>
			<h4>{{ $t('config.daemon.interfaces.iqrfUart.mappings' ) }}</h4><hr>
			<CRow>
				<CCol lg='6'>
					<InterfaceMappings interface-type='uart' @update-mapping='updateMapping' />
				</CCol>
				<CCol lg='6'>
					<InterfacePorts interface-type='uart' @update-port='updatePort' />
				</CCol>
			</CRow>
		</CCardFooter>
	</CCard>
</template>

<script lang='ts'>
import {Options, Vue, Watch} from 'vue-property-decorator';
import {AxiosError, AxiosResponse} from 'axios';
import {
	CButton,
	CCard,
	CCardBody,
	CCardFooter,
	CCardHeader,
	CCol,
	CElementCover,
	CForm,
	CFormInput,
	CFormCheck,
	CRow,
	CFormSelect,
} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {integer, required} from 'vee-validate/dist/rules';
import InterfaceMappings from '../../components/Config/InterfaceMappings.vue';
import InterfacePorts from '../../components/Config/InterfacePorts.vue';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import {IOption} from '../../interfaces/coreui';
import {IIqrfUart} from '../../interfaces/iqrfInterfaces';
import {IMapping} from '../../interfaces/mappings';
import {versionHigherEqual} from '../../helpers/versionChecker';
import {mapGetters} from 'vuex';

@Options({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardFooter,
		CCardHeader,
		CCol,
		CElementCover,
		CForm,
		CFormInput,
		CFormCheck,
		CRow,
		CFormSelect,
		InterfaceMappings,
		InterfacePorts,
		ValidationObserver,
		ValidationProvider,
	},
	computed: {
		...mapGetters({
			daemonVersion: 'daemonVersion',
		}),
	}
})

/**
 * IQRF UART communication interface configuration component
 */
export default class IqrfUart extends Vue {
	/**
	 * @var {number} baudRate UART baudrate
	 */
	private baudRate = 57600

	/**
	 * @var {number} busEnableGpioPin UART bus enable ping
	 */
	private busEnableGpioPin = -1

	/**
	 * @constant {string} component UART component name
	 */
	private component = ''

	/**
	 * @constant {string} componentName UART component name, used for REST API communication
	 */
	private componentName = 'iqrf::IqrfUart'

	/**
	 * @var {string} componentInstance UART component instance name
	 */
	private componentInstance = 'iqrf::IqrfUart-/dev/ttyS0'

	/**
	 * @var {number|null} i2cEnableGpioPin I2C interface enable pin
	 */
	private i2cEnableGpioPin: number|null = null

	/**
	 * @var {string} instance UART component instance name, used for REST API communication
	 */
	private instance = ''

	/**
	 * @var {string} IqrfInterface UART interface device name
	 */
	private IqrfInterface = '/dev/ttyS0'

	/**
	 * @var {number} pgmSwitchGpioPin UART programming mode switch pin
	 */
	private pgmSwitchGpioPin = -1

	/**
	 * @var {number} powerEnableGpioPin UART power enable pin
	 */
	private powerEnableGpioPin = 18

	/**
	 * @var {boolean} powerUser Indicates whether user role is power user
	 */
	private powerUser = false

	/**
	 * @var {number|null} spiEnableGpioPin SPI interface enable pin
	 */
	private spiEnableGpioPin: number|null = null

	/**
	 * @var {boolean} uartReset Should UART component instance reset?
	 */
	private uartReset = true

	/**
	 * @var {number|null} uartEnableGpioPin UART interface enable pin
	 */
	private uartEnableGpioPin: number|null = null

	/**
	 * @var {boolean} loadFailed Indicates whether configuration fetch failed
	 */
	private loadFailed = false

	/**
	 * Computes array of CoreUI select options for baudrate
	 * @returns {Array<BaudRateOptions} Baudrate select options
	 */
	get baudRates(): Array<IOption> {
		const baudRates: Array<number> = [1200, 2400, 4800, 9600, 19200, 38400, 57600, 115200, 230400];
		return baudRates.map((baudRate: number) => ({value: baudRate, label: baudRate + ' Bd'}));
	}

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('integer', integer);
		extend('required', required);
	}

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		if (this.$store.getters['user/getRole'] === 'power') {
			this.powerUser = true;
		}
		this.getConfig();
	}

	/**
	 * Retrieves configuration of IQRF UART interface component
	 */
	private getConfig(): Promise<void> {
		return DaemonConfigurationService.getComponent(this.componentName)
			.then((response: AxiosResponse) => {
				if (response.data.instances.length > 0) {
					this.parseConfiguration(response.data.instances[0]);
				}
				this.$emit('fetched', {name: 'iqrfUart', success: true});
			})
			.catch(() => {
				this.loadFailed = true;
				this.$emit('fetched', {name: 'iqrfUart', success: true});
			});
	}

	/**
	 * Parses IQRF UART interface configuration from REST API response
	 * @param {IIqrfUart} response Configuration object from REST API response
	 */
	private parseConfiguration(response: IIqrfUart): void {
		this.component = response.component;
		this.instance = this.componentInstance = response.instance;
		this.IqrfInterface = response.IqrfInterface;
		this.baudRate = response.baudRate;
		this.powerEnableGpioPin = response.powerEnableGpioPin;
		this.busEnableGpioPin = response.busEnableGpioPin;
		if (response.pgmSwitchGpioPin !== undefined) {
			this.pgmSwitchGpioPin = response.pgmSwitchGpioPin;
		}
		if (response.uartReset !== undefined) {
			this.uartReset = response.uartReset;
		}
		if (response.i2cEnableGpioPin !== undefined) {
			this.i2cEnableGpioPin = response.i2cEnableGpioPin;
		}
		if (response.spiEnableGpioPin !== undefined) {
			this.spiEnableGpioPin = response.spiEnableGpioPin;
		}
		if (response.uartEnableGpioPin !== undefined) {
			this.uartEnableGpioPin = response.uartEnableGpioPin;
		}
	}

	/**
	 * Creates IQRF UART component instance configuration object
	 * @returns {IIqrfUart} UART configuration
	 */
	private buildConfiguration(): IIqrfUart {
		let configuration: IIqrfUart = {
			component: this.component,
			instance: this.componentInstance,
			IqrfInterface: this.IqrfInterface,
			baudRate: this.baudRate,
			powerEnableGpioPin: this.powerEnableGpioPin,
			busEnableGpioPin: this.busEnableGpioPin,
			pgmSwitchGpioPin: this.pgmSwitchGpioPin,
			uartReset: this.uartReset
		};
		if (this.i2cEnableGpioPin !== null) {
			Object.assign(configuration, {i2cEnableGpioPin: this.i2cEnableGpioPin});
		}
		if (this.spiEnableGpioPin !== null) {
			Object.assign(configuration, {spiEnableGpioPin: this.spiEnableGpioPin});
		}
		if (this.uartEnableGpioPin !== null) {
			Object.assign(configuration, {uartEnableGpioPin: this.uartEnableGpioPin});
		}
		return configuration;
	}

	/**
	 * Saves new or updates existing configuration of IQRF UART interface component instance
	 */
	private saveConfig(): void {
		this.$store.commit('spinner/SHOW');
		if (this.instance !==  '') {
			DaemonConfigurationService.updateInstance(this.componentName, this.instance, this.buildConfiguration())
				.then(() => this.successfulSave())
				.catch((error: AxiosError) => FormErrorHandler.configError(error));
		} else {
			DaemonConfigurationService.createInstance(this.componentName, this.buildConfiguration())
				.then(() => this.successfulSave())
				.catch((error: AxiosError) => FormErrorHandler.configError(error));
		}
	}

	/**
	 * Handles successful REST API response
	 */
	private successfulSave(): void {
		this.getConfig().then(() => this.$toast.success(this.$t('config.success').toString()));
	}

	/**
	 * Updates pin configuration from mapping
	 * @param {IUartMapping} mapping Board mapping
	 */
	private updateMapping(mapping: IMapping): void {
		this.IqrfInterface = mapping.IqrfInterface;
		if (mapping.baudRate !== undefined) {
			this.baudRate = mapping.baudRate;
		}
		this.busEnableGpioPin = mapping.busEnableGpioPin;
		this.powerEnableGpioPin = mapping.powerEnableGpioPin;
		this.pgmSwitchGpioPin = mapping.pgmSwitchGpioPin;
		if (mapping.i2cEnableGpioPin !== undefined) {
			this.i2cEnableGpioPin = mapping.i2cEnableGpioPin;
		} else {
			this.i2cEnableGpioPin = null;
		}
		if (mapping.spiEnableGpioPin !== undefined) {
			this.spiEnableGpioPin = mapping.spiEnableGpioPin;
		} else {
			this.spiEnableGpioPin = null;
		}
		if (mapping.uartEnableGpioPin !== undefined) {
			this.uartEnableGpioPin = mapping.uartEnableGpioPin;
		} else {
			this.uartEnableGpioPin = null;
		}
	}

	/**
	 * Updates port in configuration from mapping
	 * @param {string} port Port
	 */
	private updatePort(port: string): void {
		this.IqrfInterface = port;
	}

}
</script>
