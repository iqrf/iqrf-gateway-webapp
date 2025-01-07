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
								required: $t("config.daemon.messagings.mqtt.errors.instance"),
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
						<v-row>
							<v-col cols='12' md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: $t("config.daemon.messagings.mqtt.errors.BrokerAddr"),
									}'
								>
									<v-text-field
										v-model='configuration.BrokerAddr'
										:label='$t("config.daemon.messagings.mqtt.form.BrokerAddr")'
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
										required: $t("forms.errors.clientId"),
									}'
								>
									<v-text-field
										v-model='configuration.ClientId'
										:label='$t("forms.fields.clientId")'
										:success='touched ? valid : null'
										:error-messages='errors'
									/>
								</ValidationProvider>
							</v-col>
						</v-row>
						<v-row>
							<v-col cols='12' md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: $t("forms.errors.requestTopic"),
									}'
								>
									<v-text-field
										v-model='configuration.TopicRequest'
										:label='$t("forms.fields.requestTopic")'
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
										required: $t("forms.errors.responseTopic"),
									}'
								>
									<v-text-field
										v-model='configuration.TopicResponse'
										:label='$t("forms.fields.responseTopic")'
										:success='touched ? valid : null'
										:error-messages='errors'
									/>
								</ValidationProvider>
							</v-col>
						</v-row>
						<v-row>
							<v-col cols='12' md='6'>
								<v-text-field
									v-model='configuration.User'
									:label='$t("config.daemon.messagings.mqtt.form.User")'
								/>
							</v-col>
							<v-col cols='12' md='6'>
								<PasswordInput
									v-model='configuration.Password'
									:label='$t("forms.fields.password")'
								/>
							</v-col>
						</v-row>
						<v-row>
							<v-col cols='12' md='6'>
								<ValidationProvider
									v-slot='{valid, touched, errors}'
									rules='required'
									:custom-messages='{
										required: $t("config.daemon.messagings.mqtt.errors.QoS"),
									}'
								>
									<v-select
										v-model='configuration.Qos'
										:label='$t("config.daemon.messagings.mqtt.form.QoS")'
										:success='touched ? valid : null'
										:error-messages='errors'
										:placeholder='$t("config.daemon.messagings.mqtt.form.QoS")'
										:items='qosOptions'
									/>
									<p>{{ $t(`config.daemon.messagings.mqtt.messages.qos.${configuration.Qos}`) }}</p>
								</ValidationProvider>
							</v-col>
							<v-col cols='12' md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: $t("config.daemon.messagings.mqtt.errors.Persistence"),
									}'
								>
									<v-select
										v-model='configuration.Persistence'
										:label='$t("config.daemon.messagings.mqtt.form.Persistence")'
										:success='touched ? valid : null'
										:error-messages='errors'
										:placeholder='$t("config.daemon.messagings.mqtt.form.Persistence")'
										:items='persistenceOptions'
									/>
									<p>{{ $t(`config.daemon.messagings.mqtt.messages.persistence.${configuration.Persistence}`) }}</p>
								</ValidationProvider>
							</v-col>
						</v-row>
						<v-row>
							<v-col cols='12' md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='integer|min:0'
									:custom-messages='{
										integer: $t("config.daemon.messagings.mqtt.errors.KeepAliveInterval"),
										min: $t("config.daemon.messagings.mqtt.errors.KeepAliveInterval"),
									}'
								>
									<v-text-field
										v-model.number='configuration.KeepAliveInterval'
										:label='$t("config.daemon.messagings.mqtt.form.KeepAliveInterval")'
										:success='touched ? valid : null'
										:error-messages='errors'
										type='number'
										min='0'
									/>
								</ValidationProvider>
							</v-col>
							<v-col cols='12' md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='integer|min:0'
									:custom-messages='{
										integer: $t("config.daemon.messagings.mqtt.errors.ConnectTimeout"),
										min: $t("config.daemon.messagings.mqtt.errors.ConnectTimeout"),
									}'
								>
									<v-text-field
										v-model.number='configuration.ConnectTimeout'
										:label='$t("config.daemon.messagings.mqtt.form.ConnectTimeout")'
										:success='touched ? valid : null'
										:error-messages='errors'
										type='number'
										min='0'
									/>
								</ValidationProvider>
							</v-col>
						</v-row>
						<v-row>
							<v-col cols='12' md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									:rules='"integer|between:0," + configuration.MaxReconnect'
									:custom-messages='{
										between: $t("config.daemon.messagings.mqtt.errors.MinReconnect"),
										integer: $t("config.daemon.messagings.mqtt.errors.MinReconnect"),
									}'
								>
									<v-text-field
										v-model.number='configuration.MinReconnect'
										:label='$t("config.daemon.messagings.mqtt.form.MinReconnect")'
										:success='touched ? valid : null'
										:error-messages='errors'
										type='number'
										:max='configuration.MaxReconnect'
										min='0'
									/>
								</ValidationProvider>
							</v-col>
							<v-col cols='12' md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									:rules='"integer|min:" + configuration.MinReconnect'
									:custom-messages='{
										integer: $t("config.daemon.messagings.mqtt.errors.MaxReconnect"),
										min: $t("config.daemon.messagings.mqtt.errors.MaxReconnect"),
									}'
								>
									<v-text-field
										v-model.number='configuration.MaxReconnect'
										:label='$t("config.daemon.messagings.mqtt.form.MaxReconnect")'
										:success='touched ? valid : null'
										:error-messages='errors'
										type='number'
										:min='configuration.MinReconnect'
									/>
								</ValidationProvider>
							</v-col>
						</v-row>
						<v-checkbox
							v-model='configuration.acceptAsyncMsg'
							:label='$t("config.daemon.messagings.acceptAsyncMsg")'
							dense
						/>
						<div v-if='hasTls'>
							<v-row>
								<v-col cols='12' md='6'>
									<v-text-field
										v-model='configuration.TrustStore'
										:label='$t("config.daemon.messagings.mqtt.form.TrustStore")'
									/>
								</v-col>
								<v-col cols='12' md='6'>
									<v-text-field
										v-model='configuration.KeyStore'
										:label='$t("forms.fields.certificate")'
									/>
								</v-col>
							</v-row>
							<v-row>
								<v-col cols='12' md='6'>
									<v-text-field
										v-model='configuration.PrivateKey'
										:label='$t("forms.fields.privateKey")'
									/>
								</v-col>
								<v-col cols='12' md='6'>
									<PasswordInput
										v-model='configuration.PrivateKeyPassword'
										:label='$t("config.daemon.messagings.mqtt.form.PrivateKeyPassword")'
									/>
								</v-col>
							</v-row>
							<v-row>
								<v-col cols='12' md='6'>
									<v-text-field
										v-model='configuration.EnabledCipherSuites'
										:label='$t("config.daemon.messagings.mqtt.form.EnabledCipherSuites")'
										dense
									/>
								</v-col>
								<v-col cols='12' md='6'>
									<v-checkbox
										v-model='configuration.EnableServerCertAuth'
										:label='$t("config.daemon.messagings.mqtt.form.EnableServerCertAuth")'
										dense
									/>
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
import PasswordInput from '@/components/Core/PasswordInput.vue';

import {between, integer, min_value, required} from 'vee-validate/dist/rules';
import {daemonInstanceName} from '@/helpers/validators';
import {extendedErrorToast} from '@/helpers/errorToast';
import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {IMqttInstance} from '@/interfaces/Config/Messaging';
import {ISelectItem} from '@/interfaces/Vuetify';
import {MetaInfo} from 'vue-meta';

@Component({
	components: {
		PasswordInput,
		ValidationObserver,
		ValidationProvider,
	},
	metaInfo(): MetaInfo {
		return {
			title: (this as unknown as MqttMessagingForm).pageTitle
		};
	}
})

/**
 * Daemon MQTT messaging component configuration form
 */
export default class MqttMessagingForm extends Vue {
	/**
	 * @constant {string} componentName MQTT messaging component name
	 */
	private componentName = 'iqrf::MqttMessaging';

	/**
	 * @var {IMqttInstance} configuration MQTT messaging component instance configuration
	 */
	private configuration: IMqttInstance = {
		component: '',
		instance: '',
		BrokerAddr: '',
		ClientId: '',
		Persistence: 1,
		Qos: 1,
		TopicRequest: '',
		TopicResponse: '',
		User: '',
		Password: '',
		KeepAliveInterval: 20,
		ConnectTimeout: 5,
		MinReconnect: 1,
		MaxReconnect: 64,
		TrustStore: '',
		KeyStore: '',
		PrivateKey: '',
		PrivateKeyPassword: '',
		EnabledCipherSuites: '',
		EnableServerCertAuth: false,
		acceptAsyncMsg: false,
	};

	/**
	 * @property {string} instance MQTT messaging component instance name
	 */
	@Prop({required: false, default: ''}) instance!: string;

	/**
	 * @var {string} pageTitle Page title
	 */
	get pageTitle(): string {
		return this.$route.path === '/config/daemon/messagings/mqtt/add' ?
			this.$t('config.daemon.messagings.mqtt.add').toString() : this.$t('config.daemon.messagings.mqtt.edit').toString();
	}

	/**
	 * @var {string} submitButton Button text
	 */
	get submitButton(): string {
		return this.$route.path === '/config/daemon/messagings/mqtt/add' ?
			this.$t('forms.add').toString() : this.$t('forms.edit').toString();
	}

	/**
	 * @var {Array<ISelectItem>} persistenceOptions Persistence select options
	 */
	get persistenceOptions(): Array<ISelectItem> {
		const options = [0, 1, 2];
		return options.map((option) => {
			return {
				value: option,
				text: this.$t(`config.daemon.messagings.mqtt.form.Persistences.${option}`).toString()
			};
		});
	}

	/**
	 * @var {Array<ISelectItem>} qosOptions QoS select options
	 */
	get qosOptions(): Array<ISelectItem> {
		const options = [0, 1, 2];
		return options.map((option) => {
			return {
				value: option,
				text: this.$t(`config.daemon.messagings.mqtt.form.QoSes.${option.toString()}`).toString(),
			};
		});
	}

	/**
	 * Checks if MQTT messaging instance uses TLS
	 */
	get hasTls(): boolean {
		return this.configuration.BrokerAddr.startsWith('ssl://') ||
			this.configuration.BrokerAddr.startsWith('mqtts://') ||
			this.configuration.BrokerAddr.startsWith('wss://');
	}

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('min', min_value);
		extend('required', required);
		extend('instance', daemonInstanceName);
	}

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		if (this.instance !== '') {
			this.getConfig();
		}
	}

	/**
	 * Retrieves configuration of the MQTT messaging component instance
	 */
	private getConfig(): void {
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.getInstance(this.componentName, this.instance)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.configuration = response.data;
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.daemon.messagings.mqtt.messages.fetchFailed', {instance: this.instance});
				this.$router.push('/config/daemon/messagings/mqtt');
			});
	}

	/**
	 * Saves new or updates existing configuration of MQTT messaging component instance
	 */
	private saveConfig(): void {
		this.$store.commit('spinner/SHOW');
		if (this.instance !== '') {
			DaemonConfigurationService.updateInstance(this.componentName, this.instance, this.configuration)
				.then(this.handleSuccess)
				.catch(this.handleFailure);
		} else {
			DaemonConfigurationService.createInstance(this.componentName, this.configuration)
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
			this.$t('config.daemon.messagings.mqtt.messages.saveSuccess', {instance: this.configuration.instance}).toString()
		);
		this.$router.push('/config/daemon/messagings/mqtt');
	}

	/**
	 * Handles REST API failure
	 */
	private handleFailure(error: AxiosError): void {
		extendedErrorToast(error, 'config.daemon.messagings.mqtt.messages.savefailed', {instance: this.configuration.instance});
	}
}
</script>
