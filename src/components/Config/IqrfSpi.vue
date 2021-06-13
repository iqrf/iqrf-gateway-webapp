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
			{{ $t('config.daemon.interfaces.iqrfSpi.title') }}
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
				<CForm
					v-if='configuration !== null'
					@submit.prevent='saveConfig'
				>
					<ValidationProvider
						v-if='powerUser'
						v-slot='{errors, touched, valid}'
						rules='required'
						:custom-messages='{required: "config.daemon.interfaces.iqrfSpi.errors.instance"}'
					>
						<CFormInput
							v-model='configuration.instance'
							:label='$t("forms.fields.instanceName")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='required'
						:custom-messages='{required: "config.daemon.interfaces.iqrfSpi.errors.iqrfInterface"}'
					>
						<CFormInput
							v-model='configuration.IqrfInterface'
							:label='$t("config.daemon.interfaces.iqrfSpi.form.iqrfInterface")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<CFormCheck
						:checked.sync='configuration.spiReset'
						:label='$t("config.daemon.interfaces.iqrfSpi.form.spiReset")'
					/>
					<CRow>
						<CCol :md='(configuration.i2cEnableGpioPin !== undefined || configuration.spiEnableGpioPin !== undefined || configuration.uartEnableGpioPin !== undefined) ? 6 : 12'>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required|integer'
								:custom-messages='{
									integer: "config.daemon.interfaces.interfaceMapping.errors.powerPin",
									required: "config.daemon.interfaces.interfaceMapping.errors.powerPin",
								}'
							>
								<CFormInput
									v-model.number='configuration.powerEnableGpioPin'
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
									v-model.number='configuration.busEnableGpioPin'
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
									required: "config.daemon.interfaces.interfaceMapping.errors.pgmPin",
								}'
							>
								<CFormInput
									v-model.number='configuration.pgmSwitchGpioPin'
									type='number'
									:label='$t("config.daemon.interfaces.interfaceMapping.form.pgmPin")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
						</CCol>
						<CCol
							v-if='(configuration.i2cEnableGpioPin !== undefined || configuration.spiEnableGpioPin !== undefined || configuration.uartEnableGpioPin !== undefined)'
							md='6'
						>
							<CFormInput
								v-if='configuration.i2cEnableGpioPin !== undefined'
								v-model.number='configuration.i2cEnableGpioPin'
								type='number'
								:label='$t("config.daemon.interfaces.interfaceMapping.form.i2cPin")'
								:disabled='true'
							/>
							<CFormInput
								v-if='configuration.spiEnableGpioPin !== undefined'
								v-model.number='configuration.spiEnableGpioPin'
								type='number'
								:label='$t("config.daemon.interfaces.interfaceMapping.form.spiPin")'
								:disabled='true'
							/>
							<CFormInput
								v-if='configuration.uartEnableGpioPin !== undefined'
								v-model.number='configuration.uartEnableGpioPin'
								type='number'
								:label='$t("config.daemon.interfaces.interfaceMapping.form.uartPin")'
								:disabled='true'
							/>
							<div>
								<i>{{ $t('config.daemon.interfaces.interfaceMapping.form.gwOnly') }}</i>
							</div><br>
						</CCol>
					</CRow>
					<CButton type='submit' color='primary' :disabled='invalid'>
						{{ $t('forms.save') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
		<CCardFooter>
			<h4>{{ $t('config.daemon.interfaces.iqrfSpi.mappings' ) }}</h4><hr>
			<CRow>
				<CCol md='6'>
					<InterfaceMappings interface-type='spi' @update-mapping='updateMapping' />
				</CCol>
				<CCol md='6'>
					<InterfacePorts interface-type='spi' @update-port='updatePort' />
				</CCol>
			</CRow>
		</CCardFooter>
	</CCard>
</template>

<script lang='ts'>
import {Options, Vue} from 'vue-property-decorator';
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
	CRow
} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {integer, required} from 'vee-validate/dist/rules';
import InterfaceMappings from '../../components/Config/InterfaceMappings.vue';
import InterfacePorts from '../../components/Config/InterfacePorts.vue';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import {IIqrfSpi} from '../../interfaces/iqrfInterfaces';
import {IMapping} from '../../interfaces/mappings';

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
		InterfaceMappings,
		InterfacePorts,
		ValidationObserver,
		ValidationProvider,
	}
})

/**
 * IQRF SPI communication interface configuration component
 */
export default class IqrfSpi extends Vue {

	/**
	 * @var {IIqrfSpi|null} configuration SPI component instance configuration
	 */
	private configuration: IIqrfSpi|null = null

	/**
	 * @constant {string} componentName SPI component name, used for REST API communication
	 */
	private componentName = 'iqrf::IqrfSpi'

	/**
	 * @var {string} instance SPI component instance name, used for REST API communication
	 */
	private instance = ''

	/**
	 * @var {boolean} powerUser Indicates whether user role is power user
	 */
	private powerUser = false

	/**
	 * @var {boolean} loadFailed Indicates whether configuration fetch failed
	 */
	private loadFailed = false

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('integer', integer);
		extend('required', required);
	}

	mounted(): void {
		if (this.$store.getters['user/getRole'] === 'power') {
			this.powerUser = true;
		}
		this.getConfig();
	}

	/**
	 * Retrieves configuration of IQRF SPI interface component
	 */
	private getConfig(): Promise<void> {
		return DaemonConfigurationService.getComponent(this.componentName)
			.then((response: AxiosResponse) => {
				if (response.data.instances.length > 0) {
					this.configuration = response.data.instances[0];
					this.instance = response.data.instances[0].instance;
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
		console.warn(this.configuration);
		this.$store.commit('spinner/SHOW');
		if (this.instance !== '') {
			DaemonConfigurationService.updateInstance(this.componentName, this.instance, this.configuration)
				.then(() => this.successfulSave())
				.catch((error: AxiosError) => FormErrorHandler.configError(error));
		} else {
			DaemonConfigurationService.createInstance(this.componentName, this.configuration)
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
	 * @param {IMapping} mapping Board mapping
	 */
	private updateMapping(mapping: IMapping): void {
		let config = {
			component: this.componentName,
			instance: this.configuration!.instance,
			IqrfInterface: mapping.IqrfInterface,
			busEnableGpioPin: mapping.busEnableGpioPin,
			powerEnableGpioPin: mapping.powerEnableGpioPin,
			pgmSwitchGpioPin: mapping.pgmSwitchGpioPin,
			spiReset: this.configuration!.spiReset,
		};
		if (mapping.i2cEnableGpioPin !== undefined) {
			Object.assign(config, {i2cEnableGpioPin: mapping.i2cEnableGpioPin});
		}
		if (mapping.spiEnableGpioPin !== undefined) {
			Object.assign(config, {spiEnableGpioPin: mapping.spiEnableGpioPin});
		}
		if (mapping.uartEnableGpioPin !== undefined) {
			Object.assign(config, {uartEnableGpioPin: mapping.uartEnableGpioPin});
		}
		this.configuration = config;
	}

	/**
	 * Updates port in configuration from mapping
	 * @param {string} port Port
	 */
	private updatePort(port: string): void {
		if (this.configuration !== null) {
			this.configuration.IqrfInterface = port;
		}
	}
}
</script>
