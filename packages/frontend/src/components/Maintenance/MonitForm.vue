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
		<v-card-text>
			<ValidationObserver
				v-if='configuration !== null'
				v-slot='{invalid}'
			>
				<v-form @submit.prevent='saveConfig'>
					<h2>{{ $t('maintenance.monit.checks.title') }}</h2>
					<div class='table-responsive'>
						<v-simple-table class='table'>
							<tbody>
								<tr>
									<th>{{ $t('maintenance.monit.checks.name') }}</th>
									<th class='text-right'>
										{{ $t('maintenance.monit.checks.enable') }}
									</th>
								</tr>
								<tr v-for='check of configuration.checks' :key='check.name'>
									<td>{{ check.name.replace('_', ' ') }}</td>
									<td>
										<CInputCheckbox :checked.sync='check.enabled' class='float-right' />
									</td>
								</tr>
							</tbody>
						</v-simple-table>
					</div>
					<h2>{{ $t('maintenance.monit.mmonit.title') }}</h2>
					<label for='mmonitEnabled'>
						<strong>{{ $t("maintenance.monit.mmonit.enable") }}</strong>
					</label><br>
					<v-switch
						id='mmonitEnabled'
						v-model='configuration.mmonit.enabled'
						color='primary'
						size='lg'
						shape='pill'
						inset
					/>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='required|mmonitServer'
						:custom-messages='{
							required: $t("maintenance.monit.mmonit.messages.emptyServer"),
							mmonitServer: $t("maintenance.monit.mmonit.messages.invalidServer"),
						}'
					>
						<v-text-field
							v-model='configuration.mmonit.server'
							:disabled='!configuration.mmonit.enabled'
							:label='$t("maintenance.monit.mmonit.server")'
							:success='touched ? valid : null'
							:error-messages='errors'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='required'
						:custom-messages='{
							required: $t("maintenance.monit.mmonit.credentials.messages.emptyUsername"),
						}'
					>
						<v-text-field
							v-model='configuration.mmonit.credentials.username'
							:disabled='!configuration.mmonit.enabled'
							:label='$t("maintenance.monit.mmonit.credentials.username")'
							:success='touched ? valid : null'
							:error-messages='errors'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='required'
						:custom-messages='{
							required: $t("maintenance.monit.mmonit.credentials.messages.emptyPassword")
						}'
					>
						<PasswordInput
							v-model='configuration.mmonit.credentials.password'
							:disabled='!configuration.mmonit.enabled'
							:label='$t("maintenance.monit.mmonit.credentials.password")'
							:success='touched ? valid : null'
							:error-messages='errors'
						/>
					</ValidationProvider>
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
import PasswordInput from '@/components/Core/PasswordInput.vue';

import {extendedErrorToast} from '@/helpers/errorToast';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required, alpha_num} from 'vee-validate/dist/rules';
import {z} from 'zod';

import {AxiosError} from 'axios';
import { useApiClient } from '@/services/ApiClient';
import {MonitConfig} from '@iqrf/iqrf-gateway-webapp-client/types/Config';

@Component({
	components: {
		PasswordInput,
		ValidationObserver,
		ValidationProvider,
	},
})

/**
 * MMonit configuration form component
 */
export default class MonitForm extends Vue {

	/**
	 * @constant {string} featureName Feature name
	 */
	private featureName = 'monit';

	/**
	 * @var {MonitConfig|null} configuration Monit configuration
	 */
	private configuration: MonitConfig|null = null;

	/**
	 * @var {MonitService} service Monit service
	 */
	private service = useApiClient().getConfigServices().getMonitService();

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
		extend('alphanum', alpha_num);
		extend('mmonitServer', {
			validate: (value: string): boolean => {
				const validator: z.ZodString = z.string().url();
				try {
					validator.parse(value);
					const url: URL = new URL(value);
					return (url.protocol === 'http:' || url.protocol === 'https:') && url.username === '' && url.password === '' && url.search === '' && url.hash === '';
				} catch (error) {
					return false;
				}
			},
		});
	}

	/**
	 * Retrieves config
	 */
	mounted(): void {
		this.getConfig();
	}

	/**
	 * Retrieves and store Monit configuration
	 */
	private getConfig(): Promise<void> {
		if (!this.$store.getters['spinner/isEnabled']) {
			this.$store.commit('spinner/SHOW');
		}
		return this.service.getConfig()
			.then((response: MonitConfig) => {
				this.configuration = response;
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'maintenance.monit.messages.fetchFailed'));
	}

	/**
	 * Saves new Monit configuration
	 */
	private saveConfig(): void {
		if (this.configuration === null) {
			return;
		}
		this.$store.commit('spinner/SHOW');
		this.service.updateConfig(this.configuration)
			.then(async () => {
				await useApiClient().getServiceService().restart('monit');
				await this.getConfig().then(() => this.$toast.success(
					this.$t('maintenance.monit.messages.saveSuccess').toString()
				));
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'maintenance.monit.messages.saveFailed'));
	}
}
</script>
