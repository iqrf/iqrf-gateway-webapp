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
		<h1>{{ pageTitle }}</h1>
		<v-card>
			<v-card-text>
				<ValidationObserver v-slot='{invalid}'>
					<v-form>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required|instance'
							:custom-messages='{
								required: $t("config.daemon.messagings.websocket.errors.instance"),
								instance: $t("config.daemon.messagings.instanceInvalid"),
							}'
						>
							<v-text-field
								v-model='messaging.instance'
								:label='$t("forms.fields.instanceName")'
								:success='touched ? valid : null'
								:error-messages='errors'
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
							<v-text-field
								v-model.number='service.WebsocketPort'
								type='number'
								:label='$t("config.daemon.messagings.websocket.form.WebsocketPort")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<v-row>
							<v-col cols='12' md='6'>
								<v-checkbox
									v-model='messaging.acceptAsyncMsg'
									:label='$t("config.daemon.messagings.acceptAsyncMsg")'
								/>
							</v-col>
							<v-col cols='12' md='6'>
								<v-checkbox
									v-model='service.acceptOnlyLocalhost'
									:label='$t("config.daemon.messagings.websocket.form.acceptOnlyLocalhost")'
								/>
							</v-col>
						</v-row>
						<v-switch
							v-model='service.tlsEnabled'
							:label='$t("config.daemon.messagings.websocket.form.tlsEnabled")'
							color='primary'
							inset
							dense
						/>
						<div v-if='service.tlsEnabled'>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required'
								:custom-messages='{
									required: $t("config.daemon.messagings.websocket.errors.tlsMode"),
								}'
							>
								<v-select
									v-model='service.tlsMode'
									:label='$t("config.daemon.messagings.websocket.form.tlsMode")'
									:items='tlsModeOptions'
									:placeholder='$t("config.daemon.messagings.websocket.errors.tlsMode")'
									:disabled='!service.tlsEnabled'
									:success='touched && service.tlsEnabled ? valid : null'
									:error-messages='errors'
									:hint='$t(`config.daemon.messagings.websocket.form.tlsModes.descriptions.${service.tlsMode}`)'
									persistent-hint
								/>
							</ValidationProvider>
							<v-row>
								<v-col cols='12' md='6'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='required'
										:custom-messages='{
											required: $t("config.daemon.messagings.websocket.errors.certificate"),
										}'
									>
										<v-text-field
											v-model='service.certificate'
											:label='$t("forms.fields.certificate")'
											:disabled='!service.tlsEnabled'
											:success='touched && service.tlsEnabled ? valid : null'
											:error-messages='errors'
										/>
									</ValidationProvider>
								</v-col>
								<v-col cols='12' md='6'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='required'
										:custom-messages='{
											required: $t("config.daemon.messagings.websocket.errors.privateKey"),
										}'
									>
										<v-text-field
											v-model='service.privateKey'
											:label='$t("forms.fields.privateKey")'
											:disabled='!service.tlsEnabled'
											:success='touched && service.tlsEnabled ? valid : null'
											:error-messages='errors'
										/>
									</ValidationProvider>
								</v-col>
							</v-row>
						</div>
						<v-btn
							color='primary'
							:disabled='invalid'
							@click='saveConfig'
						>
							{{ submitButton }}
						</v-btn>
					</v-form>
				</ValidationObserver>
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {between, integer, required} from 'vee-validate/dist/rules';
import {daemonInstanceName} from '@/helpers/validators';
import {extendedErrorToast} from '@/helpers/errorToast';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {ISelectItem} from '@/interfaces/Vuetify';
import {IWsMessaging, IWsService, ModalInstance} from '@/interfaces/Config/Messaging';
import {MetaInfo} from 'vue-meta';

@Component({
	components: {
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
	 * @constant {Array<ISelectItem>} tlsModeOptions TLS mode select options
	 */
	private tlsModeOptions: Array<ISelectItem> = [
		{
			value: 'intermediate',
			text: this.$t('config.daemon.messagings.websocket.form.tlsModes.intermediate').toString()
		},
		{
			value: 'modern',
			text: this.$t('config.daemon.messagings.websocket.form.tlsModes.modern').toString()
		},
		{
			value: 'old',
			text: this.$t('config.daemon.messagings.websocket.form.tlsModes.old').toString()
		},
	];

	/**
	 * @property {string} instance WebSocket interface instance name
	 */
	@Prop({required: false, default: ''}) instance!: string;

	/**
	 * @var {string} pageTitle Page title
	 */
	get pageTitle(): string {
		return this.$route.path === '/config/daemon/messagings/websocket/add' ?
			this.$t('config.daemon.messagings.websocket.interface.add').toString() : this.$t('config.daemon.messagings.websocket.interface.edit').toString();
	}

	/**
	 * @var {string} submitButton Button text
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
