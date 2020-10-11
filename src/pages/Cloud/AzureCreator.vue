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
				</CButton>
				<CButton
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
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {AxiosError, AxiosResponse} from 'axios';
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import CloudService from '../../services/CloudService';
import ServiceService from '../../services/ServiceService';

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

export default class AzureCreator extends Vue {
	private connectionString: string|null = null
	private serviceName = 'azure'

	created(): void {
		extend('required', required);
	}
	
	private save(): Promise<AxiosResponse|void> {
		this.$store.commit('spinner/SHOW');
		return CloudService.create(this.serviceName, {'connectionString': this.connectionString})
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(this.$t('cloud.messages.success').toString());
			})
			.catch((error: AxiosError) => {
				FormErrorHandler.cloudError(error);
				return Promise.reject(error);
			});
	}
	
	private saveAndRestart(): void {
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
					.catch((error: AxiosError) => {
						FormErrorHandler.serviceError(error);
					});
			})
			.catch(() => {return;});
	}
}
</script>

<style scoped>
.btn {
	margin: 0 3px 0 0;
}
</style>
