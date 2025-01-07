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
		<v-card flat tile>
			<v-card-text>
				<ValidationObserver v-slot='{invalid}'>
					<v-form>
						<v-radio-group
							v-model='fileType'
							:label='$t("iqrfnet.networkManager.otaUpload.form.fileType")'
							column
							dense
						>
							<v-radio
								v-for='(type, idx) of fileTypeOptions'
								:key='idx'
								:label='type.text'
								:value='type.value'
							/>
						</v-radio-group>
						<ValidationProvider
							v-slot='{errors, valid}'
							rules='required|selectedFile'
							:custom-messages='{
								required: $t("iqrfnet.networkManager.otaUpload.errors.file"),
								selectedFile: $t(`iqrfnet.networkManager.otaUpload.errors.not${fileType === FileFormat.HEX ? "Hex" : "Iqrf"}File`)
							}'
						>
							<v-file-input
								v-model='file'
								:accept='fileType === FileFormat.HEX ? ".hex" : ".iqrf"'
								:label='$t("iqrfnet.networkManager.otaUpload.form.file")'
								:error-messages='errors'
								:success='valid'
								:prepend-icon='null'
								prepend-inner-icon='mdi-file-outline'
								required
							/>
						</ValidationProvider>
						<v-select
							v-model='target'
							:class='target === NetworkTarget.NETWORK ? "mb-2": ""'
							:items='targetOptions'
							:label='$t("iqrfnet.networkManager.otaUpload.form.target")'
							:hint='target === NetworkTarget.NETWORK ? $t("iqrfnet.networkManager.otaUpload.notes.network") : ""'
							:persistent-hint='target === NetworkTarget.NETWORK'
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
							<v-text-field
								v-model.number='params.address'
								type='number'
								min='1'
								max='239'
								:label='$t("iqrfnet.networkManager.otaUpload.form.nodeAddress")'
								:success='touched ? valid : null'
								:invalid-feedback='errors'
								@input='resetChecks'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-if='target === NetworkTarget.NETWORK'
							v-slot='{errors, touched, valid}'
							rules='required|integer|between:0,65535'
							:custom-messages='{
								required: $t("iqrfnet.networkManager.otaUpload.errors.hwpid"),
								integer: $t("forms.errors.integer"),
								between: $t("iqrfnet.networkManager.otaUpload.errors.hwpid"),
							}'
						>
							<v-text-field
								v-model.number='params.hwpid'
								class='mb-2'
								type='number'
								min='0'
								max='65535'
								:label='$t("iqrfnet.networkManager.otaUpload.form.hwpidFilter")'
								:success='touched ? valid : null'
								:invalid-feedback='errors'
								:hint='$t("iqrfnet.networkManager.otaUpload.notes.hwpid")'
								persistent-hint
								@input='resetChecks'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required|integer|between:768,16383'
							:custom-messages='{
								required: $t("iqrfnet.networkManager.otaUpload.errors.eeepromAddress"),
								integer: $t("forms.errors.integer"),
								between: $t("iqrfnet.networkManager.otaUpload.errors.eeepromAddress"),
							}'
						>
							<v-text-field
								v-model.number='params.startMemAddr'
								type='number'
								min='768'
								max='16383'
								:label='$t("iqrfnet.networkManager.otaUpload.form.eeepromAddress")'
								:success='touched ? valid : null'
								:invalid-feedback='errors'
								@input='resetChecks'
							/>
						</ValidationProvider>
						<v-checkbox
							v-model='params.uploadEeprom'
							:label='$t("iqrfnet.networkManager.otaUpload.form.uploadEeprom")'
							dense
						/>
						<v-checkbox
							v-model='params.uploadEeeprom'
							:label='$t("iqrfnet.networkManager.otaUpload.form.uploadEeeprom")'
							dense
						/>
						<v-divider />
						<div>
							<v-row align='center'>
								<v-col>
									<strong>
										{{ $t('iqrfnet.networkManager.otaUpload.form.uploadSteps.uploadEeeprom') }}
									</strong>
								</v-col>
								<v-col class='d-flex justify-end'>
									<v-btn
										color='primary'
										:disabled='invalid'
										@click='uploadFile()'
									>
										{{ $t('forms.upload') }}
										<v-icon
											v-if='checks.upload'
											size='xl'
										>
											mdi-check
										</v-icon>
									</v-btn>
								</v-col>
							</v-row>
							<v-row align='center'>
								<v-col>
									<strong>
										{{ $t('iqrfnet.networkManager.otaUpload.form.uploadSteps.verifyEeeprom') }}
									</strong>
								</v-col>
								<v-col class='d-flex justify-end'>
									<v-btn
										color='primary'
										:disabled='invalid || !checks.upload'
										@click='verifyStep'
									>
										{{ $t('forms.verify') }}
										<v-icon
											v-if='checks.verify'
											size='xl'
										>
											mdi-check
										</v-icon>
									</v-btn>
								</v-col>
							</v-row>
							<v-row align='center'>
								<v-col>
									<strong>
										{{ $t('iqrfnet.networkManager.otaUpload.form.uploadSteps.loadFlash') }}
									</strong>
								</v-col>
								<v-col class='d-flex justify-end'>
									<v-btn
										color='primary'
										:disabled='invalid || !checks.verify'
										@click='flashLoadStep'
									>
										{{ $t('forms.load') }}
										<v-icon
											v-if='checks.flash'
											size='xl'
										>
											mdi-check
										</v-icon>
									</v-btn>
								</v-col>
							</v-row>
						</div>
					</v-form>
				</ValidationObserver>
			</v-card-text>
		</v-card>
		<OtaUploadResultModal ref='result' :results='results' />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import OtaUploadResultModal from '@/components/IqrfNet/NetworkManager/OtaUpload/OtaUploadResultModal.vue';

import {between, integer, required} from 'vee-validate/dist/rules';
import IqrfNetService from '@/services/IqrfNetService';
import IqrfService from '@/services/IqrfService';

import {extendedErrorToast} from '@/helpers/errorToast';
import {FileFormat} from '@/iqrfNet/fileFormat';
import {NetworkTarget} from '@/enums/IqrfNet/network';
import {OtaUploadAction} from '@/enums/IqrfNet/OtaUpload';

import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

import {AxiosError, AxiosResponse} from 'axios';
import {IOtaUploadParams, IOtaUploadResult} from '@/interfaces/DaemonApi/Iqmesh/OtaUpload';
import {ISelectItem} from '@/interfaces/Vuetify';
import {MutationPayload} from 'vuex';

interface IqmeshResultBase {
	address: number;
	result: boolean;
}

@Component({
	components: {
		OtaUploadResultModal,
		ValidationObserver,
		ValidationProvider,
	},
	data: () => ({
		FileFormat,
		NetworkTarget,
	})
})

/**
 * OTA upload component for IQRF network manager
 */
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
	 * @var {FileFormat} fileType Type of IQRF file to upload
	 */
	private fileType: FileFormat = FileFormat.HEX;

	/**
	 * @var {File|null} file Selected file
	 */
	private file: File|null = null;

	/**
	 * @constant {Array<ISelectItem>} fileTypeOptions File type select options
	 */
	private fileTypeOptions: Array<ISelectItem> = [
		{
			text: this.$t('iqrfnet.networkManager.otaUpload.form.fileTypes.hex').toString(),
			value: FileFormat.HEX
		},
		{
			text: this.$t('iqrfnet.networkManager.otaUpload.form.fileTypes.iqrf').toString(),
			value: FileFormat.IQRF
		}
	];

	/**
	 * @var {IqrfNetworkTarget} target IQRF network upload target
	 */
	private target: NetworkTarget = NetworkTarget.COORDINATOR;

	/**
	 * @constant {Array<ISelectItem>} targetOptions Upload target select options
	 */
	private targetOptions: Array<ISelectItem> = [
		{
			text: this.$t('iqrfnet.networkManager.otaUpload.form.targets.coordinator').toString(),
			value: NetworkTarget.COORDINATOR,
		},
		{
			text: this.$t('iqrfnet.networkManager.otaUpload.form.targets.node').toString(),
			value: NetworkTarget.NODE,
		},
		{
			text: this.$t('iqrfnet.networkManager.otaUpload.form.targets.network').toString(),
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
		extend('selectedFile', (file: File|null) => {
			if (!file) {
				return false;
			}
			if (this.fileType === FileFormat.HEX && !file.name.endsWith('.hex')) {
				return false;
			}
			if (this.fileType === FileFormat.IQRF && !file.name.endsWith('.iqrf')) {
				return false;
			}
			return true;
		});
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'daemonClient/SOCKET_ONERROR' ||
				mutation.type === 'daemonClient/SOCKET_ONCLOSE') {
				if (this.$store.getters['spinner/isEnabled']) {
					this.$store.commit('spinner/HIDE');
				}
				return;
			}
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
	 * Resets step checks
	 */
	private resetChecks(): void {
		this.checks.upload = this.checks.verify = this.checks.flash = false;
	}

	/**
	 * Resets the inputs and checks if target, address or filtering has changed
	 */
	private clearForm(): void {
		this.resetChecks();
	}

	/**
	 * Uploads file from file input to gateway filesystem
	 */
	private uploadFile(): void {
		if (!this.file) {
			return;
		}
		this.params.file = '';
		const formData = new FormData();
		formData.append('format', this.fileType);
		formData.append('file', this.file);
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
	 * Sends Daemon API request to execute specified step
	 */
	private executeStep(action: OtaUploadAction, message: string): void {
		this.$store.commit('spinner/SHOW');
		this.$store.commit('spinner/UPDATE_TEXT', this.$t(`iqrfnet.networkManager.otaUpload.messages.${action.toLowerCase()}Running`).toString());
		const params = JSON.parse(JSON.stringify(this.params));
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
				this.handleUnicastResponse(response);
			} else {
				this.handleBroadcastResponse(response);
			}
		}
	}

	/**
	 * Handles OTA upload response for coordinator and node devices
	 * @param response Daemon API response
	 */
	private handleUnicastResponse(response): void {
		const status = response.status;
		if (status === 0) {
			const action = response.rsp.loadingAction;
			const address = response.rsp.deviceAddr;
			this.$store.commit('spinner/HIDE');
			if (action === OtaUploadAction.UPLOAD) {
				this.checks.upload = true;
				this.$toast.success(
					this.$t(address === 0 ?
						'iqrfnet.networkManager.otaUpload.messages.coordinator.uploadStepSuccess':
						'iqrfnet.networkManager.otaUpload.messages.node.uploadStepSuccess', {address: address}
					).toString()
				);
			} else {
				this.handleUnicastVerifyLoadResponse(action, response.rsp.verifyResult[0]);
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
	 * Handles unicast verify or load action response
	 * @param {OtaUploadAction} action OTA upload action
	 * @param {IqmeshResultBase} data Verify / load response data
	 */
	private handleUnicastVerifyLoadResponse(action: OtaUploadAction, data: IqmeshResultBase): void {
		if (data.result) {
			if (action === OtaUploadAction.VERIFY) {
				this.checks.verify = true;
				this.$toast.success(
					this.$t(data.address === 0 ?
						'iqrfnet.networkManager.otaUpload.messages.coordinator.verifyStepSuccess':
						'iqrfnet.networkManager.otaUpload.messages.node.verifyStepSuccess', {address: data.address}
					).toString()
				);
			} else {
				this.checks.flash = true;
				this.$toast.success(
					this.$t(data.address === 0 ?
						'iqrfnet.networkManager.otaUpload.messages.coordinator.loadStepSuccess':
						'iqrfnet.networkManager.otaUpload.messages.node.loadStepSuccess', {address: data.address}
					).toString()
				);
			}
		} else {
			this.$toast.error(
				this.$t(action === OtaUploadAction.VERIFY ?
					'iqrfnet.networkManager.otaUpload.messages.verifyStepFail' :
					'iqrfnet.networkManager.otaUpload.messages.loadStepFail'
				).toString(),
			);
		}
	}

	/**
	 * Handles OTA upload response for network target
	 * @param response Daemon API response
	 */
	private handleBroadcastResponse(response): void {
		const action = response.rsp.loadingAction;
		this.$store.commit('spinner/HIDE');
		if (action === OtaUploadAction.UPLOAD) {
			this.checks.upload = true;
			this.$toast.success(
				this.$t('iqrfnet.networkManager.otaUpload.messages.network.uploadStepSuccess').toString()
			);
		} else if (action === OtaUploadAction.VERIFY) {
			this.results = response.rsp.verifyResult;
			(this.$refs.result as OtaUploadResultModal).showModal(action);
			this.checks.verify = true;
		} else {
			this.results = response.rsp.verifyResult;
			(this.$refs.result as OtaUploadResultModal).showModal(action);
			this.checks.flash = true;
		}
	}
}
</script>
