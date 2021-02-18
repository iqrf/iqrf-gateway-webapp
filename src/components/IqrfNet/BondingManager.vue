<template>
	<CCard class='border-top-0 border-left-0 border-right-0'>
		<CCardBody>
			<h4>{{ $t('iqrfnet.networkManager.bondingManager.title') }}</h4><br>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm>
					<CSelect
						:value.sync='bondMethod'
						:label='$t("iqrfnet.networkManager.bondingManager.form.bondMethod")'
						:options='bondMethodOptions'
					/>
					<ValidationProvider
						v-if='bondMethod !== "autoNetwork"'
						v-slot='{ errors, touched, valid }'
						rules='integer|required|between:1,239'
						:custom-messages='{
							integer: "iqrfnet.networkManager.messages.invalid.integer",
							required: "iqrfnet.networkManager.bondingManager.errors.address",
							between: "iqrfnet.networkManager.bondingManager.errors.address"
						}'
					>
						<CInput
							v-model.number='address'
							type='number'
							min='1'
							max='239'
							:label='$t("iqrfnet.networkManager.bondingManager.form.address")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
							:disabled='autoAddress'
						/>
					</ValidationProvider>
					<CInputCheckbox
						:checked.sync='autoAddress'
						:label='$t("iqrfnet.networkManager.bondingManager.form.autoAddress")'
					/>
					<ValidationProvider
						v-slot='{ errors, touched, valid}'
						rules='integer|required|between:0,255'
						:custom-messages='{
							integer: "iqrfnet.networkManager.messages.invalid.integer",
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
					<ValidationProvider
						v-if='bondMethod === "smartConnect"'
						v-slot='{ errors, valid }'
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
					<CInputCheckbox
						:checked.sync='unbondCoordinatorOnly'
						:label='$t("iqrfnet.networkManager.bondingManager.form.unbondCoordinatorOnly")'
					/>
					<CButton
						color='primary' 
						type='button' 
						:disabled='invalid' 
						@click.prevent='processSubmitBond'
					>
						{{ $t('forms.bond') }}
					</CButton> <CButton
						color='secondary' 
						type='button' 
						:disabled='invalid || autoAddress'
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
					<CModal
						:title='$t("forms.clearBonds")'
						color='danger'
						:show.sync='modalClear'
					>
						{{ $t('iqrfnet.networkManager.bondingManager.messages.clearAllPrompt') }}
						<template #footer>
							<CButton
								color='danger'
								@click='modalClear = false'
							>
								{{ $t('forms.no') }}
							</CButton> <CButton
								color='success'
								@click='processSubmitClearAll'
							>
								{{ $t('forms.yes') }}
							</CButton>
						</template>
					</CModal>
					<CModal
						:title='$t("forms.unbond")'
						color='danger'
						:show.sync='modalUnbond'
					>
						{{ $t('iqrfnet.networkManager.bondingManager.messages.unbondPrompt', {address: address}) }}
						<template #footer>
							<CButton
								color='danger'
								@click='modalUnbond = false'
							>
								{{ $t('forms.no') }}
							</CButton> <CButton
								color='success'
								@click='processSubmitUnbond'
							>
								{{ $t('forms.yes') }}
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
import {MutationPayload} from 'vuex';
import {CButton, CCard, CCardBody, CForm, CInput, CInputCheckbox, CModal, CSelect} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {between, integer, required} from 'vee-validate/dist/rules';
import IqrfNetService from '../../services/IqrfNetService';
import { WebSocketOptions } from '../../store/modules/webSocketClient.module';
import { IOption } from '../../interfaces/coreui';

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
	private address = 1

	/**
	 * @var {boolean} autoAddress Use first available address
	 */
	private autoAddress = false

	/**
	 * @var {string} bondMethod Bonding method
	 */
	private bondMethod = 'local'

	/**
	 * @constant {Array<IOption>} bondMethodOptions Bonding method options for CoreUI select
	 */
	private bondMethodOptions: Array<IOption> = [
		{
			value: 'local',
			label: this.$t('iqrfnet.networkManager.bondingManager.form.bondMethodLocal')
		},
		{
			value: 'smartConnect',
			label: this.$t('iqrfnet.networkManager.bondingManager.form.bondMethodSmart')
		}
	]

	/**
	 * @var {number} bondingRetries Number of bonding attempts
	 */
	private bondingRetries = 1

	/**
	 * @var {boolean} modalClear Show modal for clearing all bonds
	 */
	private modalClear = false

	/**
	 * @var {boolean} modalUnbond Show modal for node unbonding
	 */
	private modalUnbond = false

	/**
	 * @var {string|null} msgId Daemon api message id
	 */
	private msgId: string|null = null

	/**
	 * @var {string} scCode SmartConnect code
	 */
	private scCode = ''
	
	/**
	 * @var {boolean} unbondCoordinatorOnly Unbond node only in coordinator memory
	 */
	private unbondCoordinatorOnly = false

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;}

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		extend('scCode', (code) => {
			const regex = RegExp('^[a-zA-Z0-9]{34}$');
			return regex.test(code);
		});
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'SOCKET_ONMESSAGE') {
				if (mutation.payload.mType === 'iqmeshNetwork_BondNodeLocal' ||
					mutation.payload.mType === 'iqmeshNetwork_SmartConnect') {
					if (mutation.payload.data.msgId !== this.msgId) {
						return;
					}
					this.$store.dispatch('spinner/hide');
					this.$store.dispatch('removeMessage', this.msgId);
					this.handleBondResponse(mutation.payload.data);
				} else if (mutation.payload.mType === 'iqmeshNetwork_RemoveBondOnlyInC' ||
							mutation.payload.mType === 'iqmeshNetwork_RemoveBond') {
					if (mutation.payload.data.msgId !== this.msgId) {
						return;
					}
					this.$store.dispatch('spinner/hide');
					this.$store.dispatch('removeMessage', this.msgId);
					this.handleRemoveResponse(mutation.payload.data);
				} else if (mutation.payload.mType === 'messageError') {
					if (mutation.payload.data.msgId !== this.msgId) {
						return;
					}
					this.$store.dispatch('spinner/hide');
					this.$toast.error(
						this.$t('iqrfnet.networkManager.messages.submit.invalidMessage')
							.toString()
					);
				}
			}
		});
	}

	/**
	 * Vue lifecycle hook beforeDestroy
	 */
	beforeDestroy(): void {
		this.$store.dispatch('removeMessage', this.msgId);
		this.unsubscribe();
	}

	/**
	 * Creates WebSocket request options object
	 * @param {number} timeout Request timeout in milliseconds
	 * @param {string} message Request timeout message
	 * @returns {WebSocketOptions} WebSocket request options
	 */
	private buildOptions(timeout: number, message: string): WebSocketOptions {
		return new WebSocketOptions(null, timeout, message, () => this.msgId = null);
	}

	/**
	 * Performs local bonding or SmartConnect bonding
	 */
	private processSubmitBond(): void {
		this.$store.dispatch('spinner/show', {timeout: 11000});
		const address = this.autoAddress ? 0 : this.address;
		if (this.bondMethod === 'local') {
			IqrfNetService.bondLocal(address, this.buildOptions(11000, 'iqrfnet.networkManager.messages.submit.timeout'))
				.then((msgId: string) => this.msgId = msgId);
			this.$store.commit('spinner/UPDATE_TEXT', this.$t('iqrfnet.networkManager.bondingManager.messages.bondLocalAction').toString());
		} else if (this.bondMethod === 'smartConnect') {
			IqrfNetService.bondSmartConnect(address, this.scCode, this.bondingRetries, this.buildOptions(30000, 'iqrfnet.networkManager.messages.submit.timeout'))
				.then((msgId: string) => this.msgId = msgId);
		}
	}

	/**
	 * Handles BondNodeLocal and SmartConnect Daemon API call responses
	 */
	private handleBondResponse(response): void {
		if (response.status === 0) {
			let bondAddr = this.address;
			if (this.autoAddress) {
				if (this.bondMethod === 'local') {
					bondAddr = response.rsp.assignedAddr;
				} else {
					bondAddr = response.rsp.result.bondAddr;
				}
			}
			this.$emit('update-devices', {
				message: this.$t('iqrfnet.networkManager.bondingManager.messages.bondSuccess', {address: bondAddr}).toString(),
				type: 'success',
			});
		} else if (response.status === -1) {
			this.$toast.error(
				this.$t('iqrfnet.networkManager.messages.submit.timeout').toString()
			);
		} else {
			if (response.statusStr.includes('Dpa error')) {
				this.$toast.error(
					this.$t('iqrfnet.networkManager.bondingManager.messages.timeout').toString()
				);
				return;
			}
			this.$toast.error(
				this.$t('iqrfnet.networkManager.bondingManager.messages.genericBondError').toString()
			);
		}
	}

	/**
	 * Handles RemoveBond Daemon API call responses
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
			if (response.statusStr === 'DPA error: ERROR_FAIL') {
				this.$toast.error(
					this.$t('forms.messages.noDevice', {address: this.address}).toString()
				);
				return;
			}
		}
		if (response.statusStr === 'Transaction error: ERROR_TIMEOUT') {
			this.$toast.error(
				this.$t('forms.messages.deviceOffline', {address: this.address}).toString()
			);
			return;
		}
		if (response.statusStr === 'DPA error: ERROR_NADR') {
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

	/**
	 * Unbonds a bonded node
	 */
	private processSubmitUnbond(): void {
		this.modalUnbond = false;
		this.$store.dispatch('spinner/show', {timeout: 30000});
		IqrfNetService.removeBond(this.address, this.unbondCoordinatorOnly, this.buildOptions(30000, 'iqrfnet.networkManager.messages.submit.timeout'))
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Clears all bonds
	 */
	private processSubmitClearAll(): void {
		this.modalClear = false;
		this.$store.dispatch('spinner/show', {timeout: 120000});
		this.$store.commit('spinner/UPDATE_TEXT', this.$t('iqrfnet.networkManager.bondingManager.messages.clearAll' + (this.unbondCoordinatorOnly ? 'CStatus' : 'Status')).toString());
		IqrfNetService.clearAllBonds(this.unbondCoordinatorOnly, this.buildOptions(120000, 'iqrfnet.networkManager.messages.submit.timeout'))
			.then((msgId: string) => this.msgId = msgId);
	}
}
</script>
