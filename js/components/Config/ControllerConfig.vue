<template>
	<div>
		<CCard body-wrapper>
			<ValidationObserver v-if='config !== null'>
				<CForm @submit.prevent='processSubmit'>
					<CRow>
						<CCol md='6'>
							<h3>{{ $t("controllerConfig.form.wsServers.title") }}</h3>
							<CInput
								v-model='config.wsServers.api'
								:label='$t("controllerConfig.form.wsServers.api")'
							/>
							<CInput
								v-model='config.wsServers.monitor'
								:label='$t("controllerConfig.form.wsServers.monitor")'
							/>
						</CCol>
						<CCol md='6'>
							<h3>{{ $t("controllerConfig.form.logger.title") }}</h3>
							<CInput
								v-model='config.logger.filePath'
								:label='$t("controllerConfig.form.logger.filePath")'
							/>
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
							<CSelect
								:value.sync='config.resetButton.api'
								:options='[
									{value: "autoNetwork", label: "AutoNetwork"},
									{value: "discovery", label: "Discovery"}
								]'
								:label='$t("controllerConfig.form.resetButton.api")'
							/>
							<CInput
								v-model='config.resetButton.button'
								:label='$t("controllerConfig.form.resetButton.pin")'
							/>
						</CCol>
						<CCol md='6'>
							<h3>{{ $t("controllerConfig.form.statusLed.title") }}</h3>
							<CInput
								v-model='config.statusLed.greenLed'
								:label='$t("controllerConfig.form.statusLed.green")'
							/>
							<CInput
								v-model='config.statusLed.redLed'
								:label='$t("controllerConfig.form.statusLed.red")'
							/>
						</CCol>
					</CRow><hr>
					<CRow>
						<CCol md='6'>
							<h3>{{ $t("controllerConfig.form.daemonApi.autoNetwork.title") }}</h3>
							<CInput
								v-model='config.daemonApi.autoNetwork.actionRetries'
								:label='$t("controllerConfig.form.daemonApi.autoNetwork.actionRetries")'
							/>
							<CInputCheckbox
								:checked.sync='config.daemonApi.autoNetwork.discoveryBeforeStart'
								:label='$t("controllerConfig.form.daemonApi.autoNetwork.discoveryBeforeStart")'
							/><br>
							<CInput
								v-model='config.daemonApi.autoNetwork.discoveryTxPower'
								:label='$t("controllerConfig.form.daemonApi.autoNetwork.discoveryTxPower")'
							/>
							<CInputCheckbox
								:checked.sync='config.daemonApi.autoNetwork.skipDiscoveryEachWave'
								:label='$t("controllerConfig.form.daemonApi.autoNetwork.skipDiscoveryEachWave")'
							/><hr>
							<h4>{{ $t("controllerConfig.form.daemonApi.autoNetwork.stopConditions.title") }}</h4>
							<CInputCheckbox
								:checked.sync='config.daemonApi.autoNetwork.stopConditions.abortOnTooManyNodesFound'
								:label='$t("controllerConfig.form.daemonApi.autoNetwork.stopConditions.abortOnTooManyNodesFound")'
							/><br>
							<CInput
								v-model='config.daemonApi.autoNetwork.stopConditions.emptyWaves'
								:label='$t("controllerConfig.form.daemonApi.autoNetwork.stopConditions.emptyWaves")'
							/>
							<CInput
								v-model='config.daemonApi.autoNetwork.stopConditions.waves'
								:label='$t("controllerConfig.form.daemonApi.autoNetwork.stopConditions.waves")'
							/><hr>
							<CInputCheckbox
								:checked.sync='config.daemonApi.autoNetwork.returnVerbose'
								:label='$t("controllerConfig.form.daemonApi.autoNetwork.verbose")'
							/>
						</CCol>
						<CCol md='6'>
							<h3>{{ $t("controllerConfig.form.daemonApi.discovery.title") }}</h3>
							<CInput
								v-model='config.daemonApi.discovery.maxAddr'
								:label='$t("controllerConfig.form.daemonApi.discovery.maxAddr")'
							/>
							<CInput
								v-model='config.daemonApi.discovery.txPower'
								:label='$t("controllerConfig.form.daemonApi.discovery.txPower")'
							/>
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
							/><br>
							<CInputCheckbox
								:checked.sync='config.factoryReset.daemon'
								:label='$t("controllerConfig.form.factoryReset.daemon")'
							/><br>
							<CInputCheckbox
								:checked.sync='config.factoryReset.network'
								:label='$t("controllerConfig.form.factoryReset.network")'
							/><br>
							<CInputCheckbox
								:checked.sync='config.factoryReset.webapp'
								:label='$t("controllerConfig.form.factoryReset.webapp")'
							/><br>
						</CCol>
					</CRow>
					<CButton color='primary' type='submit'>
						{{ $t('form.saveButton') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCard>
	</div>
</template>

<script>
import {CButton, CCard , CForm, CInput, CInputCheckbox, CSelect} from '@coreui/vue';
import {ValidationObserver} from 'vee-validate';
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
	},
	data() {
		return {
			config: null,
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
				})
				.catch((error) => {
					if (error.response) {
						if (error.response.status === 500) {
							this.$toast.error(this.$t('form.messages.submitServerError'));
						}
					}
				});
		},
		processSubmit() {
			ConfigService.saveConfig('controllerConfig', this.config)
				.then((response) => {
					if (response.status === 200) {
						this.$toast.success(this.$t('form.messages.saveSuccess'));
					}
				})
				.catch((error) => {
					if (error.response) {
						if (error.response.status === 500) {
							this.$toast.error(this.$t('form.messages.submitServerError'));
						}
					} else {
						console.error(error.message);
					}
				});
		},
	}
};
</script>

<style>

</style>