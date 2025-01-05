<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

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
		<h1 v-if='$route.path === "/config/daemon/messagings/websocket/add"'>
			{{ $t('config.daemon.messagings.websocket.interface.add') }}
		</h1>
		<h1 v-else>
			{{ $t('config.daemon.messagings.websocket.interface.edit') }}
		</h1>
		<CCard body-wrapper>
			<ValidationObserver v-slot='{invalid}'>
				<CForm @submit.prevent='saveConfig'>
					<CRow>
						<CCol md='6'>
							<legend>{{ $t('config.daemon.messagings.websocket.interface.legend') }}</legend>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required|instance'
								:custom-messages='{
									required: $t("config.daemon.messagings.websocket.errors.instance"),
									instance: $t("config.daemon.messagings.instanceInvalid"),
								}'
							>
								<CInput
									v-model='messaging.instance'
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
									v-model.number='service.WebsocketPort'
									type='number'
									:label='$t("config.daemon.messagings.websocket.form.WebsocketPort")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='errors.join(", ")'
								/>
							</ValidationProvider>
							<CInputCheckbox
								:checked.sync='messaging.acceptAsyncMsg'
								:label='$t("config.daemon.messagings.acceptAsyncMsg")'
							/>
							<CInputCheckbox
								:checked.sync='service.acceptOnlyLocalhost'
								:label='$t("config.daemon.messagings.websocket.form.acceptOnlyLocalhost")'
							/>
						</CCol>
						<CCol>
							<div>
								<label style='font-size: 1.5rem;'>
									{{ $t('config.daemon.messagings.websocket.form.tlsEnabled') }}
								</label>
								<CSwitch
									color='primary'
									size='lg'
									shape='pill'
									label-on='ON'
									label-off='OFF'
									:checked.sync='service.tlsEnabled'
									style='float: right;'
								/>
								<ValidationProvider
									v-if='service.tlsEnabled'
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: $t("config.daemon.messagings.websocket.errors.tlsMode"),
									}'
								>
									<CSelect
										:value.sync='service.tlsMode'
										:label='$t("config.daemon.messagings.websocket.form.tlsMode")'
										:options='tlsModeOptions'
										:placeholder='$t("config.daemon.messagings.websocket.errors.tlsMode")'
										:disabled='!service.tlsEnabled'
										:is-valid='touched && service.tlsEnabled ? valid : null'
										:invalid-feedback='errors.join(", ")'
									/>
									<p
										v-if='service.tlsMode !== "" && service.tlsMode !== undefined'
										:class='!service.tlsEnabled ? "text-secondary" : ""'
									>
										{{ $t(`config.daemon.messagings.websocket.form.tlsModes.descriptions.${service.tlsMode}`) }}
									</p>
								</ValidationProvider>
								<ValidationProvider
									v-if='service.tlsEnabled'
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: $t("config.daemon.messagings.websocket.errors.certificate"),
									}'
								>
									<CInput
										v-model='service.certificate'
										:label='$t("forms.fields.certificate")'
										:disabled='!service.tlsEnabled'
										:is-valid='touched && service.tlsEnabled ? valid : null'
										:invalid-feedback='errors.join(", ")'
									/>
								</ValidationProvider>
								<ValidationProvider
									v-if='service.tlsEnabled'
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: $t("config.daemon.messagings.websocket.errors.privateKey"),
									}'
								>
									<CInput
										v-model='service.privateKey'
										:label='$t("forms.fields.privateKey")'
										:disabled='!service.tlsEnabled'
										:is-valid='touched && service.tlsEnabled ? valid : null'
										:invalid-feedback='errors.join(", ")'
									/>
								</ValidationProvider>
							</div>
						</CCol>
					</CRow>
					<CButton type='submit' color='primary' :disabled='invalid'>
						{{ submitButton }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput, CInputCheckbox, CSelect} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {between, integer, required} from 'vee-validate/dist/rules';
import {extendedErrorToast} from '@/helpers/errorToast';
import {daemonInstanceName} from '@/helpers/validators';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {IOption} from '@/interfaces/Coreui';
import {MetaInfo} from 'vue-meta';
import {IWsMessaging, ModalInstance, IWsService} from '@/interfaces/Config/Messaging';

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
		ValidationProvider,
	},
	metaInfo(): MetaInfo {
		return {
			title: (this as unknown as WebsocketInterfaceForm).pageTitle
		};
	}
})

/**
 * Daemon WebSocket messaging and service configuration form (normal user)
 */
export default class WebsocketInterfaceForm extends Vue {
	/**
	 * @constant {ModalInstance} componentNames Names of websocket messaging and service components
	 */
	private componentNames: ModalInstance = {
		messaging: 'iqrf::WebsocketMessaging',
		service: 'shape::WebsocketCppService',
	};

	/**
	 * @var {ModalInstance} instances Names of websocket messaging and service instances
	 */
	private instances: ModalInstance = {
		messaging: '',
		service: '',
	};

	/**
	 * @var {WsMessging} messaging WebSocket messaging component instance
	 */
	private messaging: IWsMessaging = {
		component: '',
		instance: '',
		acceptAsyncMsg: false,
		RequiredInterfaces: [],
	};

	/**
	 * @var {WsService} service WebSocket service component instance
	 */
	private service: IWsService = {
		component: '',
		instance: '',
		WebsocketPort: 1338,
		acceptOnlyLocalhost: false,
		tlsEnabled: false,
		tlsMode: '',
		certificate: '',
		privateKey: ''
	};

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
	 * @property {string} instance WebSocket interface instance name
	 */
	@Prop({required: false, default: ''}) instance!: string;

	/**
	 * Computes page title depending on the action (add, edit)
	 * @returns {string} Page title
	 */
	get pageTitle(): string {
		return this.$route.path === '/config/daemon/messagings/websocket/add' ?
			this.$t('config.daemon.messagings.websocket.interface.add').toString() : this.$t('config.daemon.messagings.websocket.interface.edit').toString();
	}

	/**
	 * Computes the text of form submit button depending on the action (add, edit)
	 * @returns {string} Button text
	 */
	get submitButton(): string {
		return this.$route.path === '/config/daemon/messagings/websocket/add' ?
			this.$t('forms.add').toString() : this.$t('forms.edit').toString();
	}

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		extend('instance', daemonInstanceName);
	}

	/**
	 * Retrieves component instance configuration
	 */
	mounted(): void {
		if (this.instance !== '') {
			this.getConfig();
		}
	}

	/**
	 * Retrieves configuration of the WebSocket messaging and service instances
	 */
	private getConfig(): void {
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.getInstance(this.componentNames.messaging, this.instance)
			.then((response: AxiosResponse) => {
				this.messaging = response.data;
				this.instances.messaging = this.instance;
				this.instances.service = this.messaging.RequiredInterfaces[0].target.instance;
				DaemonConfigurationService.getInstance(this.componentNames.service, this.instances.service)
					.then((response: AxiosResponse) => {
						this.service = response.data;
						this.$store.commit('spinner/HIDE');
					})
					.catch((error: AxiosError) => {
						extendedErrorToast(
							error,
							'config.daemon.messagings.websocket.interface.messages.getFailed',
							{interface: this.instances.service}
						);
						this.$router.push('/config/daemon/messagings/websocket');
					});
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(
					error,
					'config.daemon.messagings.websocket.interface.messages.getFailed',
					{interface: this.instance}
				);
				this.$router.push('/config/daemon/messagings/websocket');
			});
	}

	/**
	 * Saves new or updates existing configuration of WebSocket messaging and service component instances
	 */
	private saveConfig(): void {
		this.$store.commit('spinner/SHOW');
		this.service.instance = this.messaging.instance;
		if (this.messaging.RequiredInterfaces.length === 0) {
			this.messaging.RequiredInterfaces[0] = {
				name: 'shape::IWebsocketService',
				target: {
					instance: this.service.instance,
				},
			};
		} else {
			this.messaging.RequiredInterfaces[0].target.instance = this.messaging.instance;
		}
		if (this.instance === '') {
			Promise.all([
				DaemonConfigurationService.createInstance(this.componentNames.service, this.service),
				DaemonConfigurationService.createInstance(this.componentNames.messaging, this.messaging),
			])
				.then(this.handleSuccess)
				.catch(this.handleFailure);
		} else {
			Promise.all([
				DaemonConfigurationService.updateInstance(this.componentNames.service, this.instances.service, this.service),
				DaemonConfigurationService.updateInstance(this.componentNames.messaging, this.instances.messaging, this.messaging),
			])
				.then(this.handleSuccess)
				.catch(this.handleFailure);
		}
	}

	/**
	 * Handles REST API success
	 */
	private handleSuccess(): void {
		this.$store.commit('spinner/HIDE');
		this.$toast.success(
			this.$t('config.daemon.messagings.websocket.interface.messages.saveSuccess', {interface: this.instances.service}).toString()
		);
		this.$router.push('/config/daemon/messagings/websocket');
	}

	/**
	 * Handles REST API failure
	 * @param {AxiosError} err Error response
	 */
	private handleFailure(err: AxiosError): void {
		extendedErrorToast(err, 'config.daemon.messagings.websocket.interface.messages.saveFailed', {interface: this.instances.service});
	}
}
</script>
