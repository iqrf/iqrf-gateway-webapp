<template>
	<CCard>
		<CCardBody>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm @submit.prevent='saveConfig'>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{required: "config.iqrfDpa.form.messages.instance"}'
					>
						<CInput
							v-model='configuration.instance'
							:label='$t("config.iqrfDpa.form.instance")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='integer|required|min:0'
						:custom-messages='{
							integer: "forms.messages.integer",
							min: "config.iqrfDpa.form.messages.DpaHandlerTimeout",
							required: "config.iqrfDpa.form.messages.DpaHandlerTimeout"
						}'
					>
						<CInput
							v-model.number='configuration.DpaHandlerTimeout'
							type='number'
							min='0'
							:label='$t("config.iqrfDpa.form.DpaHandlerTimeout")'
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
</template>

<script lang='ts'>
import Vue from 'vue';
import {AxiosError, AxiosResponse} from 'axios';
import {CButton, CCard, CCardBody, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {integer, min_value, required} from 'vee-validate/dist/rules';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';

export default Vue.extend({
	name: 'IqrfDpa',
	components: {
		CButton,
		CCard,
		CCardBody,
		CForm,
		CInput,
		ValidationObserver,
		ValidationProvider
	},
	data(): any {
		return {
			componentName: 'iqrf::IqrfDpa',
			configuration: {
				instance: null,
				DpaHandlerTimeout: 500,
			},
			instance: null,
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
					.catch((error: AxiosError) => FormErrorHandler.configError(error));
			} else {
				DaemonConfigurationService.createInstance(this.componentName, this.configuration)
					.then(() => this.successfulSave())
					.catch((error: AxiosError) => FormErrorHandler.configError(error));
			}
		},
		successfulSave() {
			this.$store.commit('spinner/HIDE');
			this.$toast.success(this.$t('config.success').toString());
		}
	},
	metaInfo: {
		title: 'config.iqrfDpa.title',
	},
});
</script>
