<template>
	<CCard>
		<CCardBody>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{
							required: "cloud.intelimentsInteliGlue.form.messages.rootTopic"
						}'
					>
						<CInput
							v-model='topic'
							:label='$t("cloud.intelimentsInteliGlue.form.rootTopic")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required|integer|between:0,65535'
						:custom-messages='{
							between: "cloud.intelimentsInteliGlue.form.messages.assignedPortRange",
							integer: "cloud.intelimentsInteliGlue.form.messages.assignedPortRange",
							required: "cloud.intelimentsInteliGlue.form.messages.assignedPort"
						}'
					>
						<CInput
							v-model.number='port'
							type='number'
							min='0'
							max='65535'
							:label='$t("cloud.intelimentsInteliGlue.form.assignedPort")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{
							required: "cloud.intelimentsInteliGlue.form.messages.clientId"
						}'
					>
						<CInput
							v-model='clientId'
							:label='$t("cloud.intelimentsInteliGlue.form.clientId")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{
							required: "cloud.intelimentsInteliGlue.form.messages.password"
						}'
					>
						<CInput
							v-model='password'
							:label='$t("cloud.intelimentsInteliGlue.form.password")'
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
import {between, integer, required} from 'vee-validate/dist/rules';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import CloudService from '../../services/CloudService';
import ServiceService from '../../services/ServiceService';

export default {
	name: 'InteliGlueCreator',
	components: {
		CButton,
		CCard,
		CCardBody,
		CForm,
		CInput,
		ValidationObserver,
		ValidationProvider,
	},
	data() {
		return {
			serviceName: 'inteliGlue',
			topic: null,
			port: null,
			clientId: null,
			password: null,
		};
	},
	created() {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
	},
	methods: {
		buildConfig() {
			return {
				'rootTopic': this.topic,
				'assignedPort': this.port,
				'clientId': this.clientId,
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
					return Promise.reject(error);
				});
		},
		saveAndRestart() {
			this.save()
				.then(() => {
					this.$store.commit('spinner/SHOW');
					ServiceService.restart('iqrf-gateway-daemon')
						.then(() => {
							this.$store.commit('spinner/HIDE');
							this.$toast.success(this.$t('service.iqrf-gateway-daemon.messages.restart'));
						})
						.catch((error) => {
							FormErrorHandler.serviceError(error);
						});
				})
				.catch(() => {});
		},
	},
	metaInfo: {
		title: 'cloud.intelimentsInteliGlue.form.title',
	},
};
</script>
