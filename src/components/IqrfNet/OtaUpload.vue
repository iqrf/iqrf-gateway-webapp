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
						@change='resetChecks'
					/>
					<ValidationProvider
						v-if='target === "node"'
						v-slot='{errors, touched, valid}'
						rules='required|integer|between:1,239'
						:custom-messages='{
							required: $t("iqrfnet.networkManager.otaUpload.errors.nodeAddress"),
							integer: $t("forms.errors.integer"),
							between: $t("iqrfnet.networkManager.otaUpload.errors.nodeAddress"),
						}'
					>
						<CInput
							v-model.number='address'
							type='number'
							min='1'
							max='239'
							:label='$t("iqrfnet.networkManager.otaUpload.form.nodeAddress")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='errors.join(", ")'
							@input='resetChecks'
						/>
					</ValidationProvider>
					<div
						v-if='target === "network"'
						class='form-group'
					>
						<p>
							<em class='text-danger'>
								{{ $t('iqrfnet.networkManager.otaUpload.messages.networkNote') }}
							</em>
						</p>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required|integer|between:0,65535'
							:custom-messages='{
								required: $t("iqrfnet.networkManager.otaUpload.errors.hwpid"),
								integer: $t("forms.errors.integer"),
								between: $t("iqrfnet.networkManager.otaUpload.errors.hwpid"),
							}'
						>
							<CInput
								v-model.number='hwpid'
								type='number'
								min='0'
								max='65535'
								:label='$t("iqrfnet.networkManager.otaUpload.form.hwpidFilter")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='errors.join(", ")'
								@input='resetChecks'
							/>
						</ValidationProvider>
						<p>
							<em>
								{{ $t('iqrfnet.networkManager.otaUpload.messages.hwpid') }}
							</em>
						</p>
					</div>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='required|integer|between:768,16383'
						:custom-messages='{
							required: $t("iqrfnet.networkManager.otaUpload.errors.eeepromAddress"),
							integer: $t("forms.errors.integer"),
							between: $t("iqrfnet.networkManager.otaUpload.errors.eeepromAddress"),
						}'
					>
						<CInput
							v-model.number='eeepromAddress'
							type='number'
							min='768'
							max='16383'
							:label='$t("iqrfnet.networkManager.otaUpload.form.eeepromAddress")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='errors.join(", ")'
							@input='resetChecks'
						/>
					</ValidationProvider>
					<CInputCheckbox
						:checked.sync='uploadEepromData'
						:label='$t("iqrfnet.networkManager.otaUpload.form.uploadEeprom")'
					/>
					<CInputCheckbox
						:checked.sync='uploadEeepromData'
						:label='$t("iqrfnet.networkManager.otaUpload.form.uploadEeeprom")'
					/>
					<hr>
					<h4>{{ $t('iqrfnet.networkManager.otaUpload.form.manualUpload') }}</h4>
					<div class='form-group'>
						<div>
							<strong>
								{{ $t('iqrfnet.networkManager.otaUpload.form.uploadSteps.uploadEeeprom') }}
							</strong>
						</div><br>
						<div class='step-check'>
							<CButton
								color='primary'
								:disabled='invalid || fileEmpty'
								@click='uploadFile(false)'
							>
								{{ $t('forms.upload') }}
							</CButton> <CIcon
								v-if='checks.upload'
								class='text-success'
								:content='checkIcon'
								size='xl'
							/>
						</div>
					</div>
					<div class='form-group'>
						<div>
							<strong>
								{{ $t('iqrfnet.networkManager.otaUpload.form.uploadSteps.verifyEeeprom') }}
							</strong>
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
								:content='checkIcon'
								size='xl'
							/>
						</div>
					</div>
					<div class='form-group'>
						<div>
							<strong>
								{{ $t('iqrfnet.networkManager.otaUpload.form.uploadSteps.loadFlash') }}
							</strong>
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
								:content='checkIcon'
								size='xl'
							/>
						</div>
					</div><hr>
					<h4>{{ $t('iqrfnet.networkManager.otaUpload.form.automaticUpload') }}</h4>
					<CButton
						color='primary'
						:disabled='invalid || fileEmpty'
						@click='uploadFile(true)'
					>
						{{ $t('forms.allSteps') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {
	CButton,
	CCard,
	CCardBody,
	CCardHeader,
	CForm,
	CIcon,
	CInput,
	CInputCheckbox,
	CInputFile,
	CSelect
} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {cilCheckCircle} from '@coreui/icons';
import {between, integer, required} from 'vee-validate/dist/rules';
import IqrfNetService from '@/services/IqrfNetService';
import IqrfService from '@/services/IqrfService';

import {extendedErrorToast} from '@/helpers/errorToast';
import {FileFormat} from '@/iqrfNet/fileFormat';
import {NetworkTarget} from '@/iqrfNet/networkTarget';
import {OtaUploadAction} from '@/iqrfNet/otaUploadAction';

import {AxiosError, AxiosResponse} from 'axios';
import {IOption} from '@/interfaces/coreui';
import {MutationPayload} from 'vuex';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CIcon,
		CInput,
		CInputCheckbox,
		CInputFile,
		CSelect,
		ValidationObserver,
		ValidationProvider,
	}
})

/**
 * OTA upload component for IQRF network manager
 */
export default class OtaUpload extends Vue {

	/**
	 * @constant {Array<string>} checkIcon Check icon for steps
	 */
	private checkIcon = cilCheckCircle;

	/**
	 * @var {number} address Target device address for node target option
	 */
	private address = 1;

	/**
	 * @var {boolean} autoUpload Execute all steps automatically
	 */
	private autoUpload = false;

	/**
	 * @var {number} eeepromAddress External EEPROM start address for data
	 */
	private eeepromAddress = 768;

	/**
	 * @var {boolean} fileEmpty Indicates whether file input is empty or not
	 */
	private fileEmpty = true;

	/**
	 * @var {string} fileName Name of file uploaded to gateway
	 */
	private fileName = '';

	/**
	 * @var {FileFormat} fileType Type of IQRF file to upload
	 */
	private fileType: FileFormat = FileFormat.HEX;

	/**
	 * @var {boolean} uploadEepromData Should eeprom data be uploaded?
	 */
	private uploadEepromData = false;

	/**
	 * @var {boolean} uploadEeepromData Should eeeprom data be uploaded?
	 */
	private uploadEeepromData = false;

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
	 * @var {string} hwpidList HWPID to filter nodes by
	 */
	private hwpid = 65535;

	/**
	 * @var {string|null} msgId Message ID
	 */
	private msgId: string|null = null;

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
				this.$t('iqrfnet.networkManager.otaUpload.messages.notHexFile').toString()
			);
			this.clearForm();
			return;
		}
		if (this.fileType === FileFormat.IQRF && !file.name.endsWith('.iqrf')) {
			this.$toast.error(
				this.$t('iqrfnet.networkManager.otaUpload.messages.notIqrfFile').toString()
			);
			this.clearForm();
			return;
		}
		this.resetChecks();
		this.fileEmpty = false;
	}

	/**
	 * Uploads file from file input to gateway filesystem
	 * @param {boolean} autoUpload Run all steps automatically
	 */
	private uploadFile(autoUpload: boolean): void {
		this.autoUpload = autoUpload;
		this.fileName = '';
		const formData = new FormData();
		const file = this.getFiles()[0];
		formData.append('format', this.fileType);
		formData.append('file', file);
		this.$store.commit('spinner/SHOW');
		IqrfService.uploadFile(formData)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.fileName = response.data.fileName;
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
			return this.address;
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
	 * Sends Daemon API request to execute specified step
	 */
	private executeStep(action: OtaUploadAction, message: string): void {
		this.$store.commit('spinner/SHOW');
		this.$store.commit('spinner/UPDATE_TEXT', this.$t(`iqrfnet.networkManager.otaUpload.messages.${action.toLowerCase()}Running`).toString());
		IqrfNetService.otaUpload(this.getAddress(), this.hwpid, this.fileName, this.eeepromAddress, this.uploadEepromData, this.uploadEeepromData, action, new DaemonMessageOptions(null, null, message, () => this.msgId = null)).then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles Daemon API OTA upload responses
	 * @param response Daemon API response
	 */
	private handleOtaUploadResponse(response: any): void {
		if (response.status === 1001) {
			this.$store.commit('spinner/HIDE');
			this.$toast.error(
				this.$t(
					'iqrfnet.networkManager.otaUpload.messages.invalidRequest',
					{error: response.statusStr}
				).toString()
			);
			return;
		}
		if (response.status === 1003) {
			this.$store.commit('spinner/HIDE');
			this.$toast.error(
				this.$t('iqrfnet.networkManager.otaUpload.messages.invalidContent').toString()
			);
			return;
		}
		if (response.status === 1004) {
			this.$store.commit('spinner/HIDE');
			this.$toast.error(
				this.$t(
					'iqrfnet.networkManager.otaUpload.messages.invalidFile',
					{error: response.statusStr}
				).toString()
			);
			return;
		}
		if (response.status === 1005) {
			this.$store.commit('spinner/HIDE');
			this.$toast.error(
				this.$t(
					'iqrfnet.networkManager.otaUpload.messages.invalidContent',
					{error: response.statusStr}
				).toString()
			);
			return;
		}
		if (response.status === 1006) {
			this.$store.commit('spinner/HIDE');
			this.$toast.error(
				this.$t(
					'iqrfnet.networkManager.otaUpload.messages.invalidMemory',
					{error: response.statusStr}
				).toString()
			);
			return;
		}
		if (this.target === NetworkTarget.COORDINATOR ||
			this.target === NetworkTarget.NODE) {
			this.deviceResponse(response);
		} else {
			this.networkResponse(response);
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
				if (this.autoUpload) {
					this.verifyStep();
				} else {
					this.$store.commit('spinner/HIDE');
					this.checks.upload = true;
					this.$toast.success(
						(address === 0 ?
							this.$t('iqrfnet.networkManager.otaUpload.messages.coordinator.uploadStepSuccess') :
							this.$t('iqrfnet.networkManager.otaUpload.messages.node.uploadStepSuccess', {address: address})
						).toString()
					);
				}
			} else if (action === OtaUploadAction.VERIFY) {
				if (this.autoUpload) {
					this.flashLoadStep();
				} else {
					this.$store.commit('spinner/HIDE');
					this.checks.verify = true;
					this.$toast.success(
						(address === 0 ?
							this.$t('iqrfnet.networkManager.otaUpload.messages.coordinator.verifyStepSuccess') :
							this.$t('iqrfnet.networkManager.otaUpload.messages.node.verifyStepSuccess', {address: address})
						).toString()
					);
				}
			} else {
				this.$store.commit('spinner/HIDE');
				if (this.autoUpload) {
					this.$toast.success(
						(address === 0 ?
							this.$t('iqrfnet.networkManager.otaUpload.messages.coordinator.runAllSuccess') :
							this.$t('iqrfnet.networkManager.otaUpload.messages.node.runAllSuccess', {address: address})
						).toString()
					);
				} else {
					this.checks.flash = true;
					this.$toast.success(
						(address === 0 ?
							this.$t('iqrfnet.networkManager.otaUpload.messages.coordinator.loadStepSuccess') :
							this.$t('iqrfnet.networkManager.otaUpload.messages.node.loadStepSuccess', {address: address})
						).toString()
					);
				}
			}
			return;
		}
		this.$store.commit('spinner/HIDE');
		const address = response.rsp.deviceAddr;
		if (status === -1) {
			this.$toast.error(
				(address === 0 ?
					this.$t('forms.messages.coordinatorOffline') :
					this.$t('forms.messages.deviceOffline', {address: address})
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
		if (action === OtaUploadAction.UPLOAD) {
			if (this.autoUpload) {
				this.verifyStep();
			} else {
				this.$store.commit('spinner/HIDE');
				this.checks.upload = true;
				this.$toast.success(
					this.$t('iqrfnet.networkManager.otaUpload.messages.network.uploadStepSuccess').toString()
				);
			}
		} else if (action === OtaUploadAction.VERIFY) {
			if (this.autoUpload) {
				this.flashLoadStep();
			} else {
				const devices: Array<number> = [];
				response.rsp.verifyResult.forEach((item) => {
					if (!item.result) {
						devices.push(item.address);
					}
				});
				this.$store.commit('spinner/HIDE');
				this.checks.verify = true;
				if (devices.length > 0) {
					if (this.hwpid === 65535) {
						this.$toast.info(
							this.$t(
								'iqrfnet.networkManager.otaUpload.messages.network.verifyStepPartialSuccess',
								{devices: devices.join(', ')}
							).toString()
						);
					} else {
						this.$toast.info(
							this.$t(
								'iqrfnet.networkManager.otaUpload.messages.network.verifyStepHwpidPartialSuccess',
								{devices: devices.join(', '), hwpid: this.hwpid}
							).toString()
						);
					}
				} else {
					if (this.hwpid === 65535) {
						this.$toast.success(
							this.$t('iqrfnet.networkManager.otaUpload.messages.network.verifyStepSuccess').toString()
						);
					} else {
						this.$toast.success(
							this.$t(
								'iqrfnet.networkManager.otaUpload.messages.network.verifyStepHwpidSuccess',
								{hwpid: this.hwpid}
							).toString()
						);
					}
				}

			}
		} else {
			const devices: Array<number> = [];
			response.rsp.loadResult.map((item) => {
				if (!item.result) {
					devices.push(item.address);
				}
			});
			this.$store.commit('spinner/HIDE');
			if (!this.autoUpload) {
				this.checks.flash = true;
			}
			if (devices.length > 0) {
				if (this.hwpid === 65535) {
					this.$toast.info(
						this.$t(
							'iqrfnet.networkManager.otaUpload.messages.network.loadStepPartialSuccess',
							{devices: devices.join(', ')}
						).toString()
					);
				} else {
					this.$toast.info(
						this.$t(
							'iqrfnet.networkManager.otaUpload.messages.network.loadStepHwpidPartialSuccess',
							{devices: devices.join(', '), hwpid: this.hwpid}
						).toString()
					);
				}
			} else {
				if (this.hwpid === 65535) {
					this.$toast.success(
						this.$t('iqrfnet.networkManager.otaUpload.messages.network.loadStepSuccess').toString()
					);
				} else {
					this.$toast.success(
						this.$t(
							'iqrfnet.networkManager.otaUpload.messages.network.loadStepHwpidSuccess',
							{hwpid: this.hwpid}
						).toString()
					);
				}
			}
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
