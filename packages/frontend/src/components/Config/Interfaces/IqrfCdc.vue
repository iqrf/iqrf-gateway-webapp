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
		<v-card class='mb-5'>
			<v-card-title>
				{{ $t('config.daemon.interfaces.iqrfCdc.title') }}
			</v-card-title>
			<v-card-text>
				<v-overlay
					v-if='loadFailed'
					:opacity='0.65'
					absolute
				>
					{{ $t('config.daemon.messages.failedElement') }}
				</v-overlay>
				<ValidationObserver v-slot='{invalid}'>
					<form @submit.prevent='saveConfig'>
						<fieldset :disabled='loadFailed'>
							<ValidationProvider
								v-if='isAdmin'
								v-slot='{errors, touched, valid}'
								rules='required'
								:custom-messages='{
									required: $t("config.daemon.interfaces.iqrfCdc.errors.instance")
								}'
							>
								<v-text-field
									v-model='configuration.instance'
									:label='$t("forms.fields.instanceName")'
									:success='touched ? valid : null'
									:error-messages='errors'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required'
								:custom-messages='{
									required: $t("config.daemon.interfaces.iqrfCdc.errors.iqrfInterface")
								}'
							>
								<v-text-field
									v-model='configuration.IqrfInterface'
									:label='$t("config.daemon.interfaces.iqrfCdc.form.interface")'
									:success='touched ? valid : null'
									:error-messages='errors'
								/>
							</ValidationProvider>
							<v-btn
								type='submit'
								color='primary'
								:disabled='invalid'
							>
								{{ $t('forms.save') }}
							</v-btn>
						</fieldset>
					</form>
				</ValidationObserver>
			</v-card-text>
		</v-card>
		<v-card>
			<v-card-text>
				<InterfacePorts
					:interface-type='MappingType.CDC'
					@update-port='updatePort'
				/>
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {
	IqrfGatewayDaemonService
} from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {UserRole} from '@iqrf/iqrf-gateway-webapp-client/types';
import {
	IqrfGatewayDaemonCdc,
	IqrfGatewayDaemonComponent,
	IqrfGatewayDaemonComponentName,
	MappingType,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {AxiosError} from 'axios';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import {Component, Vue} from 'vue-property-decorator';

import InterfacePorts from '@/components/Config/Interfaces/InterfacePorts.vue';
import {extendedErrorToast} from '@/helpers/errorToast';
import {useApiClient} from '@/services/ApiClient';

@Component({
	components: {
		InterfacePorts,
		ValidationObserver,
		ValidationProvider,
	},
	data: () => ({
		MappingType,
	})
})

/**
 * IQRF CDC communication interface configuration component
 */
export default class IqrfCdc extends Vue {
	/**
	 * @constant {IqrfGatewayDaemonComponentName.IqrfCdc} componentName IQRF CDC interface component name
	 */
	private componentName = IqrfGatewayDaemonComponentName.IqrfCdc;

	/**
	 * @var {IqrfGatewayDaemonCdc} configuration IQRF CDC interface instance configuration
	 */
	private configuration: IqrfGatewayDaemonCdc = {
		component: IqrfGatewayDaemonComponentName.IqrfCdc,
		instance: '',
		IqrfInterface: '',
	};

	/**
	 * @var {string} instance Name of IQRF CDC component instance
	 */
	private instance = '';

	/**
	 * @var {boolean} loadFailed Indicates whether configuration fetch failed
	 */
	private loadFailed = false;

	/**
	 * @property {IqrfGatewayDaemonService} service IQRF Gateway Daemon configuration service
	 */
	private readonly service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();

	/**
	 * Checks if user is an administrator
	 * @returns {boolean} True if user is an administrator
	 */
	get isAdmin(): boolean {
		return this.$store.getters['user/getRole'] === UserRole.Admin;
	}

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('required', required);
	}

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		this.getConfig();
	}

	/**
	 * Retrieves configuration of IQRF CDC interface component
	 */
	private getConfig(): Promise<void> {
		return this.service.getComponent(IqrfGatewayDaemonComponentName.IqrfCdc)
			.then((response: IqrfGatewayDaemonComponent<IqrfGatewayDaemonComponentName.IqrfCdc>): void => {
				if (response.instances.length > 0) {
					this.configuration = response.instances[0];
					this.instance = this.configuration.instance;
				}
				this.$emit('fetched', {name: 'iqrfCdc', success: true});
			})
			.catch(() => {
				this.loadFailed = true;
				this.$emit('fetched', {name: 'iqrfCdc', success: false});
			});
	}

	/**
	 * Saves new or updates existing configuration of IQRF CDC interface component instance
	 */
	private saveConfig(): void {
		this.$store.commit('spinner/SHOW');
		if (this.instance !== '') {
			this.service.updateInstance(this.componentName, this.instance, this.configuration)
				.then(this.handleSuccess)
				.catch(this.handleFailure);
		} else {
			this.service.createInstance(this.componentName, this.configuration)
				.then(this.handleSuccess)
				.catch(this.handleFailure);
		}
	}

	/**
	 * Handles REST API success
	 */
	private handleSuccess(): void {
		this.getConfig().then(() => {
			this.$toast.success(
				this.$t('config.daemon.interfaces.iqrfCdc.messages.saveSuccess').toString()
			);
		});
	}

	/**
	 * Handles REST API failure
	 * @param {AxiosError} err Error response
	 */
	private handleFailure(err: AxiosError): void {
		extendedErrorToast(err, 'config.daemon.interfaces.iqrfCdc.messages.saveFailed');
	}

	/**
	 * Updates port in configuration from mapping
	 * @param {string} port Port
	 */
	private updatePort(port: string): void {
		this.configuration.IqrfInterface = port;
	}
}
</script>
