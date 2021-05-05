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
				</CButton> <CButton
					color='danger'
					size='sm'
					href='https://youtu.be/Z9R2vdaw3KA'
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
								required: "cloud.amazonAws.errors.endpoint"
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
								:label='$t("forms.fields.certificate")'
								@input='certInputEmpty'
								@click='certInputEmpty'
							/>
						</div>
						<div class='form-group'>
							<CInputFile
								ref='awsFormKey'
								accept='.pem,.key'
								:label='$t("forms.fields.privateKey")'
								@input='keyInputEmpty'
								@click='keyInputEmpty'
							/>
						</div>
						<CButton
							color='primary'
							:disabled='invalid || certEmpty || keyEmpty'
							@click.prevent='save'
						>
							{{ $t('forms.save') }}
						</CButton> <CButton
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
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput, CInputFile} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {daemonErrorToast, extendedErrorToast} from '../../helpers/errorToast';
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
		CInputFile,
		ValidationObserver,
		ValidationProvider
	},
	metaInfo: {
		title: 'cloud.amazonAws.form.title',
	},
})

/**
 * Aws cloud mqtt connection configuration creator card
 */
export default class AwsCreator extends Vue {
	/**
	 * @var {string} endpoint Aws cloud endpoint
	 */
	private endpoint = ''

	/**
	 * @var {boolean} certEmpty Indicates whether the form certificate file input is empty
	 */
	private certEmpty = true

	/**
	 * @var {boolean} keyEmpty Indicates whether the form key file input is empty
	 */
	private keyEmpty = true

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('required', required);
	}

	/**
	 * Creates a formData object as data for Axios request
	 * @returns {FormData} FormData object
	 */
	private buildRequest(): FormData {
		const formData = new FormData();
		formData.append('endpoint', this.endpoint);
		formData.append('certificate', this.getCertFiles()[0]);
		formData.append('privateKey', this.getKeyFiles()[0]);
		return formData;
	}

	/**
	 * Extracts uploaded files from the form certificate file input
	 * @returns {FileList} List of uploaded files
	 */
	private getCertFiles(): FileList {
		const input = ((this.$refs.awsFormCert as CInputFile).$el.children[1] as HTMLInputElement);
		return (input.files as FileList);
	}

	/**
	 * Extracts uploaded files from the form key file input
	 * @returns {FileList} List of uploaded files
	 */
	private getKeyFiles(): FileList {
		const input = ((this.$refs.awsFormKey as CInputFile).$el.children[1] as HTMLInputElement);
		return (input.files as FileList);
	}

	/**
	 * Stores new Aws cloud connection configuration in the gateway filesystem
	 * @returns {Promise<void>} Empty promise for request chaining
	 */
	private save(): Promise<void> {
		this.$store.commit('spinner/SHOW');
		return CloudService.createAws(this.buildRequest())
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(this.$t('cloud.messages.success').toString());
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'cloud.amazonAws.messages.saveFailed');
				return Promise.reject(error);
			});
	}

	/**
	 * Stores new Aws cloud configuration in the gateway filesystem and restarts Daemon
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

	/**
	 * Checks if certificate input field is empty
	 */
	private certInputEmpty(): void {
		const files = this.getFileFromInput('awsFormCert');
		this.certEmpty = files.length === 0;
	}

	/**
	 * Checks if private key input field is empty
	 */
	private keyInputEmpty(): void {
		const files = this.getFileFromInput('awsFormKey');
		this.keyEmpty = files.length === 0;
	}

	/**
	 * Extracts files from file input element specified by ID
	 * @param {string} fieldId File input ID
	 */
	private getFileFromInput(fieldId: string): FileList {
		const input = ((this.$refs[fieldId] as CInputFile).$el.children[1] as HTMLInputElement);
		return (input.files as FileList);
	}

}
</script>
