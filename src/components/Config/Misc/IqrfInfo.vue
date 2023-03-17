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
				<v-form @submit.prevent='saveConfig'>
					<fieldset :disabled='loadFailed'>
						<ValidationProvider
							v-if='isAdmin'
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("config.daemon.misc.iqrfInfo.errors.instance")
							}'
						>
							<v-text-field
								v-model='configuration.instance'
								:label='$t("forms.fields.instanceName")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<v-switch
							v-model='enumPeriodic'
							:label='$t("config.daemon.misc.iqrfInfo.form.enablePeriodic")'
							color='primary'
							inset
							dense
						/>
						<ValidationProvider
							v-if='enumPeriodic'
							v-slot='{errors, touched, valid}'
							rules='integer|min:0|required'
							:custom-messages='{
								required: $t("config.daemon.misc.iqrfInfo.errors.enumPeriod"),
								min: $t("config.daemon.misc.iqrfInfo.errors.enumPeriod"),
								integer: $t("forms.errors.integer"),
							}'
						>
							<v-text-field
								v-model.number='configuration.enumPeriod'
								type='number'
								min='0'
								:label='$t("config.daemon.misc.iqrfInfo.form.enumPeriod")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<v-checkbox
							v-model='configuration.enumAtStartUp'
							:label='$t("config.daemon.misc.iqrfInfo.form.enumAtStartUp")'
							dense
						/>
						<v-checkbox
							v-model='configuration.enumUniformDpaVer'
							:label='$t("config.daemon.misc.iqrfInfo.form.enumUniformDpaVer")'
							dense
						/>
						<v-checkbox
							v-if='!versionLowerEqual("2.3.6")'
							v-model='configuration.metaDataToMessages'
							:label='$t("config.daemon.misc.iqrfInfo.form.metaDataToMessages")'
							dense
						/>
						<v-btn type='submit' color='primary' :disabled='invalid'>
							{{ $t('forms.save') }}
						</v-btn>
					</fieldset>
				</v-form>
			</ValidationObserver>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {Component, Vue, Watch} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {extendedErrorToast} from '@/helpers/errorToast';
import {integer, min_value, required} from 'vee-validate/dist/rules';
import {mapGetters} from 'vuex';
import {versionLowerEqual} from '@/helpers/versionChecker';
import {UserRole} from '@/services/AuthenticationService';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {IIqrfInfo} from '@/interfaces/Config/Misc';

@Component({
	components: {
		ValidationObserver,
		ValidationProvider,
	},
	computed: {
		...mapGetters({
			daemonVersion: 'daemonClient/getVersion',
		}),
	},
	methods: {
		versionLowerEqual: versionLowerEqual,
	},
})

/**
 * IQRF Info component configuration
 */
export default class IqrfInfo extends Vue {

	/**
	 * @constant {string} name IQRF Info component name
	 */
	private name = 'iqrf::IqrfInfo';

	/**
	 * @var {string} instance IQRF Info component instance name
	 */
	private instance = '';

	/**
	 * @var {IIqrfInfo} configuration IQRF Info component instance configuration
	 */
	private configuration: IIqrfInfo = {
		component: '',
		instance: '',
		enumAtStartUp: false,
		enumPeriod: 0,
		enumUniformDpaVer: false,
	};

	/**
	 * @var {boolean} enumPeriodic Shows period input
	 */
	private enumPeriodic = false;

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
		return this.$store.getters['user/getRole'] === UserRole.ADMIN;
	}

	/**
	 * Daemon version computed property watcher to re-render elements dependent on version
	 */
	@Watch('daemonVersion')
	private updateForm(): void {
		if (!versionLowerEqual('2.3.6')) {
			Object.assign(this.configuration, {metaDataToMessages: false});
		}
	}

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('integer', integer);
		extend('min', min_value);
		extend('required', required);
	}

	/**
	 * Loads component configuration
	 */
	mounted(): void {
		this.loading = true;
		this.updateForm();
		this.getConfig();
	}

	/**
	 * Retrieves configuration of IQRF Info component
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
					this.$t('config.daemon.messages.configFetchFailed', {children: 'iqrfInfo'},)
						.toString()
				);
			});
	}

	/**
	 * Parses IQRF Info configuration from REST API response
	 * @param {IIqrfInfo} response Configuration from REST API response
	 */
	private parseConfiguration(response: IIqrfInfo): void {
		this.instance = response.instance;
		this.configuration = response;
		if (this.configuration.enumPeriod > 0) {
			this.enumPeriodic = true;
		}
	}

	/**
	 * Saves new or updates existing configuration of IQRF Info component instance
	 */
	private saveConfig(): void {
		if (!this.enumPeriodic) {
			this.configuration.enumPeriod = 0;
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
				this.$t('config.daemon.misc.iqrfInfo.messages.saveSuccess').toString()
			);
		});
	}

	/**
	 * Handles REST API failure
	 * @param {AxiosError} err Error response
	 */
	private handleFailure(err: AxiosError): void {
		extendedErrorToast(err, 'config.daemon.misc.iqrfInfo.messages.saveFailed');
	}
}
</script>
