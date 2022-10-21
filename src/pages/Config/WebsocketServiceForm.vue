<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software,
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied
See the License for the specific language governing permissions and
limitations under the License.
-->
<template>
	<div>
		<h1 v-if='$route.path === "/config/daemon/messagings/websocket/add-service"'>
			{{ $t('config.daemon.messagings.websocket.service.add') }}
		</h1>
		<h1 v-else>
			{{ $t('config.daemon.messagings.websocket.service.edit') }}
		</h1>
		<CCard>
			<CCardBody>
				<ValidationObserver v-slot='{invalid}'>
					<CForm>
						<legend>{{ $t('config.daemon.messagings.websocket.service.legend') }}</legend>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required|instance'
							:custom-messages='{
								required: $t("config.daemon.messagings.websocket.errors.serviceInstance"),
								instance: $t("config.daemon.messagings.instanceInvalid"),
							}'
						>
							<CInput
								v-model='componentInstance'
								:label='$t("forms.fields.instanceName")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='errors.join(", ")'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='integer|between:1,65535|required'
							:custom-messages='{
								between: $t("config.daemon.messagings.websocket.errors.WebsocketPortRange"),
								required: $t("config.daemon.messagings.websocket.errors.WebsocketPort"),
								integer: $t("forms.errors.integer"),
							}'
						>
							<CInput
								v-model.number='WebsocketPort'
								type='number'
								:label='$t("config.daemon.messagings.websocket.form.WebsocketPort")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='errors.join(", ")'
							/>
						</ValidationProvider>
						<CInputCheckbox
							:checked.sync='acceptOnlyLocalhost'
							:label='$t("config.daemon.messagings.websocket.form.acceptOnlyLocalhost")'
						/>
						<div class='form-group'>
							<legend>
								{{ $t('config.daemon.messagings.websocket.form.tlsEnabled') }}
							</legend>
							<CSwitch
								color='primary'
								size='lg'
								shape='pill'
								label-on='ON'
								label-off='OFF'
								:checked.sync='tlsEnabled'
							/>
						</div>
						<ValidationProvider
							v-if='tlsEnabled'
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("config.daemon.messagings.websocket.errors.tlsMode"),
							}'
						>
							<CSelect
								:value.sync='tlsMode'
								:label='$t("config.daemon.messagings.websocket.form.tlsMode")'
								:description='$t(`config.daemon.messagings.websocket.form.tlsModes.descriptions.${tlsMode}`)'
								:options='tlsModeOptions'
								:placeholder='$t("config.daemon.messagings.websocket.errors.tlsMode")'
								:disabled='!tlsEnabled'
								:is-valid='touched && tlsEnabled ? valid : null'
								:invalid-feedback='errors.join(", ")'
							/>
						</ValidationProvider>
						<CRow
							v-if='tlsEnabled'
							form
						>
							<CCol sm='12' lg='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: $t("config.daemon.messagings.websocket.errors.certificate"),
									}'
								>
									<CInput
										v-model='certificate'
										:label='$t("forms.fields.certificate")'
										:disabled='!tlsEnabled'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
									/>
								</ValidationProvider>
							</CCol>
							<CCol sm='12' lg='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: $t("config.daemon.messagings.websocket.errors.privateKey"),
									}'
								>
									<CInput
										v-model='privateKey'
										:label='$t("forms.fields.privateKey")'
										:disabled='!tlsEnabled'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
									/>
								</ValidationProvider>
							</CCol>
						</CRow>
						<CButton
							color='primary'
							:disabled='invalid'
							@click='saveInstance'
						>
							{{ submitButton }}
						</CButton>
					</CForm>
				</ValidationObserver>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {
	CButton,
	CCard,
	CCardBody,
	CCardHeader,
	CForm,
	CInput,
	CInputCheckbox,
	CSelect,
	CSwitch
} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {between, integer, required} from 'vee-validate/dist/rules';
import {extendedErrorToast} from '@/helpers/errorToast';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {IOption} from '@/interfaces/Coreui';
import {IWsService} from '@/interfaces/Config/Messaging';
import {MetaInfo} from 'vue-meta';

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
		CSwitch,
		ValidationObserver,
		ValidationProvider,
	},
	metaInfo(): MetaInfo {
		return {
			title: (this as unknown as WebsocketServiceForm).pageTitle
		};
	},
})

/**
 * Daemon WebsocketService component configuration form
 */
export default class WebsocketServiceForm extends Vue {
	/**
	 * @var {boolean} acceptOnlyLocalhost Accept connections only from localhost?
	 */
	private acceptOnlyLocalhost = false;

	/**
	 * @var {string} certificate Path to certificate for TLS
	 */
	private certificate = '';

	/**
	 * @var {string} component WebsocketService component name
	 */
	private component = '';

	/**
	 * @var {string} componentInstance WebsocketService component instance name
	 */
	private componentInstance = '';

	/**
	 * @constant {string} componentName Name of WebSocket service component
	 */
	private componentName = 'shape::WebsocketCppService';

	/**
	 * @var {string} privateKey Path to private key for TLS
	 */
	private privateKey = '';

	/**
	 * @var {boolean} tlsEnabled Use TLS?
	 */
	private tlsEnabled = false;

	/**
	 * @var {string} tlsMode TLS operating mode
	 */
	private tlsMode = '';

	/**
	 * @constant {Array<IOption>} tlsModeOptions Array of CoreUI select options
	 */
	private tlsModeOptions: Array<IOption> = [
		{
			value: 'intermediate',
			label: this.$t('config.daemon.messagings.websocket.form.tlsModes.intermediate').toString()
		},
		{
			value: 'modern',
			label: this.$t('config.daemon.messagings.websocket.form.tlsModes.modern').toString()
		},
		{
			value: 'old',
			label: this.$t('config.daemon.messagings.websocket.form.tlsModes.old').toString()
		},
	];

	/**
	 * @var {number} WebsocketPort Websocket port
	 */
	private WebsocketPort = 1338;

	/**
	 * @property {string} instance WebSocket service component instance name
	 */
	@Prop({required: false, default: ''}) instance!: string;

	/**
	 * Computes page title depending on the action (add, edit)
	 * @returns {string} Page title
	 */
	get pageTitle(): string {
		return this.$route.path === '/config/daemon/messagings/websocket/add-service' ?
			this.$t('config.daemon.messagings.websocket.service.add').toString() : this.$t('config.daemon.messagings.websocket.service.edit').toString();
	}

	/**
	 * Computes the text of form submit button depending on the action (add, edit)
	 * @returns {string} Button text
	 */
	get submitButton(): string {
		return this.$route.path === '/config/daemon/messagings/websocket/add-service' ?
			this.$t('forms.add').toString() : this.$t('forms.edit').toString();
	}

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		extend('instance', (item: string) => {
			const re = RegExp(/^[^&]+$/);
			return re.test(item);
		});
	}

	/**
	 * Retrieves component instance configuration
	 */
	mounted(): void {
		if (this.instance) {
			this.getConfig();
		}
	}

	/**
	 * Retrieves instance of WebSocket service component
	 */
	private getConfig(): void {
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.getInstance(this.componentName, this.instance)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.parseConfiguration(response.data);
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.daemon.messagings.websocket.service.messages.getFailed', {service: this.instance});
				this.$router.push('/config/daemon/messagings/websocket');
			});
	}

	/**
	 * Parses WebsocketService component instance configuration from REST API response
	 * @param {IWsService} response Configuration object from REST API response
	 */
	private parseConfiguration(response: IWsService): void {
		this.component = response.component;
		this.instance = this.componentInstance = response.instance;
		this.WebsocketPort = response.WebsocketPort;
		this.acceptOnlyLocalhost = response.acceptOnlyLocalhost;
		if (response.tlsEnabled !== undefined) {
			this.tlsEnabled = response.tlsEnabled;
		}
		if (response.tlsMode !== undefined) {
			this.tlsMode = response.tlsMode;
		}
		if (response.certificate !== undefined) {
			this.certificate = response.certificate;
		}
		if (response.privateKey !== undefined) {
			this.privateKey = response.privateKey;
		}
	}

	/**
	 * Creates WebsocketService component instance configuration object
	 * @returns {IWsService} WebsocketService configuration
	 */
	private buildConfiguration(): IWsService {
		return {
			component: this.component,
			instance: this.componentInstance,
			WebsocketPort: this.WebsocketPort,
			acceptOnlyLocalhost: this.acceptOnlyLocalhost,
			tlsEnabled: this.tlsEnabled,
			tlsMode: this.tlsMode,
			certificate: this.certificate,
			privateKey: this.privateKey
		};
	}

	/**
	 * Saves new or updates existing configuration of WebSocket service component instance
	 */
	private saveInstance(): void {
		this.$store.commit('spinner/SHOW');
		if (this.instance !== '') {
			DaemonConfigurationService.updateInstance(this.componentName, this.instance, this.buildConfiguration())
				.then(this.handleSuccess)
				.catch(this.handleFailure);
		} else {
			DaemonConfigurationService.createInstance(this.componentName, this.buildConfiguration())
				.then(this.handleSuccess)
				.catch(this.handleFailure);
		}
	}

	/**
	 * Handles REST API success
	 */
	private handleSuccess(): void {
		this.$toast.success(
			this.$t('config.daemon.messagings.websocket.service.messages.saveSuccess', {service: this.componentInstance}).toString()
		);
		this.$router.push('/config/daemon/messagings/websocket');
	}

	/**
	 * Handles REST API failure
	 * @param {AxiosError} err Error response
	 */
	private handleFailure(err: AxiosError): void {
		extendedErrorToast(err, 'config.daemon.messagings.websocket.service.messages.saveFailed', {service: this.componentInstance});
	}
}
</script>
