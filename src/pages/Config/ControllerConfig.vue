<template>
	<div>
		<h1>{{ $t('config.controller.title') }}</h1>
		<CCard>
			<CCardBody>
				<ValidationObserver v-if='config !== null' v-slot='{ invalid }'>
					<CForm @submit.prevent='processSubmit'>
						<CRow>
							<CCol md='6'>
								<h3>{{ $t("config.controller.form.wsServers.title") }}</h3>
								<ValidationProvider
									v-slot='{ errors, touched, valid }'
									rules='required|ws_addr'
									:custom-messages='{
										required: "config.controller.errors.missing.ws_api",
										ws_addr: "config.controller.errors.invalid.ws_format"
									}'
								>
									<CInput
										v-model='config.wsServers.api'
										:label='$t("config.controller.form.wsServers.api")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
									/>
								</ValidationProvider>
								<ValidationProvider
									v-slot='{ errors, touched, valid }'
									rules='required|ws_addr'
									:custom-messages='{
										required: "config.controller.errors.missing.ws_monitor",
										ws_addr: "config.controller.errors.invalid.ws_format"
									}'
								>
									<CInput
										v-model='config.wsServers.monitor'
										:label='$t("config.controller.form.wsServers.monitor")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
									/>
								</ValidationProvider>
							</CCol>
							<CCol md='6'>
								<h3>{{ $t("config.controller.form.logger.title") }}</h3>
								<ValidationProvider
									v-slot='{ errors, touched, valid }'
									rules='required'
									:custom-messages='{required: "config.controller.errors.missing.l_file"}'
								>
									<CInput
										v-model='config.logger.filePath'
										:label='$t("config.controller.form.logger.filePath")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
									/>
								</ValidationProvider>
								<ValidationProvider
									v-slot='{ valid, touched, errors }'
									rules='required'
									:custom-messages='{
										required: "config.controller.errors.missing.l_severity",
									}'
								>
									<CSelect
										:value.sync='config.logger.severity'
										:options='severityOptions'
										:label='$t("config.controller.form.logger.severity")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
										:placeholder='$t("config.controller.errors.missing.l_severity")'
									/>
								</ValidationProvider>
							</CCol>
						</CRow><hr>
						<CRow>
							<CCol md='6'>
								<h3>{{ $t("config.controller.form.resetButton.title") }}</h3>
								<CSelect
									:value.sync='config.resetButton.api'
									:options='apiCallOptions'
									:label='$t("config.controller.form.resetButton.api")'
								/>
								<div v-if='config.resetButton.api === "autoNetwork"'>
									<h3>
										{{ $t("config.controller.form.daemonApi.autoNetwork.title") }}
									</h3>
									<ValidationProvider
										v-slot='{ errors, touched, valid }'
										rules='required|integer|between:0,3'
										:custom-messages='{
											integer: "forms.errors.integer",
											required: "iqrfnet.networkManager.messages.autoNetwork.actionRetries",
											between: "iqrfnet.networkManager.messages.autoNetwork.actionRetries"
										}'
									>
										<CInput
											v-model.number='config.daemonApi.autoNetwork.actionRetries'
											type='number'
											min='0'
											max='3'
											:label='$t("iqrfnet.networkManager.autoNetwork.form.actionRetries")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										/>
									</ValidationProvider>
									<CInputCheckbox
										:checked.sync='config.daemonApi.autoNetwork.discoveryBeforeStart'
										:label='$t("iqrfnet.networkManager.autoNetwork.form.discoveryBeforeStart")'
									/>
									<ValidationProvider
										v-slot='{ errors, touched, valid }'
										rules='integer|required|between:0,7'
										:custom-messages='{
											integer: "forms.errors.integer",
											required: "iqrfnet.networkManager.messages.discovery.txPower",
											between: "iqrfnet.networkManager.messages.discovery.txPower"
										}'
									>
										<CInput
											v-model.number='config.daemonApi.autoNetwork.discoveryTxPower'
											type='number'
											min='0'
											max='7'
											:label='$t("iqrfnet.networkManager.autoNetwork.form.discoveryTxPower")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										/>
									</ValidationProvider>
									<CInputCheckbox
										:checked.sync='config.daemonApi.autoNetwork.skipDiscoveryEachWave'
										:label='$t("iqrfnet.networkManager.autoNetwork.form.skipDiscoveryEachWave")'
									/><hr>
									<h4>
										{{ $t("iqrfnet.networkManager.autoNetwork.form.stopConditions") }}
									</h4>
									<CInputCheckbox
										:checked.sync='config.daemonApi.autoNetwork.stopConditions.abortOnTooManyNodesFound'
										:label='$t("iqrfnet.networkManager.autoNetwork.form.abortOnTooManyNodesFound")'
									/>
									<ValidationProvider
										v-slot='{ errors, touched, valid }'
										rules='integer|required|between:1,127'
										:custom-messages='{
											integer: "forms.errors.integer",
											required: "iqrfnet.networkManager.messages.autoNetwork.emptyWaves",
											between: "iqrfnet.networkManager.messages.autoNetwork.emptyWaves"
										}'
									>
										<CInput
											v-model.number='config.daemonApi.autoNetwork.stopConditions.emptyWaves'
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
										rules='integer|required|between:1,127'
										:custom-messages='{
											integer: "forms.errors.integer",
											required: "iqrfnet.networkManager.messages.autoNetwork.waves",
											between: "iqrfnet.networkManager.messages.autoNetwork.waves"
										}'
									>
										<CInput
											v-model.number='config.daemonApi.autoNetwork.stopConditions.waves'
											type='number'
											min='1'
											max='127'
											:label='$t("iqrfnet.networkManager.autoNetwork.form.waves")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										/>
									</ValidationProvider><hr>
									<CInputCheckbox
										:checked.sync='config.daemonApi.autoNetwork.returnVerbose'
										:label='$t("forms.fields.verbose")'
									/>
								</div>
								<div v-else-if='config.resetButton.api === "discovery"'>
									<h3>
										{{ $t("config.controller.form.daemonApi.discovery.title") }}
									</h3>
									<ValidationProvider
										v-slot='{ errors, touched, valid }'
										rules='integer|required|between:0,239'
										:custom-messages='{
											integer: "forms.errors.integer",
											required: "iqrfnet.networkManager.discovery.errors.maxAddr",
											between: "iqrfnet.networkManager.discovery.errors.maxAddr"
										}'
									>
										<CInput
											v-model.number='config.daemonApi.discovery.maxAddr'
											type='number'
											min='0'
											max='239'
											:label='$t("iqrfnet.networkManager.discovery.form.maxAddr")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										/>
									</ValidationProvider>
									<ValidationProvider
										v-slot='{ errors, touched, valid }'
										rules='integer|required|between:0,7'
										:custom-messages='{
											integer: "forms.errors.integer",
											required: "iqrfnet.networkManager.discovery.errors.txPower",
											between: "iqrfnet.networkManager.discovery.errors.txPower"
										}'
									>
										<CInput
											v-model.number='config.daemonApi.discovery.txPower'
											type='number'
											min='0'
											max='7'
											:label='$t("iqrfnet.networkManager.discovery.form.txPower")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										/>
									</ValidationProvider>
									<CInputCheckbox
										:checked.sync='config.daemonApi.discovery.returnVerbose'
										:label='$t("forms.fields.verbose")'
									/>
								</div>
							</CCol>
							<CCol md='6'>
								<h3>{{ $t("config.controller.form.factoryReset.title") }}</h3>
								<CInputCheckbox
									:checked.sync='config.factoryReset.coordinator'
									:label='$t("forms.fields.coordinator")'
								/>
								<CInputCheckbox
									:checked.sync='config.factoryReset.daemon'
									:label='$t("config.controller.form.factoryReset.daemon")'
								/>
								<CInputCheckbox
									:checked.sync='config.factoryReset.network'
									:label='$t("config.controller.form.factoryReset.network")'
								/>
								<CInputCheckbox
									:checked.sync='config.factoryReset.webapp'
									:label='$t("config.controller.form.factoryReset.webapp")'
								/>
							</CCol>
						</CRow>
						<CButton color='primary' type='submit' :disabled='invalid'>
							{{ $t('forms.save') }}
						</CButton>
					</CForm>
				</ValidationObserver>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput, CInputCheckbox, CSelect} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {between, integer, required} from 'vee-validate/dist/rules';
import {controllerErrorToast, extendedErrorToast} from '../../helpers/errorToast';
import FeatureConfigService from '../../services/FeatureConfigService';
import ServiceService from '../../services/ServiceService';

import {AxiosError, AxiosResponse} from 'axios';
import {IController} from '../../interfaces/controller';
import {IOption} from '../../interfaces/coreui';
import {NavigationGuardNext, Route} from 'vue-router/types/router';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		CInputCheckbox,
		CSelect,
		ValidationObserver,
		ValidationProvider
	},
	beforeRouteEnter(to: Route, from: Route, next: NavigationGuardNext): void {
		next((vm: Vue) => {
			if (!vm.$store.getters['features/isEnabled']('iqrfGatewayController')) {
				vm.$toast.error(
					vm.$t('config.controller.messages.disabled').toString()
				);
				vm.$router.push(from.path);
			}
		});
	},
	metaInfo: {
		title: 'config.controller.description',
	},
})

/**
 * IQRF Gateway Controller configuration component
 */
export default class ControllerConfig extends Vue {
	/**
	 * @constant {Array<IOption>} apiCallOptions Array of CoreUI api call select options
	 */
	private apiCallOptions: Array<IOption> = [
		{
			value: '',
			label: this.$t('config.controller.form.resetButton.noCall').toString()
		},
		{
			value: 'autoNetwork',
			label: this.$t('iqrfnet.networkManager.autoNetwork.title').toString()
		},
		{
			value: 'discovery',
			label: this.$t('iqrfnet.networkManager.discovery.title').toString()
		}
	]

	/**
	 * @constant {string} name Name of Controller service
	 */
	private name = 'controller'

	/**
	 * @constant {Array<IOption>} severityOptions Array of CoreUI logger severity select options
	 */
	private severityOptions: Array<IOption> = [
		{
			value: 'trace',
			label: this.$t('forms.fields.messageLevel.trace').toString()
		},
		{
			value: 'debug',
			label: this.$t('forms.fields.messageLevel.debug').toString()
		},
		{
			value: 'info',
			label: this.$t('forms.fields.messageLevel.info').toString()
		},
		{
			value: 'warning',
			label: this.$t('forms.fields.messageLevel.warning').toString()
		},
		{
			value: 'error',
			label: this.$t('forms.fields.messageLevel.error').toString()
		}
	]

	/**
	 * @var {IController|null} config IQRF Gateway Controller configuration
	 */
	private config: IController|null = null

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		extend('ws_addr', (addr: string) => {
			const regex = RegExp('^ws:\\/\\/.+:([1-9]|[1-9][0-9]|[1-9][0-9]{2}|[1-9][0-9]{3}|[1-4][0-9][0-1][0-5][0-1])$');
			return regex.test(addr);
		});
	}

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		this.getConfig();
	}

	/**
	 * Retrieves configuration of IQRF Gateway Controller
	 */
	private getConfig(): void {
		if (!this.$store.getters['spinner/isEnabled']) {
			this.$store.commit('spinner/SHOW');
		}
		this.$store.commit('spinner/SHOW');
		FeatureConfigService.getConfig(this.name)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.config = response.data;
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.controller.messages.fetchFailed');
				this.$router.push('/');
			});
	}

	/**
	 * Updates the configuration of IQRF Gateway Controller
	 */
	private processSubmit(): void {
		this.$store.commit('spinner/SHOW');
		FeatureConfigService.saveConfig(this.name, this.config)
			.then(() => {
				this.restartController();
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'config.controller.messages.saveFailed'));
	}

	/**
	 * Restarts IQRF Controller service
	 */
	private restartController(): void {
		ServiceService.restart('iqrf-gateway-controller')
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('config.controller.messages.restartSuccess').toString()
				);
			})
			.catch((error: AxiosError) => controllerErrorToast(error, 'service.messages.restartFailed'));
	}

}
</script>
