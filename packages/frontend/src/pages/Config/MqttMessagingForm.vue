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
										required: $t("config.daemon.messagings.mqtt.errors.protocol"),
									}'
								>
									<CSelect
										:value.sync='protocol'
										:label='$t("forms.fields.protocol")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
										:options='protocolOptions'
									/>
								</ValidationProvider>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: $t("forms.errors.hostname"),
									}'
								>
									<CInput
										v-model='host'
										:label='$t("forms.fields.host")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
									/>
								</ValidationProvider>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									:rules='{
										between: {
											min: 1,
											max: 65535,
										},
										integer: true,
										required: true,
									}'
									:custom-messages='{
										between: $t("forms.errors.port.between"),
										integer: $t("forms.errors.integer"),
										required: $t("forms.errors.port.required"),
									}'
								>
									<CInput
										v-model.number='port'
										type='number'
										:label='$t("forms.fields.port")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
									>
										<template #append>
											<CButton
												size='sm'
												color='primary'
												@click='port = getDefaultProtocolPort(protocol)'
											>
												{{ $t('forms.setDefault') }}
											</CButton>
										</template>
									</CInput>
								</ValidationProvider>
								<CInput
									v-if='[MqttProtocols.WS, MqttProtocols.WSS].includes(protocol)'
									v-model='path'
									:label='$t("config.daemon.messagings.mqtt.form.path")'
								/>
								<CInput
									v-model='configuration.User'
									:label='$t("config.daemon.messagings.mqtt.form.User")'
								/>
								<CInput
									v-model='configuration.Password'
									:label='$t("forms.fields.password")'
								/>
								<CInputCheckbox
									:checked.sync='configuration.acceptAsyncMsg'
									:label='$t("config.daemon.messagings.acceptAsyncMsg")'
								/>
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
						</CRow>
						<CListGroup class='mb-3'>
							<CListGroupItem
								action
								class='d-flex justify-content-between'
								@click='showAdvanced = !showAdvanced'
							>
								<CIcon :content='cilCog' />
								<b>{{ $t('config.daemon.messagings.mqtt.form.advanced') }}</b>
								<CIcon :content='showAdvanced ? cilCaretTop : cilCaretBottom' />
							</CListGroupItem>
						</CListGroup>
						<CCollapse
							:show='showAdvanced'
							navbar
						>
							<CRow>
								<CCol md='6'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										:rules='{
											required: true,
											integer: true,
											min: 0,
										}'
										:custom-messages='{
											integer: $t("forms.errors.integer"),
											min: $t("config.daemon.messagings.mqtt.errors.KeepAliveInterval.minimum"),
											required: $t("config.daemon.messagings.mqtt.errors.KeepAliveInterval.required"),
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
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										:rules='{
											required: true,
											integer: true,
											min: 0
										}'
										:custom-messages='{
											integer: $t("forms.errors.integer"),
											min: $t("config.daemon.messagings.mqtt.errors.ConnectTimeout.minimum"),
											required: $t("config.daemon.messagings.mqtt.errors.ConnectTimeout.required"),
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
									<div v-if='[MqttProtocols.SSL, MqttProtocols.MQTTS, MqttProtocols.WSS].includes(protocol)'>
										<CInput
											v-model='configuration.TrustStore'
											:label='$t("config.daemon.messagings.mqtt.form.TrustStore")'
										/>
										<CInput
											v-model='configuration.PrivateKey'
											:label='$t("forms.fields.privateKey")'
										/>
										<CInput
											v-model='configuration.EnabledCipherSuites'
											:label='$t("config.daemon.messagings.mqtt.form.EnabledCipherSuites")'
										/>
									</div>
								</CCol>
								<CCol md='6'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										:rules='{
											integer: true,
											min: 1,
											max: configuration.MaxReconnect,
											required: true,
										}'
										:custom-messages='{
											integer: $t("forms.errors.integer"),
											max: $t("config.daemon.messagings.mqtt.errors.MinReconnect.maximum"),
											min: $t("config.daemon.messagings.mqtt.errors.MinReconnect.minimum"),
											required: $t("config.daemon.messagings.mqtt.errors.MinReconnect.required"),
										}'
									>
										<CInput
											v-model.number='configuration.MinReconnect'
											:label='$t("config.daemon.messagings.mqtt.form.MinReconnect")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='errors.join(", ")'
											type='number'
										/>
									</ValidationProvider>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										:rules='{
											integer: true,
											min: configuration.MinReconnect,
											required: true,
										}'
										:custom-messages='{
											required: $t("config.daemon.messagings.mqtt.errors.MaxReconnect.required"),
											integer: $t("forms.errors.integer"),
											min: $t("config.daemon.messagings.mqtt.errors.MaxReconnect.minimum"),
										}'
									>
										<CInput
											v-model.number='configuration.MaxReconnect'
											:label='$t("config.daemon.messagings.mqtt.form.MaxReconnect")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='errors.join(", ")'
											type='number'
										/>
									</ValidationProvider>
									<div v-if='[MqttProtocols.SSL, MqttProtocols.MQTTS, MqttProtocols.WSS].includes(protocol)'>
										<CInput
											v-model='configuration.KeyStore'
											:label='$t("forms.fields.certificate")'
										/>
										<PasswordInput
											v-model='configuration.PrivateKeyPassword'
											:label='$t("config.daemon.messagings.mqtt.form.PrivateKeyPassword").toString()'
										/>
										<CInputCheckbox
											:checked.sync='configuration.EnableServerCertAuth'
											:label='$t("config.daemon.messagings.mqtt.form.EnableServerCertAuth")'
										/>
									</div>
								</CCol>
							</CRow>
						</CCollapse>
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
import {
	CButton,
	CCard,
	CCardBody,
	CCardHeader,
	CCol,
	CCollapse,
	CForm,
	CInput,
	CInputCheckbox,
	CListGroup,
	CListGroupItem,
	CRow,
	CSelect,
	CSwitch,
} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {between, integer, max_value, min_value, required} from 'vee-validate/dist/rules';

import PasswordInput from '@/components/Core/PasswordInput.vue';

import {extendedErrorToast} from '@/helpers/errorToast';
import {daemonInstanceName} from '@/helpers/validators';
import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {IOption} from '@/interfaces/Coreui';
import {MetaInfo} from 'vue-meta';
import {IMqttInstance} from '@/interfaces/Config/Messaging';
import { MqttProtocols } from '@/enums/Config/Messagings';
import {
	cilCaretBottom,
	cilCaretTop,
	cilCog,
} from '@coreui/icons';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CCol,
		CCollapse,
		CForm,
		CInput,
		CInputCheckbox,
		CListGroup,
		CListGroupItem,
		CRow,
		CSelect,
		CSwitch,
		PasswordInput,
		ValidationObserver,
		ValidationProvider,
	},
	data: () => ({
		cilCaretBottom,
		cilCaretTop,
		cilCog,
	}),
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

	MqttProtocols = MqttProtocols;

	/**
	 * @property {string} instance MQTT messaging component instance name
	 */
	@Prop({required: false, default: ''}) instance!: string;

	/**
	 * @constant {RegExp} regexCapture Broker address regex
	 */
	private readonly regexCapture = new RegExp(/^(?<protocol>tcp|ssl|ws|wss|mqtt|mqtts):\/\/(?<host>[^/:]+)(:(?<port>\d+))?(\/(?<path>.*))?$/);

	/**
	 * @var {MqttProtocols} protocol Protocol
	 */
	private protocol: MqttProtocols = MqttProtocols.TCP;

	/**
	 * @var {string} host Broker host
	 */
	private host: string = 'localhost';

	/**
	 * @var {number|null} port Port number
	 */
	private port: number | null = null;

	/**
	 * @var {string} path Path
	 */
	private path: string = '';

	/**
	 * @var {boolean} showAdvanced Show advanced options
	 */
	private showAdvanced = false;

	/**
	 * Computes page title depending on the action (add, edit)
	 * @returns {string} Page title
	 */
	get pageTitle(): string {
		return this.$route.path === '/config/daemon/messagings/mqtt/add' ?
			this.$t('config.daemon.messagings.mqtt.add').toString() : this.$t('config.daemon.messagings.mqtt.edit').toString();
	}

	/**
	 * Computes array of protocol options
	 * @return {Array<IOption>} Protocol options
	 */
	get protocolOptions(): Array<IOption> {
		const options = [
			MqttProtocols.TCP,
			MqttProtocols.SSL,
			MqttProtocols.WS,
			MqttProtocols.WSS,
		];
		return options.map((option) => {
			return {
				value: option,
				label: this.$t(`config.daemon.messagings.mqtt.form.protocols.${option}`),
			};
		});
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
	 * Returns the default port for the given protocol
	 * @param {MqttProtocols} protocol Used MQTT protocol
	 * @return {number} Default port for the given protocol
	 */
	private getDefaultProtocolPort(protocol: MqttProtocols): number {
		switch (protocol) {
			case MqttProtocols.SSL:
				return 8_883;
			case MqttProtocols.WS:
				return 80;
			case MqttProtocols.WSS:
				return 443;
			case MqttProtocols.TCP:
			default:
				return 1_883;
		}
	}

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('min', min_value);
		extend('max', max_value);
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
				const parsed = this.configuration.BrokerAddr.match(this.regexCapture);
				if (parsed?.groups === undefined) {
					return;
				}
				let protocolVal = parsed.groups.protocol as MqttProtocols;
				if (protocolVal === MqttProtocols.MQTT) {
					protocolVal = MqttProtocols.TCP;
				}
				if (protocolVal === MqttProtocols.MQTTS) {
					protocolVal = MqttProtocols.SSL;
				}
				this.protocol = protocolVal;
				this.host = parsed.groups.host;
				const portVal = Number.parseInt(parsed.groups.port);
				if (!Number.isNaN(portVal)) {
					this.port = portVal;
				} else {
					this.port = this.getDefaultProtocolPort(this.protocol);
				}
				this.path = parsed.groups.path ?? null;
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
		const params = { ...this.configuration };
		let url = `${this.protocol}://${this.host}`;
		if (this.port !== null) {
			url += `:${this.port}`;
		}
		if ([MqttProtocols.WS, MqttProtocols.WSS].includes(this.protocol)) {
			url += `/${this.path}`;
		}
		params.BrokerAddr = url;
		delete params._toggled;
		if (this.instance !== '') {
			DaemonConfigurationService.updateInstance(this.componentName, this.instance, params)
				.then(this.handleSuccess)
				.catch(this.handleFailure);
		} else {
			DaemonConfigurationService.createInstance(this.componentName, params)
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
