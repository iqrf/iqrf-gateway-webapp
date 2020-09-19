<template>
	<CCard>
		<CCardHeader>
			<CButton color='primary' size='sm' href='https://github.com/iqrfsdk/iot-starter-kit/blob/master/install/pdf/iqrf-part3b.pdf'>
				{{ $t('cloud.guides.pdf') }}
			</CButton>
			<CButton color='danger' size='sm' href='https://youtu.be/SIBoTrYwR2g'>
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
							required: "cloud.msAzure.form.messages.connectionString"
						}'
					>
						<CInput
							v-model='connectionString'
							:label='$t("cloud.msAzure.form.connectionString")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
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
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput} from '@coreui/vue';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import {authorizationHeader} from '../../helpers/authorizationHeader';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import CloudService from '../../services/CloudService';

export default {
	name: 'AzureCreator',
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		ValidationObserver,
		ValidationProvider
	},
	data() {
		return {
			connectionString: null,
			serviceName: 'azure',
		};
	},
	created() {
		extend('required', required);
	},
	methods: {
		save() {
			this.$store.commit('spinner/SHOW');
			CloudService.create(this.serviceName, {'connectionString': this.connectionString}, 10000)
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
		},
	},
	metaInfo: {
		title: 'cloud.msAzure.form.title',
	},
};
</script>
