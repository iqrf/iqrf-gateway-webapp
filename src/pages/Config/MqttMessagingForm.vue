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
				<ValidationObserver v-slot='{ invalid }'>
					<CForm @submit.prevent='saveConfig'>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required'
							:custom-messages='{required: "config.daemon.messagings.mqtt.errors.instance"}'
						>
							<CInput
								v-model='configuration.instance'
								:label='$t("forms.fields.instanceName")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required'
							:custom-messages='{required: "config.daemon.messagings.mqtt.errors.BrokerAddr"}'
						>
							<CInput
								v-model='configuration.BrokerAddr'
								:label='$t("config.daemon.messagings.mqtt.form.BrokerAddr")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required'
							:custom-messages='{required: "forms.errors.clientId"}'
						>
							<CInput
								v-model='configuration.ClientId'
								:label='$t("forms.fields.clientId")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='integer|required'
							:custom-messages='{
								integer: "config.daemon.messagings.mqtt.errors.Persistence",
								required: "config.daemon.messagings.mqtt.errors.Persistence",
							}'
						>
							<CInput
								v-model.number='configuration.Persistence'
								:label='$t("config.daemon.messagings.mqtt.form.Persistence")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
								type='number'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ valid, touched, errors }'
							rules='required'
							:custom-messages='{
								required: "config.daemon.messagings.mqtt.errors.QoS",
							}'
						>
							<CSelect
								:value.sync='configuration.Qos'
								:label='$t("config.daemon.messagings.mqtt.form.QoS")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
								:placeholder='$t("config.daemon.messagings.mqtt.form.messages.QoS")'
								:options='qosOptions'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required'
							:custom-messages='{required: "forms.errors.requestTopic"}'
						>
							<CInput
								v-model='configuration.TopicRequest'
								:label='$t("forms.fields.requestTopic")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required'
							:custom-messages='{required: "forms.errors.responseTopic"}'
						>
							<CInput
								v-model='configuration.TopicResponse'
								:label='$t("forms.fields.responseTopic")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<CInput
							v-model='configuration.User'
							:label='$t("config.daemon.messagings.mqtt.form.User")'
						/>
						<CInput
							v-model='configuration.Password'
							:label='$t("forms.fields.password")'
						/>
						<CInputCheckbox
							:checked.sync='configuration.EnabledSSL'
							:label='$t("config.daemon.messagings.mqtt.form.EnabledSSL")'
						/>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='integer|min:0'
							:custom-messages='{
								integer: "config.daemon.messagings.mqtt.errors.KeepAliveInterval",
								min: "config.daemon.messagings.mqtt.errors.KeepAliveInterval",
							}'
						>
							<CInput
								v-model.number='configuration.KeepAliveInterval'
								:label='$t("config.daemon.messagings.mqtt.form.KeepAliveInterval")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
								type='number'
								min='0'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='integer|min:0'
							:custom-messages='{
								integer: "config.daemon.messagings.mqtt.errors.ConnectTimeout",
								min: "config.daemon.messagings.mqtt.errors.ConnectTimeout",
							}'
						>
							<CInput
								v-model.number='configuration.ConnectTimeout'
								:label='$t("config.daemon.messagings.mqtt.form.ConnectTimeout")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
								type='number'
								min='0'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							:rules='"integer|between:0," + configuration.MaxReconnect'
							:custom-messages='{
								between: "config.daemon.messagings.mqtt.errors.MinReconnect",
								integer: "config.daemon.messagings.mqtt.errors.MinReconnect",
							}'
						>
							<CInput
								v-model.number='configuration.MinReconnect'
								:label='$t("config.daemon.messagings.mqtt.form.MinReconnect")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
								type='number'
								:max='configuration.MaxReconnect'
								min='0'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							:rules='"integer|min:" + configuration.MinReconnect'
							:custom-messages='{
								integer: "config.daemon.messagings.mqtt.errors.MaxReconnect",
								min: "config.daemon.messagings.mqtt.errors.MaxReconnect",
							}'
						>
							<CInput
								v-model.number='configuration.MaxReconnect'
								:label='$t("config.daemon.messagings.mqtt.form.MaxReconnect")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
								type='number'
								:min='configuration.MinReconnect'
							/>
						</ValidationProvider>
						<CInput
							v-model='configuration.TrustStore'
							:label='$t("config.daemon.messagings.mqtt.form.TrustStore")'
						/>
						<CInput
							v-model='configuration.KeyStore'
							:label='$t("forms.fields.certificate")'
						/>
						<CInput
							v-model='configuration.PrivateKey'
							:label='$t("forms.fields.privateKey")'
						/>
						<CInput
							v-model='configuration.PrivateKeyPassword'
							:label='$t("config.daemon.messagings.mqtt.form.PrivateKeyPassword")'
						/>
						<CInput
							v-model='configuration.EnabledCipherSuites'
							:label='$t("config.daemon.messagings.mqtt.form.EnabledCipherSuites")'
						/>
						<CInputCheckbox
							:checked.sync='configuration.EnableServerCertAuth'
							:label='$t("config.daemon.messagings.mqtt.form.EnableServerCertAuth")'
						/>
						<CInputCheckbox
							:checked.sync='configuration.acceptAsyncMsg'
							:label='$t("config.daemon.messagings.acceptAsyncMsg")'
						/>
						<CButton type='submit' color='primary' :disabled='invalid'>
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
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput, CInputCheckbox} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import {between, integer, min_value, required} from 'vee-validate/dist/rules';
import { MqttInstance } from '../../interfaces/messagingInterfaces';
import { MetaInfo } from 'vue-meta';
import { IOption } from '../../interfaces/coreui';
import { AxiosError, AxiosResponse } from 'axios';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		CInputCheckbox,
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
	private componentName = 'iqrf::MqttMessaging'

	/**
	 * @var {MqttInstance} configuration MQTT messaging component instance configuration
	 */
	private configuration: MqttInstance = {
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
	}

	/**
	 * @property {string} instance MQTT messaging component instance name
	 */
	@Prop({required: false, default: ''}) instance!: string

	/**
	 * Computes page title depending on the action (add, edit)
	 * @returns {string} Page title
	 */
	get pageTitle(): string {
		return this.$route.path === '/config/daemon/messagings/mqtt/add' ?
			this.$t('config.daemon.messagings.mqtt.add').toString() : this.$t('config.daemon.messagings.mqtt.edit').toString();
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
				label: this.$t('config.daemon.messagings.mqtt.form.QoSes.' + option).toString(),
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
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('min', min_value);
		extend('required', required);
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
				this.$store.commit('spinner/HIDE');
				this.$router.push('/config/daemon/messagings/mqtt');
				FormErrorHandler.configError(error);
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
				.catch((error: AxiosError) => FormErrorHandler.configError(error));
		} else {
			DaemonConfigurationService.createInstance(this.componentName, this.configuration)
				.then(() => this.successfulSave())
				.catch((error: AxiosError) => FormErrorHandler.configError(error));
		}
	}

	/**
	 * Handles successful REST API response
	 */
	private successfulSave(): void {
		this.$store.commit('spinner/HIDE');
		if (this.$route.path === '/config/daemon/messagings/mqtt/add') {
			this.$toast.success(
				this.$t('config.daemon.messagings.mqtt.messages.addSuccess', {instance: this.configuration.instance})
					.toString()
			);
		} else {
			this.$toast.success(
				this.$t('config.daemon.messagings.mqtt.messages.editSuccess', {instance: this.configuration.instance})
					.toString()
			);
		}
		this.$router.push('/config/daemon/messagings/mqtt/');
	}
}
</script>
