<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

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
	<div>
		<CCard class='border-0 card-margin-bottom'>
			<CCardBody>
				<ValidationObserver v-slot='{invalid}'>
					<CForm>
						<CSelect
							:value.sync='fileType'
							:options='fileTypeOptions'
							:label='$t("iqrfnet.networkManager.otaUpload.form.fileType")'
							@change='checkSelectedFile'
						/>
						<CInputFile
							ref='fileInput'
							accept='.hex,.iqrf'
							:label='$t("iqrfnet.networkManager.otaUpload.form.file")'
							@input='checkSelectedFile'
							@click='fileInputEmpty'
						/>
						<hr>
						<CSelect
							:value.sync='target'
							:options='targetOptions'
							:label='$t("iqrfnet.networkManager.otaUpload.form.target")'
							:description='
								(target === NetworkTarget.NETWORK) ?
									$t("iqrfnet.networkManager.otaUpload.notes.network").toString():
									undefined
							'
							@change='resetChecks'
						/>
						<ValidationProvider
							v-if='target === NetworkTarget.NODE'
							v-slot='{errors, touched, valid}'
							rules='required|integer|between:1,239'
							:custom-messages='{
								required: "iqrfnet.networkManager.otaUpload.errors.nodeAddress",
								integer: "forms.errors.integer",
								between: "iqrfnet.networkManager.otaUpload.errors.nodeAddress"
							}'
						>
							<CInput
								v-model.number='params.address'
								type='number'
								min='1'
								max='239'
								:label='$t("iqrfnet.networkManager.otaUpload.form.nodeAddress")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
								@input='resetChecks'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-if='target === NetworkTarget.NETWORK'
							v-slot='{errors, touched, valid}'
							rules='required|integer|between:0,65535'
							:custom-messages='{
								required: "iqrfnet.networkManager.otaUpload.errors.hwpid",
								integer: "forms.errors.integer",
								between: "iqrfnet.networkManager.otaUpload.errors.hwpid"
							}'
						>
							<CInput
								v-model.number='params.hwpid'
								type='number'
								min='0'
								max='65535'
								:label='$t("iqrfnet.networkManager.otaUpload.form.hwpidFilter")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
								:description='$t("iqrfnet.networkManager.otaUpload.notes.hwpid")'
								@input='resetChecks'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required|integer|between:768,16383'
							:custom-messages='{
								required: "iqrfnet.networkManager.otaUpload.errors.eeepromAddress",
								integer: "forms.errors.integer",
								between: "iqrfnet.networkManager.otaUpload.errors.eeepromAddress"
							}'
						>
							<CInput
								v-model.number='params.startMemAddr'
								type='number'
								min='768'
								max='16383'
								:label='$t("iqrfnet.networkManager.otaUpload.form.eeepromAddress")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
								@input='resetChecks'
							/>
						</ValidationProvider>
						<div v-if='fileType === FileFormat.HEX'>
							<CInputCheckbox
								:checked.sync='params.uploadEeprom'
								:label='$t("iqrfnet.networkManager.otaUpload.form.uploadEeprom")'
							/>
							<CInputCheckbox
								:checked.sync='params.uploadEeeprom'
								:label='$t("iqrfnet.networkManager.otaUpload.form.uploadEeeprom")'
							/>
						</div>
						<hr>
						<div class='form-group'>
							<div>
								<b>
									{{ $t('iqrfnet.networkManager.otaUpload.form.uploadSteps.uploadEeeprom') }}
								</b>
							</div><br>
							<div class='step-check'>
								<CButton
									color='primary'
									:disabled='invalid || fileEmpty'
									@click='uploadFile'
								>
									{{ $t('forms.upload') }}
								</CButton> <CIcon
									v-if='checks.upload'
									class='text-success'
									:content='cilCheckCircle'
									size='xl'
								/>
							</div>
						</div>
						<div class='form-group'>
							<div>
								<b>
									{{ $t('iqrfnet.networkManager.otaUpload.form.uploadSteps.verifyEeeprom') }}
								</b>
							</div><br>
							<div class='step-check'>
								<CButton
									color='primary'
									:disabled='invalid || fileEmpty || !checks.upload'
									@click='verifyStep'
								>
									{{ $t('forms.verify') }}
								</CButton> <CIcon
									v-if='checks.verify'
									class='text-success'
									:content='cilCheckCircle'
									size='xl'
								/>
							</div>
						</div>
						<div class='form-group'>
							<div>
								<b>
									{{ $t('iqrfnet.networkManager.otaUpload.form.uploadSteps.loadFlash') }}
								</b>
							</div><br>
							<div class='step-check'>
								<CButton
									color='primary'
									:disabled='invalid || fileEmpty || !checks.verify'
									@click='flashLoadStep'
								>
									{{ $t('forms.load') }}
								</CButton> <CIcon
									v-if='checks.flash'
									class='text-success'
									:content='cilCheckCircle'
									size='xl'
								/>
							</div>
						</div>
					</CForm>
				</ValidationObserver>
			</CCardBody>
		</CCard>
		<OtaUploadResultModal ref='result' :results='results' />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput, CInputCheckbox, CInputFile, CSelect} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import OtaUploadResultModal from '@/components/IqrfNet/NetworkManager/OtaUpload/OtaUploadResultModal.vue';

import {between, integer, required} from 'vee-validate/dist/rules';
import {cilCheckCircle} from '@coreui/icons';
import {extendedErrorToast} from '@/helpers/errorToast';
import {FileFormat} from '@/iqrfNet/fileFormat';
import {NetworkTarget} from '@/iqrfNet/networkTarget';
import {OtaUploadAction} from '@/enums/IqrfNet/OtaUpload';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

import IqrfNetService from '@/services/IqrfNetService';
import IqrfService from '@/services/IqrfService';

import {AxiosError, AxiosResponse} from 'axios';
import {IOption} from '@/interfaces/Coreui';
import {IOtaUploadParams, IOtaUploadResult} from '@/interfaces/DaemonApi/Iqmesh/OtaUpload';
import {MutationPayload} from 'vuex';

/**
 * OTA upload component for IQRF network manager
 */
@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		CInputCheckbox,
		CInputFile,
		CSelect,
		OtaUploadResultModal,
		ValidationObserver,
		ValidationProvider,
	},
	data: () => ({
		cilCheckCircle,
		FileFormat,
		NetworkTarget,
	})
})
export default class OtaUpload extends Vue {
	/**
	 * @var {string} msgId Daemon API message ID
	 */
	private msgId = '';

	/**
	 * @var {IOtaUploadParams} params OTA upload request parameters
	 */
	private params: IOtaUploadParams = {
		address: 1,
		hwpid: 65535,
		file: '',
		loadingAction: OtaUploadAction.UPLOAD,
		startMemAddr: 768,
		uploadEeprom: true,
		uploadEeeprom: true,
	};

	/**
	 * @var {boolean} fileEmpty Indicates whether file input is empty or not
	 */
	private fileEmpty = true;

	/**
	 * @var {FileFormat} fileType Type of IQRF file to upload
	 */
	private fileType: FileFormat = FileFormat.HEX;

	/**
	 * @constant {Array<IOption>} fileTypeOptions Array of CoreUI select options for file input
	 */
	private fileTypeOptions: Array<IOption> = [
		{
			label: this.$t('iqrfnet.networkManager.otaUpload.form.fileTypes.hex'),
			value: FileFormat.HEX
		},
		{
			label: this.$t('iqrfnet.networkManager.otaUpload.form.fileTypes.iqrf'),
			value: FileFormat.IQRF
		}
	];

	/**
	 * @var {IqrfNetworkTarget} target IQRF network upload target
	 */
	private target: NetworkTarget = NetworkTarget.COORDINATOR;

	/**
	 * @constant {Array<IOption>} targetOptions Array of CoreUI select options for upload target
	 */
	private targetOptions: Array<IOption> = [
		{
			label: this.$t('iqrfnet.networkManager.otaUpload.form.targets.coordinator'),
			value: NetworkTarget.COORDINATOR,
		},
		{
			label: this.$t('iqrfnet.networkManager.otaUpload.form.targets.node'),
			value: NetworkTarget.NODE,
		},
		{
			label: this.$t('iqrfnet.networkManager.otaUpload.form.targets.network'),
			value: NetworkTarget.NETWORK,
		},
	];

	/**
	 * @var {Record<string, boolean>} checks Upload step checks
	 */
	private checks: Record<string, boolean> = {
		upload: false,
		verify: false,
		flash: false,
	};

	/**
	 * @var {Array<IOtaUploadResult} results OTA upload step results
	 */
	private results: Array<IOtaUploadResult> = [];

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type !== 'daemonClient/SOCKET_ONMESSAGE') {
				return;
			}
			if (mutation.payload.data.msgId !== this.msgId) {
				return;
			}
			this.$store.dispatch('daemonClient/removeMessage', this.msgId);
			if (mutation.payload.mType === 'iqmeshNetwork_OtaUpload') {
				this.handleOtaUploadResponse(mutation.payload.data);
			} else if (mutation.payload.mType === 'messageError') {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t('messageError', {error: mutation.payload.data.rsp.errorStr}).toString()
				);
			}
		});
	}

	/**
	 * Unregisters mutation handling
	 */
	beforeDestroy(): void {
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		this.unsubscribe();
	}

	/**
	 * Extracts file from file input
	 * @returns {FileList} List of uploaded files
	 */
	private getFiles(): FileList {
		const input = ((this.$refs.fileInput as CInputFile).$el.children[1] as HTMLInputElement);
		return (input.files as FileList);
	}

	/**
	 * Resets file input content
	 */
	private resetFileInput(): void {
		((this.$refs.fileInput as CInputFile).$el.children[1] as HTMLInputElement).value = '';
		this.fileInputEmpty();
	}

	/**
	 * Checks if file input is empty
	 */
	private fileInputEmpty(): void {
		this.fileEmpty = this.getFiles().length === 0;
	}

	/**
	 * Resets step checks
	 */
	private resetChecks(): void {
		this.checks.upload = this.checks.verify = this.checks.flash = false;
	}

	/**
	 * Resets the inputs and checks if target, address or filtering has changed
	 */
	private clearForm(): void {
		this.resetFileInput();
		this.resetChecks();
	}

	/**
	 * Checks selected file
	 */
	private checkSelectedFile(): void {
		const file = this.getFiles()[0];
		if (!file) {
			return;
		}
		if (this.fileType === FileFormat.HEX && !file.name.endsWith('.hex')) {
			this.$toast.error(
				this.$t('iqrfnet.networkManager.otaUpload.errors.notHexFile').toString()
			);
			this.clearForm();
			return;
		}
		if (this.fileType === FileFormat.IQRF && !file.name.endsWith('.iqrf')) {
			this.$toast.error(
				this.$t('iqrfnet.networkManager.otaUpload.errors.notIqrfFile').toString()
			);
			this.clearForm();
			return;
		}
		this.resetChecks();
		this.fileEmpty = false;
	}

	/**
	 * Uploads file from file input to gateway filesystem
	 */
	private uploadFile(): void {
		const formData = new FormData();
		const file = this.getFiles()[0];
		formData.append('format', this.fileType);
		formData.append('file', file);
		this.$store.commit('spinner/SHOW');
		IqrfService.uploadFile(formData)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.params.file = response.data.fileName;
				this.uploadStep();
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'iqrfnet.networkManager.otaUpload.messages.gatewayUploadFailed'));
	}

	/**
	 * Retrieves device address depending on the upload target
	 * @returns {number} Device address
	 */
	private getAddress(): number {
		if (this.target === NetworkTarget.COORDINATOR) {
			return 0;
		} else if (this.target === NetworkTarget.NODE) {
			return this.params.address;
		} else {
			return 255;
		}
	}

	/**
	 * Sends Daemon API request to execute the upload step
	 */
	private uploadStep(): void {
		this.resetChecks();
		this.executeStep(OtaUploadAction.UPLOAD, 'iqrfnet.networkManager.otaUpload.messages.uploadStepFail');
	}

	/**
	 * Sends Daemon API request to execute the verification step
	 */
	private verifyStep(): void {
		this.checks.verify = this.checks.flash = false;
		this.executeStep(OtaUploadAction.VERIFY, 'iqrfnet.networkManager.otaUpload.messages.verifyStepFail');
	}

	/**
	 * Sends Daemon API request to execute the flash load step
	 */
	private flashLoadStep(): void {
		this.checks.flash = false;
		this.executeStep(OtaUploadAction.LOAD, 'iqrfnet.networkManager.otaUpload.messages.loadStepFail');
	}

	/**
	 * Sends Daemon API rquest to execute specified step
	 */
	private executeStep(action: OtaUploadAction, message: string): void {
		this.$store.commit('spinner/SHOW');
		this.$store.commit('spinner/UPDATE_TEXT', this.$t('iqrfnet.networkManager.otaUpload.messages.' + action.toLowerCase() + 'Running').toString());
		const params = JSON.parse(JSON.stringify(this.params));
		if (this.fileType !== FileFormat.HEX) {
			delete params.uploadEeprom;
			delete params.uploadEeeprom;
		}
		params.address = this.getAddress();
		params.loadingAction = action;
		IqrfNetService.otaUpload(params, new DaemonMessageOptions(null, null, message, () => this.msgId = ''))
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles Daemon API OTA upload responses
	 * @param response Daemon API response
	 */
	private handleOtaUploadResponse(response): void {
		if (response.status > 1000) {
			let message = '';
			switch (response.status) {
				case 1001:
					message = this.$t('iqrfnet.networkManager.otaUpload.messages.invalidRequest', {error: response.statusStr}).toString();
					break;
				case 1004:
					message = this.$t('iqrfnet.networkManager.otaUpload.messages.invalidFile', {error: response.statusStr}).toString();
					break;
				case 1003:
				case 1005:
					message = this.$t('iqrfnet.networkManager.otaUpload.messages.invalidContent', {error: response.statusStr}).toString();
					break;
				case 1006:
					message = this.$t('iqrfnet.networkManager.otaUpload.messages.invalidMemory', {error: response.statusStr}).toString();
					break;
				case 1007:
					message = this.$t('iqrfnet.networkManager.otaUpload.messages.compatibilityError', {error: response.statusStr}).toString();
					break;
				case 1008:
					message = this.$t('iqrfnet.networkManager.otaUpload.messages.noDevicesError').toString();
					break;
				case 1009:
					message = this.$t('iqrfnet.networkManager.otaUpload.messages.deviceOfflineError').toString();
					break;
				case 1010:
					message = this.$t('iqrfnet.networkManager.otaUpload.messages.noHwpidMatch').toString();
					break;
				case 1002:
				default:
					message = this.$t('iqrfnet.networkManager.otaUpload.messages.generalError', {error: response.statusStr}).toString();
			}
			this.$store.commit('spinner/HIDE');
			this.$toast.error(message);
		} else {
			if (this.target === NetworkTarget.COORDINATOR ||
				this.target === NetworkTarget.NODE) {
				this.deviceResponse(response);
			} else {
				this.networkResponse(response);
			}
		}
	}

	/**
	 * Handles OTA upload response for coordinator and node devices
	 * @param response Daemon API response
	 */
	private deviceResponse(response): void {
		const status = response.status;
		if (status === 0) {
			const action = response.rsp.loadingAction;
			const address = response.rsp.deviceAddr;
			if (action === OtaUploadAction.UPLOAD) {
				this.$store.commit('spinner/HIDE');
				this.checks.upload = true;
				this.$toast.success(
					this.$t(address === 0 ?
						'iqrfnet.networkManager.otaUpload.messages.coordinator.uploadStepSuccess':
						'iqrfnet.networkManager.otaUpload.messages.node.uploadStepSuccess', {address: address}
					).toString()
				);
			} else if (action === OtaUploadAction.VERIFY) {
				this.$store.commit('spinner/HIDE');
				this.checks.verify = true;
				this.$toast.success(
					this.$t(address === 0 ?
						'iqrfnet.networkManager.otaUpload.messages.coordinator.verifyStepSuccess':
						'iqrfnet.networkManager.otaUpload.messages.node.verifyStepSuccess', {address: address}
					).toString()
				);
			} else {
				this.$store.commit('spinner/HIDE');
				this.checks.flash = true;
				this.$toast.success(
					this.$t(address === 0 ?
						'iqrfnet.networkManager.otaUpload.messages.coordinator.loadStepSuccess':
						'iqrfnet.networkManager.otaUpload.messages.node.loadStepSuccess', {address: address}
					).toString()
				);
			}
			return;
		}
		this.$store.commit('spinner/HIDE');
		const address = response.rsp.deviceAddr;
		if (status === -1) {
			this.$toast.error(
				this.$t(address === 0 ?
					'forms.messages.coordinatorOffline':
					'forms.messages.deviceOffline', {address: address}
				).toString()
			);
		} else if (status === 8) {
			this.$toast.error(
				this.$t('forms.messages.noDevice', {address: address}).toString()
			);
		} else {
			this.$toast.error(
				this.$t('iqrfnet.networkManager.otaUpload.messages.genericError').toString()
			);
		}

	}

	/**
	 * Handles OTA upload response for network target
	 * @param response Daemon API response
	 */
	private networkResponse(response): void {
		const action = response.rsp.loadingAction;
		this.$store.commit('spinner/HIDE');
		if (action === OtaUploadAction.UPLOAD) {
			this.checks.upload = true;
			this.$toast.success(
				this.$t('iqrfnet.networkManager.otaUpload.messages.network.uploadStepSuccess').toString()
			);
		} else {
			if (action === OtaUploadAction.VERIFY) {
				this.results = response.rsp.verifyResult;
				this.checks.verify = true;
			} else {
				this.results = response.rsp.loadResult;
				this.checks.flash = true;
			}
			(this.$refs.result as OtaUploadResultModal).showModal(action);
		}
	}
}
</script>

<style scoped>
.step-check {
	display: flex;
	align-items: center;
	justify-content: space-between;
}
</style>
