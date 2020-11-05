<template>
	<CCard>
		<CCardHeader>
			<h3>{{ $t('config.iqrfUart.title') }}</h3>
		</CCardHeader>
		<CCardBody>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm @submit.prevent='saveConfig'>
					<ValidationProvider
						v-if='powerUser'
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{required: "config.iqrfUart.form.messages.instance"}'
					>
						<CInput
							v-model='componentInstance'
							:label='$t("config.iqrfUart.form.instance")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{required: "config.iqrfUart.form.messages.IqrfInterface"}'
					>
						<CInput
							v-model='IqrfInterface'
							:label='$t("config.iqrfUart.form.IqrfInterface")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ valid, touched, errors }'
						rules='required'
						:custom-messages='{
							required: "config.iqrfUart.form.messages.baudRate",
						}'
					>
						<CSelect
							:value.sync='baudRate'
							:label='$t("config.iqrfUart.form.baudRate")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
							:placeholder='$t("config.iqrfUart.form.messages.baudRate")'
							:options='baudRates'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required|integer'
						:custom-messages='{
							integer: "config.iqrfUart.form.messages.powerEnableGpioPin",
							required: "config.iqrfUart.form.messages.powerEnableGpioPin",
						}'
					>
						<CInput
							v-model.number='powerEnableGpioPin'
							type='number'
							:label='$t("config.iqrfUart.form.powerEnableGpioPin")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-if='daemonHigher230'
						v-slot='{ errors, touched, valid }'
						rules='required|integer'
						:custom-messages='{
							integer: "config.iqrfUart.form.messages.pgmSwitchGpioPin",
							required: "config.iqrfUart.form.messages.pgmSwitchGpioPin",
						}'
					>
						<CInput
							v-model.number='pgmSwitchGpioPin'
							type='number'
							:label='$t("config.iqrfUart.form.pgmSwitchGpioPin")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required|integer'
						:custom-messages='{
							integer: "config.iqrfUart.form.messages.busEnableGpioPin",
							required: "config.iqrfUart.form.messages.busEnableGpioPin",
						}'
					>
						<CInput
							v-model.number='busEnableGpioPin'
							type='number'
							:label='$t("config.iqrfUart.form.busEnableGpioPin")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<CInputCheckbox
						:checked.sync='uartReset'
						:label='$t("config.iqrfUart.form.uartReset")'
					/>
					<CButton type='submit' color='primary' :disabled='invalid'>
						{{ $t('forms.save') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
		<CCardFooter>
			<h4>{{ $t('config.iqrfUart.mappings' ) }}</h4><hr>
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
import {Component, Vue} from 'vue-property-decorator';
import {AxiosError, AxiosResponse} from 'axios';
import {
	CButton,
	CCard,
	CCardBody,
	CCardFooter,
	CCardHeader,
	CCol,
	CForm,
	CInput,
	CInputCheckbox,
	CRow,
	CSelect,
} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {integer, required} from 'vee-validate/dist/rules';
import InterfaceMappings from '../../components/Config/InterfaceMappings.vue';
import InterfacePorts from '../../components/Config/InterfacePorts.vue';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import {IOption} from '../../interfaces/coreui';
import {IIqrfUart, IUartMapping} from '../../interfaces/iqrfUart';
import {versionHigherThan} from '../../helpers/versionChecker';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardFooter,
		CCardHeader,
		CCol,
		CForm,
		CInput,
		CInputCheckbox,
		CRow,
		CSelect,
		InterfaceMappings,
		InterfacePorts,
		ValidationObserver,
		ValidationProvider,
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
	 * 
	 */
	private daemonHigher230 = false

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
	 * @var {boolean} uartReset Should UART component instance reset?
	 */
	private uartReset = true
	
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
		if (versionHigherThan('2.3.0')) {
			this.daemonHigher230 = true;
		}

		if (this.$store.getters['user/role'] === 'power') {
			this.powerUser = true;
		}

		this.getConfig();
	}

	/**
	 * Retrieves configuration of IQRF UART interface component
	 */
	private getConfig(): void {
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.getComponent(this.componentName)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				if (response.data.instances.length > 0) {
					this.parseConfiguration(response.data.instances[0]);
				}
			})
			.catch((error: AxiosError) => FormErrorHandler.configError(error));
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
		if (this.daemonHigher230) {
			if (response.pgmSwitchGpioPin !== undefined) {
				this.pgmSwitchGpioPin = response.pgmSwitchGpioPin;
			}
			if (response.uartReset !== undefined) {
				this.uartReset = response.uartReset;
			}
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
			busEnableGpioPin: this.busEnableGpioPin
		};
		if (this.daemonHigher230) {
			Object.assign(configuration, {pgmSwitchGpioPin: this.pgmSwitchGpioPin, uartReset: this.uartReset});
		}
		return configuration;
	}
	
	/**
	 * Saves new or updates existing configuration of IQRF UART interface component instance
	 */
	private saveConfig(): void {
		this.$store.commit('spinner/SHOW');
		if (this.instance !== null) {
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
		this.$store.commit('spinner/HIDE');
		this.$toast.success(this.$t('config.success').toString());
	}

	/**
	 * Updates pin configuration from mapping
	 * @param {IUartMapping} mapping Board mapping
	 */
	private updateMapping(mapping: IUartMapping): void {
		this.IqrfInterface = mapping.IqrfInterface;
		this.baudRate = mapping.baudRate;
		this.busEnableGpioPin = mapping.busEnableGpioPin;
		this.powerEnableGpioPin = mapping.powerEnableGpioPin;
		if (this.daemonHigher230) {
			this.pgmSwitchGpioPin = mapping.pgmSwitchGpioPin;
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
