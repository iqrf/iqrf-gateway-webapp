<template>
	<div>
		<h1>{{ $t('cloud.ibmCloud.form.title') }}</h1>
		<CCard>
			<CCardHeader>
				<CButton
					color='primary'
					size='sm'
					href='https://github.com/iqrfsdk/iot-starter-kit/blob/master/install/pdf/iqrf-part3c.pdf'
				>
					{{ $t('cloud.guides.pdf') }}
				</CButton> <CButton
					color='danger'
					size='sm'
					href='https://youtu.be/xoAReOyrkZ4'
				>
					{{ $t('cloud.guides.video') }}
				</CButton>
			</CCardHeader>
			<CCardBody>
				<ValidationObserver v-slot='{invalid}'>
					<CForm>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: "cloud.ibmCloud.errors.organizationId"
							}'
						>
							<CInput
								v-model='config.organizationId'
								:label='$t("cloud.ibmCloud.form.organizationId")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: "cloud.ibmCloud.errors.deviceType"
							}'
						>
							<CInput
								v-model='config.deviceType'
								:label='$t("cloud.ibmCloud.form.deviceType")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: "cloud.ibmCloud.errors.deviceId"
							}'
						>
							<CInput
								v-model='config.deviceId'
								:label='$t("cloud.ibmCloud.form.deviceId")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: "cloud.ibmCloud.errors.token"
							}'
						>
							<CInput
								v-model='config.token'
								:label='$t("cloud.ibmCloud.form.token")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: "cloud.ibmCloud.errors.eventId"
							}'
						>
							<CInput
								v-model='config.eventId'
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
						</CButton> <CButton
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
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {daemonErrorToast, extendedErrorToast} from '../../helpers/errorToast';
import {required} from 'vee-validate/dist/rules';
import CloudService from '../../services/CloudService';
import ServiceService from '../../services/ServiceService';

import {AxiosError} from 'axios';
import {IIbmCloud} from '../../interfaces/clouds';

@Component({
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
	metaInfo: {
		title: 'cloud.ibmCloud.form.title',
	},
})

/**
 * Ibm cloud mqtt connection configuration creator card
 */
export default class IbmCreator extends Vue {
	/**
	 * @constant {string} serviceName Ibm cloud service name
	 */
	private serviceName = 'ibmCloud'

	/**
	 * @var {IIbmCloud} config Ibm cloud connection configuration
	 */
	private config: IIbmCloud = {
		organizationId: '',
		deviceType: '',
		deviceId: '',
		token: '',
		eventId: 'iqrf'
	}

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('required', required);
	}

	/**
	 * Stores new ibm cloud connection configuration in the gateway filesystem
	 * @returns {Promise<void>} Empty promise for request chaining
	 */
	private save(): Promise<void> {
		this.$store.commit('spinner/SHOW');
		return CloudService.create(this.serviceName, this.config)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(this.$t('cloud.messages.success').toString());
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'cloud.ibmCloud.messages.saveFailed');
				//return Promise.reject(error);
			});
	}

	/**
	 * Stores new ibm cloud connection configuration in the gateway filesystem and restarts Daemon
	 */
	private saveAndRestart(): void {
		this.save().then(() => {
			this.$store.commit('spinner/SHOW');
			ServiceService.restart('iqrf-gateway-daemon')
				.then(() => {
					this.$store.commit('spinner/HIDE');
					this.$toast.success(
						this.$t('service.iqrf-gateway-daemon.messages.restart')
							.toString()
					);
				})
				.catch((error: AxiosError) => daemonErrorToast(error, 'service.messages.restartFailed'));
		});
	}
	
}
</script>
