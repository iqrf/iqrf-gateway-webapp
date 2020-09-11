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
						<CInput
							v-model='autoNetwork.discoveryTxPower'
							type='number'
							min='0'
							max='7'
							:label='$t("iqrfnet.networkManager.autoNetwork.form.discoveryTxPower")'
						/>
						<CInputCheckbox
							:checked.sync='autoNetwork.discoveryBeforeStart'
							:label='$t("iqrfnet.networkManager.autoNetwork.form.discoveryBeforeStart")'
						/>
						<CInputCheckbox
							:checked.sync='autoNetwork.skipDiscoveryEachWave'
							:label='$t("iqrfnet.networkManager.autoNetwork.form.skipDiscoveryEachWave")'
						/><hr>
						<CInput
							v-model='autoNetwork.actionRetries'
							type='number'
							min='0'
							:label='$t("iqrfnet.networkManager.autoNetwork.form.actionRetries")'
						/><hr>
						<h4>{{ $t('iqrfnet.networkManager.autoNetwork.form.overlappingNetworks') }}</h4>
						<CInput
							v-model='autoNetwork.overlappingNetworks.networks'
							type='number'
							min='1'
							:label='$t("iqrfnet.networkManager.autoNetwork.form.networks")'
						/>
						<CInput
							v-model='autoNetwork.overlappingNetworks.network'
							type='number'
							min='1'
							:label='$t("iqrfnet.networkManager.autoNetwork.form.network")'
						/><hr>
						<CInput
							v-model='autoNetwork.hwpidFiltering'
							:label='$t("iqrfnet.networkManager.autoNetwork.form.hwpidFiltering")'
						/><hr>
						<h4>{{ $t('iqrfnet.networkManager.autoNetwork.form.stopConditions') }}</h4>
						<CInput
							v-model='autoNetwork.stopConditions.waves'
							type='number'
							min='1'
							:label='$t("iqrfnet.networkManager.autoNetwork.form.waves")'
						/>
						<CInput
							v-model='autoNetwork.stopConditions.emptyWaves'
							type='number'
							min='1'
							:label='$t("iqrfnet.networkManager.autoNetwork.form.emptyWaves")'
						/>
						<CInput
							v-model='autoNetwork.stopConditions.numberOfTotalNodes'
							type='number'
							:label='$t("iqrfnet.networkManager.autoNetwork.form.numberOfTotalNodes")'
						/>
						<CInput
							v-model='autoNetwork.stopConditions.numberOfNewNodes'
							type='number'
							:label='$t("iqrfnet.networkManager.autoNetwork.form.numberOfNewNodes")'
						/>
						<CInputCheckbox
							:checked.sync='autoNetwork.stopConditions.abortOnTooManyNodesFound'
							:label='$t("iqrfnet.networkManager.autoNetwork.form.abortOnTooManyNodesFound")'
						/><hr>
						<CInputCheckbox
							:checked.sync='autoNetwork.returnVerbose'
							:label='$t("iqrfnet.networkManager.autoNetwork.form.verbose")'
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
							v-model='address'
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
						rules='integer|required'
						:custom-mesasges='{
							integer: "iqrfnet.networkManager.messages.invalid.bonding.bondingRetries",
							required: "iqrfnet.networkManager.messages.missing.bonding.bondingRetries"
						}'
					>
						<CInput
							v-model='bondingRetries'
							type='number'
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
								{{ $t('iqrfnet.networkManager.forms.cancel') }}
							</CButton>
							<CButton color='primary' @click='processSubmitClearAll'>
								{{ $t('iqrfnet.networkManager.forms.ok') }}
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
					numberOfTotalNodes: 0,
					numberOfNewNodes: 0,
					abortOnTooManyNodesFound: false
				},
				returnVerbose: true
			},
			bondMethod: 'local',
			bondingRetries: 1,
			modalClear: false,
			modalUnbond: false,
			unbondCoordinatorOnly: false,
			responseReceived: false,
			scCode: ''
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
		this.unsubscribe = this.$store.subscribe(mutation => {
			if (mutation.type === 'SOCKET_ONSEND' &&
				(mutation.payload.mType === 'iqmeshNetwork_BondNodeLocal' ||
					mutation.payload.mType === 'iqmeshNetwork_SmartConnect' ||
					mutation.payload.mType === 'iqrfEmbedCoordinator_ClearAllBonds' ||
					mutation.payload.mType === 'iqrfEmbedCoordinator_RemoveBond')) {
				this.responseReceived = false;
				setTimeout(() => {this.timedOut();}, 10000);
			}
			if (mutation.type === 'SOCKET_ONMESSAGE') {
				if (mutation.payload.mType === 'iqmeshNetwork_BondNodeLocal' ||
					mutation.payload.mType === 'iqmeshNetwork_SmartConnect') {
					this.responseReceived = true;
					this.$store.commit('spinner/HIDE');
					switch(mutation.payload.data.status) {
						case -1:
							this.$toast.error(this.$t('iqrfnet.networkManager.messages.submit.timeout'));
							break;
						case 0:
							this.$toast.success(this.$t('iqrfnet.networkManager.messages.submit.bonding.success'));
							break;
						default:
							this.$toast.error(this.$t('iqrfnet.networkManager.messages.submit.bonding.error_fail'));
							break;
					}
				} else if (mutation.payload.mType === 'iqrfEmbedCoordinator_ClearAllBonds' ||
							mutation.payload.mType === 'iqmeshNetwork_RemoveBond') {
					this.responseReceived = true;
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
							break;
						case 1002:
							this.$toast.error(this.$t('iqrfnet.networkManager.messages.submit.removeBond.remove_error'));
							break;
						default:
							this.$toast.error(this.$t('iqrfnet.networkManager.messages.submit.removeBond.error_fail'));
							break;
					}
				}
			}
		});
	},
	beforeDestroy() {
		this.unsubscribe();
	},
	methods: {
		processSubmitAutoNetwork() {
			//
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
			if (!this.responseReceived) {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(this.$t('iqrfnet.networkManager.messages.submit.timeout'));
			}
		}
	}
};
</script>

<style>

</style>