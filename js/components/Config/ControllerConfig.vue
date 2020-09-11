<template>
	<div>
		<CCard body-wrapper>
			<ValidationObserver v-if='config !== null' v-slot='{ invalid }'>
				<CForm @submit.prevent='processSubmit'>
					<CRow>
						<CCol md='6'>
							<h3>{{ $t("controllerConfig.form.wsServers.title") }}</h3>
							<ValidationProvider
								v-slot='{ errors, touched, valid }'
								rules='required|ws_addr'
								:custom-messages='{
									required: "controllerConfig.form.messages.missing.ws_api",
									ws_addr: "controllerConfig.form.messages.invalid.ws_format"
								}'
							>
								<CInput
									v-model='config.wsServers.api'
									:label='$t("controllerConfig.form.wsServers.api")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{ errors, touched, valid }'
								rules='required|ws_addr'
								:custom-messages='{
									required: "controllerConfig.form.messages.missing.ws_monitor",
									ws_addr: "controllerConfig.form.messages.invalid.ws_format"
								}'
							>
								<CInput
									v-model='config.wsServers.monitor'
									:label='$t("controllerConfig.form.wsServers.monitor")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
						</CCol>
						<CCol md='6'>
							<h3>{{ $t("controllerConfig.form.logger.title") }}</h3>
							<ValidationProvider
								v-slot='{ errors, touched, valid }'
								rules='required'
								:custom-messages='{required: "controllerConfig.form.messages.missing.l_file"}'
							>
								<CInput
									v-model='config.logger.filePath'
									:label='$t("controllerConfig.form.logger.filePath")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<CSelect 
								:value.sync='config.logger.severity'
								:options='[
									{value: "trace", label: "Trace"},
									{value: "debug", label: "Debug"},
									{value: "info", label: "Info"},
									{value: "warning", label: "Warning"},
									{value: "error", label: "Error"}
								]'
								:label='$t("controllerConfig.form.logger.severity")'
							/>
						</CCol>
					</CRow><hr>
					<CRow>
						<CCol md='6'>
							<h3>{{ $t("controllerConfig.form.resetButton.title") }}</h3>
							<CInputCheckbox 
								:checked.sync='apiCallSetCustom'
								:label='$t("controllerConfig.form.resetButton.setCustom")'
								@change='updateApiCall'
							/>
							<CSelect 
								v-if='!apiCallSetCustom'
								:value.sync='config.resetButton.api'
								:options='[
									{value: "autoNetwork", label: "AutoNetwork"},
									{value: "discovery", label: "Discovery"},
								]'
								:label='$t("controllerConfig.form.resetButton.api")'
							/>
							<ValidationProvider 
								v-else
								v-slot='{ errors, touched, valid }'
								rules='required'
								:custom-messages='{required: "controllerConfig.form.messages.missing.rb_custom"}'
							>
								<CInput 
									v-model='config.resetButton.api'
									:label='$t("controllerConfig.form.resetButton.custom")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{ errors, touched, valid }'
								rules='integer|required'
								:custom-messages='{
									integer: "controllerConfig.form.messages.invalid.rb_pin",
									required: "controllerConfig.form.messages.missing.rb_pin"
								}'
							>
								<CInput
									v-model='config.resetButton.button'
									type='number'
									min='0'
									:label='$t("controllerConfig.form.resetButton.pin")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
						</CCol>
						<CCol md='6'>
							<h3>{{ $t("controllerConfig.form.statusLed.title") }}</h3>
							<ValidationProvider
								v-slot='{ errors, touched, valid }'
								rules='integer|required'
								:custom-messages='{
									integer: "controllerConfig.form.messages.invalid.gpio",
									required: "controllerConfig.form.messages.missing.sl_green"
								}'
							>
								<CInput
									v-model='config.statusLed.greenLed'
									type='number'
									min='0'
									:label='$t("controllerConfig.form.statusLed.green")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{ errors, touched, valid }'
								rules='integer|required'
								:custom-messages='{
									integer: "controllerConfig.form.messages.invalid.gpio",
									required: "controllerConfig.form.messages.missing.sl_red"
								}'
							>
								<CInput
									v-model='config.statusLed.redLed'
									type='number'
									min='0'
									:label='$t("controllerConfig.form.statusLed.red")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
						</CCol>
					</CRow><hr>
					<CRow>
						<CCol md='6'>
							<h3>{{ $t("controllerConfig.form.daemonApi.autoNetwork.title") }}</h3>
							<ValidationProvider
								v-slot='{ errors, touched, valid }'
								rules='integer|required|actionRetries'
								:custom-messages='{
									integer: "controllerConfig.form.messages.invalid.integer",
									required: "controllerConfig.form.messages.missing.da_retries",
									actionRetries: "controllerConfig.form.messages.invalid.actionRetries"
								}'
							>
								<CInput
									v-model='config.daemonApi.autoNetwork.actionRetries'
									type='number'
									min='0'
									max='3'
									:label='$t("controllerConfig.form.daemonApi.autoNetwork.actionRetries")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<CInputCheckbox
								:checked.sync='config.daemonApi.autoNetwork.discoveryBeforeStart'
								:label='$t("controllerConfig.form.daemonApi.autoNetwork.discoveryBeforeStart")'
							/>
							<ValidationProvider
								v-slot='{ errors, touched, valid }'
								rules='integer|required|txpower'
								:custom-messages='{
									integer: "controllerConfig.form.messages.invalid.integer",
									required: "controllerConfig.form.messages.missing.da_txpower",
									txpower: "controllerConfig.form.messages.invalid.da_txpower"
								}'
							>
								<CInput
									v-model='config.daemonApi.autoNetwork.discoveryTxPower'
									type='number'
									min='0'
									max='7'
									:label='$t("controllerConfig.form.daemonApi.autoNetwork.discoveryTxPower")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<CInputCheckbox
								:checked.sync='config.daemonApi.autoNetwork.skipDiscoveryEachWave'
								:label='$t("controllerConfig.form.daemonApi.autoNetwork.skipDiscoveryEachWave")'
							/><hr>
							<h4>{{ $t("controllerConfig.form.daemonApi.autoNetwork.stopConditions.title") }}</h4>
							<CInputCheckbox
								:checked.sync='config.daemonApi.autoNetwork.stopConditions.abortOnTooManyNodesFound'
								:label='$t("controllerConfig.form.daemonApi.autoNetwork.stopConditions.abortOnTooManyNodesFound")'
							/>
							<ValidationProvider
								v-slot='{ errors, touched, valid }'
								rules='integer|required|waves'
								:custom-messages='{
									integer: "controllerConfig.form.messages.invalid.integer",
									required: "controllerConfig.form.messages.missing.da_ewaves",
									waves: "controllerConfig.form.messages.invalid.da_ewaves"
								}'
							>
								<CInput
									v-model='config.daemonApi.autoNetwork.stopConditions.emptyWaves'
									type='number'
									min='1'
									max='127'
									:label='$t("controllerConfig.form.daemonApi.autoNetwork.stopConditions.emptyWaves")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{ errors, touched, valid }'
								rules='integer|required|waves'
								:custom-messages='{
									integer: "controllerConfig.form.messages.invalid.integer",
									required: "controllerConfig.form.messages.missing.da_waves",
									waves: "controllerConfig.form.messages.invalid.da_ewaves"
								}'
							>
								<CInput
									v-model='config.daemonApi.autoNetwork.stopConditions.waves'
									type='number'
									min='1'
									max='127'
									:label='$t("controllerConfig.form.daemonApi.autoNetwork.stopConditions.waves")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider><hr>
							<CInputCheckbox
								:checked.sync='config.daemonApi.autoNetwork.returnVerbose'
								:label='$t("controllerConfig.form.daemonApi.autoNetwork.verbose")'
							/>
						</CCol>
						<CCol md='6'>
							<h3>{{ $t("controllerConfig.form.daemonApi.discovery.title") }}</h3>
							<ValidationProvider
								v-slot='{ errors, touched, valid }'
								rules='addr_range|integer|required'
								:custom-messages='{
									addr_range: "controllerConfig.form.messages.invalid.dd_addr",
									integer: "controllerConfig.form.messages.invalid.integer",
									required: "controllerConfig.form.messages.missing.dd_addr"
								}'
							>
								<CInput
									v-model='config.daemonApi.discovery.maxAddr'
									type='number'
									min='0'
									max='239'
									:label='$t("controllerConfig.form.daemonApi.discovery.maxAddr")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{ errors, touched, valid }'
								rules='integer|required|txpower'
								:custom-messages='{
									integer: "controllerConfig.form.messages.invalid.integer",
									required: "controllerConfig.form.messages.missing.dd_txpower",
									invalid: "controllerConfig.form.messages.missing.da_txpower"
								}'
							>
								<CInput
									v-model='config.daemonApi.discovery.txPower'
									type='number'
									min='0'
									max='7'
									:label='$t("controllerConfig.form.daemonApi.discovery.txPower")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<CInputCheckbox
								:checked.sync='config.daemonApi.discovery.returnVerbose'
								:label='$t("controllerConfig.form.daemonApi.discovery.verbose")'
							/>
						</CCol>
					</CRow><hr>
					<CRow>
						<CCol md='6'>
							<h3>{{ $t("controllerConfig.form.factoryReset.title") }}</h3>
							<CInputCheckbox
								:checked.sync='config.factoryReset.coordinator'
								:label='$t("controllerConfig.form.factoryReset.coordinator")'
							/>
							<CInputCheckbox
								:checked.sync='config.factoryReset.daemon'
								:label='$t("controllerConfig.form.factoryReset.daemon")'
							/>
							<CInputCheckbox
								:checked.sync='config.factoryReset.network'
								:label='$t("controllerConfig.form.factoryReset.network")'
							/>
							<CInputCheckbox
								:checked.sync='config.factoryReset.webapp'
								:label='$t("controllerConfig.form.factoryReset.webapp")'
							/>
						</CCol>
					</CRow>
					<CButton color='primary' type='submit' :disabled='invalid'>
						{{ $t('forms.save') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCard>
	</div>
</template>

<script>
import {CButton, CCard , CForm, CInput, CInputCheckbox, CSelect} from '@coreui/vue';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {integer, required} from 'vee-validate/dist/rules';
import ConfigService from '../../services/ConfigService';

export default {
	name: 'ControllerConfig',
	components: {
		CButton,
		CCard,
		CForm,
		CInput,
		CInputCheckbox,
		CSelect,
		ValidationObserver,
		ValidationProvider
	},
	data() {
		return {
			apiCallCustom: null,
			apiCallSetCustom: false,
			config: null,
			previousApiCall: null
		};
	},
	created() {
		extend('integer', integer);
		extend('required', required);
		extend('addr_range', (addr) => {
			return ((addr >= 0) && (addr <= 239));
		});
		extend('actionRetries', (val) => {
			return ((val >= 0) && (val<=3));
		});
		extend('txpower', (val) => {
			return ((val >= 0) && (val<=7));
		});
		extend('waves', (val) => {
			return ((val >= 1) && (val<=127));
		});
		extend('ws_addr', (addr) => {
			const regex = RegExp('^ws:\\/\\/.+:([1-9]|[1-9][0-9]|[1-9][0-9]{2}|[1-9][0-9]{3}|[1-4][0-9][0-1][0-5][0-1])$');
			return regex.test(addr);
		});
		this.getConfig();
	},
	methods: {
		getConfig() {
			this.$store.commit('spinner/SHOW');
			ConfigService.getConfig('controllerConfig')
				.then((response) => {
					this.$store.commit('spinner/HIDE');
					this.config = response.data;
					if (this.config.resetButton.api !== ('autoNetwork' && 'discovery')) {
						this.apiCallCustom = this.config.resetButton.api;
						this.apiCallSetCustom = true;
						this.previousApiCall = 'autoNetwork';
					}
				})
				.catch((error) => {
					this.$store.commit('spinner/HIDE');
					if (error.response) {
						if (error.response.status === 500) {
							this.$toast.error(this.$t('forms.messages.submitServerError'));
						}
					}
				});
		},
		processSubmit() {
			this.$store.commit('spinner/SHOW');
			ConfigService.saveConfig('controllerConfig', this.config)
				.then((response) => {
					this.$store.commit('spinner/HIDE');
					if (response.status === 200) {
						this.$toast.success(this.$t('forms.messages.saveSuccess'));
					}
				})
				.catch((error) => {
					this.$store.commit('spinner/HIDE');
					if (error.response) {
						if (error.response.status === 500) {
							this.$toast.error(this.$t('forms.messages.submitServerError'));
						}
					} else {
						console.error(error.message);
					}
				});
		},
		updateApiCall() {
			if (this.apiCallSetCustom) {
				this.previousApiCall = this.config.resetButton.api;
				this.config.resetButton.api = this.apiCallCustom;
			} else {
				this.config.resetButton.api = this.previousApiCall;
			}
		}
	}
};
</script>

<style>

</style>