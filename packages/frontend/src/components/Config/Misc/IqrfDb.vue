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
		<v-card-text>
			<v-overlay
				v-if='loadFailed'
				:opacity='0.65'
				absolute
			>
				{{ $t('config.daemon.messages.failedElement') }}
			</v-overlay>
			<ValidationObserver v-slot='{ invalid }'>
				<v-form @submit.prevent='saveConfig'>
					<ValidationProvider
						v-if='isAdmin'
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{
							required: $t("config.daemon.misc.iqrfDb.errors.instance")
						}'
					>
						<v-text-field
							v-model='config.instance'
							:label='$t("forms.fields.instanceName")'
							:success='touched ? valid : null'
							:error-messages='errors'
						/>
					</ValidationProvider>
					<v-checkbox
						v-model='config.autoEnumerateBeforeInvoked'
						:label='$t("config.daemon.misc.iqrfDb.form.autoEnum")'
						dense
					/>
					<v-checkbox
						v-model='config.enumerateOnLaunch'
						:label='$t("config.daemon.misc.iqrfDb.form.launchEnum")'
						dense
					/>
					<v-checkbox
						v-model='config.metadataToMessages'
						:label='$t("config.daemon.misc.iqrfDb.form.metadata")'
						dense
					/>
					<v-btn
						color='primary'
						type='submit'
						:disabled='invalid'
					>
						{{ $t('forms.save') }}
					</v-btn>
				</v-form>
			</ValidationObserver>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import { Component, Vue } from 'vue-property-decorator';
import { extend, ValidationObserver, ValidationProvider } from 'vee-validate';

import { extendedErrorToast } from '@/helpers/errorToast';
import { required } from 'vee-validate/dist/rules';
import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import { AxiosError, AxiosResponse } from 'axios';
import { IIqrfDb } from '@/interfaces/Config/Misc';
import {UserRole} from '@iqrf/iqrf-gateway-webapp-client/types';

@Component({
	components: {
		ValidationObserver,
		ValidationProvider,
	},
})
export default class IqrfDb extends Vue {

	/**
	 * @constant {string} name IQRF DB component name
	 */
	private name = 'iqrf::IqrfDb';

	/**
	 * @var {string} instance IQRF DB component instance name
	 */
	private instance = '';

	/**
	 * @var {IIqrfDb} config IQRF DB component configuration
	 */
	private config: IIqrfDb = {
		component: '',
		instance: '',
		autoEnumerateBeforeInvoked: false,
		enumerateOnLaunch: false,
		metadataToMessages: false,
	};

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
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
	}

	/**
	 * Retrieves component configuration on component mount
	 */
	mounted(): void {
		this.loading = true;
		this.getConfig();
	}

	/**
	 * Retrieves IQRF DB component configuration
	 */
	private getConfig(): Promise<void> {
		return DaemonConfigurationService.getComponent(this.name)
			.then((response: AxiosResponse) => {
				if (response.data.instances.length > 0) {
					this.config = response.data.instances[0];
					this.instance = this.config.instance;
				}
				this.loading = false;
			})
			.catch(() => {
				this.loading = false;
				this.loadFailed = true;
				this.$toast.error(
					this.$t(
						'config.daemon.messages.configFetchFailed', {children: 'db'}
					).toString()
				);
			});
	}

	/**
	 * Saves IQRF DB component configuration
	 */
	private saveConfig(): void {
		this.$store.commit('spinner/SHOW');
		if (this.instance.length > 0) {
			DaemonConfigurationService.updateInstance(this.name, this.instance, this.config)
				.then(this.handleSuccess)
				.catch(this.handleFailure);
		} else {
			DaemonConfigurationService.createInstance(this.name, this.config)
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
				this.$t('config.daemon.misc.iqrfDb.messages.saveSuccess').toString()
			);
		});
	}

	/**
	 * Handles REST API failure
	 * @param {AxiosError} err Error response
	 */
	private handleFailure(err: AxiosError): void {
		extendedErrorToast(err, 'config.daemon.misc.iqrfDb.messages.saveFailed');
	}

}
</script>
