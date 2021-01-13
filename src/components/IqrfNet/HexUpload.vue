<template>
	<CCard>
		<CCardHeader>{{ $t('iqrfnet.trUpload.hexUpload.title') }}</CCardHeader>
		<CCardBody>
			<CForm @submit.prevent='submitUpload'>
				<div class='form-group'>
					<CInputFile
						ref='fileUpload'
						accept='.hex'
						:label='$t("iqrfnet.trUpload.hexUpload.form.file")'
						@input='isEmpty'
						@click='isEmpty'
					/>
					<p v-if='fileEmpty && !fileUntouched' style='color:red'>
						{{ $t('iqrfnet.trUpload.hexUpload.errors.file') }}
					</p>
				</div>
				<CButton
					type='submit'
					color='primary'
					:disabled='fileEmpty'
				>
					{{ $t('forms.upload') }}
				</CButton>
			</CForm>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInputFile, CSelect} from '@coreui/vue/src';

import {AxiosResponse, AxiosError} from 'axios';
import {FileUpload} from '../../interfaces/trUpload';

import {FileFormat} from '../../iqrfNet/fileFormat';

import FormErrorHandler from '../../helpers/FormErrorHandler';
import IqrfService from '../../services/IqrfService';
import NativeUploadService from '../../services/NativeUploadService';
import ServiceService from '../../services/ServiceService';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInputFile,
		CSelect
	}
})

/**
 * Custom DPA handler upload card for TrUpload component
 */
export default class HexUpload extends Vue {
	/**
	 * @var {boolean} failed Indicates that upload request has failed
	 */
	private failed = false

	/**
	 * @var {boolean} fileEmpty Indicates that the form file input is empty
	 */
	private fileEmpty = true

	/**
	 * @var {boolean} fileUntouched Indicates whether the form file input has been interacted with
	 */
	private fileUntouched = true

	/**
	 * @var {boolean} uploadMessage Spinner text
	 */
	private uploadMessage = '';

	/**
	 * Extracts uploaded files from form file input
	 * @returns {Filelist} list of uploaded files
	 */
	private getFiles(): FileList {
		const input = (this.$refs.fileUpload as CInputFile).$el.children[1] as HTMLInputElement;
		return (input.files as FileList);
	}

	/**
	 * Attempts to upload provided file to the gateway file storage
	 */
	private submitUpload(): void {
		// build form data
		const formData = new FormData();
		formData.append('format', FileFormat.HEX);
		const files = this.getFiles();
		if (!files[0].name.endsWith('.hex')) {
			this.$toast.error(
				this.$t('iqrfnet.trUpload.hexUpload.messages.invalidFormat').toString()
			);
			return;
		}
		formData.append('file', files[0]);

		// status message and spinner update
		this.uploadMessage = this.$t(
			'iqrfnet.trUpload.hexUpload.messages.gatewayUpload',
			{file: files[0].name}
		).toString();
		this.$store.commit('spinner/SHOW');
		this.$store.commit('spinner/UPDATE_TEXT', this.uploadMessage);

		// REST API upload
		NativeUploadService.uploadREST(formData)
			.then((response: AxiosResponse) => {
				this.uploadMessage += '\n' + this.$t(
					'iqrfnet.trUpload.hexUpload.messages.gatewayUploadSuccess',
					{file: files[0].name}
				).toString();
				this.$store.commit('spinner/UPDATE_TEXT', this.uploadMessage);
				this.stopDaemon(response.data);
			})
			.catch(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t('iqrfnet.trUpload.messages.gatewayUploadFailure').toString()
				);
			});
	}

	/**
	 * Sends IQRF Gateway Uploader REST API request to upload Custom DPA handler
	 * @param {FileUpload} response REST API response containing uploaded file metadata
	 */
	private uploadFile(response: FileUpload): void {
		// update spinner status
		this.uploadMessage += '\n' + this.$t(
			'iqrfnet.trUpload.osUpload.messages.fileUploading',
			{file: response.fileName}
		).toString();
		this.$store.commit('spinner/UPDATE_TEXT', this.uploadMessage);

		// run uploader
		IqrfService.uploader({name: response.fileName, type: 'HEX'})
			.then(() => {
				this.uploadMessage += '\n' + this.$t(
					'iqrfnet.trUpload.osUpload.messages.fileUploaded',
					{file: response.fileName}
				).toString();
				this.$store.commit('spinner/UPDATE_TEXT', this.uploadMessage);
				this.startDaemon();
			})
			.catch((error: AxiosError) => FormErrorHandler.uploadUtilError(error));
	}

	/**
	 * Stops the IQRF Daemon service before upgrading OS
	 * @param {FileUpload} response Files to be uploaded
	 */
	private stopDaemon(response: FileUpload): void {
		ServiceService.stop('iqrf-gateway-daemon')
			.then(() => {
				this.uploadMessage += '\n' + this.$t(
					'service.iqrf-gateway-daemon.messages.stop'
				).toString();
				this.$store.commit('spinner/UPDATE_TEXT', this.uploadMessage);
				this.uploadFile(response);
			})
			.catch((error: AxiosError) => FormErrorHandler.serviceError(error));
	}

	/**
	 * Starts the IQRF Daemon service upon successful OS upgrade
	 */
	private startDaemon(): void {
		ServiceService.start('iqrf-gateway-daemon')
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('iqrfnet.trUpload.hexUpload.uploadSuccess').toString()
				);
			})
			.catch((error: AxiosError) => FormErrorHandler.serviceError(error));
	}

	/**
	 * Checks if form file input is empty
	 */
	private isEmpty(): void {
		if (this.fileUntouched) {
			this.fileUntouched = false;
		}
		this.fileEmpty = this.getFiles().length === 0;
	}
}
</script>
