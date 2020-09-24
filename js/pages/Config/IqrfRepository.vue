<template>
	<CCard>
		<CCardBody>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm @submit.prevent='saveConfig'>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{required: "config.iqrfRepository.form.messages.instance"}'
					>
						<CInput
							v-model='repository.instance'
							:label='$t("config.iqrfRepository.form.instance")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{required: "config.iqrfRepository.form.messages.urlRepo"}'
					>
						<CInput
							v-model='repository.urlRepo'
							:label='$t("config.iqrfRepository.form.urlRepo")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='integer|required|min:0'
						:custom-messages='{
							integer: "forms.messages.integer",
							required: "config.iqrfRepository.form.messages.checkPeriod",
							min: "config.iqrfRepository.form.messages.checkPeriod"
						}'
					>
						<CInput
							v-model.number='repository.checkPeriod'
							type='number'
							min='0'
							:label='$t("config.iqrfRepository.form.checkPeriod")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<CInputCheckbox
						:checked.sync='repository.downloadIfRepoCacheEmpty'
						:label='$t("config.iqrfRepository.form.downloadIfEmpty")'
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
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {integer, min_value, required} from 'vee-validate/dist/rules';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import ComponentConfigService from '../../services/ComponentConfigService';

export default {
	name: 'IqrfRepository',
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
			componentName: 'iqrf::JsCache',
			repository: {
				instance: null,
				urlRepo: null,
				checkPeriod: 0,
				downloadIfRepoCacheEmpty: true,
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
			ComponentConfigService.getConfig(this.componentName)
				.then((response) => {
					this.$store.commit('spinner/HIDE');
					if (response.data.instances.length > 0) {
						this.hasInstance = true;
						this.repository = response.data.instances[0];
					}
				})
				.catch((error) => {
					FormErrorHandler.configError(error);
				});
		},
		saveConfig() {
			this.$store.commit('spinner/SHOW');
			if (this.hasInstance) {
				ComponentConfigService.saveConfig(this.componentName, this.repository.instance, this.repository)
					.then(() => this.successfulSave())
					.catch((error) => {
						FormErrorHandler.configError(error);
					});
			} else {
				ComponentConfigService.createConfig(this.componentName, this.repository)
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
		title: 'config.iqrfRepository.title',
	},
};
</script>
