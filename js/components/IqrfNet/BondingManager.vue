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
							{value: "sc", label: $t("iqrfnet.networkManager.bonding.form.bondMethodSmart")}
						]'
					/>
					<ValidationProvider
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
						:checked.sync='autoAddress'
						:label='$t("iqrfnet.networkManager.bonding.form.autoAddress")'
					/>
					<ValidationProvider
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
						v-slot='{ errors, valid }'
						rules='required|scCode'
						:custom-messages='{
							required: "iqrfnet.networkManager.messages.missing.bonding.scCode",
							scCode: "iqrfnet.networkManager.messages.invalid.bonding.scCode"
						}'
					>
						<CInput
							v-if='bondMethod === "sc"'
							v-model='scCode'
							:label='$t("iqrfnet.networkManager.bonding.form.smartConnect")'
							:is-valid='valid'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<CInputCheckbox
						:checked.sync='unbondCoordinatorOnly'
						:label='$t("iqrfnet.networkManager.bonding.form.unbondCoordinatorOnly")'
					/>
					<CButton 
						color='primary' 
						type='button' 
						:disabled='invalid' 
						@click.prevent='processSubmitBond'
					>
						{{ $t('forms.bond') }}
					</CButton>
					<CButton 
						color='secondary' 
						type='button' 
						:disabled='invalid'
						@click='modalUnbond = true'
					>
						{{ $t('forms.unbond') }}
					</CButton>
					<CButton 
						color='secondary' 
						type='button'
						@click='modalClear = true'
					>
						{{ $t('forms.clearBonds') }}
					</CButton>
					<CModal
						title='Clear All Bonds'
						color='secondary'
						:show.sync='modalClear'
					>
						{{ $t('iqrfnet.networkManager.messages.submit.removeBond.confirmClear') }}
						<template #footer>
							<CButton color='secondary' @click='modalClear = false'>
								Cancel
							</CButton>
							<CButton color='primary' @click='processSubmitClearAll'>
								OK
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
								Cancel
							</CButton>
							<CButton color='primary' @click='processSubmitUnbond'>
								OK
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
		processSubmitBond() {
			this.$store.commit('spinner/SHOW');
			if (this.bondMethod === 'local') {
				if (this.autoAddress) {
					IqmeshNetworkService.bondLocal(0);
				} else {
					IqmeshNetworkService.bondLocal(this.address);
				}
			} else if (this.bondMethod === 'sc') {
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