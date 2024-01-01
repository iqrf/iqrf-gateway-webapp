<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
	<v-card>
		<v-card-title>{{ $t('iqrfnet.trUpload.hexUpload.title') }}</v-card-title>
		<v-card-text>
			<ValidationObserver v-slot='{invalid}'>
				<v-form>
					<ValidationProvider
						v-slot='{errors, valid}'
						rules='required|hexFile'
						:custom-messages='{
							required: $t("iqrfnet.trUpload.hexUpload.errors.file"),
							hexFile: $t("iqrfnet.trUpload.hexUpload.errors.invalidFormat")
						}'
					>
						<v-file-input
							v-model='file'
							accept='.hex'
							:label='$t("iqrfnet.trUpload.hexUpload.form.file")'
							:error-messages='errors'
							:success='valid'
							:prepend-icon='null'
							prepend-inner-icon='mdi-file-outline'
							required
						/>
					</ValidationProvider>
					<v-btn
						color='primary'
						:disabled='invalid'
						@click='gatewayUpload'
					>
						{{ $t('forms.upload') }}
					</v-btn>
				</v-form>
			</ValidationObserver>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {ServiceService} from '@iqrf/iqrf-gateway-webapp-client/services';
import {AxiosResponse, AxiosError} from 'axios';
import {Component, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';

import {daemonErrorToast, extendedErrorToast} from '@/helpers/errorToast';
import {FileFormat} from '@/iqrfNet/fileFormat';
import IqrfService from '@/services/IqrfService';

import {FileUpload} from '@/interfaces/trUpload';
import {useApiClient} from '@/services/ApiClient';

@Component({
	components: {
		ValidationObserver,
		ValidationProvider,
	},
})

/**
 * Custom DPA handler upload card for TrUpload component
 */
export default class HexUpload extends Vue {
	/**
	 * @var {File|null} file Selected file
	 */
	private file: File|null = null;

	/**
   * @property {ServiceService} service Service service
   * @private
   */
	private service: ServiceService = useApiClient().getServiceService();

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
		extend('hexFile', (file: File|null) => {
			if (!file) {
				return false;
			}
			return (file.type === 'text/x-hex');
		});
	}

	/**
	 * Attempts to upload provided file to the gateway file storage
	 */
	private gatewayUpload(): void {
		if (this.file === null) {
			return;
		}
		const formData = new FormData();
		formData.append('format', FileFormat.HEX);
		formData.append('file', this.file);
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
		this.service.stop('iqrf-gateway-daemon')
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
		this.service.start('iqrf-gateway-daemon')
			.then(() => {
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => daemonErrorToast(error, 'service.messages.startFailed'));
	}
}
</script>
