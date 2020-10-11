<template>
	<div>
		<h1>{{ $t('config.iqrfSpi.title') }}</h1>
		<CCard>
			<CCardBody>
				<ValidationObserver v-slot='{ invalid }'>
					<CForm @submit.prevent='saveConfig'>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required'
							:custom-messages='{required: "config.iqrfSpi.form.messages.instance"}'
						>
							<CInput
								v-model='configuration.instance'
								:label='$t("config.iqrfSpi.form.instance")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required'
							:custom-messages='{required: "config.iqrfSpi.form.messages.interface"}'
						>
							<CInput
								v-model='configuration.IqrfInterface'
								:label='$t("config.iqrfSpi.form.IqrfInterface")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required|integer'
							:custom-messages='{
								integer: "config.iqrfSpi.form.messages.powerEnableGpioPin",
								required: "config.iqrfSpi.form.messages.powerEnableGpioPin",
							}'
						>
							<CInput
								v-model='configuration.powerEnableGpioPin'
								:label='$t("config.iqrfSpi.form.powerEnableGpioPin")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required|integer'
							:custom-messages='{
								integer: "config.iqrfSpi.form.messages.busEnableGpioPin",
								required: "config.iqrfSpi.form.messages.busEnableGpioPin",
							}'
						>
							<CInput
								v-model='configuration.busEnableGpioPin'
								:label='$t("config.iqrfSpi.form.busEnableGpioPin")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required|integer'
							:custom-messages='{
								integer: "config.iqrfSpi.form.messages.pgmSwitchGpioPin",
								required: "config.iqrfSpi.form.messages.pgmSwitchGpioPin",
							}'
						>
							<CInput
								v-model='configuration.pgmSwitchGpioPin'
								:label='$t("config.iqrfSpi.form.pgmSwitchGpioPin")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<CInputCheckbox
							:checked.sync='configuration.spiReset'
							:label='$t("config.iqrfSpi.form.spiReset")'
						/>
						<CButton type='submit' color='primary' :disabled='invalid'>
							{{ $t('forms.save') }}
						</CButton>
					</CForm>
				</ValidationObserver>
			</CCardBody>
		</CCard>
		<CCard>
			<CCardHeader>{{ $t('config.iqrfSpi.mappings' ) }}</CCardHeader>
			<CCardBody>
				<CRow>
					<CCol md='6'>
						<InterfaceMappings interface-type='spi' @update-mapping='updateMapping' />
					</CCol>
					<CCol md='6'>
						<InterfacePorts interface-type='spi' @update-port='updatePort' />
					</CCol>
				</CRow>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import Vue from 'vue';
import {AxiosError, AxiosResponse} from 'axios';
import {
	CButton,
	CCard,
	CCardBody,
	CCardHeader,
	CCol,
	CForm,
	CInput,
	CInputCheckbox,
	CRow
} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {integer, required} from 'vee-validate/dist/rules';
import InterfaceMappings from '../../components/Config/InterfaceMappings.vue';
import InterfacePorts from '../../components/Config/InterfacePorts.vue';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';

export default Vue.extend({
	name: 'IqrfSpi',
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CCol,
		CForm,
		CInput,
		CInputCheckbox,
		CRow,
		InterfaceMappings,
		InterfacePorts,
		ValidationObserver,
		ValidationProvider,
	},
	data(): any {
		return {
			componentName: 'iqrf::IqrfSpi',
			configuration: {
				instance: null,
				IqrfInterface: null,
				powerEnableGpioPin: null,
				busEnableGpioPin: null,
				pgmSwitchGpioPin: null,
				spiReset: null,
			},
			instance: null,
		};
	},
	created() {
		extend('integer', integer);
		extend('required', required);
		this.getConfig();
	},
	methods: {
		getConfig() {
			this.$store.commit('spinner/SHOW');
			DaemonConfigurationService.getComponent(this.componentName)
				.then((response: AxiosResponse) => {
					this.$store.commit('spinner/HIDE');
					if (response.data.instances.length > 0) {
						this.configuration = response.data.instances[0];
						this.instance = this.configuration.instance;
					}
				})
				.catch((error: AxiosError) => FormErrorHandler.configError(error));
		},
		saveConfig() {
			this.$store.commit('spinner/SHOW');
			if (this.instance !== null) {
				DaemonConfigurationService.updateInstance(this.componentName, this.instance, this.configuration)
					.then(() => this.successfulSave())
					.catch((error) => FormErrorHandler.configError(error));
			} else {
				DaemonConfigurationService.createInstance(this.componentName, this.configuration)
					.then(() => this.successfulSave())
					.catch((error) => FormErrorHandler.configError(error));
			}
		},
		successfulSave() {
			this.$store.commit('spinner/HIDE');
			this.$toast.success(this.$t('config.success').toString());
		},
		updateMapping(mapping: any) {
			Object.assign(this.configuration, mapping);
		},
		updatePort(port: string) {
			this.configuration.IqrfInterface = port;
		},
	},
	metaInfo: {
		title: 'config.iqrfSpi.title',
	},
});
</script>
