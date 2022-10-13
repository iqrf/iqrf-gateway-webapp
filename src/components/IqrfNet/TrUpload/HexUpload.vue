<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software,
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied
See the License for the specific language governing permissions and
limitations under the License.
-->
<template>
	<CCard>
		<CCardHeader>{{ $t('iqrfnet.trUpload.hexUpload.title') }}</CCardHeader>
		<CCardBody>
			<CForm>
				<div class='form-group'>
					<CInputFile
						ref='fileUpload'
						accept='.hex'
						:label='$t("iqrfnet.trUpload.hexUpload.form.file")'
						@input='isEmpty'
						@click='isEmpty'
					/>
					<p v-if='fileEmpty && !fileUntouched' style='color: red;'>
						{{ $t('iqrfnet.trUpload.hexUpload.errors.file') }}
					</p>
				</div>
				<CButton
					color='primary'
					:disabled='fileEmpty'
					@click='gatewayUpload'
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

import {daemonErrorToast, extendedErrorToast} from '@/helpers/errorToast';
import {FileFormat} from '@/iqrfNet/fileFormat';
import IqrfService from '@/services/IqrfService';
import ServiceService from '@/services/ServiceService';

import {AxiosResponse, AxiosError} from 'axios';
import {FileUpload} from '@/interfaces/trUpload';

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
	 * @var {boolean} fileEmpty Indicates that the form file input is empty
	 */
	private fileEmpty = true;

	/**
	 * @var {boolean} fileUntouched Indicates whether the form file input has been interacted with
	 */
	private fileUntouched = true;

	/**
	 * Extracts uploaded files from form file input
	 * @returns {FileList} list of uploaded files
	 */
	private getFiles(): FileList {
		const input = (this.$refs.fileUpload as CInputFile).$el.children[1] as HTMLInputElement;
		return (input.files as FileList);
	}

	/**
	 * Attempts to upload provided file to the gateway file storage
	 */
	private gatewayUpload(): void {
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

		this.$store.commit('spinner/SHOW');
		this.$store.commit('spinner/UPDATE_TEXT',
			this.$t('iqrfnet.trUpload.hexUpload.messages.gatewayUpload').toString()
		);
		IqrfService.uploadFile(formData)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/UPDATE_TEXT',
					this.$t('iqrfnet.trUpload.hexUpload.messages.gatewayUploadSuccess').toString()
				);
				this.stopDaemon(response.data);
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'iqrfnet.trUpload.hexUpload.messages.gatewayUploadFailed'));
	}

	/**
	 * Sends IQRF Gateway Uploader REST API request to upload Custom DPA handler
	 * @param {FileUpload} response REST API response containing uploaded file metadata
	 */
	private trUpload(response: FileUpload): void {
		this.$store.commit('spinner/UPDATE_TEXT',
			this.$t('iqrfnet.trUpload.hexUpload.messages.trUpload').toString()
		);
		IqrfService.uploader({name: response.fileName, type: 'HEX'})
			.then(() => {
				this.$store.commit('spinner/UPDATE_TEXT',
					this.$t('iqrfnet.trUpload.hexUpload.messages.trUploadSuccess').toString()
				);
				this.startDaemon();
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'iqrfnet.trUpload.hexUpload.messages.trUploadFailed'));
	}

	/**
	 * Stops the IQRF Daemon service before upgrading OS
	 * @param {FileUpload} response Files to be uploaded
	 */
	private stopDaemon(response: FileUpload): void {
		ServiceService.stop('iqrf-gateway-daemon')
			.then(() => {
				this.$store.commit('spinner/UPDATE_TEXT',
					this.$t('service.iqrf-gateway-daemon.messages.stop').toString()
				);
				this.trUpload(response);
			})
			.catch((error: AxiosError) => daemonErrorToast(error, 'service.messages.stopFailed'));
	}

	/**
	 * Starts the IQRF Daemon service upon successful OS upgrade
	 */
	private startDaemon(): void {
		ServiceService.start('iqrf-gateway-daemon')
			.then(() => {
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => daemonErrorToast(error, 'service.messages.startFailed'));
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
