<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

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
		<h1 v-if='$route.path === "/config/daemon/messagings/mqtt/add"'>
			{{ $t('config.daemon.messagings.mqtt.add') }}
		</h1>
		<h1 v-else>
			{{ $t('config.daemon.messagings.mqtt.edit') }}
		</h1>
		<CCard>
			<CCardBody>
				<ValidationObserver v-slot='{invalid}'>
					<CForm>
						<legend>{{ $t('config.daemon.messagings.mqtt.legend') }}</legend>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required|instance'
							:custom-messages='{
								required: $t("config.daemon.messagings.mqtt.errors.instance"),
								instance: $t("config.daemon.messagings.instanceInvalid"),
							}'
						>
							<CInput
								v-model='configuration.instance'
								:label='$t("forms.fields.instanceName")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='errors.join(", ")'
							/>
						</ValidationProvider>
						<CRow>
							<CCol md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: $t("config.daemon.messagings.mqtt.errors.BrokerAddr"),
									}'
								>
									<CInput
										v-model='configuration.BrokerAddr'
										:label='$t("config.daemon.messagings.mqtt.form.BrokerAddr")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
									/>
								</ValidationProvider>
							</CCol>
							<CCol md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: $t("forms.errors.clientId"),
									}'
								>
									<CInput
										v-model='configuration.ClientId'
										:label='$t("forms.fields.clientId")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
									/>
								</ValidationProvider>
							</CCol>
							<CCol md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: $t("forms.errors.requestTopic"),
									}'
								>
									<CInput
										v-model='configuration.TopicRequest'
										:label='$t("forms.fields.requestTopic")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
									/>
								</ValidationProvider>
							</CCol>
							<CCol md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: $t("forms.errors.responseTopic"),
									}'
								>
									<CInput
										v-model='configuration.TopicResponse'
										:label='$t("forms.fields.responseTopic")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
									/>
								</ValidationProvider>
							</CCol>
							<CCol md='6'>
								<CInput
									v-model='configuration.User'
									:label='$t("config.daemon.messagings.mqtt.form.User")'
								/>
							</CCol>
							<CCol md='6'>
								<CInput
									v-model='configuration.Password'
									:label='$t("forms.fields.password")'
								/>
							</CCol>
							<CCol md='6'>
								<ValidationProvider
									v-slot='{valid, touched, errors}'
									rules='required'
									:custom-messages='{
										required: $t("config.daemon.messagings.mqtt.errors.QoS"),
									}'
								>
									<CSelect
										:value.sync='configuration.Qos'
										:label='$t("config.daemon.messagings.mqtt.form.QoS")'
										:description='$t(`config.daemon.messagings.mqtt.messages.qos.${configuration.Qos}`)'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
										:placeholder='$t("config.daemon.messagings.mqtt.form.QoS")'
										:options='qosOptions'
									/>
								</ValidationProvider>
							</CCol>
							<CCol md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: $t("config.daemon.messagings.mqtt.errors.Persistence"),
									}'
								>
									<CSelect
										:value.sync='configuration.Persistence'
										:label='$t("config.daemon.messagings.mqtt.form.Persistence")'
										:description='$t(`config.daemon.messagings.mqtt.messages.persistence.${configuration.Persistence}`)'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
										:placeholder='$t("config.daemon.messagings.mqtt.form.Persistence")'
										:options='persistenceOptions'
									/>
								</ValidationProvider>
							</CCol>
							<CCol md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='integer|min:0'
									:custom-messages='{
										integer: $t("config.daemon.messagings.mqtt.errors.KeepAliveInterval"),
										min: $t("config.daemon.messagings.mqtt.errors.KeepAliveInterval"),
									}'
								>
									<CInput
										v-model.number='configuration.KeepAliveInterval'
										:label='$t("config.daemon.messagings.mqtt.form.KeepAliveInterval")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
										type='number'
										min='0'
									/>
								</ValidationProvider>
							</CCol>
							<CCol md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='integer|min:0'
									:custom-messages='{
										integer: $t("config.daemon.messagings.mqtt.errors.ConnectTimeout"),
										min: $t("config.daemon.messagings.mqtt.errors.ConnectTimeout"),
									}'
								>
									<CInput
										v-model.number='configuration.ConnectTimeout'
										:label='$t("config.daemon.messagings.mqtt.form.ConnectTimeout")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
										type='number'
										min='0'
									/>
								</ValidationProvider>
							</CCol>
							<CCol md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									:rules='"integer|between:0," + configuration.MaxReconnect'
									:custom-messages='{
										between: $t("config.daemon.messagings.mqtt.errors.MinReconnect"),
										integer: $t("config.daemon.messagings.mqtt.errors.MinReconnect"),
									}'
								>
									<CInput
										v-model.number='configuration.MinReconnect'
										:label='$t("config.daemon.messagings.mqtt.form.MinReconnect")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
										type='number'
										:max='configuration.MaxReconnect'
										min='0'
									/>
								</ValidationProvider>
							</CCol>
							<CCol md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									:rules='"integer|min:" + configuration.MinReconnect'
									:custom-messages='{
										integer: $t("config.daemon.messagings.mqtt.errors.MaxReconnect"),
										min: $t("config.daemon.messagings.mqtt.errors.MaxReconnect"),
									}'
								>
									<CInput
										v-model.number='configuration.MaxReconnect'
										:label='$t("config.daemon.messagings.mqtt.form.MaxReconnect")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
										type='number'
										:min='configuration.MinReconnect'
									/>
								</ValidationProvider>
							</CCol>
							<CCol md='6'>
								<CInputCheckbox
									:checked.sync='configuration.acceptAsyncMsg'
									:label='$t("config.daemon.messagings.acceptAsyncMsg")'
								/>
							</CCol>
						</CRow>
						<CRow v-if='hasTls'>
							<CCol md='6'>
								<CInput
									v-model='configuration.TrustStore'
									:label='$t("config.daemon.messagings.mqtt.form.TrustStore")'
								/>
							</CCol>
							<CCol md='6'>
								<CInput
									v-model='configuration.KeyStore'
									:label='$t("forms.fields.certificate")'
								/>
							</CCol>
							<CCol md='6'>
								<CInput
									v-model='configuration.PrivateKey'
									:label='$t("forms.fields.privateKey")'
								/>
							</CCol>
							<CCol md='6'>
								<PasswordInput
									v-model='configuration.PrivateKeyPassword'
									:label='$t("config.daemon.messagings.mqtt.form.PrivateKeyPassword").toString()'
								/>
							</CCol>
							<CCol md='6'>
								<CInput
									v-model='configuration.EnabledCipherSuites'
									:label='$t("config.daemon.messagings.mqtt.form.EnabledCipherSuites")'
								/>
							</CCol>
							<CCol md='6'>
								<CInputCheckbox
									:checked.sync='configuration.EnableServerCertAuth'
									:label='$t("config.daemon.messagings.mqtt.form.EnableServerCertAuth")'
								/>
							</CCol>
						</CRow>
						<CButton
							color='primary'
							:disabled='invalid'
							@click='saveConfig'
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
import {CButton, CCard, CCardBody, CCardHeader, CCol, CForm, CInput, CInputCheckbox, CRow, CSelect, CSwitch} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {between, integer, min_value, required} from 'vee-validate/dist/rules';

import PasswordInput from '@/components/Core/PasswordInput.vue';

import {extendedErrorToast} from '@/helpers/errorToast';
import {daemonInstanceName} from '@/helpers/validators';
import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {IOption} from '@/interfaces/Coreui';
import {MetaInfo} from 'vue-meta';
import {IMqttInstance} from '@/interfaces/Config/Messaging';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CCol,
		CForm,
		CInput,
		CInputCheckbox,
		CRow,
		CSelect,
		CSwitch,
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
	 * Computes page title depending on the action (add, edit)
	 * @returns {string} Page title
	 */
	get pageTitle(): string {
		return this.$route.path === '/config/daemon/messagings/mqtt/add' ?
			this.$t('config.daemon.messagings.mqtt.add').toString() : this.$t('config.daemon.messagings.mqtt.edit').toString();
	}

	/**
	 * Computes array of CoreUI persistence select options
	 * @returns {Array<IOption>} Persistence select options
	 */
	get persistenceOptions(): Array<IOption> {
		const options = [0, 1, 2];
		return options.map((option) => {
			return {
				value: option,
				label: this.$t(`config.daemon.messagings.mqtt.form.Persistences.${option}`).toString()
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
				label: this.$t(`config.daemon.messagings.mqtt.form.QoSes.${option.toString()}`).toString(),
			};
		});
	}

	/**
	 * Computes the text of form submit button depending on the action (add, edit)
	 * @returns {string} Button text
	 */
	get submitButton(): string {
		return this.$route.path === '/config/daemon/messagings/mqtt/add' ?
			this.$t('forms.add').toString() : this.$t('forms.edit').toString();
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
