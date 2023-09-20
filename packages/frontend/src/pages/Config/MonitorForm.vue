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
		<h1>{{ pageTitle }}</h1>
		<v-card>
			<v-card-text>
				<ValidationObserver v-slot='{invalid}'>
					<v-form>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("config.daemon.misc.monitor.errors.instance"),
							}'
						>
							<v-text-field
								v-model='monitor.instance'
								:label='$t("forms.fields.instanceName")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<v-row>
							<v-col cols='12' md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='integer|min:1|required'
									:custom-messages='{
										required: $t("config.daemon.misc.monitor.errors.reportPeriod"),
										min: $t("config.daemon.misc.monitor.errors.reportPeriod"),
										integer: $t("forms.errors.integer"),
									}'
								>
									<v-text-field
										v-model.number='monitor.reportPeriod'
										type='number'
										:label='$t("config.daemon.misc.monitor.form.reportPeriod")'
										:success='touched ? valid : null'
										:error-messages='errors'
									/>
								</ValidationProvider>
							</v-col>
							<v-col cols='12' md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='integer|between:1,65535|required'
									:custom-messages='{
										between: $t("config.daemon.messagings.websocket.errors.WebsocketPortRange"),
										required: $t("config.daemon.misc.monitor.errors.WebsocketPort"),
										integer: $t("forms.errors.integer"),
									}'
								>
									<v-text-field
										v-model.number='webSocket.WebsocketPort'
										type='number'
										:label='$t("config.daemon.misc.monitor.form.WebsocketPort")'
										:success='touched ? valid : null'
										:error-messages='errors'
									/>
								</ValidationProvider>
							</v-col>
						</v-row>
						<v-checkbox
							v-model='webSocket.acceptOnlyLocalhost'
							:label='$t("config.daemon.misc.monitor.form.acceptOnlyLocalhost")'
							dense
						/>
						<v-switch
							v-model='webSocket.tlsEnabled'
							:label='$t("config.daemon.messagings.tlsTitle")'
							inset
							dense
						/>
						<v-row>
							<v-col>
								<ValidationProvider
									v-if='webSocket.tlsEnabled'
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: $t("config.daemon.messagings.websocket.errors.tlsMode"),
									}'
								>
									<v-select
										v-model='webSocket.tlsMode'
										:label='$t("config.daemon.messagings.websocket.form.tlsMode")'
										:items='tlsModeOptions'
										:placeholder='$t("config.daemon.messagings.websocket.errors.tlsMode")'
										:disabled='!webSocket.tlsEnabled'
										:success='touched && webSocket.tlsEnabled ? valid : null'
										:error-messages='errors'
										:hint='$t(`config.daemon.messagings.websocket.form.tlsModes.descriptions.${webSocket.tlsMode}`)'
										persistent-hint
									/>
								</ValidationProvider>
							</v-col>
						</v-row>
						<v-row>
							<v-col cols='12' md='6'>
								<ValidationProvider
									v-if='webSocket.tlsEnabled'
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: $t("config.daemon.messagings.websocket.errors.certificate"),
									}'
								>
									<v-text-field
										v-model='webSocket.certificate'
										:label='$t("forms.fields.certificate")'
										:disabled='!webSocket.tlsEnabled'
										:success='touched ? valid : null'
										:error-messages='errors'
									/>
								</ValidationProvider>
							</v-col>
							<v-col cols='12' md='6'>
								<ValidationProvider
									v-if='webSocket.tlsEnabled'
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: $t("config.daemon.messagings.websocket.errors.privateKey"),
									}'
								>
									<v-text-field
										v-model='webSocket.privateKey'
										:label='$t("forms.fields.privateKey")'
										:disabled='!webSocket.tlsEnabled'
										:success='touched ? valid : null'
										:error-messages='errors'
									/>
								</ValidationProvider>
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

import {between, integer, required, min_value} from 'vee-validate/dist/rules';
import {extendedErrorToast} from '@/helpers/errorToast';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {IMonitorInstance, IMonitorWs} from '@/interfaces/Config/Misc';
import {ISelectItem} from '@/interfaces/Vuetify';

import {MetaInfo} from 'vue-meta';

@Component({
	components: {
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
	 * @var {Array<ISelectItem>} tlsModeOptions TLS mode options
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
		this.$toast.success(
			this.$t(
				`config.daemon.misc.monitor.messages.${this.$route.path === '/config/daemon/misc/monitor/add' ? 'addSuccess' : 'editSuccess'}`,
				{instance: this.monitor.instance}
			).toString()
		);
		this.$router.push({
			name: 'misc',
			params: {
				tabName: 'monitor'
			}
		});
	}
}
</script>
