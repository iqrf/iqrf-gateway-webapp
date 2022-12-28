<!--
Copyright 2017-2022 IQRF Tech s.r.o.
Copyright 2019-2022 MICRORISC s.r.o.

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
	<CCard class='border-top-0 border-left-0 border-right-0 card-margin-bottom'>
		<CCardBody>
			<h4>{{ $t('iqrfnet.networkManager.bondingManager.title') }}</h4><br>
			<ValidationObserver v-slot='{invalid}'>
				<CForm>
					<CSelect
						v-if='bondTargetAvailable'
						:value.sync='bondTarget'
						:label='$t("iqrfnet.networkManager.bondingManager.form.bondTarget")'
						:options='bondTargetOptions'
					/>
					<div v-if='bondTarget === "device"'>
						<CSelect
							:value.sync='bondMethod'
							:label='$t("iqrfnet.networkManager.bondingManager.form.bondMethod")'
							:options='bondMethodOptions'
						/>
						<div class='form-group'>
							<label for='addressSwitch'>
								<b>
									{{ $t('iqrfnet.networkManager.bondingManager.form.autoAddress') }}
								</b>
							</label><br>
							<CSwitch
								id='addressSwitch'
								shape='pill'
								size='lg'
								color='primary'
								label-on='ON'
								label-off='OFF'
								:checked.sync='autoAddress'
							/>
						</div>
						<ValidationProvider
							v-if='!autoAddress'
							v-slot='{errors, touched, valid}'
							rules='integer|required|between:1,239'
							:custom-messages='{
								integer: "forms.errors.integer",
								required: "iqrfnet.networkManager.bondingManager.errors.address",
								between: "iqrfnet.networkManager.bondingManager.errors.address"
							}'
						>
							<CInput
								v-model.number='address'
								type='number'
								min='1'
								max='239'
								:label='$t("forms.fields.address")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
								:disabled='autoAddress'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-if='bondMethod === "smartconnect"'
							v-slot='{errors, valid}'
							rules='required|scCode'
							:custom-messages='{
								required: "iqrfnet.networkManager.bondingManager.errors.scCodeMissing",
								scCode: "iqrfnet.networkManager.bondingManager.errors.scCodeInvalid"
							}'
						>
							<CInput
								v-model='scCode'
								:label='$t("iqrfnet.networkManager.bondingManager.form.smartConnect")'
								:is-valid='valid'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='integer|required|between:0,255'
							:custom-messages='{
								integer: "forms.errors.integer",
								required: "iqrfnet.networkManager.bondingManager.errors.bondingRetries",
								between: "iqrfnet.networkManager.bondingManager.errors.bondingRetries"
							}'
						>
							<CInput
								v-model.number='bondingRetries'
								type='number'
								min='0'
								max='255'
								:label='$t("iqrfnet.networkManager.bondingManager.form.bondingRetries")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<CInputCheckbox
							:checked.sync='unbondCoordinatorOnly'
							:label='$t("iqrfnet.networkManager.bondingManager.form.unbondCoordinatorOnly")'
						/>
						<CButton
							color='primary'
							:disabled='invalid'
							@click.prevent='bond'
						>
							{{ $t('forms.bond') }}
						</CButton> <CButton
							color='secondary'
							:disabled='(autoAddress || (address < 1 || address > 239 || !Number.isInteger(address)))'
							@click='modalUnbond = true'
						>
							{{ $t('forms.unbond') }}
						</CButton> <CButton
							color='secondary'
							type='button'
							@click='modalClear = true'
						>
							{{ $t('forms.clearBonds') }}
						</CButton>
					</div>
					<div v-else>
						<CSelect
							:value.sync='bondTool'
							:label='$t("iqrfnet.networkManager.bondingManager.form.toolType")'
							:options='bondToolOptions'
						/>
						<CButton
							v-if='bondTool === "nfc"'
							color='primary'
							@click='bondNfc'
						>
							{{ $t('iqrfnet.networkManager.bondingManager.form.bondNfcReader') }}
						</CButton>
					</div>
					<CModal
						color='danger'
						:show.sync='modalClear'
					>
						<template #header>
							<h5 class='modal-title'>
								{{ $t('iqrfnet.networkManager.bondingManager.modal.clearAllTitle') }}
							</h5>
						</template>
						{{ $t('iqrfnet.networkManager.bondingManager.modal.clearAllPrompt') }}
						<template #footer>
							<CButton
								color='danger'
								@click='clearAll'
							>
								{{ $t('forms.clearBonds') }}
							</CButton> <CButton
								color='secondary'
								@click='modalClear = false'
							>
								{{ $t('forms.cancel') }}
							</CButton>
						</template>
					</CModal>
					<CModal
						color='danger'
						:show.sync='modalUnbond'
					>
						<template #header>
							<h5 class='modal-title'>
								{{ $t('iqrfnet.networkManager.bondingManager.modal.unbondTitle') }}
							</h5>
						</template>
						{{ $t('iqrfnet.networkManager.bondingManager.modal.unbondPrompt', {address: address}) }}
						<template #footer>
							<CButton
								color='danger'
								@click='unbond'
							>
								{{ $t('forms.unbond') }}
							</CButton> <CButton
								color='secondary'
								@click='modalUnbond = false'
							>
								{{ $t('forms.cancel') }}
							</CButton>
						</template>
					</CModal>
				</CForm>
			</ValidationObserver>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CForm, CInput, CInputCheckbox, CModal, CSelect} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {between, integer, required} from 'vee-validate/dist/rules';
import {versionHigherEqual} from '@/helpers/versionChecker';
import {compare} from 'compare-versions';

import {BondingMethod, BondingTarget, Tool} from '@/enums/IqrfNet/bonding';
import IqrfNetService from '@/services/IqrfNetService';

import {IOption} from '@/interfaces/coreui';
import {MutationPayload} from 'vuex';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CForm,
		CInput,
		CInputCheckbox,
		CModal,
		CSelect,
		ValidationObserver,
		ValidationProvider
	}
})

/**
 * Bonding manager card for Network Manager
 */
export default class BondingManager extends Vue {
	/**
	 * @var {number} address Address to assign a newly bonded node
	 */
	private address = 1;

	/**
	 * @var {boolean} autoAddress Use first available address
	 */
	private autoAddress = false;

	/**
	 * @var {BondingMethod} bondMethod Bonding method
	 */
	private bondMethod = BondingMethod.LOCAL;

	/**
	 * @constant {Array<IOption>} bondMethodOptions Bonding method options for CoreUI select
	 */
	private bondMethodOptions: Array<IOption> = [
		{
			value: BondingMethod.LOCAL,
			label: this.$t('iqrfnet.networkManager.bondingManager.form.bondMethods.local')
		},
		{
			value: BondingMethod.SMARTCONNECT,
			label: this.$t('iqrfnet.networkManager.bondingManager.form.bondMethods.smart')
		}
	];

	/**
	 * @var {BondingTarget} bondTarget Bonding target
	 */
	private bondTarget = BondingTarget.DEVICE;

	/**
	 * @var {boolean} bondTargetAvailable Shows bond target select
	 */
	private bondTargetAvailable = false;

	/**
	 * @constant {Array<IOption>} bondTargetOptions Bonding target options for coreui select
	 */
	private bondTargetOptions: Array<IOption> = [
		{
			value: BondingTarget.DEVICE,
			label: this.$t('iqrfnet.networkManager.bondingManager.form.bondingTargets.device')
		},
		{
			value: BondingTarget.SERVICETOOL,
			label: this.$t('iqrfnet.networkManager.bondingManager.form.bondingTargets.service')
		}
	];

	/**
	 * @var {Tool} tool Bond tool
	 */
	private bondTool = Tool.NFC;

	/**
	 * @constant {Array<IOption>} toolOptions Tool options for coreui select
	 */
	private bondToolOptions: Array<IOption> = [
		{
			value: Tool.NFC,
			label: this.$t('iqrfnet.networkManager.bondingManager.form.toolTypes.nfc')
		}
	];

	/**
	 * @var {number} bondingRetries Number of bonding attempts
	 */
	private bondingRetries = 1;

	/**
	 * @var {boolean} modalClear Show modal for clearing all bonds
	 */
	private modalClear = false;

	/**
	 * @var {boolean} modalUnbond Show modal for node unbonding
	 */
	private modalUnbond = false;

	/**
	 * @var {string|null} msgId Daemon api message id
	 */
	private msgId: string|null = null;

	/**
	 * @var {string} scCode SmartConnect code
	 */
	private scCode = '';

	/**
	 * @var {boolean} unbondCoordinatorOnly Unbond node only in coordinator memory
	 */
	private unbondCoordinatorOnly = false;

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * @var {boolean} daemon236 Indicates that Daemon version is 2.3.6 or higher
	 */
	private daemon236 = false;

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		extend('scCode', (code) => {
			const regex = RegExp('^[1-9a-km-tv-zA-HJ-NP-Z]{34}$');
			return regex.test(code);
		});
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'daemonClient/SOCKET_ONMESSAGE') {
				if (mutation.payload.data.msgId !== this.msgId) {
					return;
				}
				this.$store.dispatch('daemonClient/removeMessage', this.msgId);
				if (mutation.payload.mType === 'iqmeshNetwork_EnumerateDevice') {
					this.handleEnumerationResponse(mutation.payload.data);
					return;
				}
				this.$store.dispatch('spinner/hide');
				if (mutation.payload.mType === 'iqmeshNetwork_BondNodeLocal') {
					this.handleBondResponse(mutation.payload.data);
				} else if (mutation.payload.mType === 'iqmeshNetwork_SmartConnect') {
					this.handleSmartConnectResponse(mutation.payload.data);
				} else if (mutation.payload.mType === 'iqmeshNetwork_RemoveBondOnlyInC' ||
					mutation.payload.mType === 'iqmeshNetwork_RemoveBond') {
					this.handleRemoveResponse(mutation.payload.data);
				} else if (mutation.payload.mType === 'iqrfRaw') {
					this.handleBondNfcResponse(mutation.payload.data);
				} else if (mutation.payload.mType === 'messageError') {
					this.$toast.error(
						this.$t('messageError', {error: mutation.payload.data.rsp.errorStr}).toString()
					);
				}
			}
		});
	}

	/**
	 * Initializes daemon version for error handling
	 */
	mounted(): void {
		this.daemon236 = versionHigherEqual('2.3.6');
		this.enumerateCoordinator();
	}

	/**
	 * Vue lifecycle hook beforeDestroy
	 */
	beforeDestroy(): void {
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		this.unsubscribe();
	}

	/**
	 * Creates WebSocket request options object
	 * @param {number} timeout Request timeout in milliseconds
	 * @param {string} message Request timeout message
	 * @returns {DaemonMessageOptions} WebSocket request options
	 */
	private buildOptions(timeout: number, message: string): DaemonMessageOptions {
		return new DaemonMessageOptions(null, timeout, message, () => this.msgId = null);
	}

	/**
	 * Enumerates coordinator device
	 */
	private enumerateCoordinator(): void {
		IqrfNetService.enumerateDevice(0, 60000)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles enumeration Daemon API responses
	 */
	private handleEnumerationResponse(response): void {
		if (response.status !== 0) {
			return;
		}
		const os = response.rsp.osRead.osBuild;
		if (parseInt(os, 16) < 0x08d7) {
			return;
		}
		const dpa = response.rsp.peripheralEnumeration.dpaVer;
		if (compare(dpa, '4.16', '<')) {
			return;
		}
		this.bondTargetAvailable = true;
	}

	/**
	 * Bonds a new node device via local bonding or smartconnect
	 */
	private bond(): void {
		this.$store.dispatch('spinner/show', {timeout: 30000});
		const address = this.autoAddress ? 0 : this.address;
		if (this.bondMethod === 'local') {
			IqrfNetService.bondLocal(address, this.bondingRetries, this.buildOptions(30000, 'iqrfnet.networkManager.bondingManager.messages.bondTimeout'))
				.then((msgId: string) => this.msgId = msgId);
			this.$store.commit('spinner/UPDATE_TEXT', this.$t('iqrfnet.networkManager.bondingManager.messages.bondLocalAction').toString());
		} else if (this.bondMethod === BondingMethod.SMARTCONNECT) {
			IqrfNetService.bondSmartConnect(address, this.scCode, this.bondingRetries, this.buildOptions(30000, 'iqrfnet.networkManager.bondingManger.messages.bondTimeout'))
				.then((msgId: string) => this.msgId = msgId);
		}
	}

	/**
	 * Handles BondNodeLocal and SmartConnect Daemon API responses
	 */
	private handleBondResponse(response): void {
		if (response.status === 0) {
			const bondAddr = this.autoAddress ? response.rsp.assignedAddr : this.address;
			this.$emit('update-devices', {
				message: this.$t('iqrfnet.networkManager.bondingManager.messages.bondSuccess', {address: bondAddr}).toString(),
				type: 'success',
			});
			return;
		}

		if (response.status === 1003) {
			this.$toast.error(
				this.$t('iqrfnet.networkManager.bondingManager.messages.bondExists', {address: this.address}).toString()
			);
		} else if (response.status === 1004) {
			this.$toast.error(
				this.$t('iqrfnet.networkManager.bondingManager.messages.noAddressAvailable').toString()
			);
		} else {
			this.$toast.error(
				this.$t('iqrfnet.networkManager.bondingManager.messages.genericBondError').toString()
			);
		}
	}

	/**
	 * Handles SmartConnect Daemon API response
	 */
	private handleSmartConnectResponse(response) {
		if (response.status === 0) {
			const bondAddr = this.autoAddress ? response.rsp.assignedAddr: this.address;
			this.$emit('update-devices', {
				message: this.$t('iqrfnet.networkManager.bondingManager.messages.bondSuccess', {address: bondAddr}).toString(),
				type: 'success',
			});
			return;
		}

		if (response.status === 1003) {
			this.$toast.error(
				this.$t('iqrfnet.networkManager.bondingManager.messages.bondExists', {address: this.address}).toString()
			);
		} else if (response.status === 1004) {
			this.$toast.error(
				this.$t('iqrfnet.networkManager.bondingManager.messages.noAddressAvailable').toString()
			);
		} else {
			this.$toast.error(
				this.$t('iqrfnet.networkManager.bondingManager.messages.smartConnectErrorMessage', {message: response.statusStr}).toString()
			);
		}
	}

	/**
	 * Bonds NFC reader
	 */
	private bondNfc(): void {
		this.$store.dispatch('spinner/show', {timeout: 12000});
		IqrfNetService.bondNfc(new DaemonMessageOptions(null, 12000,'iqrfnet.networkManager.bondingManager.messages.bondTimeout', () => this.msgId = null))
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles Coordinator bond node Daemon API responses
	 */
	private handleBondNfcResponse(response): void {
		if (response.status === 0) {
			this.$toast.success(
				this.$t('iqrfnet.networkManager.bondingManager.messages.bondNfcSuccess').toString()
			);
		} else {
			this.$toast.error(
				this.$t('iqrfnet.networkManager.bondingManager.messages.bondNfcFailure').toString()
			);
		}
	}

	/**
	 * Unbonds a bonded node
	 */
	private unbond(): void {
		this.modalUnbond = false;
		this.$store.dispatch('spinner/show', {timeout: 30000});
		IqrfNetService.removeBond(this.address, this.unbondCoordinatorOnly, this.buildOptions(30000, 'iqrfnet.networkManager.bondingManager.messages.unbondTimeout'))
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Clears all bonds
	 */
	private clearAll(): void {
		this.modalClear = false;
		this.$store.dispatch('spinner/show', {timeout: 120000});
		this.$store.commit('spinner/UPDATE_TEXT', this.$t('iqrfnet.networkManager.bondingManager.messages.clearAll' + (this.unbondCoordinatorOnly ? 'CStatus' : 'Status')).toString());
		IqrfNetService.clearAllBonds(this.unbondCoordinatorOnly, this.buildOptions(120000, 'iqrfnet.networkManager.bondingManager.messages.unbondTimeout'))
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles RemoveBond Daemon API responses
	 */
	private handleRemoveResponse(response) {
		if (response.status === 0) {
			if (response.rsp.nodesNr === 0) { // all bonds cleared
				if (this.unbondCoordinatorOnly) {
					this.$emit('update-devices', {
						message: this.$t('iqrfnet.networkManager.bondingManager.messages.clearAllCSuccess').toString(),
						type: 'success',
					});
				} else {
					this.$emit('update-devices', {
						message: this.$t('iqrfnet.networkManager.bondingManager.messages.clearAllSuccess').toString(),
						type: 'success',
					});
				}
			} else { // select nodes
				if (this.unbondCoordinatorOnly) {
					this.$emit('update-devices', {
						message: this.$t('iqrfnet.networkManager.bondingManager.messages.unbondSuccessC', {address: this.address}).toString(),
						type: 'success',
					});
				} else {
					if (response.rsp.removeBondFailedNodes) { //clear all, but some were offline
						this.$emit('update-devices', {
							message: this.$t('iqrfnet.networkManager.bondingManager.messages.clearAllPartialSuccess', {nodes: response.rsp.removeBondFailedNodes.join(', ')}).toString(),
							type: 'info',
						});
					} else {
						this.$emit('update-devices', {
							message: this.$t('iqrfnet.networkManager.bondingManager.messages.unbondSuccess', {address: this.address}).toString(),
							type: 'success',
						});
					}
				}
			}
			return;
		}
		if (this.unbondCoordinatorOnly) {
			if (response.status === 1 || (response.statusStr === 'DPA error: ERROR_FAIL' && !this.daemon236)) {
				this.$toast.error(
					this.$t('forms.messages.noDevice', {address: this.address}).toString()
				);
				return;
			}
		}
		if (response.status === -1 || (response.statusStr === 'Transaction error: ERROR_TIMEOUT' && !this.daemon236)) {
			this.$toast.error(
				this.$t('forms.messages.deviceOffline', {address: this.address}).toString()
			);
			return;
		}
		if (response.status === 8 || (response.statusStr === 'DPA error: ERROR_NADR' && !this.daemon236)) {
			this.$toast.error(
				this.$t('forms.messages.noDevice', {address: this.address}).toString()
			);
			return;
		}
		if (response.statusStr === 'Bad FRC status: (int)status="255" ') {
			this.$toast.error(
				this.$t('iqrfnet.networkManager.bondingManager.messages.noDevices').toString()
			);
			return;
		}
		this.$toast.error(
			this.$t('iqrfnet.networkManager.bondingManager.messages.unbondFailure', {address: this.address}).toString()
		);
	}
}
</script>
