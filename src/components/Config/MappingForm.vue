<template>
	<ValidationObserver v-slot='{invalid}'>
		<CModal
			:show.sync='modalShown'
			:color='mappingId === null ? "success": "primary"'
			size='lg'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ modalTitle }}
				</h5>
			</template>
			<CForm>
				<CSelect
					:value.sync='type'
					:label='$t("config.daemon.interfaces.interfaceMapping.form.type")'
					:options='typeOptions'
					:placeholder='$t("config.daemon.interfaces.interfaceMapping.errors.typeSelect")'
				/>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					rules='required'
					:custom-messages='{
						required: "config.daemon.interfaces.interfaceMapping.errors.name"
					}'
				>
					<CInput
						v-model='name'
						:label='$t("config.daemon.interfaces.interfaceMapping.form.name")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='$t(errors[0])'
					/>
				</ValidationProvider>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					rules='required'
					:custom-messages='{
						required: "config.daemon.interfaces.interfaceMapping.errors.interface"
					}'
				>
					<CInput
						v-model='deviceName'
						:label='$t("config.daemon.interfaces.interfaceMapping.form.interface")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='$t(errors[0])'
					/>
				</ValidationProvider>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					rules='integer|required'
					:custom-messages='{
						integer: "forms.messages.integer",
						required: "config.daemon.interfaces.interfaceMapping.errors.powerPin"
					}'
				>
					<CInput
						v-model.number='powerPin'
						type='number'
						:label='$t("config.daemon.interfaces.interfaceMapping.form.powerPin")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='$t(errors[0])'
					/>
				</ValidationProvider>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					rules='integer|required'
					:custom-messages='{
						integer: "forms.messages.integer",
						required: "config.daemon.interfaces.interfaceMapping.errors.busPin"
					}'
				>
					<CInput
						v-model.number='busPin'
						type='number'
						:label='$t("config.daemon.interfaces.interfaceMapping.form.busPin")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='$t(errors[0])'
					/>
				</ValidationProvider>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					rules='integer|required'
					:custom-messages='{
						integer: "forms.messages.integer",
						required: "config.daemon.interfaces.interfaceMapping.errors.pgmPin"
					}'
				>
					<CInput
						v-model.number='pgmPin'
						type='number'
						:label='$t("config.daemon.interfaces.interfaceMapping.form.pgmPin")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='$t(errors[0])'
					/>
				</ValidationProvider>
				<CSelect
					v-if='type === "uart"'
					:value.sync='baudRate'
					:options='baudRateOptions'
					:label='$t("config.daemon.interfaces.interfaceMapping.form.baudRate")'
				/>
			</CForm>
			<template #footer>
				<CButton 
					color='success'
					:disabled='invalid'
					@click='saveMapping'
				>
					{{ $t('forms.save') }}
				</CButton> <CButton 
					color='secondary'
					@click='hideModal'
				>
					{{ $t('forms.cancel') }}
				</CButton>
			</template>
		</CModal>
	</ValidationObserver>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CForm, CInput, CModal, CSelect} from '@coreui/vue/src';
import {IOption} from '../../interfaces/coreui';
import {IMapping} from '../../interfaces/mappings';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {integer, required} from 'vee-validate/dist/rules';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import MappingService from '../../services/MappingService';
import {AxiosError, AxiosResponse} from 'axios';

@Component({
	components: {
		CButton,
		CForm,
		CInput,
		CModal,
		CSelect,
		ValidationObserver,
		ValidationProvider,
	}
})

/**
 * Modal form to add or edit mapping
 */
export default class MappingForm extends Vue {
	/**
	 * @var {number} baudRate Mapping UART baud rate
	 */
	private baudRate = 57600

	/**
	 * @var {number} busPin Mapping bus enable pin number
	 */
	private busPin = 0

	/**
	 * @var {string} deviceName Mapping device name
	 */
	private deviceName = ''

	/**
	 * @var {boolean} gatewayMapping Indicates that mapping is for IQRF Gateway
	 */
	private gatewayMapping = false

	/**
	 * @var {number} i2cPin Mapping I2C interface enable pin number
	 */
	private i2cPin = 0

	/**
	 * @var {number|null} mappingId Mapping ID
	 */
	private mappingId: number|null = null;

	/**
	 * @var {boolean} modalShown Controls whether modal mapping form is shown
	 */
	private modalShown = false;

	/**
	 * @var {string} name Mapping name
	 */
	private name = ''

	/**
	 * @var {number} pgmPin Mapping programming mode switch pin number
	 */
	private pgmPin = 0

	/**
	 * @var {number} powerPin Mapping power enable pin number
	 */
	private powerPin = 0

	/**
	 * @var {number} spiPin Mapping SPI interface enable pin number
	 */
	private spiPin = 0

	/**
	 * @var {string} type Mapping type
	 */
	private type = 'spi'

	/**
	 * @var {number} uartPin Mapping UART interface enable pin number
	 */
	private uartPin = 0;

	/**
	 * @constant {Array<IOption>} typeOptions Array of CoreUI select options for mapping type
	 */
	private typeOptions: Array<IOption> = [
		{
			value: 'spi',
			label: this.$t('config.daemon.interfaces.interfaceMapping.form.types.spi').toString()
		},
		{
			value: 'uart',
			label: this.$t('config.daemon.interfaces.interfaceMapping.form.types.uart').toString()
		}
	]

	/**
	 * Computes title of mapping modal
	 * @returns {string} Mapping modal title
	 */
	get modalTitle(): string {
		return this.mappingId === null ? 
			this.$t('config.daemon.interfaces.interfaceMapping.form.addTitle').toString(): this.$t('config.daemon.interfaces.interfaceMapping.form.editTitle').toString() + ' ' +this.name;
	}

	/**
	 * Computes array of CoreUI select options for baudrate
	 * @returns {Array<IOption>} Baudrate select options
	 */
	get baudRateOptions(): Array<IOption> {
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
	 * Shows this modal form
	 * @param {number|null} mappingId Mapping Id
	 */
	public showModal(mappingId: number|null): void {
		if (mappingId !== null) {
			this.mappingId = mappingId;
			this.getMapping();
		}
		this.modalShown = true;
	}

	/**
	 * Hides this modal form
	 */
	private hideModal(): void {
		this.modalShown = false;
		this.clearFields();
	}

	/**
	 * Resets fields to default values
	 */
	private clearFields(): void {
		this.type = 'spi';
		this.name = '';
		this.deviceName = '';
		this.busPin = 0;
		this.pgmPin = 0;
		this.powerPin = 0;
		this.baudRate = 57600;
		this.mappingId = null;
		this.gatewayMapping = false;
		this.i2cPin = 0;
		this.spiPin = 0;
		this.uartPin = 0;
	}

	/**
	 * Retrieves mapping if mappingId is passeds
	 */
	private getMapping(): void {
		if (this.mappingId === null) {
			return;
		}
		MappingService.getMapping(this.mappingId)
			.then((response: AxiosResponse) => {
				const mapping: IMapping = response.data;
				this.type = mapping.type;
				this.name = mapping.name;
				this.deviceName = mapping.IqrfInterface;
				this.busPin = mapping.busEnableGpioPin;
				this.pgmPin = mapping.pgmSwitchGpioPin;
				this.powerPin = mapping.powerEnableGpioPin;
				if (mapping.type === 'uart' && mapping.baudRate !== undefined) {
					this.baudRate = mapping.baudRate;
				}
				if (mapping.i2cEnableGpioPin === undefined && mapping.spiEnableGpioPin === undefined && mapping.uartEnableGpioPin === undefined) {
					return;
				}
				this.gatewayMapping = true;
				if (mapping.i2cEnableGpioPin !== undefined) {
					this.i2cPin = mapping.i2cEnableGpioPin;
				}
				if (mapping.spiEnableGpioPin !== undefined) {
					this.spiPin = mapping.spiEnableGpioPin;
				}
				if (mapping.uartEnableGpioPin !== undefined) {
					this.uartPin = mapping.uartEnableGpioPin;
				}
			})
			.catch((error: AxiosError) => {
				FormErrorHandler.mappingError(error);
				this.hideModal();
			});
	}

	/**
	 * Creates a mapping object for REST API request
	 * @returns {IMapping} Mapping object
	 */
	private buildMapping(): IMapping {
		let mapping: IMapping = {
			type: this.type,
			name: this.name,
			IqrfInterface: this.deviceName,
			busEnableGpioPin: this.busPin,
			pgmSwitchGpioPin: this.pgmPin,
			powerEnableGpioPin: this.powerPin
		};
		if (this.type === 'uart') {
			Object.assign(mapping, {baudRate: this.baudRate});
		}
		if (this.gatewayMapping) {
			Object.assign(mapping, {i2cEnableGpioPin: this.i2cPin, spiEnableGpioPin: this.spiPin, uartEnableGpioPin: this.uartPin});
		}
		return mapping;
	}

	/**
	 * Saves new or updates existing mapping
	 */
	private saveMapping(): void {
		this.$store.commit('spinner/SHOW');
		if (this.mappingId !== null) {
			MappingService.editMapping(this.mappingId, this.buildMapping())
				.then(() => this.successfulSave())
				.catch((error: AxiosError) => {
					FormErrorHandler.mappingError(error);
					this.hideModal();
				});
		} else {
			MappingService.addMapping(this.buildMapping())
				.then(() => this.successfulSave())
				.catch((error: AxiosError) => {
					FormErrorHandler.mappingError(error);
					this.hideModal();
				});
		}
	}

	/**
	 * Handles successful REST API response
	 */
	private successfulSave(): void {
		this.$store.commit('spinner/HIDE');
		if (this.mappingId === null) {
			this.$toast.success(
				this.$t('config.daemon.interfaces.interfaceMapping.messages.addSuccess', {mapping: this.name}).toString()
			);
		} else {
			this.$toast.success(
				this.$t('config.daemon.interfaces.interfaceMapping.messages.editSuccess', {mapping: this.name}).toString()
			);
		}
		this.hideModal();
		this.$emit('update-mappings');
	}

}
</script>
