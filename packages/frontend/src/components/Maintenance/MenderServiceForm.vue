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
		<v-card-text>
			<ValidationObserver v-slot='{invalid}'>
				<v-form @submit.prevent='processSubmit'>
					<h5>{{ $t('maintenance.mender.service.form.connection') }}</h5>
					<ValidationProvider
						v-for='idx in config.client.config.Servers.keys()'
						:key='"server -" + idx'
						v-slot='{errors, touched, valid}'
						rules='required|serverUrl'
						:custom-messages='{
							required: $t("maintenance.mender.service.errors.serverMissing"),
							serverUrl: $t("maintenance.mender.service.errors.serverInvalid")
						}'
					>
						<v-text-field
							v-model='config.client.config.Servers[0]'
							:label='$t("maintenance.mender.service.form.client.server")'
							:success='touched ? valid : null'
							:error-messages='errors'
							@change='serverUrlFixup(idx)'
						/>
					</ValidationProvider>
					<v-text-field
						v-model='config.client.config.ServerCertificate'
						:label='$t("maintenance.mender.service.form.client.cert")'
						:disabled='uploadCert'
					/>
					<v-switch
						v-model='uploadCert'
						:label='$t("maintenance.mender.service.form.client.uploadCert")'
						color='primary'
						inset
						dense
					/>
					<ValidationProvider
						v-if='uploadCert'
						v-slot='{errors, touched, valid}'
						rules='required'
						:custom-messages='{
							required: $t("maintenance.mender.service.errors.tenantToken"),
						}'
					>
						<v-file-input
							v-model='certificate'
							accept='.crt'
							:label='$t("maintenance.mender.service.form.client.newCert")'
							:success='touched ? valid : null'
							:error-messages='errors'
							:prepend-icon='null'
							prepend-inner-icon='mdi-file-certificate'
						/>
					</ValidationProvider>
					<v-divider class='my-2' />
					<h5>{{ $t('maintenance.mender.service.form.inventory') }}</h5>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='required'
						:custom-messages='{
							required: $t("maintenance.mender.service.errors.tenantToken"),
						}'
					>
						<v-text-field
							v-model='config.client.config.TenantToken'
							:label='$t("maintenance.mender.service.form.client.tenantToken")'
							:placeholder='$t("maintenance.mender.service.form.placeholders.tenantToken")'
							:success='touched ? valid : null'
							:error-messages='errors'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='min:0|required|integer'
						:custom-messages='{
							integer: $t("forms.errors.integer"),
							min: $t("maintenance.mender.service.errors.inventoryPollInterval"),
							required: $t("maintenance.mender.service.errors.inventoryPollInterval"),
						}'
					>
						<v-text-field
							v-model.number='config.client.config.InventoryPollIntervalSeconds'
							:label='$t("maintenance.mender.service.form.client.inventoryPollInterval")'
							type='number'
							min='0'
							:success='touched ? valid : null'
							:error-messages='errors'
						>
							<template #append>
								<v-chip
									color='info'
									label
									small
								>
									{{ inventoryPollTime }}
								</v-chip>
							</template>
						</v-text-field>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='min:0|required|integer'
						:custom-messages='{
							integer: $t("forms.errors.integer"),
							min: $t("maintenance.mender.service.errors.retryPollInterval"),
							required: $t("maintenance.mender.service.errors.retryPollInterval"),
						}'
					>
						<v-text-field
							v-model.number='config.client.config.RetryPollIntervalSeconds'
							:label='$t("maintenance.mender.service.form.client.retryPollInterval")'
							type='number'
							min='0'
							:success='touched ? valid : null'
							:error-messages='errors'
						>
							<template #append>
								<v-chip
									color='info'
									label
									small
								>
									{{ retryPollTime }}
								</v-chip>
							</template>
						</v-text-field>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='min:0|required|integer'
						:custom-messages='{
							integer: $t("forms.errors.integer"),
							min: $t("maintenance.mender.service.errors.updatePollInterval"),
							required: $t("maintenance.mender.service.errors.updatePollInterval"),
						}'
					>
						<v-text-field
							v-model.number='config.client.config.UpdatePollIntervalSeconds'
							:label='$t("maintenance.mender.service.form.client.updatePollInterval")'
							type='number'
							min='0'
							:success='touched ? valid : null'
							:error-messages='errors'
						>
							<template #append>
								<v-chip
									color='info'
									label
									small
								>
									{{ updatePollTime }}
								</v-chip>
							</template>
						</v-text-field>
					</ValidationProvider>
					<v-divider class='my-2' />
					<h5>{{ $t('maintenance.mender.service.form.features') }}</h5>
					<v-checkbox
						v-model='config.connect.config.FileTransfer'
						:label='$t("maintenance.mender.service.form.connect.fileTransfer")'
						dense
					/>
					<v-checkbox
						v-model='config.connect.config.PortForward'
						:label='$t("maintenance.mender.service.form.connect.portForward")'
						dense
					/>
					<v-btn
						color='primary'
						type='submit'
						:disabled='invalid || uploadCert'
					>
						{{ $t('forms.save') }}
					</v-btn>
				</v-form>
			</ValidationObserver>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {MenderService} from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {MenderConfig} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {AxiosError} from 'axios';
import humanizeDuration from 'humanize-duration';
import {Component, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {extendedErrorToast} from '@/helpers/errorToast';
import {integer, min_value, required} from 'vee-validate/dist/rules';
import {menderServerUrl} from '@/helpers/validationRules/Maintenance';
import {useApiClient} from '@/services/ApiClient';

@Component({
	components: {
		ValidationObserver,
		ValidationProvider,
	}
})

/**
 * Mender configuration form component
 */
export default class MenderForm extends Vue {

	/**
	 * @var {MenderConfig} configuration Mender configuration
	 */
	public config: MenderConfig = {
		client: {
			config: {
				Servers: ['https://hosted.mender.io'],
				ServerCertificate: '',
				TenantToken: 'dummy',
				InventoryPollIntervalSeconds: 28800,
				RetryPollIntervalSeconds: 300,
				UpdatePollIntervalSeconds: 1800,
			},
			version: 3
		},
		connect: {
			config: {
				FileTransfer: false,
				PortForward: false,
			},
			version: 2,
		},
	};

	/**
	 * @var {File|null} certificate Certificate file to upload
	 */
	public certificate: File|null = null;

	/**
	 * @var {boolean} uploadCert Controls Mender form
	 */
	public uploadCert = false;

	/**
	 * @var {MenderService} service Mender service
	 */
	private service: MenderService = useApiClient().getConfigServices().getMenderService();

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('integer', integer);
		extend('required', required);
		extend('min', min_value);
		extend('serverUrl', menderServerUrl);
	}

	/**
	 * Retrieves mender configuration
	 */
	mounted(): void {
		this.getConfig();
	}

	/**
	 * Calculates human readable time string for inventory poll interval
	 * @return {string} Inventory poll interval string
	 */
	get inventoryPollTime(): string {
		return humanizeDuration(this.config.client.config.InventoryPollIntervalSeconds * 1_000);
	}

	/**
	 * Calculates human readable time string for retry poll interval
	 * @return {string} Retry poll interval string
	 */
	get retryPollTime(): string {
		return humanizeDuration(this.config.client.config.RetryPollIntervalSeconds * 1_000);
	}

	/**
	 * Calculates human readable time string for update poll interval
	 * @return {string} Retry update interval string
	 */
	get updatePollTime(): string {
		return humanizeDuration(this.config.client.config.UpdatePollIntervalSeconds * 1_000);
	}

	/**
	 * Merges two arrays in alternating order into a string
	 * @param {Array<string>} array1 First array to merge
	 * @param {Array<string>} array2 Second array to merge
	 * @return {string} Time string
	 */
	private mergeArrays(array1: Array<number>, array2: Array<string>): string {
		const result: Array<string> = [];
		for (let i = 0; i < array1.length; i++) {
			result.push(array1[i] + array2[i]);
		}
		return result.join(', ');
	}

	/**
	 * Retrieves configuration of the Mender feature
	 */
	public getConfig(): Promise<void> {
		if (!this.$store.getters['spinner/isEnabled']) {
			this.$store.commit('spinner/SHOW');
		}
		return this.service.getConfig()
			.then((response: MenderConfig) => {
				if (this.uploadCert) {
					this.uploadCert = false;
				}
				this.config = response;
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'maintenance.mender.service.messages.fetchFailed'));
	}

	/**
	 * Updates configuration of the Mender feature
	 */
	public processSubmit(): void {
		this.$store.commit('spinner/SHOW');
		if (this.uploadCert && this.certificate !== null) {
			this.service.uploadCert(this.certificate)
				.then((path: string) => {
					this.config.client.config.ServerCertificate = path;
					this.saveConfig();
				})
				.catch((error: AxiosError) => extendedErrorToast(
					error,
					'maintenance.mender.service.messages.certificateFailed',
				));
		} else {
			this.saveConfig();
		}
	}

	/**
	 * Saves Mender client configuration
	 */
	private saveConfig(): void {
		const config: MenderConfig = { ...this.config };
		this.service.updateConfig(config)
			.then(() => this.getConfig().then(() => this.$toast.success(
				this.$t('maintenance.mender.service.messages.saveSuccess').toString()
			)))
			.catch((error: AxiosError) => extendedErrorToast(error, 'maintenance.mender.service.messages.saveFailed'));
	}

	/**
	 * Fixes up server URL
	 * @param {number} idx Index of the server
	 */
	public serverUrlFixup(idx: number): void {
		const server = this.config.client.config.Servers[idx];
		if (!/^https?:\/\//i.test(server)) {
			this.config.client.config.Servers[idx] = `https://${server}`;
		}
	}
}
</script>
