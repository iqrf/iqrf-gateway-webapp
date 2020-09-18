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
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput} from '@coreui/vue';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import {timeout} from '../../helpers/timeout';
import CloudService from '../../services/CloudService';
import ServiceService from '../../services/ServiceService';

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
			timeout: null,
			restart: false,
		};
	},
	created() {
		extend('required', required);
	},
	methods: {
		save() {
			this.$store.commit('spinner/SHOW');
			this.timeout = timeout('cloud.messages.timeout', 10000);
			CloudService.create(this.serviceName, {'connectionString': this.connectionString})
				.then(() => {
					clearTimeout(this.timeout);
					this.$store.commit('spinner/HIDE');
					this.$toast.success(this.$t('cloud.messages.success'));
					if (this.restart) {
						this.restart = false;
						this.$store.commit('spinner/SHOW');
						this.timeout = timeout('service.errors.processTimeout', 10000);
						ServiceService.restart('iqrf-gateway-daemon')
							.then(() => {
								clearTimeout(this.timeout);
								this.$store.commit('spinner/HIDE');
								this.$toast.success(this.$t('service.iqrf-gateway-daemon.messages.restart'));
							})
							.catch((error) => {
								clearTimeout(this.timeout);
								FormErrorHandler.serviceError(error);
							});
					}
				})
				.catch((error) => {
					clearTimeout(this.timeout);
					FormErrorHandler.cloudError(error);
				});
		},
		saveAndRestart() {
			this.restart = true;
			this.save();
		}
	}
};
</script>
