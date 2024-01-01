<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
								required: $t("config.daemon.messagings.websocket.errors.messagingInstance"),
								instance: $t("config.daemon.messagings.instanceInvalid"),
							}'
						>
							<v-text-field
								v-model='configuration.instance'
								:label='$t("forms.fields.instanceName")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<v-checkbox
							v-model='configuration.acceptAsyncMsg'
							:label='$t("config.daemon.messagings.acceptAsyncMsg")'
							dense
						/>
						<fieldset>
							<h5>{{ $t('config.daemon.messagings.websocket.form.requiredInterfaces') }}</h5>
							<v-row
								v-for='(iface, idx) of configuration.RequiredInterfaces'
								:key='idx'
							>
								<v-col cols='12' md='6'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='required'
										:custom-messages='{
											required: $t("config.daemon.messagings.websocket.errors.interfaceName"),
										}'
									>
										<v-select
											v-model='iface.name'
											:label='$t("config.daemon.messagings.websocket.form.requiredInterface.name")'
											:placeholder='$t("config.daemon.messagings.websocket.errors.interfaceName")'
											:items='[
												{value: "shape::IWebsocketService", text: "shape::IWebsocketService"}
											]'
											:success='touched ? valid : null'
											:error-messages='errors'
										/>
									</ValidationProvider>
								</v-col>
								<v-col cols='12' md='6'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='required'
										:custom-messages='{
											required: $t("config.daemon.messagings.websocket.errors.interfaceName"),
										}'
									>
										<v-select
											v-model='iface.instance'
											:label='$t("config.daemon.messagings.websocket.form.requiredInterface.instance")'
											:placeholder='$t("config.daemon.messagings.websocket.errors.interfaceInstance")'
											:items='services'
											:success='touched ? valid : null'
											:error-messages='errors'
										>
											<template #append-outer>
												<v-btn
													v-if='configuration.RequiredInterfaces.length > 1'
													color='error'
													small
													@click='removeInterface(idx)'
												>
													<v-icon>
														mdi-delete-outline
													</v-icon>
												</v-btn>
												<v-btn
													v-if='idx === (configuration.RequiredInterfaces.length - 1)'
													:class='configuration.RequiredInterfaces.length > 1 ? "ml-1": ""'
													color='success'
													small
													@click='addInterface'
												>
													<v-icon>
														mdi-plus
													</v-icon>
												</v-btn>
											</template>
										</v-select>
									</ValidationProvider>
								</v-col>
							</v-row>
						</fieldset>
						<v-btn
							color='primary'
							:disabled='invalid'
							@click='saveInstance'
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

import {daemonInstanceName} from '@/helpers/validators';
import {extendedErrorToast} from '@/helpers/errorToast';
import {required} from 'vee-validate/dist/rules';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {MetaInfo} from 'vue-meta';
import {ModalInstance, IWsService} from '@/interfaces/Config/Messaging';
import {RequiredInterface} from '@/interfaces/Config/RequiredInterfaces';


interface ServiceInstance {
	text: string
	value: string
}

interface LocalRequiredInterface {
	instance: string
	name: string
}

interface LocalWsMessaging {
	component: string
	instance: string
	acceptAsyncMsg: boolean
	RequiredInterfaces: Array<LocalRequiredInterface>
}

@Component({
	components: {
		ValidationObserver,
		ValidationProvider,
	},
	metaInfo(): MetaInfo {
		return {
			title: (this as unknown as WebsocketMessagingForm).pageTitle
		};
	},
})

/**
 * Daemon WebSocket messaging component configuration form
 */
export default class WebsocketMessagingForm extends Vue {
	/**
	 * @constant {ModalInstance} componentNames Names of WebSocket messaging and service components
	 */
	private componentNames: ModalInstance = {
		messaging: 'iqrf::WebsocketMessaging',
		service: 'shape::WebsocketCppService',
	};

	/**
	 * @var {LocalWsMessaging} configuration WebSocket messaging component configuration
	 */
	private configuration: LocalWsMessaging = {
		component: '',
		instance: '',
		acceptAsyncMsg: false,
		RequiredInterfaces: [{instance: '', name: ''}],
	};

	/**
	 * @var {Array<ServiceInstance>} services Array of WebSocket service component instances
	 */
	private services: Array<ServiceInstance> = [];

	/**
	 * @property {string} instance Name of WebSocket messaging component instance
	 */
	@Prop({required: false, default: ''}) instance!: string;

	/**
	 * @var {string} pageTitle Page title
	 */
	get pageTitle(): string {
		return this.$route.path === '/config/daemon/messagings/websocket/add-messaging' ?
			this.$t('config.daemon.messagings.websocket.messaging.add').toString() : this.$t('config.daemon.messagings.websocket.messaging.edit').toString();
	}

	/**
	 * @var {string} submitButton Button text
	 */
	get submitButton(): string {
		return this.$route.path === '/config/daemon/messagings/websocket/add-messaging' ?
			this.$t('forms.add').toString() : this.$t('forms.edit').toString();
	}

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
		extend('instance', daemonInstanceName);
	}

	/**
	 * Retrieves component instance configuration
	 */
	mounted(): void {
		if (this.instance) {
			this.getConfig();
		} else {
			this.getServices();
		}
	}

	/**
	 * Adds a new RequiredInterface object to array of required interfaces
	 */
	private addInterface(): void  {
		this.configuration.RequiredInterfaces.push({instance: '', name: ''});
	}

	/**
	 * Removes a RequiredInterface instance specified by index
	 * @param {number} index Index of RequiredInterface instance
	 */
	private removeInterface(index: number) {
		this.configuration.RequiredInterfaces.splice(index, 1);
	}

	/**
	 * Retrieves configuration of WebSocket messaging component instance and service component
	 */
	private getConfig(): void {
		this.$store.commit('spinner/SHOW');
		Promise.all([
			DaemonConfigurationService.getInstance(this.componentNames.messaging, this.instance),
			DaemonConfigurationService.getComponent(this.componentNames.service),
		])
			.then((responses: Array<AxiosResponse>) => {
				this.$store.commit('spinner/HIDE');
				const interfaces: Array<LocalRequiredInterface> = [];
				const configuration = responses[0].data;
				configuration.RequiredInterfaces.forEach((item: RequiredInterface) => {
					interfaces.push({name: item.name, instance: item.target.instance});
				});
				configuration.RequiredInterfaces = interfaces;
				this.configuration = configuration;
				responses[1].data.instances.forEach((item: IWsService) => {
					this.services.push({value: item.instance, text: item.instance});
				});
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.daemon.messagings.websocket.messaging.messages.getFailed', {messaging: this.instance});
				this.$router.push('/config/daemon/messagings/websocket');
			});
	}

	/**
	 * Retrieves instances of WebSocket service component
	 */
	private getServices(): void {
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.getComponent(this.componentNames.service)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				response.data.instances.forEach((item: IWsService) => {
					this.services.push({value: item.instance, text: item.instance});
				});
			});
	}

	/**
	 * Saves new or updates existing configuration of WebSocket messaging component instance
	 */
	private saveInstance(): void {
		const instance = {
			component: this.configuration.component,
			instance: this.configuration.instance,
			acceptAsyncMsg: this.configuration.acceptAsyncMsg,
			RequiredInterfaces: []
		};
		const RequiredInterfaces: Array<RequiredInterface> = [];
		this.configuration.RequiredInterfaces.forEach((item: LocalRequiredInterface) => {
			RequiredInterfaces.push({name: item.name, target: {instance: item.instance}});
		});
		Object.assign(instance.RequiredInterfaces, RequiredInterfaces);
		this.$store.commit('spinner/SHOW');
		if (this.instance !== '') {
			DaemonConfigurationService.updateInstance(this.componentNames.messaging, this.instance, instance)
				.then(this.handleSuccess)
				.catch(this.handleFailure);
		} else {
			DaemonConfigurationService.createInstance(this.componentNames.messaging, instance)
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
			this.$t('config.daemon.messagings.websocket.messaging.messages.saveSuccess', {messaging: this.configuration.instance}).toString()
		);
		this.$router.push('/config/daemon/messagings/websocket');
	}

	/**
	 * Handles REST API failure
	 * @param {AxiosError} err Error response
	 */
	private handleFailure(err: AxiosError): void {
		extendedErrorToast(err, 'config.daemon.messagings.websocket.messaging.messages.saveFailed', {messaging: this.configuration.instance});
	}
}
</script>
