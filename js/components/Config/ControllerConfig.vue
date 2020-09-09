<template>
	<div>
		<CCard body-wrapper>
			<ValidationObserver v-if='config !== null' v-slot='{ invalid }'>
				<CForm @submit.prevent='processSubmit'>
					<CRow>
						<CCol md='6'>
							<h3>{{ $t("controllerConfig.form.wsServers.title") }}</h3>
							<ValidationProvider
								v-slot='{ valid, errors }'
								rules='required|ws_addr'
								:custom-messages='{
									required: "controllerConfig.form.messages.missing.ws_api",
									ws_addr: "controllerConfig.form.messages.invalid.ws_format"
								}'
							>
								<CInput
									v-model='config.wsServers.api'
									:label='$t("controllerConfig.form.wsServers.api")'
									:is-valid='valid'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{ valid, errors }'
								rules='required|ws_addr'
								:custom-messages='{
									required: "controllerConfig.form.messages.missing.ws_monitor",
									ws_addr: "controllerConfig.form.messages.invalid.ws_format"
								}'
							>
								<CInput
									v-model='config.wsServers.monitor'
									:label='$t("controllerConfig.form.wsServers.monitor")'
									:is-valid='valid'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
						</CCol>
						<CCol md='6'>
							<h3>{{ $t("controllerConfig.form.logger.title") }}</h3>
							<ValidationProvider
								v-slot='{ valid, errors }'
								rules='required'
								:custom-messages='{required: "controllerConfig.form.messages.missing.l_file"}'
							>
								<CInput
									v-model='config.logger.filePath'
									:label='$t("controllerConfig.form.logger.filePath")'
									:is-valid='valid'
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
								v-slot='{ valid, errors }'
								rules='required'
								:custom-messages='{required: "controllerConfig.form.messages.missing.rb_custom"}'
							>
								<CInput 
									v-model='config.resetButton.api'
									:label='$t("controllerConfig.form.resetButton.custom")'
									:is-valid='valid'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{ valid, errors }'
								rules='integer|required'
								:custom-messages='{
									integer: "controllerConfig.form.messages.invalid.rb_pin",
									required: "controllerConfig.form.messages.missing.rb_pin"
								}'
							>
								<CInput
									v-model='config.resetButton.button'
									:label='$t("controllerConfig.form.resetButton.pin")'
									:is-valid='valid'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
						</CCol>
						<CCol md='6'>
							<h3>{{ $t("controllerConfig.form.statusLed.title") }}</h3>
							<ValidationProvider
								v-slot='{ valid, errors }'
								rules='integer|required'
								:custom-messages='{
									integer: "controllerConfig.form.messages.invalid.gpio",
									required: "controllerConfig.form.messages.missing.sl_green"
								}'
							>
								<CInput
									v-model='config.statusLed.greenLed'
									:label='$t("controllerConfig.form.statusLed.green")'
									:is-valid='valid'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{ valid, errors }'
								rules='integer|required'
								:custom-messages='{
									integer: "controllerConfig.form.messages.invalid.gpio",
									required: "controllerConfig.form.messages.missing.sl_red"
								}'
							>
								<CInput
									v-model='config.statusLed.redLed'
									:label='$t("controllerConfig.form.statusLed.red")'
									:is-valid='valid'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
						</CCol>
					</CRow><hr>
					<CRow>
						<CCol md='6'>
							<h3>{{ $t("controllerConfig.form.daemonApi.autoNetwork.title") }}</h3>
							<ValidationProvider
								v-slot='{ valid, errors }'
								rules='integer|required'
								:custom-messages='{
									integer: "controllerConfig.form.messages.invalid.integer",
									required: "controllerConfig.form.messages.missing.da_retries"
								}'
							>
								<CInput
									v-model='config.daemonApi.autoNetwork.actionRetries'
									:label='$t("controllerConfig.form.daemonApi.autoNetwork.actionRetries")'
									:is-valid='valid'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<CInputCheckbox
								:checked.sync='config.daemonApi.autoNetwork.discoveryBeforeStart'
								:label='$t("controllerConfig.form.daemonApi.autoNetwork.discoveryBeforeStart")'
							/>
							<ValidationProvider
								v-slot='{ valid, errors }'
								rules='integer|required'
								:custom-messages='{
									integer: "controllerConfig.form.messages.invalid.integer",
									required: "controllerConfig.form.messages.missing.da_txpower"
								}'
							>
								<CInput
									v-model='config.daemonApi.autoNetwork.discoveryTxPower'
									:label='$t("controllerConfig.form.daemonApi.autoNetwork.discoveryTxPower")'
									:is-valid='valid'
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
								v-slot='{ valid, errors }'
								rules='integer|required'
								:custom-messages='{
									integer: "controllerConfig.form.messages.invalid.integer",
									required: "controllerConfig.form.messages.missing.da_ewaves"
								}'
							>
								<CInput
									v-model='config.daemonApi.autoNetwork.stopConditions.emptyWaves'
									:label='$t("controllerConfig.form.daemonApi.autoNetwork.stopConditions.emptyWaves")'
									:is-valid='valid'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{ valid, errors }'
								rules='integer|required'
								:custom-messages='{
									integer: "controllerConfig.form.messages.invalid.integer",
									required: "controllerConfig.form.messages.missing.da_waves"
								}'
							>
								<CInput
									v-model='config.daemonApi.autoNetwork.stopConditions.waves'
									:label='$t("controllerConfig.form.daemonApi.autoNetwork.stopConditions.waves")'
									:is-valid='valid'
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
								v-slot='{ valid, errors }'
								rules='addr_range|integer|required'
								:custom-messages='{
									addr_range: "controllerConfig.form.messages.invalid.dd_addr",
									integer: "controllerConfig.form.messages.invalid.integer",
									required: "controllerConfig.form.messages.missing.dd_addr"
								}'
							>
								<CInput
									v-model='config.daemonApi.discovery.maxAddr'
									:label='$t("controllerConfig.form.daemonApi.discovery.maxAddr")'
									:is-valid='valid'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{ valid, errors }'
								rules='integer|required'
								:custom-messages='{
									integer: "controllerConfig.form.messages.invalid.integer",
									required: "controllerConfig.form.messages.missing.dd_txpower"
								}'
							>
								<CInput
									v-model='config.daemonApi.discovery.txPower'
									:label='$t("controllerConfig.form.daemonApi.discovery.txPower")'
									:is-valid='valid'
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

extend('integer', integer);
extend('required', required);
extend('addr_range', (addr) => {
	return ((addr >= 0) && (addr <= 239));
});
extend('ws_addr', (addr) => {
	const regex = RegExp('ws:\\/\\/.+:([1-9]|[1-9][0-9]|[1-9][0-9]{2}|[1-9][0-9]{3}|[1-4][0-9][0-1][0-5][0-1])');
	return regex.test(addr);
});

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
		this.getConfig();
	},
	methods: {
		getConfig() {
			ConfigService.getConfig('controllerConfig')
				.then((response) => {
					this.config = response.data;
					if (this.config.resetButton.api !== ('autoNetwork' && 'discovery')) {
						this.apiCallCustom = this.config.resetButton.api;
						this.apiCallSetCustom = true;
						this.previousApiCall = 'autoNetwork';
					}
				})
				.catch((error) => {
					if (error.response) {
						if (error.response.status === 500) {
							this.$toast.error(this.$t('forms.messages.submitServerError'));
						}
					}
				});
		},
		processSubmit() {
			ConfigService.saveConfig('controllerConfig', this.config)
				.then((response) => {
					if (response.status === 200) {
						this.$toast.success(this.$t('forms.messages.saveSuccess'));
					}
				})
				.catch((error) => {
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