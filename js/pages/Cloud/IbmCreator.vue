<template>
	<CCard>
		<CCardHeader>
			<CButton
				color='primary'
				size='sm'
				href='https://github.com/iqrfsdk/iot-starter-kit/blob/master/install/pdf/iqrf-part3c.pdf'
			>
				{{ $t('cloud.guides.pdf') }}
			</CButton>
			<CButton
				color='danger'
				size='sm'
				href='https://youtu.be/xoAReOyrkZ4'
			>
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
							required: "cloud.ibmCloud.form.messages.organizationId"
						}'
					>
						<CInput
							v-model='organizationId'
							:label='$t("cloud.ibmCloud.form.organizationId")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{
							required: "cloud.ibmCloud.form.messages.deviceType"
						}'
					>
						<CInput
							v-model='deviceType'
							:label='$t("cloud.ibmCloud.form.deviceType")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{
							required: "cloud.ibmCloud.form.messages.deviceId"
						}'
					>
						<CInput
							v-model='deviceId'
							:label='$t("cloud.ibmCloud.form.deviceId")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{
							required: "cloud.ibmCloud.form.messages.token"
						}'
					>
						<CInput
							v-model='token'
							:label='$t("cloud.ibmCloud.form.token")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{
							required: "cloud.ibmCloud.form.messages.eventId"
						}'
					>
						<CInput
							v-model='eventId'
							:label='$t("cloud.ibmCloud.form.eventId")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<CButton
						color='primary'
						:disabled='invalid'
						@click.prevent='save'
					>
						{{ $t('forms.save') }}
					</CButton>
					<CButton
						color='secondary'
						:disabled='invalid'
						@click.prevent='saveAndRestart'
					>
						{{ $t('forms.saveRestart') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
	</CCard>
</template>

<script>
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import CloudService from '../../services/CloudService';
import ServiceService from '../../services/ServiceService';

export default {
	name: 'IbmCreator',
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
			serviceName: 'ibmCloud',
			organizationId: null,
			deviceType: null,
			deviceId: null,
			token: null,
			eventId: 'iqrf',
		};
	},
	created() {
		extend('required', required);
	},
	methods: {
		buildConfig() {
			return {
				'organizationId': this.organizationId,
				'deviceType': this.deviceType,
				'deviceId': this.deviceId,
				'token': this.token,
				'eventId': this.eventId
			};
		},
		save() {
			this.$store.commit('spinner/SHOW');
			CloudService.create(this.serviceName, this.buildConfig())
				.then(() => {
					this.$store.commit('spinner/HIDE');
					this.$toast.success(this.$t('cloud.messages.success').toString());
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
							this.$toast.success(
								this.$t('service.iqrf-gateway-daemon.messages.restart')
									.toString()
							);
						})
						.catch((error) => {
							FormErrorHandler.serviceError(error);
						});
				})
				.catch(() => {});
		},
	},
	metaInfo: {
		title: 'cloud.ibmCloud.form.title',
	},
};
</script>
