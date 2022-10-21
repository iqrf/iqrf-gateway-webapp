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
		<h1 v-if='$route.path === "/config/daemon/misc/monitor/add"'>
			{{ $t('config.daemon.misc.monitor.add') }}
		</h1>
		<h1 v-else>
			{{ $t('config.daemon.misc.monitor.edit') }}
		</h1>
		<CCard body-wrapper>
			<ValidationObserver v-slot='{invalid}'>
				<CForm @submit.prevent='saveConfig'>
					<CRow>
						<CCol md='6'>
							<legend>{{ $t('config.daemon.misc.monitor.form.title') }}</legend>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required'
								:custom-messages='{
									required: $t("config.daemon.misc.monitor.errors.instance"),
								}'
							>
								<CInput
									v-model='monitor.instance'
									:label='$t("forms.fields.instanceName")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='errors.join(", ")'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='integer|min:1|required'
								:custom-messages='{
									required: $t("config.daemon.misc.monitor.errors.reportPeriod"),
									min: $t("config.daemon.misc.monitor.errors.reportPeriod"),
									integer: $t("forms.errors.integer"),
								}'
							>
								<CInput
									v-model.number='monitor.reportPeriod'
									type='number'
									:label='$t("config.daemon.misc.monitor.form.reportPeriod")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='errors.join(", ")'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='integer|between:1,65535|required'
								:custom-messages='{
									between: $t("config.daemon.messagings.websocket.errors.WebsocketPortRange"),
									required: $t("config.daemon.misc.monitor.errors.WebsocketPort"),
									integer: $t("forms.errors.integer"),
								}'
							>
								<CInput
									v-model.number='webSocket.WebsocketPort'
									type='number'
									:label='$t("config.daemon.misc.monitor.form.WebsocketPort")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='errors.join(", ")'
								/>
							</ValidationProvider>
							<CInputCheckbox
								:checked.sync='webSocket.acceptOnlyLocalhost'
								:label='$t("config.daemon.misc.monitor.form.acceptOnlyLocalhost")'
							/>
						</CCol>
						<CCol md='6'>
							<div>
								<label style='font-size: 1.5rem;'>
									{{ $t('config.daemon.messagings.tlsTitle') }}
								</label>
								<CSwitch
									color='primary'
									size='lg'
									shape='pill'
									label-on='ON'
									label-off='OFF'
									:checked.sync='webSocket.tlsEnabled'
									style='float: right;'
								/>
								<ValidationProvider
									v-if='webSocket.tlsEnabled'
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: $t("config.daemon.messagings.websocket.errors.tlsMode"),
									}'
								>
									<CSelect
										:value.sync='webSocket.tlsMode'
										:label='$t("config.daemon.messagings.websocket.form.tlsMode")'
										:options='tlsModeOptions'
										:placeholder='$t("config.daemon.messagings.websocket.errors.tlsMode")'
										:disabled='!webSocket.tlsEnabled'
										:is-valid='touched && webSocket.tlsEnabled ? valid : null'
										:invalid-feedback='errors.join(", ")'
									/>
									<p
										v-if='webSocket.tlsMode !== "" && webSocket.tlsMode !== undefined'
										:class='!webSocket.tlsEnabled ? "text-secondary" : ""'
									>
										{{ $t(`config.daemon.messagings.websocket.form.tlsModes.descriptions.${webSocket.tlsMode}`) }}
									</p>
								</ValidationProvider>
								<ValidationProvider
									v-if='webSocket.tlsEnabled'
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: $t("config.daemon.messagings.websocket.errors.certificate"),
									}'
								>
									<CInput
										v-model='webSocket.certificate'
										:label='$t("forms.fields.certificate")'
										:disabled='!webSocket.tlsEnabled'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
									/>
								</ValidationProvider>
								<ValidationProvider
									v-if='webSocket.tlsEnabled'
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: $t("config.daemon.messagings.websocket.errors.privateKey"),
									}'
								>
									<CInput
										v-model='webSocket.privateKey'
										:label='$t("forms.fields.privateKey")'
										:disabled='!webSocket.tlsEnabled'
										:is-valid='touched ? valid : null'
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
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput, CInputCheckbox} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {between, integer, required, min_value} from 'vee-validate/dist/rules';
import {extendedErrorToast} from '@/helpers/errorToast';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {IMonitorInstance, IMonitorWs} from '@/interfaces/Config/Misc';
import {IOption} from '@/interfaces/Coreui';
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
		ValidationObserver,
		ValidationProvider,
	},
	metaInfo(): MetaInfo {
		return {
			title: (this as unknown as MonitorForm).pageTitle
		};
	}
})

/**
 * Daemon monitoring component configuration card
 */
export default class MonitorForm extends Vue {
	/**
	 * @constant {Record<string, string>} componentNames Names of components required for the monitoring service
	 */
	private componentNames: Record<string, string> = {
		monitor: 'iqrf::MonitorService',
		webSocket: 'shape::WebsocketCppService',
	};

	/**
	 * @var {Record<string, string>} instances Names of component instances required for the monitoring service
	 */
	private instances: Record<string, string> = {
		monitor: '',
		webSocket: '',
	};

	/**
	 * @var {IMonitorInstance} monitor Daemon monitoring instance configuration
	 */
	private monitor: IMonitorInstance = {
		component: '',
		instance: '',
		reportPeriod: 10,
		RequiredInterfaces: []
	};

	/**
	 * @var {IMonitorWs} webSocket Daemon websocket instance configuration
	 */
	private webSocket: IMonitorWs = {
		instance: '',
		WebsocketPort: 1438,
		acceptOnlyLocalhost: false,
		tlsEnabled: false,
		tlsMode: '',
		certificate: '',
		privateKey: ''
	};

	/**
	 * @var {Array<IOption>} tlsModeOptions Array of CoreUI select options for TLS mode
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
	 * @property {string} instance Monitoring service instance name
	 */
	@Prop({required: false, default: ''}) instance!: string;

	/**
	 * Computes page title depending on the action (add, edit)
	 * @returns {string} Page title
	 */
	get pageTitle(): string {
		return this.$route.path === '/config/daemon/misc/monitor/add' ?
			this.$t('config.daemon.misc.monitor.add').toString() :
			this.$t('config.daemon.misc.monitor.edit').toString();
	}

	/**
	 * Computes form submit button text depending on the action (add, edit)
	 * @returns {string} Button text
	 */
	get submitButton(): string {
		return this.$route.path === '/config/daemon/misc/monitor/add' ?
			this.$t('forms.add').toString() :
			this.$t('forms.save').toString();
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
	 * Retrieves configuration of the monitoring component and websocket instance
	 */
	private getConfig(): void {
		if (this.componentNames.monitor === '' || this.componentNames.webSocket === '') {
			return;
		}
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.getInstance(this.componentNames.monitor, this.instance)
			.then((response: AxiosResponse) => {
				this.monitor = response.data;
				this.instances.monitor = this.instance;
				this.instances.webSocket = this.monitor.RequiredInterfaces[0].target.instance;
				DaemonConfigurationService.getInstance(this.componentNames.webSocket, this.instances.webSocket)
					.then((response: AxiosResponse) => {
						this.webSocket = response.data;
						this.$store.commit('spinner/HIDE');
					});
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(
					error,
					'config.daemon.misc.monitor.messages.fetchFailed',
					{instance: this.instance}
				);
				this.$router.push({
					name: 'misc',
					params: {
						tabName: 'monitor'
					}
				});
			});
	}

	/**
	 * Saves new or updates existing configurations of monitoring and websocket component instances
	 */
	private saveConfig(): void {
		this.$store.commit('spinner/SHOW');
		this.webSocket.instance = this.monitor.instance;
		if (this.monitor.RequiredInterfaces.length === 0) {
			this.monitor.RequiredInterfaces[0] = {
				name: 'shape::IWebsocketService',
				target: {
					instance: this.webSocket.instance,
				},
			};
		} else {
			this.monitor.RequiredInterfaces[0].target.instance = this.monitor.instance;
		}
		if (this.instance === '') {
			Promise.all([
				DaemonConfigurationService.createInstance(this.componentNames.webSocket, this.webSocket),
				DaemonConfigurationService.createInstance(this.componentNames.monitor, this.monitor),
			])
				.then(() => this.successfulSave())
				.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.misc.monitor.messages.addFailed'));
		} else {
			Promise.all([
				DaemonConfigurationService.updateInstance(this.componentNames.webSocket, this.instances.webSocket, this.webSocket),
				DaemonConfigurationService.updateInstance(this.componentNames.monitor, this.instances.monitor, this.monitor),
			])
				.then(() => this.successfulSave())
				.catch((error: AxiosError) => extendedErrorToast(
					error,
					'config.daemon.misc.monitor.messages.editFailed',
					{instance: this.instance}
				));
		}
	}

	/**
	 * Handles successful REST APi response
	 */
	private successfulSave() {
		this.$store.commit('spinner/HIDE');
		if (this.$route.path === '/config/daemon/misc/monitor/add') {
			this.$toast.success(
				this.$t('config.daemon.misc.monitor.messages.addSuccess', {instance: this.monitor.instance})
					.toString()
			);
		} else {
			this.$toast.success(
				this.$t('config.daemon.misc.monitor.messages.editSuccess', {instance: this.monitor.instance})
					.toString()
			);
		}
		this.$router.push({
			name: 'misc',
			params: {
				tabName: 'monitor'
			}
		});
	}
}
</script>
