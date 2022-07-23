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
		<h1>{{ pageTitle }}</h1>
		<v-card>
			<v-card-text>
				<ValidationObserver v-slot='{invalid}'>
					<v-form>
						<legend>{{ $t('config.daemon.messagings.mqtt.legend') }}</legend>
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
							<v-col md='6'>
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
							<v-col md='6'>
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
							<v-col md='6'>
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
							<v-col md='6'>
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
							<v-col md='6'>
								<v-text-field
									v-model='configuration.User'
									:label='$t("config.daemon.messagings.mqtt.form.User")'
								/>
							</v-col>
							<v-col md='6'>
								<v-text-field
									v-model='configuration.Password'
									:label='$t("forms.fields.password")'
								/>
							</v-col>
							<v-col md='6'>
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
							<v-col md='6'>
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
							<v-col md='6'>
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
							<v-col md='6'>
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
							<v-col md='6'>
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
							<v-col md='6'>
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
							<v-col md='6'>
								<v-checkbox
									v-model='configuration.acceptAsyncMsg'
									:label='$t("config.daemon.messagings.acceptAsyncMsg")'
								/>
							</v-col>
						</v-row>
						<v-switch
							v-model='configuration.EnabledSSL'
							:label='$t("config.daemon.messagings.tlsTitle")'
							color='primary'
							inset
							dense
						/>
						<v-row v-if='configuration.EnabledSSL'>
							<v-col md='6'>
								<v-text-field
									v-model='configuration.TrustStore'
									:label='$t("config.daemon.messagings.mqtt.form.TrustStore")'
								/>
							</v-col>
							<v-col md='6'>
								<v-text-field
									v-model='configuration.KeyStore'
									:label='$t("forms.fields.certificate")'
								/>
							</v-col>
							<v-col md='6'>
								<v-text-field
									v-model='configuration.PrivateKey'
									:label='$t("forms.fields.privateKey")'
								/>
							</v-col>
							<v-col md='6'>
								<v-text-field
									v-model='configuration.PrivateKeyPassword'
									:type='passwordVisible ? "text" : "password"'
									:label='$t("config.daemon.messagings.mqtt.form.PrivateKeyPassword")'
									:append-icon='passwordVisible ? "mdi-eye" : "mdi-eye-off"'
									@click:append='passwordVisible = !passwordVisible'
								/>
							</v-col>
							<v-col md='6'>
								<v-text-field
									v-model='configuration.EnabledCipherSuites'
									:label='$t("config.daemon.messagings.mqtt.form.EnabledCipherSuites")'
								/>
							</v-col>
							<v-col md='6'>
								<v-checkbox
									v-model='configuration.EnableServerCertAuth'
									:label='$t("config.daemon.messagings.mqtt.form.EnableServerCertAuth")'
								/>
							</v-col>
						</v-row>
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

import {between, integer, min_value, required} from 'vee-validate/dist/rules';
import {extendedErrorToast} from '@/helpers/errorToast';
import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {IMqttInstance} from '@/interfaces/Config/Messaging';
import {IOption} from '@/interfaces/coreui';
import {MetaInfo} from 'vue-meta';

@Component({
	components: {
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
		EnabledSSL: false,
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
	 * @var {bool} passwordVisible Controls visibility of password field
	 */
	private passwordVisible = false;

	/**
	 * @property {string} instance MQTT messaging component instance name
	 */
	@Prop({required: false, default: ''}) instance!: string;

	/**
	 * @var {string} pageTitle Page title
	 */
	get pageTitle(): string {
		return this.$t(`config.daemon.messagings.mqtt.${this.$route.path === '/config/daemon/messagings/mqtt/add' ? 'add' : 'edit'}`).toString();
	}

	/**
	 * @var {string} submitButton Button text
	 */
	get submitButton(): string {
		return this.$t(`forms.${this.$route.path === '/config/daemon/messagings/mqtt/add' ? 'add' : 'edit'}`).toString();
	}

	/**
	 * @var {Array<IOption>} persistenceOptions Persistence select options
	 */
	get persistenceOptions(): Array<IOption> {
		const options = [0, 1, 2];
		return options.map((option) => {
			return {
				value: option,
				text: this.$t(`config.daemon.messagings.mqtt.form.Persistences.${option}`).toString()
			};
		});
	}

	/**
	 * Computes array of CoreUI qos select options
	 * @returns {Array<IOption>} QoS select options
	 */
	get qosOptions(): Array<IOption> {
		const options = [0, 1, 2];
		return options.map((option) => {
			return {
				value: option,
				text: this.$t(`config.daemon.messagings.mqtt.form.QoSes.${option.toString()}`).toString(),
			};
		});
	}

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('min', min_value);
		extend('required', required);
		extend('instance', (item: string) => {
			const re = RegExp(/^[^&]+$/);
			return re.test(item);
		});
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
				.then(() => this.successfulSave())
				.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.messagings.mqtt.messages.editFailed', {instance: this.instance}));
		} else {
			DaemonConfigurationService.createInstance(this.componentName, this.configuration)
				.then(() => this.successfulSave())
				.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.messagings.mqtt.messages.addFailed'));
		}
	}

	/**
	 * Handles successful REST API response
	 */
	private successfulSave(): void {
		this.$store.commit('spinner/HIDE');
		this.$toast.success(
			this.$t(
				`config.daemon.messagings.mqtt.messages.${this.$route.path === '/config/daemon/messagings/mqtt/add' ? 'add' : 'edit'}Success`,
				{instance: this.configuration.instance},
			).toString()
		);
		this.$router.push('/config/daemon/messagings/mqtt/');
	}
}
</script>
