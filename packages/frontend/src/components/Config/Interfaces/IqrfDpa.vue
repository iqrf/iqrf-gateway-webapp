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
	<v-card>
		<v-card-title>
			{{ $t('config.daemon.interfaces.iqrfDpa.title') }}
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
							:custom-messages='{required: $t("config.daemon.interfaces.iqrfDpa.errors.instance")}'
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
							rules='integer|required|min:0'
							:custom-messages='{
								integer: $t("forms.errors.integer"),
								min: $t("config.daemon.interfaces.iqrfDpa.errors.DpaHandlerTimeout"),
								required: $t("config.daemon.interfaces.iqrfDpa.errors.DpaHandlerTimeout"),
							}'
						>
							<v-text-field
								v-model.number='configuration.DpaHandlerTimeout'
								type='number'
								min='0'
								:label='$t("config.daemon.interfaces.iqrfDpa.form.DpaHandlerTimeout")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<v-btn type='submit' color='primary' :disabled='invalid'>
							{{ $t('forms.save') }}
						</v-btn>
					</fieldset>
				</form>
			</ValidationObserver>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {
	IqrfGatewayDaemonService
} from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {UserRole} from '@iqrf/iqrf-gateway-webapp-client/types';
import {
	IqrfGatewayDaemonComponent,
	IqrfGatewayDaemonComponentName,
	IqrfGatewayDaemonDpa
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {AxiosError} from 'axios';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {integer, min_value, required} from 'vee-validate/dist/rules';
import {Component, Vue} from 'vue-property-decorator';

import {extendedErrorToast} from '@/helpers/errorToast';
import {useApiClient} from '@/services/ApiClient';

@Component({
	components: {
		ValidationObserver,
		ValidationProvider
	}
})

/**
 * IQRF DPA component configuration
 */
export default class IqrfDpa extends Vue {
	/**
	 * @constant {string} componentName IQRF DPA component name
	 */
	private componentName = IqrfGatewayDaemonComponentName.IqrfDpa;

	/**
	 * @var {IqrfGatewayDaemonDpa} configuration IQRF DPA component instance configuration
	 */
	private configuration: IqrfGatewayDaemonDpa = {
		component: IqrfGatewayDaemonComponentName.IqrfDpa,
		instance: '',
		DpaHandlerTimeout: 500,
	};

	/**
	 * @var {string} instance IQRF DPA component instance name
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
		extend('integer', integer);
		extend('min', min_value);
		extend('required', required);
	}

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		this.getConfig();
	}

	/**
	 * Retrieves configuration of IQRF DPA component
	 */
	private getConfig(): Promise<void> {
		return this.service.getComponent(IqrfGatewayDaemonComponentName.IqrfDpa)
			.then((response: IqrfGatewayDaemonComponent<IqrfGatewayDaemonComponentName.IqrfDpa>) => {
				if (response.instances.length > 0) {
					this.configuration = response.instances[0];
					this.instance = this.configuration.instance;
				}
				this.$emit('fetched', {name: 'iqrfDpa', success: true});
			})
			.catch(() => {
				this.loadFailed = true;
				this.$emit('fetched', {name: 'iqrfDpa', success: false});
			});
	}

	/**
	 * Saves new or updates existing configuration of IQRF DPA component instance
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
				this.$t('config.daemon.interfaces.iqrfDpa.messages.saveSuccess').toString()
			);
		});
	}

	/**
	 * Handles REST API failure
	 * @param {AxiosError} err Error response
	 */
	private handleFailure(err: AxiosError): void {
		extendedErrorToast(err, 'config.daemon.interfaces.iqrfDpa.messages.saveFailed');
	}
}
</script>
