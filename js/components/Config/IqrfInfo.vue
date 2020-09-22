<template>
	<CCard>
		<CCardBody>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm @submit.prevent='saveConfig'>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{
							required: "config.iqrfInfo.messages.instance"
						}'
					>
						<CInput
							v-model='info.instance'
							:label='$t("config.iqrfInfo.form.instance")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<CInputCheckbox
						:checked.sync='info.enumAtStartUp'
						:label='$t("config.iqrfInfo.form.enumAtStartUp")'
					/>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						:rules='info.enumAtStartUp ? "integer|min:0|required": ""'
						:custom-messages='{
							required: "config.iqrfInfo.messages.enumPeriod",
							min: "config.iqrfInfo.messages.enumPeriod",
							integer: "iqrfnet.networkManager.messages.invalid.integer"
						}'
					>
						<CInput
							v-model.number='info.enumPeriod'
							type='number'
							min='0'
							:label='$t("config.iqrfInfo.form.enumPeriod")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<CInputCheckbox
						:checked.sync='info.enumUniformDpaVer'
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
import ConfigService from '../../services/ConfigService';
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
			serviceName: 'daemon/iqrf::IqrfInfo',
			info: {
				instance: '',
				enumAtStartUp: false,
				enumPeriod: 0,
				enumUniformDpaVer: false,
			},
			hasInstance: false,
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
			ConfigService.getConfig(this.serviceName, 10000)
				.then((response) => {
					this.$store.commit('spinner/HIDE');
					if (response.data.instances) {
						this.hasInstance = true;
						this.info.instance = response.data.instances[0].instance;
						this.info.enumAtStartUp = response.data.instances[0].enumAtStartUp;
						this.info.enumPeriod = response.data.instances[0].enumPeriod;
						this.info.enumUniformDpaVer = response.data.instances[0].enumUniformDpaVer;	
					}	
				})
				.catch((error) => {
					FormErrorHandler.configError(error);
				});
		},
		saveConfig() {
			this.$store.commit('spinner/SHOW');
			if (this.hasInstance) {
				ConfigService.saveConfig(this.serviceName + '/' + this.info.instance, this.info, 10000)
					.then(() => {
						this.$store.commit('spinner/HIDE');
						this.$toast.success(this.$t('config.success'));
					})
					.catch((error) => {
						FormErrorHandler.configError(error);
					});
			} else {
				ConfigService.createConfig(this.serviceName, this.info, 10000)
					.then(() => {
						this.$store.commit('spinner/HIDE');
						this.$toast.success(this.$t('config.success'));
					})
					.catch((error) => {
						FormErrorHandler.configError(error);
					});
			}
		}
	}
};
</script>
