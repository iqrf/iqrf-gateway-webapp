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
			<v-card-title>{{ $t('iqrfnet.networkManager.bondingManager.title') }}</v-card-title>
			<v-card-text>
				<ValidationObserver v-slot='{invalid}'>
					<v-form>
						<v-select
							v-if='bondTargetAvailable'
							v-model='bondTarget'
							:label='$t("iqrfnet.networkManager.bondingManager.form.bondTarget")'
							:items='bondTargetOptions'
						/>
						<div v-if='bondTarget === BondingTarget.DEVICE'>
							<v-select
								v-model='bondMethod'
								:label='$t("iqrfnet.networkManager.bondingManager.form.bondMethod")'
								:items='bondMethodOptions'
							/>
							<v-switch
								v-model='autoAddress'
								:label='$t("iqrfnet.networkManager.bondingManager.form.autoAddress")'
								inset
								dense
							/>
							<ValidationProvider
								v-if='!autoAddress'
								v-slot='{errors, touched, valid}'
								rules='integer|required|between:1,239'
								:custom-messages='{
									integer: $t("forms.errors.integer"),
									required: $t("iqrfnet.networkManager.bondingManager.errors.address"),
									between: $t("iqrfnet.networkManager.bondingManager.errors.address"),
								}'
							>
								<v-text-field
									v-model.number='address'
									type='number'
									min='1'
									max='239'
									:label='$t("forms.fields.address")'
									:success='touched ? valid : null'
									:error-messages='errors'
									:disabled='autoAddress'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-if='bondMethod === BondingMethod.SMARTCONNECT'
								v-slot='{errors, valid}'
								rules='required|scCode'
								:custom-messages='{
									required: $t("iqrfnet.networkManager.bondingManager.errors.scCodeMissing"),
									scCode: $t("iqrfnet.networkManager.bondingManager.errors.scCodeInvalid"),
								}'
							>
								<v-text-field
									v-model='scCode'
									:label='$t("iqrfnet.networkManager.bondingManager.form.smartConnect")'
									:success='valid'
									:error-messages='errors'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='integer|required|between:0,255'
								:custom-messages='{
									integer: $t("forms.errors.integer"),
									required: $t("iqrfnet.networkManager.bondingManager.errors.bondingRetries"),
									between: $t("iqrfnet.networkManager.bondingManager.errors.bondingRetries"),
								}'
							>
								<v-text-field
									v-model.number='bondingRetries'
									type='number'
									min='0'
									max='255'
									:label='$t("iqrfnet.networkManager.bondingManager.form.bondingRetries")'
									:success='touched ? valid : null'
									:error-messages='errors'
								/>
							</ValidationProvider>
							<v-checkbox
								v-model='unbondCoordinatorOnly'
								:label='$t("iqrfnet.networkManager.bondingManager.form.unbondCoordinatorOnly")'
								dense
							/>
							<v-btn
								class='mr-1'
								color='primary'
								:disabled='invalid'
								@click.prevent='bond'
							>
								{{ $t('forms.bond') }}
							</v-btn>
							<UnbondModal
								:address='address'
								:auto-address='autoAddress'
								@unbond='unbond'
							/>
							<ClearAllModal @clear='clearAll' />
						</div>
						<div v-else>
							<v-select
								v-model='bondTool'
								:label='$t("iqrfnet.networkManager.bondingManager.form.toolType")'
								:items='bondToolOptions'
							/>
							<v-btn
								v-if='bondTool === BondingTool.NFC'
								color='primary'
								@click='bondNfc'
							>
								{{ $t('iqrfnet.networkManager.bondingManager.form.bondNfcReader') }}
							</v-btn>
						</div>
					</v-form>
				</ValidationObserver>
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import ClearAllModal from '@/components/IqrfNet/NetworkManager/Iqmesh/ClearAllModal.vue';
import UnbondModal from '@/components/IqrfNet/NetworkManager/Iqmesh/UnbondModal.vue';

import {between, integer, required} from 'vee-validate/dist/rules';
import {smartConnectCode} from '@/helpers/validationRules/Iqrfnet';

import {BondingMethod, BondingTarget, BondingTool} from '@/enums/IqrfNet/Bonding';
import IqrfNetService from '@/services/IqrfNetService';

import {ISelectItem} from '@/interfaces/Vuetify';
import {MutationPayload} from 'vuex';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

/**
 * Bonding manager card for Network Manager
 */
@Component({
	components: {
		ClearAllModal,
		UnbondModal,
		ValidationObserver,
		ValidationProvider
	},
	data: () => ({
		BondingMethod,
		BondingTarget,
		BondingTool,
	}),
})

export default class BondingManager extends Vue {
	/**
	 * @var {string} msgId Daemon api message id
	 */
	private msgId = '';

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
	 * @constant {Array<ISelectItem>} bondMethodOptions Bonding method select options
	 */
	private bondMethodOptions: Array<ISelectItem> = [
		{
			value: BondingMethod.LOCAL,
			text: this.$t('iqrfnet.networkManager.bondingManager.form.bondMethods.local').toString(),
		},
		{
			value: BondingMethod.SMARTCONNECT,
			text: this.$t('iqrfnet.networkManager.bondingManager.form.bondMethods.smart').toString(),
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
	 * @constant {Array<ISelectItem>} bondTargetOptions Bonding target select options
	 */
	private bondTargetOptions: Array<ISelectItem> = [
		{
			value: BondingTarget.DEVICE,
			text: this.$t('iqrfnet.networkManager.bondingManager.form.bondingTargets.device').toString(),
		},
		{
			value: BondingTarget.SERVICETOOL,
			text: this.$t('iqrfnet.networkManager.bondingManager.form.bondingTargets.service').toString(),
		}
	];

	/**
	 * @var {BondingTool} tool Bond tool
	 */
	private bondTool = BondingTool.NFC;

	/**
	 * @constant {Array<ISelectItem>} toolOptions Tool select options
	 */
	private bondToolOptions: Array<ISelectItem> = [
		{
			value: BondingTool.NFC,
			text: this.$t('iqrfnet.networkManager.bondingManager.form.toolTypes.nfc').toString(),
		}
	];

	/**
	 * @var {number} bondingRetries Number of bonding attempts
	 */
	private bondingRetries = 1;

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
	 * Initializes validation rules and mutation handling
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		extend('scCode', smartConnectCode);
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type !== 'daemonClient/SOCKET_ONMESSAGE') {
				return;
			}
			if (mutation.payload.data.msgId !== this.msgId) {
				return;
			}
			this.$store.dispatch('daemonClient/removeMessage', this.msgId);
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

		});
	}

	/**
	 * Vue lifecycle hook beforeDestroy
	 */
	beforeDestroy(): void {
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		this.unsubscribe();
	}

	/**
	 * Enables bonding target selection
	 */
	public enableBondNfc(): void {
		this.bondTargetAvailable = true;
	}

	/**
	 * Creates WebSocket request options object
	 * @param {number} timeout Request timeout in milliseconds
	 * @param {string} message Request timeout message
	 * @returns {DaemonMessageOptions} WebSocket request options
	 */
	private buildOptions(timeout: number, message: string): DaemonMessageOptions {
		return new DaemonMessageOptions(null, timeout, message, () => this.msgId = '');
	}

	/**
	 * Bonds a new node device via local bonding or smartconnect
	 */
	private bond(): void {
		this.$store.dispatch('spinner/show', {timeout: 30000});
		const address = this.autoAddress ? 0 : this.address;
		if (this.bondMethod === BondingMethod.LOCAL) {
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
		IqrfNetService.bondNfc(new DaemonMessageOptions(null, 12000,'iqrfnet.networkManager.bondingManager.messages.bondTimeout', () => this.msgId = ''))
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
		this.$store.dispatch('spinner/show', {timeout: 30000});
		IqrfNetService.removeBond(this.address, this.unbondCoordinatorOnly, this.buildOptions(30000, 'iqrfnet.networkManager.bondingManager.messages.unbondTimeout'))
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Clears all bonds
	 */
	private clearAll(): void {
		this.$store.dispatch('spinner/show', {timeout: 120000});
		this.$store.commit('spinner/UPDATE_TEXT', this.$t(
			`iqrfnet.networkManager.bondingManager.messages.clearAll${this.unbondCoordinatorOnly ? 'CStatus' : 'Status'}`
		).toString());
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
					this.$emit('update-devices', {
						message: this.$t('iqrfnet.networkManager.bondingManager.messages.unbondSuccess', {address: this.address}).toString(),
						type: 'success',
					});
				}
			}
			return;
		}
		if (this.unbondCoordinatorOnly) {
			if (response.status === 1) {
				this.$toast.error(
					this.$t('forms.messages.noDevice', {address: this.address}).toString()
				);
				return;
			}
		}
		if (response.status === -1) {
			this.$toast.error(
				this.$t('forms.messages.deviceOffline', {address: this.address}).toString()
			);
			return;
		}
		if (response.status === 1003) {
			this.$toast.error(
				this.$t('forms.messages.noDevice', {address: this.address}).toString()
			);
			return;
		}
		if (response.status === 1004) {
			const nodes: number[] = response.rsp.result.filter(item => item.bonded && !item.removed).map(item => item.address);
			this.$emit('update-devices', {
				message: this.$t('iqrfnet.networkManager.bondingManager.messages.clearAllPartialSuccess', {nodes: nodes}),
				type: 'error',
			});
			return;
		}
		this.$toast.error(
			this.$t('iqrfnet.networkManager.bondingManager.messages.unbondFailure', {address: this.address}).toString()
		);
	}
}
</script>
