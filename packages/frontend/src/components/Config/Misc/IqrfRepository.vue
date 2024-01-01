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
	<v-card :loading='loading'>
		<v-card-text v-if='!loading'>
			<v-overlay
				v-if='loadFailed'
				:opacity='0.65'
				absolute
			>
				{{ $t('config.daemon.messages.failedElement') }}
			</v-overlay>
			<ValidationObserver v-slot='{invalid}'>
				<v-form>
					<ValidationProvider
						v-if='isAdmin'
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{
							required: $t("config.daemon.misc.iqrfRepository.errors.instance")
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
						:custom-messages='{required: $t("config.daemon.misc.iqrfRepository.errors.urlRepo")}'
					>
						<v-text-field
							v-model='configuration.urlRepo'
							:label='$t("config.daemon.misc.iqrfRepository.form.urlRepo")'
							:success='touched ? valid : null'
							:error-messages='errors'
						/>
					</ValidationProvider>
					<v-switch
						v-model='checkEnabled'
						:label='$t("config.daemon.misc.iqrfRepository.form.enableCheck")'
						color='primary'
						inset
						dense
					/>
					<ValidationProvider
						v-if='checkEnabled'
						v-slot='{errors, touched, valid}'
						rules='integer|required|min:0'
						:custom-messages='{
							integer: $t("forms.errors.integer"),
							required: $t("config.daemon.misc.iqrfRepository.errors.checkPeriod"),
							min: $t("config.daemon.misc.iqrfRepository.errors.checkPeriod"),
						}'
					>
						<v-text-field
							v-model.number='configuration.checkPeriodInMinutes'
							type='number'
							min='0'
							:label='$t("config.daemon.misc.iqrfRepository.form.checkPeriod")'
							:success='touched ? valid : null'
							:error-messages='errors'
						/>
					</ValidationProvider>
					<v-checkbox
						v-model='configuration.downloadIfRepoCacheEmpty'
						:label='$t("config.daemon.misc.iqrfRepository.form.downloadIfEmpty")'
						dense
					/>
					<v-btn
						class='mr-1'
						color='primary'
						:disabled='invalid'
						@click='saveConfig'
					>
						{{ $t('forms.save') }}
					</v-btn>
					<v-btn
						color='primary'
						@click='updateCache'
					>
						{{ $t('config.daemon.misc.iqrfRepository.cacheUpdate') }}
					</v-btn>
				</v-form>
			</ValidationObserver>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {buildDaemonMessageOptions} from '@/store/modules/daemonClient.module';
import {extendedErrorToast} from '@/helpers/errorToast';
import {integer, min_value, required} from 'vee-validate/dist/rules';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';
import ManagementService from '@/services/DaemonApi/ManagementService';

import {AxiosError, AxiosResponse} from 'axios';
import {IIqrfRepository} from '@/interfaces/Config/Misc';
import {MutationPayload} from 'vuex';
import {UserRole} from '@iqrf/iqrf-gateway-webapp-client/types';

/**
 * IQRF Repository component configuration
 */
@Component({
	components: {
		ValidationObserver,
		ValidationProvider,
	},
})
export default class IqrfRepository extends Vue {
	/**
	 * @var {string} msgId Daemon API message ID
	 */
	private msgId = '';

	/**
	 * @constant {string} name IQRF Repository component name, used for REST API communication
	 */
	private name = 'iqrf::JsCache';

	/**
	 * @var {string} instance IQRF Repository component instance name
	 */
	private instance = '';

	/**
	 * @var {IIqrfRepository} configuration IQRF Repository component instance configuration
	 */
	private configuration: IIqrfRepository = {
		component: '',
		instance: '',
		urlRepo: '',
		checkPeriodInMinutes: 0,
		downloadIfRepoCacheEmpty: true,
	};

	/**
	 * @var {boolean} checkEnabled Enable periodical update check
	 */
	private checkEnabled = false;

	/**
	 * @var {boolean} loadFailed Indicates whether configuration fetch failed
	 */
	private loadFailed = false;

	/**
	 * @var {boolean} loading Flag for loading state
	 */
	private loading = false;

	/**
	 * Checks if user is an administrator
	 * @returns {boolean} True if user is an administrator
	 */
	get isAdmin(): boolean {
		return this.$store.getters['user/getRole'] === UserRole.Admin;
	}

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * Initializes validation rules and mutation handler
	 */
	created(): void {
		extend('integer', integer);
		extend('min', min_value);
		extend('required', required);
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type !== 'daemonClient/SOCKET_ONMESSAGE') {
				return;
			}
			if (mutation.payload.data.msgId !== this.msgId) {
				return;
			}
			this.$store.dispatch('daemonClient/removeMessage', this.msgId);
			this.$store.dispatch('spinner/hide');
			if (mutation.payload.mType === 'mngDaemon_UpdateCache') {
				this.handleUpdateCacheResponse(mutation.payload.data);
			}
		});

	}

	/**
	 * Retrieves user role and iqrf repository configuration
	 */
	mounted(): void {
		this.loading = true;
		this.getConfig();
	}

	/**
	 * Unregisters mutation handling
	 */
	beforeDestroy(): void {
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		this.unsubscribe();
	}

	/**
	 * Retrieves configuration of IQRF Repository component
	 */
	private getConfig(): Promise<void> {
		return DaemonConfigurationService.getComponent(this.name)
			.then((response: AxiosResponse) => {
				if (response.data.instances.length > 0) {
					this.parseConfiguration(response.data.instances[0]);
				}
				this.loading = false;
			})
			.catch(() => {
				this.loading = false;
				this.loadFailed = true;
				this.$toast.error(
					this.$t('config.daemon.messages.configFetchFailed', {children: 'iqrfRepository'})
						.toString()
				);
			});
	}

	/**
	 * Parses IQRF Repository configuration from REST API response
	 * @param {IIqrfRepository} response Configuration from REST API response
	 */
	private parseConfiguration(response: IIqrfRepository): void {
		this.instance = response.instance;
		this.configuration = response;
		if (this.configuration.checkPeriodInMinutes !== undefined && this.configuration.checkPeriodInMinutes > 0) {
			this.checkEnabled = true;
		}
	}

	/**
	 * Saves new or updates existing configuration of IQRF Repository component instance
	 */
	private saveConfig(): void {
		if (!this.checkEnabled) {
			this.configuration.checkPeriodInMinutes = 0;
		}
		this.$store.commit('spinner/SHOW');
		if (this.instance !== '') {
			DaemonConfigurationService.updateInstance(this.name, this.instance, this.configuration)
				.then(this.handleSuccess)
				.catch(this.handleFailure);
		} else {
			DaemonConfigurationService.createInstance(this.name, this.configuration)
				.then(this.handleSuccess)
				.catch(this.handleFailure);
		}
	}

	/**
	 * Handles REST API success
	 */
	private handleSuccess(): void {
		this.getConfig().then(() => {
			this.$store.commit('spinner/HIDE');
			this.$toast.success(
				this.$t('config.daemon.misc.iqrfRepository.messages.saveSuccess').toString()
			);
		});
	}

	/**
	 * Handles REST API failure
	 * @param {AxiosError} err Error response
	 */
	private handleFailure(err: AxiosError): void {
		extendedErrorToast(err, 'config.daemon.misc.iqrfRepository.messages.saveFailed');
	}

	/**
	 * Sends cache update request
	 */
	private updateCache(): void {
		const options = buildDaemonMessageOptions(60000, 'config.daemon.misc.iqrfRepository.messages.cacheUpdateTimeout', () => this.msgId = '');
		this.$store.dispatch('spinner/show', {timeout: 60000});
		ManagementService.updateCache(options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles cache update response
	 * @param response Response
	 */
	private handleUpdateCacheResponse(response): void {
		if (response.status === 0) {
			const updated: boolean = response.status.updated;
			this.$toast.success(
				this.$t(
					'config.daemon.misc.iqrfRepository.messages.cacheUpdate' + (updated ? 'Success' : 'NotNeeded')
				).toString()
			);
		} else {
			this.$toast.error(
				this.$t(
					'config.daemon.misc.iqrfRepository.messages.cacheUpdateFailed',
					{error: response.errorStr}
				).toString()
			);
		}
	}
}
</script>
