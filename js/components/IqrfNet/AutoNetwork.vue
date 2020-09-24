<template>
	<CCard>
		<CCardBody>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm>
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
					<div class='form-group'>
						<CInputCheckbox
							:checked.sync='useWaves'
							:label='$t("iqrfnet.networkManager.autoNetwork.form.waves")'
						/>
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
								v-model.number='stopConditions.waves'
								type='number'
								min='1'
								max='127'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
								:disabled='!useWaves'
							/>
						</ValidationProvider>
					</div>
					<div class='form-group'>
						<CInputCheckbox
							:checked.sync='useEmptyWaves'
							:label='$t("iqrfnet.networkManager.autoNetwork.form.emptyWaves")'
						/>
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
								v-model.number='stopConditions.emptyWaves'
								type='number'
								min='1'
								max='127'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
								:disabled='!useEmptyWaves'
							/>
						</ValidationProvider>
					</div>
					<div class='form-group'>
						<CInputCheckbox
							:checked.sync='useNodes'
							:label='$t("iqrfnet.networkManager.autoNetwork.form.nodes")'
						/>
						<CSelect
							:value.sync='nodeCondition'
							:options='[
								{value: "total", label: "Total"},
								{value: "new", label: "New"}
							]'
							:disabled='!useNodes'
						/>
						<ValidationProvider
							v-if='nodeCondition === "total"'
							v-slot='{ errors, touched, valid }'
							rules='integer|required|between:1,239'
							:custom-messages='{
								integer: "iqrfnet.networkManager.messages.invalid.integer",
								required: "iqrfnet.networkManager.messages.autoNetwork.totalNodes",
								between: "iqrfnet.networkManager.messages.autoNetwork.totalNodes"
							}'
						>
							<CInput
								v-model.number='stopConditions.numberOfTotalNodes'
								type='number'
								min='1'
								max='239'
								:label='$t("iqrfnet.networkManager.autoNetwork.form.numberOfTotalNodes")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
								:disabled='!useNodes'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-if='nodeCondition === "new"'
							v-slot='{ errors, touched, valid }'
							rules='integer|required|between:1,239'
							:custom-messages='{
								integer: "iqrfnet.networkManager.messages.invalid.integer",
								required: "iqrfnet.networkManager.messages.autoNetwork.newNodes",
								between: "iqrfnet.networkManager.messages.autoNetwork.newNodes"
							}'
						>
							<CInput
								v-model.number='stopConditions.numberOfNewNodes'
								type='number'
								min='1'
								max='239'
								:label='$t("iqrfnet.networkManager.autoNetwork.form.numberOfNewNodes")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
								:disabled='!useNodes'
							/>
						</ValidationProvider>
					</div>
					<CInputCheckbox
						:checked.sync='stopConditions.abortOnTooManyNodesFound'
						:label='$t("iqrfnet.networkManager.autoNetwork.form.abortOnTooManyNodesFound")'
						:disabled='!useNodes'
					/>
					<CButton 
						color='primary'
						type='button'
						:disabled='invalid'
						@click='processSubmitAutoNetwork'
					>
						{{ $t('forms.start') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
	</CCard>
</template>

<script>
import {CButton, CCard, CCardBody, CForm, CInput, CInputCheckbox} from '@coreui/vue/src';
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
		ValidationObserver,
		ValidationProvider
	},
	data() {
		return {
			autoAddress: false,
			autoNetwork: {
				discoveryTxPower: 7,
				discoveryBeforeStart: false,
				skipDiscoveryEachWave: false,
				actionRetries: 1,
			},
			stopConditions: {
				waves: 10,
				emptyWaves: 2,
				numberOfTotalNodes: 1,
				numberOfNewNodes: 1,
				abortOnTooManyNodesFound: false,
			},
			overlappingNetworks: {
				networks: 1,
				network: 1
			},
			hwpidFiltering: '',
			timeout: null,
			useHwpidFiltering: false,
			useOverlappingNetworks: false,
			useEmptyWaves: true,
			useWaves: false,
			useNodes: true,
			nodeCondition: 'total',
		};
	},
	created() {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		extend('hwpidFilter', (val) => {
			const regex = RegExp('^(6553[0-5]|655[0-2][0-9]|65[0-4][0-9]{2}|6[0-4][0-9]{3}|[1-5][0-9]{4}|[1-9][0-9]{1,3}|[1-9])( (6553[0-5]|655[0-2][0-9]|65[0-4][0-9]{2}|6[0-4][0-9]{3}|[1-5][0-9]{4}|[1-9][0-9]{1,3}|[1-9]))*$');
			return regex.test(val);
		});
		this.unsubscribe = this.$store.subscribe(mutation => {
			if (mutation.type === 'SOCKET_ONSEND' &&
				mutation.payload.mType === 'iqmeshNetwork_autoNetwork') {
				this.timeout = timeout('iqrfnet.networkManager.messages.submit.timeout', 30000);
			}
			if (mutation.type === 'SOCKET_ONMESSAGE') {
				if (mutation.payload.mType === 'iqmeshNetwork_AutoNetwork') {
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
			if (this.autoNetwork.stopConditions.waves) {
				return 'Wave ' + response.rsp.wave + '/' + this.autoNetwork.stopConditions.waves + ' [' + response.rsp.progress + '%]';
			} else {
				return 'Wave ' + response.rsp.wave + ' [' + response.rsp.progress + '%]';
			}
		},
		processSubmitAutoNetwork() {
			let submitData = this.autoNetwork;
			let stopConditions = {};
			if (this.useEmptyWaves) {
				stopConditions['emptyWaves'] = this.stopConditions.emptyWaves;
			}
			if (this.useWaves) {
				stopConditions['waves'] = this.stopConditions.waves;
			}
			if (this.useNodes) {
				stopConditions['abortOnTooManyNodesFound'] = this.stopConditions.abortOnTooManyNodesFound;
				if (this.nodeCondition === 'total') {
					stopConditions['numberOfTotalNodes'] = this.stopConditions.numberOfTotalNodes;
				} else {
					stopConditions['numberOfNewNodes'] = this.stopConditions.numberOfNewNodes;
				}
			}
			if (Object.keys(stopConditions).length > 0) {
				submitData['stopConditions'] = stopConditions;
			}
			if (this.useOverlappingNetworks) {
				submitData['overlappingNetworks'] = this.overlappingNetworks;
			}
			if (this.useHwpidFiltering && this.hwpidFiltering.length > 0) {
				submitData['hwpidFiltering'] = this.hwpidFiltering.split(', ').map((i) => parseInt(i));
			}
			this.$store.commit('spinner/SHOW');
			IqrfNetService.autoNetwork(submitData);
		}
	}
};
</script>
