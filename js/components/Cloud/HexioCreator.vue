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
import axios from 'axios';
import {CButton, CCard, CCardBody, CForm, CInput} from '@coreui/vue';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import {authorizationHeader} from '../../helpers/authorizationHeader';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import CloudService from '../../services/CloudService';

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
			address: 'connect.hexio.cloud',
			clientId: null,
			requestTopic: 'Iqrf/DpaRequest',
			responseTopic: 'Iqrf/DpaResponse',
			username: null,
			password: null,
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
			CloudService.create(this.serviceName, this.buildConfig(), 10000)
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
		title: 'cloud.hexio.form.title',
	},
};
</script>
