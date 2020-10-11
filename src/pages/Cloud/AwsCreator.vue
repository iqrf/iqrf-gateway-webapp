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
							<p v-if='certEmpty && !certUntouched' style='color:red'>
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
							<p v-if='keyEmpty && !keyUntouched' style='color:red'>
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
import {Component, Vue} from 'vue-property-decorator';
import {AxiosError} from 'axios';
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput, CInputFile} from '@coreui/vue/src';
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
		CInputFile,
		ValidationObserver,
		ValidationProvider
	},
	metaInfo: {
		title: 'cloud.amazonAws.form.title',
	},
})

export default class AwsCreator extends Vue {
	private endpoint: string|null = null
	private certEmpty = true
	private certUntouched = true
	private keyEmpty = true
	private keyUntouched = true

	created(): void {
		extend('required', required);
	}

	private buildRequest(): FormData {
		const formData = new FormData();
		formData.append('endpoint', this.endpoint ?? '');
		formData.append('certificate', this.getCertFiles()[0]);
		formData.append('privateKey', this.getKeyFiles()[0]);
		return formData;
	}

	private getCertFiles(): FileList {
		const input = ((this.$refs.awsFormCert as CInputFile).$el.children[1] as HTMLInputElement);
		return (input.files as FileList);
	}

	private getKeyFiles(): FileList {
		const input = ((this.$refs.awsFormKey as CInputFile).$el.children[1] as HTMLInputElement);
		return (input.files as FileList);
	}

	private save(): Promise<void> {
		this.$store.commit('spinner/SHOW');
		return CloudService.createAws(this.buildRequest())
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

	private isEmpty(button: string): void {
		if (button === 'cert') {
			if (this.certUntouched) {
				this.certUntouched = false;
			}
			const certFiles = this.getCertFiles();
			this.certEmpty = certFiles === null || certFiles.length === 0;
		} else {
			if (this.keyUntouched) {
				this.keyUntouched = false;
			}
			const keyFiles = this.getKeyFiles();
			this.keyEmpty = keyFiles === null || keyFiles.length === 0;
		}
	}
}
</script>

<style scoped>
.btn {
	margin: 0 3px 0 0;
}
</style>
