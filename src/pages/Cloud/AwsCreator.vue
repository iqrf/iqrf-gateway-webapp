<template>
	<div>
		<h1>{{ $t('cloud.amazonAws.form.title') }}</h1>
		<CCard>
			<CCardHeader>
				<CButton
					color='primary'
					size='sm'
					href='https://github.com/iqrfsdk/iot-starter-kit/blob/master/install/pdf/iqrf-part3a.pdf'
				>
					{{ $t('cloud.guides.pdf') }}
				</CButton>
				<CButton
					color='danger'
					size='sm'
					href='https://youtu.be/Z9R2vdaw3KA'
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
						<div class='form-group'>
							<CInputFile
								ref='awsFormCert'
								accept='.pem'
								:label='$t("cloud.amazonAws.form.certificate")'
								@input='isEmpty("cert")'
								@click='isEmpty("cert")'
							/>
							<p v-if='certEmpty && !firstCert' style='color:red'>
								{{ $t('cloud.amazonAws.form.messages.certificate') }}
							</p>
						</div>
						<div class='form-group'>
							<CInputFile
								ref='awsFormKey'
								accept='.pem,.key'
								:label='$t("cloud.amazonAws.form.key")'
								@input='isEmpty("key")'
								@click='isEmpty("key")'
							/>
							<p v-if='keyEmpty && !firstKey' style='color:red'>
								{{ $t('cloud.amazonAws.form.messages.key') }}
							</p>
						</div>
						<CButton
							color='primary'
							:disabled='invalid || certEmpty || keyEmpty'
							@click.prevent='save'
						>
							{{ $t('forms.save') }}
						</CButton>
						<CButton
							color='secondary'
							:disabled='invalid || certEmpty || keyEmpty'
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
import Vue from 'vue';
import {AxiosError} from 'axios';
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput, CInputFile} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import CloudService from '../../services/CloudService';
import ServiceService from '../../services/ServiceService';

export default Vue.extend({
	name: 'AwsCreator',
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
	data(): any {
		return {
			endpoint: null,
			serviceName: 'aws',
			certEmpty: true,
			keyEmpty: true,
			firstCert: true,
			firstKey: true,
		};
	},
	created() {
		extend('required', required);
	},
	methods: {
		buildRequest() {
			const formData = new FormData();
			formData.append('endpoint', this.endpoint);
			formData.append('certificate', this.$refs.awsFormCert.$el.children[1].files[0]);
			formData.append('privateKey', this.$refs.awsFormKey.$el.children[1].files[0]);
			return formData;
		},
		save() {
			this.$store.commit('spinner/SHOW');
			return CloudService.create(this.serviceName, this.buildRequest())
				.then(() => {
					this.$store.commit('spinner/HIDE');
					this.$toast.success(this.$t('cloud.messages.success').toString());
				})
				.catch((error: AxiosError) => {
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
						.catch((error: AxiosError) => {
							FormErrorHandler.serviceError(error);
						});
				})
				.catch(() => {});
		},
		isEmpty(button: string) {
			if (button === 'cert') {
				if (this.firstCert) {
					this.firstCert = false;
				}
				this.certEmpty = this.$refs.awsFormCert.$el.children[1].files.length === 0;
			} else {
				if (this.firstKey) {
					this.firstKey = false;
				}
				this.keyEmpty = this.$refs.awsFormKey.$el.children[1].files.length === 0;
			}
		}
	}
});
</script>
