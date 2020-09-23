<template>
	<CCard>
		<CCardHeader>{{ $t('iqrfnet.networkManager.bonding.title') }}</CCardHeader>
		<CCardBody>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm>
					<CSelect
						:value.sync='bondMethod'
						:label='$t("iqrfnet.networkManager.bonding.form.bondMethod")'
						:options='[
							{value: "local", label: $t("iqrfnet.networkManager.bonding.form.bondMethodLocal")},
							{value: "smartConnect", label: $t("iqrfnet.networkManager.bonding.form.bondMethodSmart")},
							{value: "autoNetwork", label: $t("iqrfnet.networkManager.autoNetwork.title")}
						]'
					/>
					<span v-if='bondMethod === "autoNetwork"'>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='integer|required|between:0,7'
							:custom-messages='{
								integer: "iqrfnet.networkManager.messages.invalid.integer",
								required: "iqrfnet.networkManager.messages.discovery.txPower",
								between: "iqrfnet.networkManager.messages.discovery.txPower"
							}'
						>
							<CInput
								v-model.number='autoNetwork.discoveryTxPower'
								requred='true'
								type='number'
								min='0'
								max='7'
								:label='$t("iqrfnet.networkManager.autoNetwork.form.discoveryTxPower")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<CInputCheckbox
							:checked.sync='autoNetwork.discoveryBeforeStart'
							:label='$t("iqrfnet.networkManager.autoNetwork.form.discoveryBeforeStart")'
						/>
						<CInputCheckbox
							:checked.sync='autoNetwork.skipDiscoveryEachWave'
							:label='$t("iqrfnet.networkManager.autoNetwork.form.skipDiscoveryEachWave")'
						/><hr>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='integer|required|between:0,3'
							:custom-messages='{
								integer: "iqrfnet.networkManager.messages.invalid.integer",
								required: "iqrfnet.networkManager.messages.autoNetwork.actionRetries",
								between: "iqrfnet.networkManager.messages.autoNetwork.actionRetries"
							}'
						>
							<CInput
								v-model.number='autoNetwork.actionRetries'
								type='number'
								min='0'
								max='3'
								:label='$t("iqrfnet.networkManager.autoNetwork.form.actionRetries")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider><hr>
						<h4>
							{{ $t('iqrfnet.networkManager.autoNetwork.form.bondingControl') }}
						</h4>
						<CInputCheckbox
							:checked.sync='useOverlappingNetworks'
							:label='$t("iqrfnet.networkManager.autoNetwork.form.overlappingNetworks")'
						/>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='integer|required|between:1,50'
							:custom-messages='{
								integer: "iqrfnet.networkManager.messages.invalid.integer",
								required: "iqrfnet.networkManager.messages.autoNetwork.networks",
								between: "iqrfnet.networkManager.messages.autoNetwork.networks"
							}'
						>
							<CInput
								v-model.number='overlappingNetworks.networks'
								type='number'
								min='1'
								max='50'
								:label='$t("iqrfnet.networkManager.autoNetwork.form.networks")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
								:disabled='!useOverlappingNetworks'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='integer|required|between:1,50'
							:custom-messages='{
								integer: "iqrfnet.networkManager.messages.invalid.integer",
								required: "iqrfnet.networkManager.messages.autoNetwork.network",
								between: "iqrfnet.networkManager.messages.autoNetwork.network"
							}'
						>
							<CInput
								v-model.number='overlappingNetworks.network'
								type='number'
								min='1'
								max='50'
								:label='$t("iqrfnet.networkManager.autoNetwork.form.network")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
								:disabled='!useOverlappingNetworks'
							/>
						</ValidationProvider><hr>
						<h4>
							{{ $t('iqrfnet.networkManager.autoNetwork.form.hwpidFiltering') }}
						</h4>
						<CInputCheckbox
							:checked.sync='useHwpidFiltering'
							:label='$t("iqrfnet.networkManager.autoNetwork.form.hwpidEnable")'
						/>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='hwpidFilter'
							:custom-messages='{
								hwpidFilter: "iqrfnet.networkManager.messages.invalid.autoNetwork.hwpidFilter"
							}'
						>
							<CInput
								v-model='hwpidFiltering'
								:label='$t("iqrfnet.networkManager.autoNetwork.form.hwpids")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
								:disabled='!useHwpidFiltering'
							/>
						</ValidationProvider><hr>
						<h4>
							{{ $t('iqrfnet.networkManager.autoNetwork.form.stopConditions') }}
						</h4>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='integer|required|between:1,127'
							:custom-messages='{
								integer: "iqrfnet.networkManager.messages.invalid.integer",
								required: "iqrfnet.networkManager.messages.autoNetwork.waves",
								between: "iqrfnet.networkManager.messages.autoNetwork.waves"
							}'
						>
							<CInput
								v-model.number='autoNetwork.stopConditions.waves'
								type='number'
								min='1'
								max='127'
								:label='$t("iqrfnet.networkManager.autoNetwork.form.waves")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='integer|required|between:1,127'
							:custom-messages='{
								integer: "iqrfnet.networkManager.messages.invalid.integer",
								required: "iqrfnet.networkManager.messages.autoNetwork.emptyWaves",
								between: "iqrfnet.networkManager.messages.autoNetwork.emptyWaves"
							}'
						>
							<CInput
								v-model.number='autoNetwork.stopConditions.emptyWaves'
								type='number'
								min='1'
								max='127'
								:label='$t("iqrfnet.networkManager.autoNetwork.form.emptyWaves")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='integer|required|between:1,239'
							:custom-messages='{
								integer: "iqrfnet.networkManager.messages.invalid.integer",
								required: "iqrfnet.networkManager.messages.autoNetwork.totalNodes",
								between: "iqrfnet.networkManager.messages.autoNetwork.totalNodes"
							}'
						>
							<CInput
								v-model.number='autoNetwork.stopConditions.numberOfTotalNodes'
								type='number'
								min='1'
								max='239'
								:label='$t("iqrfnet.networkManager.autoNetwork.form.numberOfTotalNodes")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='integer|required|between:1,239'
							:custom-messages='{
								integer: "iqrfnet.networkManager.messages.invalid.integer",
								required: "iqrfnet.networkManager.messages.autoNetwork.newNodes",
								between: "iqrfnet.networkManager.messages.autoNetwork.newNodes"
							}'
						>
							<CInput
								v-model.number='autoNetwork.stopConditions.numberOfNewNodes'
								type='number'
								min='1'
								max='239'
								:label='$t("iqrfnet.networkManager.autoNetwork.form.numberOfNewNodes")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<CInputCheckbox
							:checked.sync='autoNetwork.stopConditions.abortOnTooManyNodesFound'
							:label='$t("iqrfnet.networkManager.autoNetwork.form.abortOnTooManyNodesFound")'
						/>
					</span>
					<ValidationProvider
						v-if='bondMethod !== "autoNetwork"'
						v-slot='{ errors, touched, valid }'
						rules='integer|required|between:1,239'
						:custom-messages='{
							required: "iqrfnet.networkManager.messages.bonding.address",
							integer: "iqrfnet.networkManager.messages.invalid.integer",
							between: "iqrfnet.networkManager.messages.bonding.address"
						}'
					>
						<CInput
							v-model.number='address'
							type='number'
							min='1'
							max='239'
							:label='$t("iqrfnet.networkManager.bonding.form.address")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
							:disabled='autoAddress'
						/>
					</ValidationProvider>
					<CInputCheckbox
						v-if='bondMethod !== "autoNetwork"'
						:checked.sync='autoAddress'
						:label='$t("iqrfnet.networkManager.bonding.form.autoAddress")'
					/>
					<ValidationProvider
						v-if='bondMethod !== "autoNetwork"'
						v-slot='{ errors, touched, valid}'
						rules='integer|required|between:0,255'
						:custom-messages='{
							integer: "iqrfnet.networkManager.messages.invalid.integer",
							required: "iqrfnet.networkManager.messages.bonding.bondingRetries",
							between: "iqrfnet.networkManager.messages.bonding.bondingRetries"
						}'
					>
						<CInput
							v-model.number='bondingRetries'
							type='number'
							min='0'
							max='255'
							:label='$t("iqrfnet.networkManager.bonding.form.bondingRetries")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-if='bondMethod === "smartConnect"'
						v-slot='{ errors, valid }'
						rules='required|scCode'
						:custom-messages='{
							required: "iqrfnet.networkManager.messages.missing.bonding.scCode",
							scCode: "iqrfnet.networkManager.messages.invalid.bonding.scCode"
						}'
					>
						<CInput
							v-model='scCode'
							:label='$t("iqrfnet.networkManager.bonding.form.smartConnect")'
							:is-valid='valid'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<CInputCheckbox
						v-if='bondMethod !== "autoNetwork"'
						:checked.sync='unbondCoordinatorOnly'
						:label='$t("iqrfnet.networkManager.bonding.form.unbondCoordinatorOnly")'
					/>
					<CButton
						v-if='bondMethod !== "autoNetwork"'
						color='primary' 
						type='button' 
						:disabled='invalid' 
						@click.prevent='processSubmitBond'
					>
						{{ $t('forms.bond') }}
					</CButton>
					<CButton
						v-if='bondMethod !== "autoNetwork"'
						color='secondary' 
						type='button' 
						:disabled='invalid || autoAddress'
						@click='modalUnbond = true'
					>
						{{ $t('forms.unbond') }}
					</CButton>
					<CButton
						v-if='bondMethod !== "autoNetwork"'
						color='secondary' 
						type='button'
						:disabled='autoAddress'
						@click='modalClear = true'
					>
						{{ $t('forms.clearBonds') }}
					</CButton>
					<CButton 
						v-if='bondMethod === "autoNetwork"'
						color='primary'
						type='button'
						:disabled='invalid'
						@click='processSubmitAutoNetwork'
					>
						{{ $t('forms.autoNetwork') }}
					</CButton>
					<CModal
						:title='$t("forms.clearBonds")'
						color='danger'
						:show.sync='modalClear'
					>
						{{ $t('iqrfnet.networkManager.messages.submit.removeBond.confirmClear') }}
						<template #footer>
							<CButton color='danger' @click='modalClear = false'>
								{{ $t('forms.no') }}
							</CButton>
							<CButton color='success' @click='processSubmitClearAll'>
								{{ $t('forms.yes') }}
							</CButton>
						</template>
					</CModal>
					<CModal
						:title='$t("forms.unbond")'
						color='danger'
						:show.sync='modalUnbond'
					>
						{{ $t('iqrfnet.networkManager.messages.submit.removeBond.confirmUnbond') }}
						<template #footer>
							<CButton color='danger' @click='modalUnbond = false'>
								{{ $t('forms.no') }}
							</CButton>
							<CButton color='success' @click='processSubmitUnbond'>
								{{ $t('forms.yes') }}
							</CButton>
						</template>
					</CModal>
				</CForm>
			</ValidationObserver>
		</CCardBody>
	</CCard>
</template>

<script>
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput, CInputCheckbox, CModal, CSelect} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {timeout} from '../../helpers/timeout';
import {between, integer, required} from 'vee-validate/dist/rules';
import IqrfNetService from '../../services/IqrfNetService';

export default {
	name: 'BondingManager',
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		CInputCheckbox,
		CModal,
		CSelect,
		ValidationObserver,
		ValidationProvider
	},
	data() {
		return {
			address: 1,
			autoAddress: false,
			autoNetwork: {
				discoveryTxPower: 7,
				discoveryBeforeStart: false,
				skipDiscoveryEachWave: false,
				actionRetries: 1,
				stopConditions: {
					waves: 2,
					emptyWaves: 2,
					numberOfTotalNodes: 239,
					numberOfNewNodes: 239,
					abortOnTooManyNodesFound: false
				}
			},
			overlappingNetworks: {
				networks: 1,
				network: 1
			},
			hwpidFiltering: '',
			bondMethod: 'local',
			bondingRetries: 1,
			modalClear: false,
			modalUnbond: false,
			unbondCoordinatorOnly: false,
			scCode: '',
			timeout: null,
			useHwpidFiltering: false,
			useOverlappingNetworks: false,
		};
	},
	created() {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		extend('scCode', (code) => {
			const regex = RegExp('^[a-zA-Z0-9]{34}$');
			return regex.test(code);
		});
		extend('hwpidFilter', (val) => {
			const regex = RegExp('^(6553[0-5]|655[0-2][0-9]|65[0-4][0-9]{2}|6[0-4][0-9]{3}|[1-5][0-9]{4}|[1-9][0-9]{1,3}|[1-9])( (6553[0-5]|655[0-2][0-9]|65[0-4][0-9]{2}|6[0-4][0-9]{3}|[1-5][0-9]{4}|[1-9][0-9]{1,3}|[1-9]))*$');
			return regex.test(val);
		});
		this.unsubscribe = this.$store.subscribe(mutation => {
			if (mutation.type === 'SOCKET_ONSEND' &&
				mutation.payload.mType === ('iqmeshNetwork_BondNodeLocal' ||
					'iqmeshNetwork_SmartConnect' ||'iqrfEmbedCoordinator_ClearAllBonds' ||
					'iqrfEmbedCoordinator_RemoveBond' || 'iqmeshNetwork_autoNetwork')) {
				this.timeout = timeout('iqrfnet.networkManager.messages.submit.timeout', 30000);
			}
			if (mutation.type === 'SOCKET_ONMESSAGE') {
				if (mutation.payload.mType === 'iqmeshNetwork_BondNodeLocal' ||
					mutation.payload.mType === 'iqmeshNetwork_SmartConnect') {
					clearTimeout(this.timeout);
					this.$store.commit('spinner/HIDE');
					switch(mutation.payload.data.status) {
						case -1:
							this.$toast.error(this.$t('iqrfnet.networkManager.messages.submit.timeout').toString());
							break;
						case 0:
							this.$toast.success(this.$t('iqrfnet.networkManager.messages.submit.bonding.success').toString());
							this.$emit('update-devices');
							break;
						default:
							this.$toast.error(this.$t('iqrfnet.networkManager.messages.submit.bonding.error_fail').toString());
							break;
					}
				} else if (mutation.payload.mType === 'iqrfEmbedCoordinator_ClearAllBonds' ||
							mutation.payload.mType === 'iqmeshNetwork_RemoveBond') {
					clearTimeout(this.timeout);
					this.$store.commit('spinner/HIDE');
					switch(mutation.payload.data.status) {
						case -1:
							this.$toast.error(this.$t('iqrfnet.networkManager.messages.submit.timeout').toString());
							break;
						case 0:
							if (mutation.payload.data.rsp.nodesNr === 0) {
								this.$toast.success(this.$t('iqrfnet.networkManager.messages.submit.removeBond.clearSuccess').toString());
							} else {
								this.$toast.success(this.$t('iqrfnet.networkManager.messages.submit.removeBond.success').toString());
							}
							this.$emit('update-devices');
							break;
						case 1002:
							this.$toast.error(this.$t('iqrfnet.networkManager.messages.submit.removeBond.remove_error').toString());
							break;
						default:
							this.$toast.error(this.$t('iqrfnet.networkManager.messages.submit.removeBond.error_fail').toString());
							break;
					}
				} else if (mutation.payload.mType === 'iqmeshNetwork_AutoNetwork') {
					clearTimeout(this.timeout);
					switch(mutation.payload.data.status) {
						case -1:
							this.$store.commit('spinner/HIDE');
							this.$toast.error(this.$t('iqrfnet.networkManager.messages.submit.timeout').toString());
							break;
						case 0:
							this.$store.commit('spinner/UPDATE_TEXT', this.autoNetworkProgress(mutation.payload.data));
							if (mutation.payload.data.rsp.lastWave) {
								this.$store.commit('spinner/HIDE');
								this.$toast.success(this.$t('iqrfnet.networkManager.messages.submit.autoNetwork.success').toString());
								this.$emit('update-devices');
							}
							break;
						default:
							this.$toast.error(this.$t('iqrfnet.networkManager.messages.submit.autoNetwork.failure').toString());
							break;
					}
				} else if (mutation.payload.mType === 'messageError') {
					clearTimeout(this.timeout);
					this.$store.commit('spinner/HIDE');
					this.$toast.error(this.$t('iqrfnet.networkManager.messages.submit.invalidMessage').toString());
				}
			}
		});
	},
	beforeDestroy() {
		this.unsubscribe();
	},
	methods: {
		autoNetworkProgress(response) {
			return 'Wave ' + response.rsp.wave + '/' + this.autoNetwork.stopConditions.waves + ' [' + response.rsp.progress + '%]';
		},
		processSubmitAutoNetwork() {
			let submitData = this.autoNetwork;
			if (this.useOverlappingNetworks) {
				submitData['overlappingNetworks'] = this.overlappingNetworks;
			}
			if (this.useHwpidFiltering && this.hwpidFiltering.length > 0) {
				submitData['hwpidFiltering'] = this.hwpidFiltering.split(', ').map((i) => parseInt(i));
			}
			this.$store.commit('spinner/SHOW');
			IqrfNetService.autoNetwork(submitData);
		},
		processSubmitBond() {
			this.$store.commit('spinner/SHOW');
			if (this.bondMethod === 'local') {
				if (this.autoAddress) {
					IqrfNetService.bondLocal(0);
				} else {
					IqrfNetService.bondLocal(this.address);
				}
			} else if (this.bondMethod === 'smartConnect') {
				if (this.autoAddress) {
					IqrfNetService.bondSmartConnect(0, this.scCode, this.bondingRetries);
				} else {
					IqrfNetService.bondSmartConnect(this.address, this.scCode, this.bondingRetries);
				}
			}
		},
		processSubmitUnbond() {
			this.modalUnbond = false;
			this.$store.commit('spinner/SHOW');
			IqrfNetService.removeBond(this.address, this.unbondCoordinatorOnly);
		},
		processSubmitClearAll() {
			this.modalClear = false;
			this.$store.commit('spinner/SHOW');
			IqrfNetService.clearAllBonds(this.unbondCoordinatorOnly);
		}
	}
};
</script>
