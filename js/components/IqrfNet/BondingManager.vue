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
							{value: "autoNetwork", label: $t("iqrfnet.networkManager.autoNetwork.title")},
							{value: "local", label: $t("iqrfnet.networkManager.bonding.form.bondMethodLocal")},
							{value: "smartConnect", label: $t("iqrfnet.networkManager.bonding.form.bondMethodSmart")}
						]'
					/>
					<span v-if='bondMethod === "autoNetwork"'>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='integer|required|txpower'
							:custom-messages='{
								integer: "iqrfnet.networkManager.messages.invalid.integer",
								required: "iqrfnet.networkManager.messages.missing.discovery.txPower",
								txpower: "iqrfnet.networkManager.messages.invalid.discovery.txPower"
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
							rules='integer|required|actionRetries'
							:custom-messages='{
								integer: "iqrfnet.networkManager.messages.invalid.integer",
								required: "iqrfnet.networkManager.messages.missing.autoNetwork.actionRetries",
								actionRetries: "iqrfnet.networkManager.messages.invalid.autoNetwork.actionRetries"
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
						<h4>{{ $t('iqrfnet.networkManager.autoNetwork.form.overlappingNetworks') }}</h4>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='integer|required|networks'
							:custom-messages='{
								integer: "iqrfnet.networkManager.messages.invalid.integer",
								required: "iqrfnet.networkManager.messages.missing.autoNetwork.networks",
								networks: "iqrfnet.networkManager.messages.invalid.autoNetwork.networks"
							}'
						>
							<CInput
								v-model.number='autoNetwork.overlappingNetworks.networks'
								type='number'
								min='1'
								max='50'
								:label='$t("iqrfnet.networkManager.autoNetwork.form.networks")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='integer|required|networks'
							:custom-messages='{
								integer: "iqrfnet.networkManager.messages.invalid.integer",
								required: "iqrfnet.networkManager.messages.missing.autoNetwork.network",
								networks: "iqrfnet.networkManager.messages.invalid.autoNetwork.network"
							}'
						>
							<CInput
								v-model.number='autoNetwork.overlappingNetworks.network'
								type='number'
								min='1'
								max='50'
								:label='$t("iqrfnet.networkManager.autoNetwork.form.network")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider><hr>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='hwpidFilter'
							:custom-messages='{
								hwpidFilter: "iqrfnet.networkManager.messages.invalid.autoNetwork.hwpidFilter"
							}'
						>
							<CInput
								:label='$t("iqrfnet.networkManager.autoNetwork.form.hwpidFiltering")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider><hr>
						<h4>{{ $t('iqrfnet.networkManager.autoNetwork.form.stopConditions') }}</h4>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='integer|required|waves'
							:custom-messages='{
								integer: "iqrfnet.networkManager.messages.invalid.integer",
								required: "iqrfnet.networkManager.messages.missing.autoNetwork.waves",
								waves: "iqrfnet.networkManager.messages.invalid.autoNetwork.waves"
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
							rules='integer|required|waves'
							:custom-messages='{
								integer: "iqrfnet.networkManager.messages.invalid.integer",
								required: "iqrfnet.networkManager.messages.missing.autoNetwork.emptyWaves",
								waves: "iqrfnet.networkManager.messages.invalid.autoNetwork.emptyWaves"
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
							rules='integer|required|addr_range'
							:custom-messages='{
								integer: "iqrfnet.networkManager.messages.invalid.integer",
								required: "iqrfnet.networkManager.messages.missing.autoNetwork.totalNodes",
								addr_range: "iqrfnet.networkManager.messages.invalid.autoNetwork.totalNodes"
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
							rules='integer|required|addr_range'
							:custom-messages='{
								integer: "iqrfnet.networkManager.messages.invalid.integer",
								required: "iqrfnet.networkManager.messages.missing.autoNetwork.newNodes",
								addr_range: "iqrfnet.networkManager.messages.invalid.autoNetwork.newNodes"
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
						rules='integer|required|addr_range'
						:custom-messages='{
							required: "iqrfnet.networkManager.messages.missing.bonding.maxAddr",
							integer: "iqrfnet.networkManager.messages.invalid.bonding.maxAddr",
							addr_range: "iqrfnet.networkManager.messages.invalid.bonding.maxAddr"
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
						rules='integer|required|testRetries'
						:custom-mesasges='{
							integer: "iqrfnet.networkManager.messages.invalid.bonding.bondingRetries",
							required: "iqrfnet.networkManager.messages.missing.bonding.bondingRetries"
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
						title='Clear All Bonds'
						color='secondary'
						:show.sync='modalClear'
					>
						{{ $t('iqrfnet.networkManager.messages.submit.removeBond.confirmClear') }}
						<template #footer>
							<CButton color='secondary' @click='modalClear = false'>
								{{ $t('forms.cancel') }}
							</CButton>
							<CButton color='primary' @click='processSubmitClearAll'>
								{{ $t('forms.ok') }}
							</CButton>
						</template>
					</CModal>
					<CModal
						title='Unbond Node'
						color='secondary'
						:show.sync='modalUnbond'
					>
						{{ $t('iqrfnet.networkManager.messages.submit.removeBond.confirmUnbond') }}
						<template #footer>
							<CButton color='secondary' @click='modalUnbond = false'>
								{{ $t('forms.cancel') }}
							</CButton>
							<CButton color='primary' @click='processSubmitUnbond'>
								{{ $t('forms.ok') }}
							</CButton>
						</template>
					</CModal>
				</CForm>
			</ValidationObserver>
		</CCardBody>
	</CCard>
</template>

<script>
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput, CInputCheckbox, CModal, CSelect} from '@coreui/vue';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {integer, required} from 'vee-validate/dist/rules';
import IqmeshNetworkService from '../../services/IqmeshNetworkService';

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
				overlappingNetworks: {
					networks: 1,
					network: 1
				},
				hwpidFiltering: [],
				stopConditions: {
					waves: 2,
					emptyWaves: 2,
					numberOfTotalNodes: 239,
					numberOfNewNodes: 239,
					abortOnTooManyNodesFound: false
				}
			},
			bondMethod: 'local',
			bondingRetries: 1,
			modalClear: false,
			modalUnbond: false,
			unbondCoordinatorOnly: false,
			scCode: '',
			timeoutVar: null
		};
	},
	created() {
		extend('integer', integer);
		extend('required', required);
		extend('addr_range', (addr) => {
			return ((addr >= 1) && (addr <= 239));
		});
		extend('scCode', (code) => {
			const regex = RegExp('^[a-zA-Z0-9]{34}$');
			return regex.test(code);
		});
		extend('testRetries', (val) => {
			return ((val>=0) && (val<=255));
		});
		extend('txpower', (val) => {
			return ((val>=0) && (val<=7));
		});
		extend('actionRetries', (val) => {
			return ((val>=0) && (val<=3));
		});
		extend('waves', (val) => {
			return ((val>=1) && (val<=127));
		});
		extend('networks', (val) => {
			return ((val>=1) && (val<=50));
		});
		extend('hwpidFilter', (val) => {
			const regex = RegExp('^[a-zA-Z0-9]{4}(,[a-zA-Z0-9]{4})*$');
			return regex.test(val);
		});
		this.unsubscribe = this.$store.subscribe(mutation => {
			if (mutation.type === 'SOCKET_ONSEND' &&
				mutation.payload.mType === ('iqmeshNetwork_BondNodeLocal' ||
					'iqmeshNetwork_SmartConnect' ||'iqrfEmbedCoordinator_ClearAllBonds' ||
					'iqrfEmbedCoordinator_RemoveBond' || 'iqmeshNetwork_autoNetwork')) {
				this.timeoutVar = setTimeout(() => {this.timedOut();}, 20000);
			}
			if (mutation.type === 'SOCKET_ONMESSAGE') {
				if (mutation.payload.mType === 'iqmeshNetwork_BondNodeLocal' ||
					mutation.payload.mType === 'iqmeshNetwork_SmartConnect') {
					clearTimeout(this.timeoutVar);
					this.$store.commit('spinner/HIDE');
					switch(mutation.payload.data.status) {
						case -1:
							this.$toast.error(this.$t('iqrfnet.networkManager.messages.submit.timeout'));
							break;
						case 0:
							this.$toast.success(this.$t('iqrfnet.networkManager.messages.submit.bonding.success'));
							this.$emit('update-devices');
							break;
						default:
							this.$toast.error(this.$t('iqrfnet.networkManager.messages.submit.bonding.error_fail'));
							break;
					}
				} else if (mutation.payload.mType === 'iqrfEmbedCoordinator_ClearAllBonds' ||
							mutation.payload.mType === 'iqmeshNetwork_RemoveBond') {
					clearTimeout(this.timeoutVar);
					this.$store.commit('spinner/HIDE');
					switch(mutation.payload.data.status) {
						case -1:
							this.$toast.error(this.$t('iqrfnet.networkManager.messages.submit.timeout'));
							break;
						case 0:
							if (mutation.payload.data.rsp.nodesNr === 0) {
								this.$toast.success(this.$t('iqrfnet.networkManager.messages.submit.removeBond.clearSuccess'));
							} else {
								this.$toast.success(this.$t('iqrfnet.networkManager.messages.submit.removeBond.success'));
							}
							this.$emit('update-devices');
							break;
						case 1002:
							this.$toast.error(this.$t('iqrfnet.networkManager.messages.submit.removeBond.remove_error'));
							break;
						default:
							this.$toast.error(this.$t('iqrfnet.networkManager.messages.submit.removeBond.error_fail'));
							break;
					}
				} else if (mutation.payload.mType === 'iqmeshNetwork_AutoNetwork') {
					clearTimeout(this.timeoutVar);
					switch(mutation.payload.data.status) {
						case -1:
							this.$store.commit('spinner/HIDE');
							this.$toast.error(this.$t('iqrfnet.networkManager.messages.submit.timeout'));
							break;
						case 0:
							if (mutation.payload.data.rsp.lastWave) {
								this.$store.commit('spinner/HIDE');
								this.$toast.success(this.$t('iqrfnet.networkManager.messages.submit.autoNetwork.success'));
								this.$emit('update-devices');
							}
							break;
						default:
							this.$toast.error(this.$t('iqrfnet.networkManager.messages.submit.autoNetwork.failure'));
							break;
					}
				} else if (mutation.payload.mType === 'messageError') {
					clearTimeout(this.timeoutVar);
					this.$store.commit('spinner/HIDE');
					this.$toast.error(this.$t('iqrfnet.networkManager.messages.submit.invalidMessage'));
				}
			}
		});
	},
	beforeDestroy() {
		this.unsubscribe();
	},
	methods: {
		processSubmitAutoNetwork() {
			this.$store.commit('spinner/SHOW');
			IqmeshNetworkService.autoNetwork(this.autoNetwork);
		},
		processSubmitBond() {
			this.$store.commit('spinner/SHOW');
			if (this.bondMethod === 'local') {
				if (this.autoAddress) {
					IqmeshNetworkService.bondLocal(0);
				} else {
					IqmeshNetworkService.bondLocal(this.address);
				}
			} else if (this.bondMethod === 'smartConnect') {
				if (this.autoAddress) {
					IqmeshNetworkService.bondSmartConnect(0, this.scCode, this.bondingRetries);
				} else {
					IqmeshNetworkService.bondSmartConnect(this.address, this.scCode, this.bondingRetries);
				}
			}
		},
		processSubmitUnbond() {
			this.modalUnbond = false;
			this.$store.commit('spinner/SHOW');
			IqmeshNetworkService.removeBond(this.address, this.unbondCoordinatorOnly);
		},
		processSubmitClearAll() {
			this.modalClear = false;
			this.$store.commit('spinner/SHOW');
			IqmeshNetworkService.clearAllBonds(this.unbondCoordinatorOnly);
		},
		timedOut() {
			this.$store.commit('spinner/HIDE');
			this.$toast.error(this.$t('iqrfnet.networkManager.messages.submit.timeout'));
		}
	}
};
</script>

<style>

</style>