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
	<div>
		<v-card class='mb-5'>
			<v-card-title>
				{{ $t('config.daemon.interfaces.iqrfSpi.title') }}
			</v-card-title>
			<v-card-text>
				<v-overlay
					v-if='loadFailed'
					:opacity='0.65'
					absolute
				>
					{{ $t('config.daemon.messages.failedElement') }}
				</v-overlay>
				<ValidationObserver v-slot='{invalid}'>
					<v-form @submit.prevent='saveConfig'>
						<ValidationProvider
							v-if='isAdmin'
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("config.daemon.interfaces.iqrfSpi.errors.instance"),
							}'
						>
							<v-text-field
								v-model='configuration.instance'
								:label='$t("forms.fields.instanceName")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("config.daemon.interfaces.iqrfSpi.errors.iqrfInterface"),
							}'
						>
							<v-text-field
								v-model='configuration.IqrfInterface'
								:label='$t("config.daemon.interfaces.iqrfSpi.form.iqrfInterface")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<v-checkbox
							v-model='configuration.spiReset'
							:label='$t("config.daemon.interfaces.iqrfSpi.form.spiReset")'
							dense
						/>
						<v-row>
							<v-col cols='12' md='4'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required|integer'
									:custom-messages='{
										integer: $t("config.daemon.interfaces.interfaceMapping.errors.powerPin"),
										required: $t("config.daemon.interfaces.interfaceMapping.errors.powerPin"),
									}'
								>
									<v-text-field
										v-model.number='configuration.powerEnableGpioPin'
										type='number'
										:label='$t("config.daemon.interfaces.interfaceMapping.form.powerPin")'
										:success='touched ? valid : null'
										:error-messages='errors'
									/>
								</ValidationProvider>
							</v-col>
							<v-col cols='12' md='4'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required|integer'
									:custom-messages='{
										integer: $t("config.daemon.interfaces.interfaceMapping.errors.busPin"),
										required: $t("config.daemon.interfaces.interfaceMapping.errors.busPin"),
									}'
								>
									<v-text-field
										v-model.number='configuration.busEnableGpioPin'
										type='number'
										:label='$t("config.daemon.interfaces.interfaceMapping.form.busPin")'
										:success='touched ? valid : null'
										:error-messages='errors'
									/>
								</ValidationProvider>
							</v-col>
							<v-col cols='12' md='4'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required|integer'
									:custom-messages='{
										integer: $t("config.daemon.interfaces.interfaceMapping.errors.pgmPin"),
										required: $t("config.daemon.interfaces.interfaceMapping.errors.pgmPin"),
									}'
								>
									<v-text-field
										v-model.number='configuration.pgmSwitchGpioPin'
										type='number'
										:label='$t("config.daemon.interfaces.interfaceMapping.form.pgmPin")'
										:success='touched ? valid : null'
										:error-messages='errors'
									/>
								</ValidationProvider>
							</v-col>
						</v-row>
						<v-checkbox
							v-model='useAdditionalPins'
							:label='$t("config.daemon.interfaces.interfaceMapping.form.useAdditionalPins")'
							dense
						/>
						<v-row>
							<v-col cols='12' md='4'>
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
										v-model.number='configuration.i2cEnableGpioPin'
										type='number'
										:label='$t("config.daemon.interfaces.interfaceMapping.form.i2cPin")'
										:success='touched ? valid : null'
										:error-messages='errors'
										:disabled='!useAdditionalPins'
									/>
								</ValidationProvider>
							</v-col>
							<v-col cols='12' md='4'>
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
										v-model.number='configuration.spiEnableGpioPin'
										type='number'
										:label='$t("config.daemon.interfaces.interfaceMapping.form.spiPin")'
										:success='touched ? valid : null'
										:error-messages='errors'
										:disabled='!useAdditionalPins'
									/>
								</ValidationProvider>
							</v-col>
							<v-col cols='12' md='4'>
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
										v-model.number='configuration.uartEnableGpioPin'
										type='number'
										:label='$t("config.daemon.interfaces.interfaceMapping.form.uartPin")'
										:success='touched ? valid : null'
										:error-messages='errors'
										:disabled='!useAdditionalPins'
									/>
								</ValidationProvider>
							</v-col>
						</v-row>
						<v-btn
							type='submit'
							color='primary'
							:disabled='invalid'
						>
							{{ $t('forms.save') }}
						</v-btn>
					</v-form>
				</ValidationObserver>
			</v-card-text>
		</v-card>
		<v-card>
			<v-card-text>
				<InterfaceMappings
					:interface-type='MappingType.SPI'
					@update-mapping='updateMapping'
				/>
				<v-divider class='my-2' />
				<InterfacePorts
					:interface-type='MappingType.SPI'
					@update-port='updatePort'
				/>
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import InterfaceMappings from '@/components/Config/Interfaces/InterfaceMappings.vue';
import InterfacePorts from '@/components/Config/Interfaces/InterfacePorts.vue';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {extendedErrorToast} from '@/helpers/errorToast';
import {integer, required} from 'vee-validate/dist/rules';
import {MappingType} from '@/enums/Config/ConfigurationProfiles';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {IIqrfSpi} from '@/interfaces/Config/IqrfInterfaces';
import {IMapping} from '@/interfaces/Config/Mapping';
import {UserRole} from '@iqrf/iqrf-gateway-webapp-client';

@Component({
	components: {
		InterfaceMappings,
		InterfacePorts,
		ValidationObserver,
		ValidationProvider,
	},
	data: () => ({
		MappingType,
	})
})

/**
 * IQRF SPI communication interface configuration component
 */
export default class IqrfSpi extends Vue {
	/**
	 * @var {IIqrfSpi} configuration SPI component instance configuration
	 */
	private configuration: IIqrfSpi = {
		component: 'iqrf::IqrfSpi',
		instance: '',
		IqrfInterface: '',
		powerEnableGpioPin: 0,
		busEnableGpioPin: 0,
		pgmSwitchGpioPin: 0,
		spiReset: true,
		i2cEnableGpioPin: 0,
		spiEnableGpioPin: 0,
		uartEnableGpioPin: 0
	};

	/**
	 * @var {boolean} useAdditionalPins Use additional pins
	 */
	private useAdditionalPins = false;

	/**
	 * @constant {string} componentName SPI component name, used for REST API communication
	 */
	private componentName = 'iqrf::IqrfSpi';

	/**
	 * @var {string} instance SPI component instance name, used for REST API communication
	 */
	private instance = '';

	/**
	 * @var {boolean} loadFailed Indicates whether configuration fetch failed
	 */
	private loadFailed = false;

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('integer', integer);
		extend('required', required);
	}

	/**
	 * Retrieves user role and spi configuration
	 */
	mounted(): void {
		this.getConfig();
	}

	/**
	 * Checks if user is an administrator
   * @returns {boolean} True if user is an administrator
   */
	get isAdmin(): boolean {
		return this.$store.getters['user/getRole'] === UserRole.Admin;
	}

	/**
	 * Retrieves configuration of IQRF SPI interface component
	 */
	private getConfig(): Promise<void> {
		return DaemonConfigurationService.getComponent(this.componentName)
			.then((response: AxiosResponse) => {
				if (response.data.instances.length > 0) {
					this.instance = response.data.instances[0].instance;
					const config: IIqrfSpi = response.data.instances[0];
					this.useAdditionalPins = !(config.i2cEnableGpioPin === undefined && config.spiEnableGpioPin === undefined && config.uartEnableGpioPin === undefined);
					this.configuration = config;
				}
				this.$emit('fetched', {name: 'iqrfSpi', success: true});
			})
			.catch(() => {
				this.loadFailed = true;
				this.$emit('fetched', {name: 'iqrfSpi', success: false});
			});
	}

	/**
	 * Saves new or updates existing configuration of IQRF SPI interface component instance
	 */
	private saveConfig(): void {
		const config: IIqrfSpi = JSON.parse(JSON.stringify(this.configuration));
		if (!this.useAdditionalPins) {
			delete config.i2cEnableGpioPin;
			delete config.spiEnableGpioPin;
			delete config.uartEnableGpioPin;
		}
		this.$store.commit('spinner/SHOW');
		if (this.instance !== '') {
			DaemonConfigurationService.updateInstance(this.componentName, this.instance, config)
				.then(this.handleSuccess)
				.catch(this.handleFailure);
		} else {
			DaemonConfigurationService.createInstance(this.componentName, config)
				.then(this.handleSuccess)
				.catch(this.handleFailure);
		}
	}

	/**
	 * Handles REST API success
	 */
	private handleSuccess(): void {
		this.getConfig().then(() => {
			this.$toast.success(
				this.$t('config.daemon.interfaces.iqrfSpi.messages.saveSuccess').toString()
			);
		});
	}

	/**
	 * Handles REST API failure
	 * @param {AxiosError} err Error response
	 */
	private handleFailure(err: AxiosError): void {
		extendedErrorToast(err, 'config.daemon.interfaces.iqrfSpi.messages.saveFailed');
	}

	/**
	 * Updates pin configuration from mapping
	 * @param {IMapping} mapping Board mapping
	 */
	private updateMapping(mapping: IMapping): void {
		this.configuration.IqrfInterface = mapping.IqrfInterface;
		this.configuration.powerEnableGpioPin = mapping.powerEnableGpioPin;
		this.configuration.pgmSwitchGpioPin = mapping.pgmSwitchGpioPin;
		this.configuration.busEnableGpioPin = mapping.busEnableGpioPin;
		if (mapping.i2cEnableGpioPin !== undefined && mapping.spiEnableGpioPin !== undefined && mapping.uartEnableGpioPin !== undefined) {
			this.configuration.i2cEnableGpioPin = mapping.i2cEnableGpioPin;
			this.configuration.spiEnableGpioPin = mapping.spiEnableGpioPin;
			this.configuration.uartEnableGpioPin = mapping.uartEnableGpioPin;
			this.useAdditionalPins = true;
		} else {
			this.configuration.i2cEnableGpioPin = this.configuration.spiEnableGpioPin = this.configuration.uartEnableGpioPin = 0;
			this.useAdditionalPins = false;
		}
	}

	/**
	 * Updates port in configuration from mapping
	 * @param {string} port Port
	 */
	private updatePort(port: string): void {
		this.configuration.IqrfInterface = port;
	}
}
</script>
