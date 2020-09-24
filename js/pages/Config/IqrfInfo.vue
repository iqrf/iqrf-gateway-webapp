<template>
	<CCard>
		<CCardBody>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm @submit.prevent='saveConfig'>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{required: "config.iqrfInfo.messages.instance"}'
					>
						<CInput
							v-model='configuration.instance'
							:label='$t("config.iqrfInfo.form.instance")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<CInputCheckbox
						:checked.sync='configuration.enumAtStartUp'
						:label='$t("config.iqrfInfo.form.enumAtStartUp")'
					/>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						:rules='configuration.enumAtStartUp ? "integer|min:0|required": ""'
						:custom-messages='{
							required: "config.iqrfInfo.messages.enumPeriod",
							min: "config.iqrfInfo.messages.enumPeriod",
							integer: "forms.messages.integer"
						}'
					>
						<CInput
							v-model.number='configuration.enumPeriod'
							type='number'
							min='0'
							:label='$t("config.iqrfInfo.form.enumPeriod")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<CInputCheckbox
						:checked.sync='configuration.enumUniformDpaVer'
						:label='$t("config.iqrfInfo.form.enumUniformDpaVer")'
					/>
					<CButton type='submit' color='primary' :disabled='invalid'>
						{{ $t('forms.save') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
	</CCard>
</template>

<script>
import {CButton, CCard, CCardBody, CForm, CInput, CInputCheckbox} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate/';
import {integer, min_value, required} from 'vee-validate/dist/rules';
import ComponentConfigService from '../../services/ComponentConfigService';
import FormErrorHandler from '../../helpers/FormErrorHandler';

export default {
	name: 'IqrfInfo',
	components: {
		CButton,
		CCard,
		CCardBody,
		CForm,
		CInput,
		CInputCheckbox,
		ValidationObserver,
		ValidationProvider,
	},
	data() {
		return {
			componentName: 'iqrf::IqrfInfo',
			instance: null,
			configuration: {
				instance: '',
				enumAtStartUp: false,
				enumPeriod: 0,
				enumUniformDpaVer: false,
			},
		};
	},
	created() {
		extend('integer', integer);
		extend('min', min_value);
		extend('required', required);
		this.getConfig();
	},
	methods: {
		getConfig() {
			this.$store.commit('spinner/SHOW');
			ComponentConfigService.getConfig(this.componentName)
				.then((response) => {
					this.$store.commit('spinner/HIDE');
					if (response.data.instances.length > 0) {
						this.configuration = response.data.instances[0];
						this.instance = this.configuration.instance;
					}	
				})
				.catch((error) => {
					FormErrorHandler.configError(error);
				});
		},
		saveConfig() {
			this.$store.commit('spinner/SHOW');
			if (this.instance !== null) {
				ComponentConfigService.saveConfig(this.componentName, this.configuration.instance, this.configuration)
					.then(() => this.saveConfig())
					.catch((error) => {
						FormErrorHandler.configError(error);
					});
			} else {
				ComponentConfigService.createConfig(this.componentName, this.configuration)
					.then(() => this.successfulSave())
					.catch((error) => {
						FormErrorHandler.configError(error);
					});
			}
		},
		successfulSave() {
			this.$store.commit('spinner/HIDE');
			this.$toast.success(this.$t('config.success').toString());
		},
	},
	metaInfo: {
		title: 'config.iqrfInfo.title',
	},
};
</script>
