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
		<v-dialog
			v-model='show'
			width='50%'
			persistent
		>
			<template #activator='{on, attrs}'>
				<v-list-item v-bind='attrs' v-on='on'>
					<v-icon dense>
						mdi-pencil
					</v-icon>
					{{ $t('config.daemon.interfaces.interfaceMapping.edit') }}
				</v-list-item>
			</template>
			<v-card>
				<v-card-title>{{ modalTitle }}</v-card-title>
				<v-card-text>
					<form>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("config.daemon.interfaces.interfaceMapping.errors.name"),
							}'
						>
							<v-text-field
								v-model='mapping.name'
								:label='$t("config.daemon.interfaces.interfaceMapping.form.name")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<v-select
							v-model='mapping.type'
							:label='$t("config.daemon.interfaces.interfaceMapping.form.type")'
							:items='typeOptions'
							:placeholder='$t("config.daemon.interfaces.interfaceMapping.errors.typeSelect")'
						/>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("config.daemon.interfaces.interfaceMapping.errors.interface"),
							}'
						>
							<v-text-field
								v-model='mapping.IqrfInterface'
								:label='$t("config.daemon.interfaces.interfaceMapping.form.interface")'
								:success='touched ? valid : null'
								:error-messages='errors'
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
							<v-text-field
								v-model.number='mapping.powerEnableGpioPin'
								type='number'
								:label='$t("config.daemon.interfaces.interfaceMapping.form.powerPin")'
								:success='touched ? valid : null'
								:error-messages='errors'
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
							<v-text-field
								v-model.number='mapping.busEnableGpioPin'
								type='number'
								:label='$t("config.daemon.interfaces.interfaceMapping.form.busPin")'
								:success='touched ? valid : null'
								:error-messages='errors'
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
							<v-text-field
								v-model.number='mapping.pgmSwitchGpioPin'
								type='number'
								:label='$t("config.daemon.interfaces.interfaceMapping.form.pgmPin")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<v-checkbox
							v-model='useAdditionalPins'
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
							<v-text-field
								v-model.number='mapping.i2cEnableGpioPin'
								type='number'
								:label='$t("config.daemon.interfaces.interfaceMapping.form.i2cPin")'
								:success='touched ? valid : null'
								:error-messages='errors'
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
							<v-text-field
								v-model.number='mapping.spiEnableGpioPin'
								type='number'
								:label='$t("config.daemon.interfaces.interfaceMapping.form.spiPin")'
								:success='touched ? valid : null'
								:error-messages='errors'
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
							<v-text-field
								v-model.number='mapping.uartEnableGpioPin'
								type='number'
								:label='$t("config.daemon.interfaces.interfaceMapping.form.uartPin")'
								:success='touched ? valid : null'
								:error-messages='errors'
								:disabled='!useAdditionalPins'
							/>
						</ValidationProvider>
						<v-select
							v-if='mapping.type === "uart"'
							v-model='mapping.baudRate'
							:items='baudRateOptions'
							:label='$t("config.daemon.interfaces.interfaceMapping.form.baudRate")'
						/>
					</form>
				</v-card-text>
				<v-card-actions>
					<v-btn
						:color='modalColor'
						:disabled='invalid'
						@click='saveMapping'
					>
						{{ $t('forms.save') }}
					</v-btn>
					<v-spacer />
					<v-btn
						color='secondary'
						@click='deactivateModal'
					>
						{{ $t('forms.cancel') }}
					</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>
	</ValidationObserver>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {extendedErrorToast} from '@/helpers/errorToast';
import {integer, required} from 'vee-validate/dist/rules';

import MappingService from '@/services/MappingService';

import {AxiosError} from 'axios';
import {IMapping, MappingType} from '@/interfaces/mappings';
import {IOption} from '@/interfaces/coreui';

@Component({
	components: {
		ValidationObserver,
		ValidationProvider,
	}
})

/**
 * Modal form to add or edit mapping
 */
export default class MappingForm extends Vue {
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
		type: null,
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
	 * @constant {Array<IOption>} typeOptions Array of CoreUI select options for mapping type
	 */
	private typeOptions: Array<IOption> = [
		{
			value: 'spi',
			text: this.$t('config.daemon.interfaces.types.spi').toString(),
		},
		{
			value: 'uart',
			text: this.$t('config.daemon.interfaces.types.uart').toString(),
		}
	];

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
		return baudRates.map((baudRate: number) => ({value: baudRate, text: baudRate + ' Bd'}));
	}

	/**
	 * Vue lifecycle hook created
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
			this.mapping = mapping;
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
