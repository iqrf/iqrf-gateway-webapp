<template>
	<CCard class='border-top-0 border-left-0 border-right-0'>
		<CCardBody>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm>
					<CSelect
						:value.sync='bondMethod'
						:label='$t("iqrfnet.networkManager.bonding.form.bondMethod")'
						:options='[
							{value: "local", label: $t("iqrfnet.networkManager.bonding.form.bondMethodLocal")},
							{value: "smartConnect", label: $t("iqrfnet.networkManager.bonding.form.bondMethodSmart")},
						]'
					/>
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
						:checked.sync='autoAddress'
						:label='$t("iqrfnet.networkManager.bonding.form.autoAddress")'
					/>
					<ValidationProvider
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
						:disabled='invalid || autoAddress'
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
import {CButton, CCard, CCardBody, CForm, CInput, CInputCheckbox, CModal, CSelect} from '@coreui/vue/src';
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
			scCode: '',
			timeout: null,
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
		this.unsubscribe = this.$store.subscribe(mutation => {
			if (mutation.type === 'SOCKET_ONSEND' &&
				mutation.payload.mType === ('iqmeshNetwork_BondNodeLocal' ||
					'iqmeshNetwork_SmartConnect' ||'iqrfEmbedCoordinator_ClearAllBonds' ||
					'iqrfEmbedCoordinator_RemoveBond')) {
				this.timeout = timeout('iqrfnet.networkManager.messages.submit.timeout', 30000);
			}
			if (mutation.type === 'SOCKET_ONMESSAGE') {
				if (mutation.payload.mType === 'iqmeshNetwork_BondNodeLocal' ||
					mutation.payload.mType === 'iqmeshNetwork_SmartConnect') {
					clearTimeout(this.timeout);
					this.$store.commit('spinner/HIDE');
					switch(mutation.payload.data.status) {
						case -1:
							this.$toast.error(
								this.$t('iqrfnet.networkManager.messages.submit.timeout')
									.toString()
							);
							break;
						case 0:
							this.$toast.success(
								this.$t('iqrfnet.networkManager.messages.submit.bonding.success')
									.toString()
							);
							this.$emit('update-devices');
							break;
						default:
							this.$toast.error(
								this.$t('iqrfnet.networkManager.messages.submit.bonding.error_fail')
									.toString()
							);
							break;
					}
				} else if (mutation.payload.mType === 'iqrfEmbedCoordinator_ClearAllBonds' ||
							mutation.payload.mType === 'iqmeshNetwork_RemoveBond') {
					clearTimeout(this.timeout);
					this.$store.commit('spinner/HIDE');
					switch(mutation.payload.data.status) {
						case -1:
							this.$toast.error(
								this.$t('iqrfnet.networkManager.messages.submit.timeout')
									.toString()
							);
							break;
						case 0:
							if (mutation.payload.data.rsp.nodesNr === 0) {
								this.$toast.success(
									this.$t('iqrfnet.networkManager.messages.submit.removeBond.clearSuccess')
										.toString()
								);
							} else {
								this.$toast.success(
									this.$t('iqrfnet.networkManager.messages.submit.removeBond.success')
										.toString()
								);
							}
							this.$emit('update-devices');
							break;
						case 1002:
							this.$toast.error(
								this.$t('iqrfnet.networkManager.messages.submit.removeBond.remove_error')
									.toString()
							);
							break;
						default:
							this.$toast.error(
								this.$t('iqrfnet.networkManager.messages.submit.removeBond.error_fail')
									.toString()
							);
							break;
					}
				} else if (mutation.payload.mType === 'messageError') {
					clearTimeout(this.timeout);
					this.$store.commit('spinner/HIDE');
					this.$toast.error(
						this.$t('iqrfnet.networkManager.messages.submit.invalidMessage')
							.toString()
					);
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
