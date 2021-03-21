<template>
	<div>
		<h1>{{ $t('cloud.msAzure.form.title') }}</h1>
		<CCard>
			<CCardHeader>
				<CButton
					color='primary'
					size='sm'
					href='https://github.com/iqrfsdk/iot-starter-kit/blob/master/install/pdf/iqrf-part3b.pdf'
				>
					{{ $t('cloud.guides.pdf') }}
				</CButton> <CButton
					color='danger'
					size='sm'
					href='https://youtu.be/SIBoTrYwR2g'
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
								required: "cloud.msAzure.errors.connectionString"
							}'
						>
							<CInput
								v-model='connectionString'
								:label='$t("cloud.msAzure.form.connectionString")'
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

import {required} from 'vee-validate/dist/rules';
import CloudService from '../../services/CloudService';
import ServiceService from '../../services/ServiceService';

import {AxiosError} from 'axios';

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
		title: 'cloud.msAzure.form.title',
	},
})

/**
 * Azure cloud mqtt connection configuration creator card
 */
export default class AzureCreator extends Vue {
	/**
	 * @var {string} connectionString Azure cloud connection string
	 */
	private connectionString = ''

	/**
	 * @constant {string} serviceName Azure cloud service name
	 */
	private serviceName = 'azure'

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('required', required);
	}
	
	/**
	 * Stores new Azure cloud connection configuration in the gateway filesystem
	 * @returns {Promise<void>} Empty promise for request chaining
	 */
	private save(): Promise<void> {
		this.$store.commit('spinner/SHOW');
		return CloudService.create(this.serviceName, {'connectionString': this.connectionString})
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(this.$t('cloud.messages.success').toString());
			})
			.catch((error: AxiosError) => {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t(
						'cloud.msAzure.messages.saveFailed',
						{error: error.response ? error.response.data.message : error.message}
					).toString()
				);
				return Promise.reject();
			});
	}
	
	/**
	 * Stores new Azure cloud connection configuration in the gateway filesystem and restarts Daemon
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
				.catch((error: AxiosError) => {
					this.$store.commit('spinner/HIDE');
					this.$toast.error(
						this.$t(
							'service.messages.restartFailed',
							{error: error.response ? error.response.data.message : error.message, service: 'IQRF Daemon'}
						).toString()
					);
				});
		});
	}
}
</script>
