<template>
	<CCard>
		<CCardBody>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{
							required: "cloud.hexio.form.messages.broker"
						}'
					>
						<CInput
							v-model='address'
							:label='$t("cloud.hexio.form.broker")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{
							required: "cloud.hexio.form.messages.clientId"
						}'
					>
						<CInput
							v-model='clientId'
							:label='$t("cloud.hexio.form.clientId")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{
							required: "cloud.hexio.form.messages.topicRequest"
						}'
					>
						<CInput
							v-model='requestTopic'
							:label='$t("cloud.hexio.form.topicRequest")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{
							required: "cloud.hexio.form.messages.topicResponse"
						}'
					>
						<CInput
							v-model='responseTopic'
							:label='$t("cloud.hexio.form.topicResponse")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{
							required: "cloud.hexio.form.messages.username"
						}'
					>
						<CInput
							v-model='username'
							:label='$t("cloud.hexio.form.username")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{
							required: "cloud.hexio.form.messages.password"
						}'
					>
						<CInput
							v-model='password'
							:label='$t("cloud.hexio.form.password")'
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
import {CButton, CCard, CCardBody, CForm, CInput} from '@coreui/vue';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import {timeout} from '../../helpers/timeout';
import CloudService from '../../services/CloudService';
import ServiceService from '../../services/ServiceService';

export default {
	name: 'HexioCreator',
	components: {
		CButton,
		CCard,
		CCardBody,
		CForm,
		CInput,
		ValidationObserver,
		ValidationProvider
	},
	data() {
		return {
			serviceName: 'hexio',
			address: null,
			clientId: null,
			requestTopic: 'Iqrf/DpaRequest',
			responseTopic: 'Iqrf/DpaResponse',
			username: null,
			password: null,
			timeout: null,
			restart: false,
		};
	},
	created() {
		extend('required', required);
	},
	methods: {
		buildConfig() {
			return {
				'broker': this.address,
				'clientId': this.clientId,
				'topicRequest': this.requestTopic,
				'topicResponse': this.responseTopic,
				'username': this.username,
				'password': this.password,
			};
		},
		save() {
			this.$store.commit('spinner/SHOW');
			this.timeout = timeout('cloud.messages.timeout', 10000);
			CloudService.create(this.serviceName, this.buildConfig())
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
