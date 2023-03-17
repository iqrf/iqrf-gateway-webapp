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
	<v-dialog
		v-model='showModal'
		width='50%'
		persistent
		no-click-animation
	>
		<ValidationObserver v-if='mapping !== null' v-slot='{invalid}'>
			<v-card>
				<v-card-title>
					{{ modalTitle }}
				</v-card-title>
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
							:items='mappingTypeOptions'
							:placeholder='$t("config.daemon.interfaces.interfaceMapping.errors.typeSelect")'
						/>
						<v-select
							v-model='mapping.deviceType'
							:label='$t("config.daemon.interfaces.interfaceMapping.form.deviceType")'
							:items='deviceTypeOptions'
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
						<v-select
							v-if='mapping.type === MappingType.UART'
							v-model='mapping.baudRate'
							:items='baudRateOptions'
							:label='$t("config.daemon.interfaces.interfaceMapping.form.baudRate")'
						/>
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
							dense
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
					</form>
				</v-card-text>
				<v-card-actions>
					<v-spacer />
					<v-btn
						@click='hideModal'
					>
						{{ $t('forms.cancel') }}
					</v-btn>
					<v-btn
						:color='modalColor'
						:disabled='invalid'
						@click='saveMapping'
					>
						{{ $t('forms.save') }}
					</v-btn>
				</v-card-actions>
			</v-card>
		</ValidationObserver>
	</v-dialog>
</template>

<script lang='ts'>
import {Component, VModel, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {extendedErrorToast} from '@/helpers/errorToast';
import {integer, required} from 'vee-validate/dist/rules';
import {ConfigDeviceType, MappingType} from '@/enums/Config/ConfigurationProfiles';

import MappingService from '@/services/MappingService';

import {AxiosError} from 'axios';
import {IMapping} from '@/interfaces/Config/Mapping';
import {ISelectItem} from '@/interfaces/Vuetify';

@Component({
	components: {
		ValidationObserver,
		ValidationProvider,
	},
	data: () => ({
		MappingType,
	}),
})

/**
 * Modal form to add or edit mapping
 */
export default class MappingFormModal extends Vue {

	/**
	 * @property {IMapping|null} mapping Edited mapping
	 */
	@VModel({required: true, default: null}) mapping!: IMapping|null;

	/**
	 * @var {boolean} useAdditionalPins Use additional pins
	 */
	private useAdditionalPins = false;

	/**
	 * Computes modal display condition
	 */
	get showModal(): boolean {
		return this.mapping !== null;
	}

	/**
	 * Computes mapping type options
	 * @return {Array<ISelectItem>} Mapping type options
	 */
	get mappingTypeOptions(): Array<ISelectItem> {
		const types: Array<MappingType> = [MappingType.SPI, MappingType.UART];
		return types.map((item: MappingType): ISelectItem => ({
			text: this.$t(`config.daemon.interfaces.types.${item}`).toString(),
			value: item,
		}));
	}

	/**
	 * Computes device type options
	 * @return {Array<ISelectItem>} Device type options
	 */
	get deviceTypeOptions(): Array<ISelectItem> {
		const types: Array<ConfigDeviceType> = [ConfigDeviceType.ADAPTER, ConfigDeviceType.BOARD];
		return types.map((item: ConfigDeviceType): ISelectItem => ({
			text: this.$t(`config.daemon.interfaces.interfaceMapping.form.deviceTypes.${item}`).toString(),
			value: item,
		}));
	}

	/**
	 * Computes select options for baudrate
	 * @returns {Array<ISelectItem>} Baudrate select options
	 */
	get baudRateOptions(): Array<ISelectItem> {
		const baudRates: Array<number> = [1200, 2400, 4800, 9600, 19200, 38400, 57600, 115200, 230400];
		return baudRates.map((baudRate: number) => ({
			value: baudRate,
			text: baudRate + ' Bd',
		}));
	}

	/**
	 * Computes modal color
	 * @returns {string} Modal color
	 */
	get modalColor(): string {
		if (this.mapping?.id === undefined) {
			return 'success';
		}
		return 'primary';
	}

	/**
	 * Computes title of mapping modal
	 * @returns {string} Mapping modal title
	 */
	get modalTitle(): string {
		if (this.mapping?.id === undefined) {
			return this.$t('config.daemon.interfaces.interfaceMapping.add').toString();
		}
		return this.$t('config.daemon.interfaces.interfaceMapping.edit').toString();
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
		if (this.mapping === null) {
			return;
		}
		const mapping = {...this.mapping};
		if (!this.useAdditionalPins) {
			delete mapping.i2cEnableGpioPin;
			delete mapping.spiEnableGpioPin;
			delete mapping.uartEnableGpioPin;
		}
		if (mapping.type !== MappingType.UART) {
			delete mapping.baudRate;
		}
		const id = mapping.id;
		const name = mapping.name;
		delete mapping.id;
		this.$store.commit('spinner/SHOW');
		if (id === undefined) {
			MappingService.addMapping(mapping)
				.then(() => this.handleSuccess(name))
				.catch((error: AxiosError) => this.handleFailure(error, name));

		} else {
			MappingService.editMapping(id, mapping)
				.then(() => this.handleSuccess(name))
				.catch((error: AxiosError) => this.handleFailure(error, name));
		}
	}

	/**
	 * Handles REST API success
	 * @param {string} name Mapping name
	 */
	private handleSuccess(name: string): void {
		this.$store.commit('spinner/HIDE');
		this.$toast.success(
			this.$t('config.daemon.interfaces.interfaceMapping.messages.saveSuccess', {mapping: name}).toString()
		);
		this.hideModal();
		this.$emit('update-mappings');
	}

	/**
	 * Handles REST API failure
	 * @param {AxiosError} error Error response
	 * @param {string} name Mapping name
	 */
	private handleFailure(error: AxiosError, name: string): void {
		extendedErrorToast(error, 'config.daemon.interfaces.interfaceMapping.messages.saveFailed', {mapping: name});
	}

	/**
	 * Hides modal window
	 */
	private hideModal(): void {
		this.mapping = null;
	}
}
</script>
