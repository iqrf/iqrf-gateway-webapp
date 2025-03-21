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
								required: $t("config.daemon.messagings.mq.errors.instance"),
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
										required: $t("config.daemon.messagings.mq.errors.LocalMqName"),
									}'
								>
									<v-text-field
										v-model='configuration.LocalMqName'
										:label='$t("config.daemon.messagings.mq.form.LocalMqName")'
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
										required: $t("config.daemon.messagings.mq.errors.RemoteMqName"),
									}'
								>
									<v-text-field
										v-model='configuration.RemoteMqName'
										:label='$t("config.daemon.messagings.mq.form.RemoteMqName")'
										:success='touched ? valid : null'
										:error-messages='errors'
									/>
								</ValidationProvider>
							</v-col>
						</v-row>
						<v-checkbox
							v-model='configuration.acceptAsyncMsg'
							:label='$t("config.daemon.messagings.acceptAsyncMsg")'
							dense
						/>
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
import {
	IqrfGatewayDaemonService,
} from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	IqrfGatewayDaemonComponentInstanceConfiguration,
	IqrfGatewayDaemonComponentName,
	IqrfGatewayDaemonMqMessaging,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { AxiosError } from 'axios';
import { extend, ValidationObserver, ValidationProvider } from 'vee-validate';
import { required } from 'vee-validate/dist/rules';
import { MetaInfo } from 'vue-meta';
import { Component, Prop, Vue } from 'vue-property-decorator';

import { daemonInstanceName } from '@/helpers/validators';
import { extendedErrorToast } from '@/helpers/errorToast';
import { useApiClient } from '@/services/ApiClient';

@Component({
	components: {
		ValidationObserver,
		ValidationProvider,
	},
	metaInfo(): MetaInfo {
		return {
			title: (this as unknown as MqMessagingForm).pageTitle
		};
	},
})

/**
 * Daemon MQ messaging component configuration form
 */
export default class MqMessagingForm extends Vue {
	/**
	 * @constant {IqrfGatewayDaemonComponentName} componentName MQ messaging component name
	 */
	private componentName = IqrfGatewayDaemonComponentName.IqrfMqMessaging;

	/**
	 * @var {IqrfGatewayDaemonMqMessaging} configuration MQ messaging component instance configuration
	 */
	private configuration: IqrfGatewayDaemonMqMessaging = {
		component: IqrfGatewayDaemonComponentName.IqrfMqMessaging,
		instance: '',
		LocalMqName: '',
		RemoteMqName: '',
		acceptAsyncMsg: false,
	};

	/**
	 * @property {IqrfGatewayDaemonService} service IQRF Gateway Daemon service
	 */
	private readonly service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();

	/**
	 * @property {string} instance MQ messaging component instance name
	 */
	@Prop({required: false, default: ''}) instance!: string;

	/**
	 * @var {string} pageTitle Page title
	 */
	get pageTitle(): string {
		return this.$route.path === '/config/daemon/messagings/mq/add' ?
			this.$t('config.daemon.messagings.mq.add').toString() : this.$t('config.daemon.messagings.mq.edit').toString();
	}

	/**
	 * @var {string} submitButton Button text
	 */
	get submitButton(): string {
		return this.$route.path === '/config/daemon/messagings/mq/add' ?
			this.$t('forms.add').toString() : this.$t('forms.edit').toString();
	}

	/**
	 * Initialize validation rules
	 */
	created(): void {
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
	 * Retrieves configuration of the MQ messaging component instance
	 */
	private getConfig(): void  {
		this.$store.commit('spinner/SHOW');
		this.service.getInstance(IqrfGatewayDaemonComponentName.IqrfMqMessaging, this.instance)
			.then((response: IqrfGatewayDaemonComponentInstanceConfiguration<IqrfGatewayDaemonComponentName.IqrfMqMessaging>) => {
				this.$store.commit('spinner/HIDE');
				this.configuration = response;
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.daemon.messagings.mq.messages.fetchFailed', {instance: this.instance});
				this.$router.push('/config/daemon/messagings/mq');
			});
	}

	/**
	 * Saves new or updates existing configuration of MQ messaging component instance
	 */
	private saveConfig(): void  {
		this.$store.commit('spinner/SHOW');
		if (this.instance !== '') {
			this.service.updateInstance(this.componentName, this.instance, this.configuration)
				.then(() => this.successfulSave())
				.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.messagings.mq.messages.editFailed', {instance: this.instance}));
		} else {
			this.service.createInstance(this.componentName, this.configuration)
				.then(() => this.successfulSave())
				.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.messagings.mq.messages.addFailed'));
		}
	}

	/**
	 * Handles successful REST API response
	 */
	private successfulSave(): void  {
		this.$store.commit('spinner/HIDE');
		this.$toast.success(
			this.$t(
				`config.daemon.messagings.mq.messages.${this.$route.path === '/config/daemon/messagings/mq/add' ? 'add' : 'edit'}Success`,
				{instance: this.configuration.instance},
			).toString()
		);
		this.$router.push('/config/daemon/messagings/mq/');
	}
}
</script>
