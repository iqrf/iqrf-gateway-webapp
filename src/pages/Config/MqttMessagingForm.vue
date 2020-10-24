<template>
	<div>
		<h1 v-if='$route.path === "/config/mqtt/add"'>
			{{ $t('config.mqtt.add') }}
		</h1>
		<h1 v-else>
			{{ $t('config.mqtt.edit') }}
		</h1>
		<CCard body-wrapper>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm @submit.prevent='saveConfig'>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{required: "config.mqtt.form.messages.instance"}'
					>
						<CInput
							v-model='configuration.instance'
							:label='$t("config.mqtt.form.instance")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{required: "config.mqtt.form.messages.BrokerAddr"}'
					>
						<CInput
							v-model='configuration.BrokerAddr'
							:label='$t("config.mqtt.form.BrokerAddr")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{required: "config.mqtt.form.messages.ClientId"}'
					>
						<CInput
							v-model='configuration.ClientId'
							:label='$t("config.mqtt.form.ClientId")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='integer|required'
						:custom-messages='{
							integer: "config.mqtt.form.messages.Persistence",
							required: "config.mqtt.form.messages.Persistence",
						}'
					>
						<CInput
							v-model.number='configuration.Persistence'
							:label='$t("config.mqtt.form.Persistence")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
							type='number'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ valid, touched, errors }'
						rules='required'
						:custom-messages='{
							required: "config.mqtt.form.messages.QoS",
						}'
					>
						<CSelect
							:value.sync='configuration.Qos'
							:label='$t("config.mqtt.form.QoS")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
							:placeholder='$t("config.mqtt.form.messages.QoS")'
							:options='qosOptions'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{required: "config.mqtt.form.messages.TopicRequest"}'
					>
						<CInput
							v-model='configuration.TopicRequest'
							:label='$t("config.mqtt.form.TopicRequest")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{required: "config.mqtt.form.messages.TopicResponse"}'
					>
						<CInput
							v-model='configuration.TopicResponse'
							:label='$t("config.mqtt.form.TopicResponse")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<CInput
						v-model='configuration.User'
						:label='$t("config.mqtt.form.User")'
					/>
					<CInput
						v-model='configuration.Password'
						:label='$t("config.mqtt.form.Password")'
					/>
					<CInputCheckbox
						:checked.sync='configuration.EnabledSSL'
						:label='$t("config.mqtt.form.EnabledSSL")'
					/>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='integer|min:0'
						:custom-messages='{
							integer: "config.mqtt.form.messages.KeepAliveInterval",
							min: "config.mqtt.form.messages.KeepAliveInterval",
						}'
					>
						<CInput
							v-model.number='configuration.KeepAliveInterval'
							:label='$t("config.mqtt.form.KeepAliveInterval")'
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
							integer: "config.mqtt.form.messages.ConnectTimeout",
							min: "config.mqtt.form.messages.ConnectTimeout",
						}'
					>
						<CInput
							v-model.number='configuration.ConnectTimeout'
							:label='$t("config.mqtt.form.ConnectTimeout")'
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
							between: "config.mqtt.form.messages.MinReconnect",
							integer: "config.mqtt.form.messages.MinReconnect",
						}'
					>
						<CInput
							v-model.number='configuration.MinReconnect'
							:label='$t("config.mqtt.form.MinReconnect")'
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
							integer: "config.mqtt.form.messages.MaxReconnect",
							min: "config.mqtt.form.messages.MaxReconnect",
						}'
					>
						<CInput
							v-model.number='configuration.MaxReconnect'
							:label='$t("config.mqtt.form.MaxReconnect")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
							type='number'
							:min='configuration.MinReconnect'
						/>
					</ValidationProvider>
					<CInput
						v-model='configuration.TrustStore'
						:label='$t("config.mqtt.form.TrustStore")'
					/>
					<CInput
						v-model='configuration.KeyStore'
						:label='$t("config.mqtt.form.KeyStore")'
					/>
					<CInput
						v-model='configuration.PrivateKey'
						:label='$t("config.mqtt.form.PrivateKey")'
					/>
					<CInput
						v-model='configuration.PrivateKeyPassword'
						:label='$t("config.mqtt.form.PrivateKeyPassword")'
					/>
					<CInput
						v-model='configuration.EnabledCipherSuites'
						:label='$t("config.mqtt.form.EnabledCipherSuites")'
					/>
					<CInputCheckbox
						:checked.sync='configuration.EnableServerCertAuth'
						:label='$t("config.mqtt.form.EnableServerCertAuth")'
					/>
					<CInputCheckbox
						:checked.sync='configuration.acceptAsyncMsg'
						:label='$t("config.mqtt.form.acceptAsyncMsg")'
					/>
					<CButton type='submit' color='primary' :disabled='invalid'>
						{{ submitButton }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCard>
	</div>
</template>

<script>
import {CButton, CCard, CForm, CInput, CInputCheckbox} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import {between, integer, min_value, required} from 'vee-validate/dist/rules';

export default {
	name: 'MqttMessagingForm',
	components: {
		CButton,
		CCard,
		CForm,
		CInput,
		CInputCheckbox,
		ValidationObserver,
		ValidationProvider,
	},
	props: {
		instance: {
			type: String,
			required: false,
			default: null,
		},
	},
	data() {
		return {
			componentName: 'iqrf::MqttMessaging',
			configuration: {
				instance: null,
				BrokerAddr: null,
				ClientId: null,
				Persistence: 1,
				Qos: 1,
				TopicRequest: null,
				TopicResponse: null,
				User: null,
				Password: null,
				EnabledSSL: false,
				KeepAliveInterval: 20,
				ConnectTimeout: 5,
				MinReconnect: 1,
				MaxReconnect: 64,
				TrustStore: null,
				KeyStore: null,
				PrivateKey: null,
				PrivateKeyPassword: null,
				EnabledCipherSuites: null,
				EnableServerCertAuth: false,
				acceptAsyncMsg: false,
			}
		};
	},
	computed: {
		qosOptions() {
			const options = [0, 1, 2];
			return options.map((option) => {
				return {
					value: option,
					label: this.$t('config.mqtt.form.QoSes.' + option).toString(),
				};
			});
		},
		submitButton() {
			return this.$route.path === '/config/mqtt/add' ?
				this.$t('forms.add') : this.$t('forms.save');
		},
	},
	created() {
		extend('between', between);
		extend('integer', integer);
		extend('min', min_value);
		extend('required', required);
		if (this.instance !== null) {
			this.getConfig();
		}
	},
	methods: {
		getConfig() {
			this.$store.commit('spinner/SHOW');
			DaemonConfigurationService.getInstance(this.componentName, this.instance)
				.then((response) => {
					this.$store.commit('spinner/HIDE');
					this.configuration = response.data;
				})
				.catch((error) => {
					this.$store.commit('spinner/HIDE');
					this.$router.push('/config/mqtt/');
					FormErrorHandler.configError(error);
				});
		},
		saveConfig() {
			this.$store.commit('spinner/SHOW');
			if (this.instance !== null) {
				DaemonConfigurationService.updateInstance(this.componentName, this.instance, this.configuration)
					.then(() => this.successfulSave())
					.catch((error) => FormErrorHandler.configError(error));
			} else {
				DaemonConfigurationService.createInstance(this.componentName, this.configuration)
					.then(() => this.successfulSave())
					.catch((error) => FormErrorHandler.configError(error));
			}
		},
		successfulSave() {
			this.$store.commit('spinner/HIDE');
			if (this.$route.path === '/config/mqtt/add') {
				this.$toast.success(
					this.$t('config.mqtt.messages.add.success', {instance: this.configuration.instance})
						.toString()
				);
			} else {
				this.$toast.success(
					this.$t('config.mqtt.messages.edit.success', {instance: this.configuration.instance})
						.toString()
				);
			}
			this.$router.push('/config/mqtt/');
		},
	},
	metaInfo() {
		return {
			title: this.$route.path === '/config/mqtt/add' ?
				'config.mqtt.add' : 'config.mqtt.edit',
		};
	},
};
</script>
