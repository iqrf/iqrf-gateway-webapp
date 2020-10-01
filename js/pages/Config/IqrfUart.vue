<template>
	<div>
		<CCard>
			<CCardBody>
				<ValidationObserver v-slot='{ invalid }'>
					<CForm @submit.prevent='saveConfig'>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required'
							:custom-messages='{required: "config.iqrfUart.form.messages.instance"}'
						>
							<CInput
								v-model='configuration.instance'
								:label='$t("config.iqrfUart.form.instance")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required'
							:custom-messages='{required: "config.iqrfUart.form.messages.interface"}'
						>
							<CInput
								v-model='configuration.IqrfInterface'
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
								:value.sync='configuration.baudRate'
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
								v-model='configuration.powerEnableGpioPin'
								:label='$t("config.iqrfUart.form.powerEnableGpioPin")'
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
								v-model='configuration.busEnableGpioPin'
								:label='$t("config.iqrfUart.form.busEnableGpioPin")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<CButton type='submit' color='primary' :disabled='invalid'>
							{{ $t('forms.save') }}
						</CButton>
					</CForm>
				</ValidationObserver>
			</CCardBody>
		</CCard>
		<CCard>
			<CCardHeader>{{ $t('config.iqrfUart.mappings' ) }}</CCardHeader>
			<CCardBody>
				<CRow>
					<CCol md='6'>
						<InterfaceMappings interface-type='spi' @updateMapping='updateMapping' />
					</CCol>
					<CCol md='6'>
						<InterfacePorts interface-type='spi' @updatePort='updatePort' />
					</CCol>
				</CRow>
			</CCardBody>
		</CCard>
	</div>
</template>

<script>
import {
	CButton,
	CCard,
	CCardBody,
	CCardHeader,
	CCol,
	CForm,
	CInput,
	CRow,
	CSelect,
} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {integer, required} from 'vee-validate/dist/rules';
import InterfaceMappings from '../../components/Config/InterfaceMappings';
import InterfacePorts from '../../components/Config/InterfacePorts';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';

export default {
	name: 'IqrfUart',
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CCol,
		CForm,
		CInput,
		CRow,
		CSelect,
		InterfaceMappings,
		InterfacePorts,
		ValidationObserver,
		ValidationProvider,
	},
	data() {
		return {
			componentName: 'iqrf::IqrfUart',
			configuration: {
				instance: null,
				IqrfInterface: null,
				baudRate: null,
				powerEnableGpioPin: null,
				busEnableGpioPin: null,
			},
			instance: null,
		};
	},
	computed: {
		baudRates() {
			const baudRates = [1200, 2400, 4800, 9600, 19200, 38400, 57600, 115200, 230400];
			return baudRates.map((baudRate) => ({value: baudRate, label: baudRate + ' Bd'}));
		}
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
				.then((response) => {
					this.$store.commit('spinner/HIDE');
					if (response.data.instances.length > 0) {
						this.configuration = response.data.instances[0];
						this.instance = this.configuration.instance;
					}
				})
				.catch((error) => FormErrorHandler.configError(error));
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
		updateMapping(mapping) {
			Object.assign(this.configuration, mapping);
		},
		updatePort(port) {
			this.configuration.IqrfInterface = port;
		},
	},
	metaInfo: {
		title: 'config.iqrfUart.title',
	},
};
</script>
