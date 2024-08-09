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
	<v-card>
		<v-card-title>
			{{ $t('service.unattended-upgrades.configuration') }}
		</v-card-title>
		<v-card-text>
			<ValidationObserver v-slot='{invalid}'>
				<v-form @submit.prevent='updateConfig'>
					<v-row>
						<v-col v-if='isAdmin' cols='12' md='4'>
							<ValidationProvider
								v-if='isAdmin'
								v-slot='{errors, touched, valid}'
								rules='integer|min:0|required'
								:custom-messages='{
									integer: $t("forms.errors.integer"),
									min: $t("service.unattended-upgrades.errors.listUpdateInterval"),
									required: $t("service.unattended-upgrades.errors.listUpdateInterval"),
								}'
							>
								<v-text-field
									v-model='config.packageListUpdateInterval'
									type='number'
									min='0'
									:label='$t("service.unattended-upgrades.form.listUpdateInterval")'
									:success='touched ? valid : null'
									:error-messages='errors'
								/>
							</ValidationProvider>
						</v-col>
						<v-col cols='12' md='4'>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='integer|min:0|required'
								:custom-messages='{
									integer: $t("forms.errors.integer"),
									min: $t("service.unattended-upgrades.errors.upgradeInterval"),
									required: $t("service.unattended-upgrades.errors.upgradeInterval"),
								}'
							>
								<v-text-field
									v-model='config.packageUpdateInterval'
									type='number'
									min='0'
									:label='$t("service.unattended-upgrades.form.upgradeInterval")'
									:success='touched ? valid : null'
									:error-messages='errors'
								/>
							</ValidationProvider>
						</v-col>
						<v-col v-if='isAdmin' cols='12' md='4'>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='integer|min:0|required'
								:custom-messages='{
									integer: $t("forms.errors.integer"),
									min: $t("service.unattended-upgrades.errors.removeInterval"),
									required: $t("service.unattended-upgrades.errors.removeInterval"),
								}'
							>
								<v-text-field
									v-model='config.packageRemovalInterval'
									type='number'
									min='0'
									:label='$t("service.unattended-upgrades.form.removeInterval")'
									:success='touched ? valid : null'
									:error-messages='errors'
								/>
							</ValidationProvider>
						</v-col>
					</v-row>
					<v-checkbox
						v-model='config.rebootOnKernelUpdate'
						:label='$t("service.unattended-upgrades.form.rebootOnKernelUpdate")'
						dense
					/>
					<div><em>{{ $t('service.unattended-upgrades.form.intervalNote') }}</em></div><br>
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
import {Component, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {extendedErrorToast} from '@/helpers/errorToast';
import {integer, min_value, required} from 'vee-validate/dist/rules';

import {AxiosError} from 'axios';
import {UserRole} from '@iqrf/iqrf-gateway-webapp-client/types';
import {useApiClient} from '@/services/ApiClient';
import {AptService} from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {AptConfig} from '@iqrf/iqrf-gateway-webapp-client/types/Config';

@Component({
	components: {
		ValidationObserver,
		ValidationProvider
	},
})

/**
 * Gateway APT configuration component for service control
 */
export default class AptConfiguration extends Vue {

	private config: AptConfig = {
		enabled: false,
		packageRemovalInterval: 0,
		packageListUpdateInterval: 1,
		packageUpdateInterval: 1,
		rebootOnKernelUpdate: false,
	};

	private service: AptService = useApiClient().getConfigServices().getAptService();

	/**
	 * Checks if user is an administrator
	 * @returns {boolean} True if user is an administrator
	 */
	get isAdmin(): boolean {
		return this.$store.getters['user/getRole'] === UserRole.Admin;
	}

	/**
	 * Vue lifecycle hook created
	 * Defines keywords for form validation rules
	 */
	created(): void {
		extend('integer', integer);
		extend('min', min_value);
		extend('required', required);
	}

	/**
	 * Vue lifecycle hook mounted
	 * Checks for user role and sends REST API request to get unattended upgrades configuration
	 */
	mounted(): void {
		this.getConfig();
	}

	/**
	 * Retrieves unattended upgrades configuration
	 */
	private getConfig(): Promise<void> {
		return this.service.getConfig()
			.then((config: AptConfig) => {this.config = config;})
			.catch((error: AxiosError) => extendedErrorToast(error, 'service.unattended-upgrades.messages.getFailed'));
	}

	/**
	 * Creates apt configuration object and saves configuration
	 */
	private updateConfig(): void {
		this.$store.commit('spinner/SHOW');
		this.service.updateConfig(this.config)
			.then(() => {
				this.getConfig().then(() => {
					this.$store.commit('spinner/HIDE');
					this.$toast.success(
						this.$t('service.unattended-upgrades.messages.saveSuccess').toString()
					);
				});
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'service.unattended-upgrades.messages.saveFailed');
			});
	}

}
</script>
