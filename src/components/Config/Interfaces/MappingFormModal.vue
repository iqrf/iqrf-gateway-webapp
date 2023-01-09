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
	<ValidationObserver v-slot='{invalid}'>
		<CModal
			:show.sync='show'
			:color='modalColor'
			size='lg'
			:close-on-backdrop='false'
			:fade='false'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ modalTitle }}
				</h5>
			</template>
			<CForm>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					rules='required'
					:custom-messages='{
						required: $t("config.daemon.interfaces.interfaceMapping.errors.name"),
					}'
				>
					<CInput
						v-model='mapping.name'
						:label='$t("config.daemon.interfaces.interfaceMapping.form.name")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='errors.join(", ")'
					/>
				</ValidationProvider>
				<CSelect
					:value.sync='mapping.type'
					:label='$t("config.daemon.interfaces.interfaceMapping.form.type")'
					:options='mappingTypeOptions'
				/>
				<CSelect
					:value.sync='mapping.deviceType'
					:label='$t("config.daemon.interfaces.interfaceMapping.form.deviceType")'
					:options='deviceTypeOptions'
				/>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					rules='required'
					:custom-messages='{
						required: $t("config.daemon.interfaces.interfaceMapping.errors.interface"),
					}'
				>
					<CInput
						v-model='mapping.IqrfInterface'
						:label='$t("config.daemon.interfaces.interfaceMapping.form.interface")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='errors.join(", ")'
					/>
				</ValidationProvider>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					rules='integer|required'
					:custom-messages='{
						integer: $t("config.daemon.interfaces.interfaceMapping.errors.powerPin"),
						required: $t("config.daemon.interfaces.interfaceMapping.errors.powerPin"),
					}'
				>
					<CInput
						v-model.number='mapping.powerEnableGpioPin'
						type='number'
						:label='$t("config.daemon.interfaces.interfaceMapping.form.powerPin")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='errors.join(", ")'
					/>
				</ValidationProvider>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					rules='integer|required'
					:custom-messages='{
						integer: $t("config.daemon.interfaces.interfaceMapping.errors.busPin"),
						required: $t("config.daemon.interfaces.interfaceMapping.errors.busPin"),
					}'
				>
					<CInput
						v-model.number='mapping.busEnableGpioPin'
						type='number'
						:label='$t("config.daemon.interfaces.interfaceMapping.form.busPin")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='errors.join(", ")'
					/>
				</ValidationProvider>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					rules='integer|required'
					:custom-messages='{
						integer: $t("config.daemon.interfaces.interfaceMapping.errors.pgmPin"),
						required: $t("config.daemon.interfaces.interfaceMapping.errors.pgmPin"),
					}'
				>
					<CInput
						v-model.number='mapping.pgmSwitchGpioPin'
						type='number'
						:label='$t("config.daemon.interfaces.interfaceMapping.form.pgmPin")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='errors.join(", ")'
					/>
				</ValidationProvider>
				<CInputCheckbox
					:checked.sync='useAdditionalPins'
					:label='$t("config.daemon.interfaces.interfaceMapping.form.useAdditionalPins")'
				/>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					:rules='{
						integer: useAdditionalPins,
						required: useAdditionalPins,
					}'
					:custom-messages='{
						integer: $t("config.daemon.interfaces.interfaceMapping.errors.i2cPin"),
						required: $t("config.daemon.interfaces.interfaceMapping.errors.i2cPin"),
					}'
				>
					<CInput
						v-model.number='mapping.i2cEnableGpioPin'
						type='number'
						:label='$t("config.daemon.interfaces.interfaceMapping.form.i2cPin")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='errors.join(", ")'
						:disabled='!useAdditionalPins'
					/>
				</ValidationProvider>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					:rules='{
						integer: useAdditionalPins,
						required: useAdditionalPins,
					}'
					:custom-messages='{
						integer: $t("config.daemon.interfaces.interfaceMapping.errors.spiPin"),
						required: $t("config.daemon.interfaces.interfaceMapping.errors.spiPin"),
					}'
				>
					<CInput
						v-model.number='mapping.spiEnableGpioPin'
						type='number'
						:label='$t("config.daemon.interfaces.interfaceMapping.form.spiPin")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='errors.join(", ")'
						:disabled='!useAdditionalPins'
					/>
				</ValidationProvider>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					:rules='{
						integer: useAdditionalPins,
						required: useAdditionalPins,
					}'
					:custom-messages='{
						integer: $t("config.daemon.interfaces.interfaceMapping.errors.uartPin"),
						required: $t("config.daemon.interfaces.interfaceMapping.errors.uartPin"),
					}'
				>
					<CInput
						v-model.number='mapping.uartEnableGpioPin'
						type='number'
						:label='$t("config.daemon.interfaces.interfaceMapping.form.uartPin")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='errors.join(", ")'
						:disabled='!useAdditionalPins'
					/>
				</ValidationProvider>
				<CSelect
					v-if='mapping.type === "uart"'
					:value.sync='mapping.baudRate'
					:options='baudRateOptions'
					:label='$t("config.daemon.interfaces.interfaceMapping.form.baudRate")'
				/>
			</CForm>
			<template #footer>
				<CButton
					:color='modalColor'
					:disabled='invalid'
					@click='saveMapping'
				>
					{{ $t('forms.save') }}
				</CButton> <CButton
					color='secondary'
					@click='deactivateModal'
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
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {extendedErrorToast} from '@/helpers/errorToast';
import {integer, required} from 'vee-validate/dist/rules';
import {MappingDeviceType, MappingType} from '@/enums/Config/Mapping';

import MappingService from '@/services/MappingService';

import {AxiosError} from 'axios';
import {IMapping} from '@/interfaces/Config/Mapping';
import {IOption} from '@/interfaces/Coreui';

@Component({
	components: {
		CButton,
		CForm,
		CInput,
		CModal,
		CSelect,
		ValidationObserver,
		ValidationProvider,
	},
})

/**
 * Modal form to add or edit mapping
 */
export default class MappingFormModal extends Vue {
	/**
	 * @var {boolean} show Controls whether modal mapping form is shown
	 */
	private show = false;

	/**
	 * @var {number} id Mapping id
	 */
	private id = -1;

	/**
	 * @var {IMapping} mapping Default mapping
	 */
	private defaultMapping: IMapping = {
		name: '',
		type: MappingType.SPI,
		deviceType: MappingDeviceType.ADAPTER,
		IqrfInterface: '',
		powerEnableGpioPin: 0,
		pgmSwitchGpioPin: 0,
		busEnableGpioPin: 0,
		i2cEnableGpioPin: 0,
		spiEnableGpioPin: 0,
		uartEnableGpioPin: 0,
		baudRate: 57600,
	};

	/**
	 * @var {IMapping} mapping Mapping
	 */
	private mapping: IMapping = this.defaultMapping;

	/**
	 * @var {boolean} useAdditionalPins Use additional pins
	 */
	private useAdditionalPins = false;

	/**
	 * Computes mapping type options
	 * @return {Array<IOption>} Mapping type options
	 */
	get mappingTypeOptions(): Array<IOption> {
		const types: Array<MappingType> = [MappingType.SPI, MappingType.UART];
		return types.map((item: MappingType): IOption => ({
			label: this.$t(`config.daemon.interfaces.types.${item}`).toString(),
			value: item,
		}));
	}

	/**
	 * Computes device type options
	 * @return {Array<IOption>} Device type options
	 */
	get deviceTypeOptions(): Array<IOption> {
		const types: Array<MappingDeviceType> = [MappingDeviceType.ADAPTER, MappingDeviceType.BOARD];
		return types.map((item: MappingDeviceType): IOption => ({
			label: this.$t(`config.daemon.interfaces.interfaceMapping.form.deviceTypes.${item}`).toString(),
			value: item,
		}));
	}

	/**
	 * Computes title of mapping modal
	 * @returns {string} Mapping modal title
	 */
	get modalTitle(): string {
		if (this.id === -1) {
			return this.$t('config.daemon.interfaces.interfaceMapping.add').toString();
		}
		return this.$t('config.daemon.interfaces.interfaceMapping.edit').toString();
	}

	/**
	 * Computes modal color
	 * @returns {string} Modal color
	 */
	get modalColor(): string {
		return (this.id === -1 ? 'success' : 'primary');
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
	 * Initializes validation rules
	 */
	created(): void {
		extend('integer', integer);
		extend('required', required);
	}

	/**
	 * Saves new or updates existing mapping
	 */
	private saveMapping(): void {
		const mapping: IMapping = JSON.parse(JSON.stringify(this.mapping));
		delete mapping.id;
		if (!this.useAdditionalPins) {
			delete mapping.i2cEnableGpioPin;
			delete mapping.spiEnableGpioPin;
			delete mapping.uartEnableGpioPin;
		}
		if (mapping.type !== MappingType.UART) {
			delete mapping.baudRate;
		}
		this.$store.commit('spinner/SHOW');
		if (this.id !== -1) {
			MappingService.editMapping(this.id, mapping)
				.then(this.handleSuccess)
				.catch(this.handleFailure);
		} else {
			MappingService.addMapping(mapping)
				.then(this.handleSuccess)
				.catch(this.handleFailure);
		}
	}

	/**
	 * Handles REST API success
	 */
	private handleSuccess(): void {
		this.$store.commit('spinner/HIDE');
		this.$toast.success(
			this.$t('config.daemon.interfaces.interfaceMapping.messages.saveSuccess', {mapping: this.mapping.name}).toString()
		);
		this.deactivateModal();
		this.$emit('update-mappings');
	}

	/**
	 * Handles REST API failure
	 * @param {AxiosError} err Error response
	 */
	private handleFailure(err: AxiosError): void {
		extendedErrorToast(err, 'config.daemon.interfaces.interfaceMapping.messages.saveFailed', {mapping: this.mapping.name});
	}

	/**
	 * Stores mapping and renders the modal window
	 */
	public activateModal(mapping: IMapping|null): void {
		if (mapping !== null) {
			this.mapping = {...mapping};
			this.id = (mapping.id as number);
			if (mapping.uartEnableGpioPin !== undefined && mapping.spiEnableGpioPin !== undefined) {
				this.useAdditionalPins = true;
			}
		}
		this.show = true;
	}

	/**
	 * Clears mapping and closes the modal window
	 */
	private deactivateModal(): void {
		this.show = false;
		this.id = -1;
		this.mapping = this.defaultMapping;
		this.useAdditionalPins = false;
	}
}
</script>
