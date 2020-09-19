<template>
	<CCard>
		<CCardHeader>
			<CButton color='primary' size='sm' href='https://github.com/iqrfsdk/iot-starter-kit/blob/master/install/pdf/iqrf-part3a.pdf'>
				{{ $t('cloud.guides.pdf') }}
			</CButton>
			<CButton color='danger' size='sm' href='https://youtu.be/Z9R2vdaw3KA'>
				{{ $t('cloud.guides.video') }}
			</CButton>
		</CCardHeader>
		<CCardBody>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{
							required: "cloud.amazonAws.form.messages.endpoint"
						}'
					>
						<CInput
							v-model='endpoint'
							:label='$t("cloud.amazonAws.form.endpoint")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, validate }'
						rules='required'
						:custom-messages='{
							required: "cloud.amazonAws.form.messages.certificate"
						}'
					>
						<CInputFile
							ref='awsFormCert'
							:label='$t("cloud.amazonAws.form.certificate")'
							:invalid-feedback='$t(errors[0])'
							@change='validate'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, validate }'
						rules='required'
						:custom-messages='{
							required: "cloud.amazonAws.form.messages.key"
						}'
					>
						<CInputFile
							ref='awsFormKey'
							:label='$t("cloud.amazonAws.form.key")'
							:invalid-feedback='$t(errors[0])'
							@change='validate'
						/>
					</ValidationProvider>
					<CButton color='primary' :disabled='invalid' @click.prevent='save'>
						{{ $t('forms.save') }}
					</CButton>
					<CButton color='secondary' :disabled='invalid' @click.prevent='saveAndRestart'>
						{{ $t('forms.saveRestart') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
	</CCard>
</template>

<script>
import axios from 'axios';
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput, CInputFile} from '@coreui/vue';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import {authorizationHeader} from '../../helpers/authorizationHeader';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import CloudService from '../../services/CloudService';

export default {
	name: 'AWSCreator',
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		CInputFile,
		ValidationObserver,
		ValidationProvider
	},
	data() {
		return {
			endpoint: null,
			serviceName: 'aws'
		};
	},
	created() {
		extend('required', required);
	},
	methods: {
		buildRequest() {
			let	formData = new FormData();
			formData.append('endpoint', this.endpoint);
			formData.append('certificate', this.$refs.awsFormCert.files[0]);
			formData.append('privateKey', this.$refs.awsFormKey.files[0]);
			return formData;
		},
		save() {
			this.$store.commit('spinner/SHOW');
			CloudService.createWithFile(this.serviceName, this.buildRequest(), 10000)
				.then(() => {
					this.$store.commit('spinner/HIDE');
					this.$toast.success(this.$t('cloud.messages.success'));
				
				})
				.catch((error) => {
					FormErrorHandler.cloudError(error);
				});
		},
		saveAndRestart() {
			axios.interceptors.response.use(
				(response) => {
					if (response.config.url === 'clouds/' + this.serviceName) {
						this.$store.commit('spinner/SHOW');
						axios.post('services/iqrf-gateway-daemon/restart', null, {headers: authorizationHeader(), timeout: 20000})
							.then(() => {
								this.$store.commit('spinner/HIDE');
								this.$toast.success(this.$t('service.iqrf-gateway-daemon.messages.restart'));
							})
							.catch((error) => {
								FormErrorHandler.serviceError(error);
							});
					}
					return response;
				},
				(error) => {
					return Promise.reject(error);
				}
			);
			this.save();
		}
	}
};
</script>
