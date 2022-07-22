<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

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
				<v-form @submit.prevent='save'>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='required'
						:custom-messages='{
							required: $t("maintenance.mender.service.errors.server"),
						}'
					>
						<v-text-field
							v-model='configuration.ServerURL'
							:label='$t("maintenance.mender.service.form.server")'
							:placeholder='$t("maintenance.mender.service.form.placeholders.server")'
							:success='touched ? valid : null'
							:error-messages='errors'
						/>
					</ValidationProvider>
					<v-text-field
						v-model='configuration.ServerCertificate'
						:label='$t("maintenance.mender.service.form.cert")'
						:disabled='uploadCert'
					/>
					<v-switch
						v-model='uploadCert'
						:label='$t("maintenance.mender.service.form.uploadCert")'
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
							:label='$t("maintenance.mender.service.form.newCert")'
							prepend-icon='mdi-file-certificate'
							:success='touched ? valid : null'
							:error-messages='errors'
						/>
					</ValidationProvider>
					<v-select
						v-model='configuration.ClientProtocol'
						:label='$t("maintenance.mender.service.form.protocol")'
						:items='protocolOptions'
					/>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='required'
						:custom-messages='{
							required: $t("maintenance.mender.service.errors.tenantToken"),
						}'
					>
						<v-text-field
							v-model='configuration.TenantToken'
							:label='$t("maintenance.mender.service.form.tenantToken")'
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
							v-model.number='configuration.InventoryPollIntervalSeconds'
							:label='$t("maintenance.mender.service.form.inventoryPollInterval")'
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
							v-model.number='configuration.RetryPollIntervalSeconds'
							:label='$t("maintenance.mender.service.form.retryPollInterval")'
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
							v-model.number='configuration.UpdatePollIntervalSeconds'
							:label='$t("maintenance.mender.service.form.updatePollInterval")'
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
					<v-btn
						color='primary'
						type='submit'
						:disabled='invalid || (uploadCert && inputEmpty)'
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

import {Duration} from 'luxon';
import {extendedErrorToast} from '@/helpers/errorToast';
import {integer, min_value, required} from 'vee-validate/dist/rules';
import {MenderProtocols} from '@/enums/Maintenance/Mender';

import FeatureConfigService from '@/services/FeatureConfigService';

import {AxiosError, AxiosResponse} from 'axios';
import {IMenderConfig} from '@/interfaces/maintenance';
import {IOption} from '@/interfaces/coreui';
import MenderService from '@/services/MenderService';

@Component({
	components: {
		ValidationObserver,
		ValidationProvider,
	}
})

/**
 * Mender configuration form component
 */
export default class MenderServiceForm extends Vue {

	/**
	 * @constant {string} featureName Feature name
	 */
	private featureName = 'mender';

	/**
	 * @var {File|null} certificate Certificate file to upload
   */
	private certificate: File|null = null;

	/**
	 * @var {IMenderConfig} configuration Mender configuration
	 */
	private configuration: IMenderConfig = {
		ClientProtocol: MenderProtocols.HTTPS,
		ServerURL: '',
		ServerCertificate: '',
		TenantToken: 'dummy',
		InventoryPollIntervalSeconds: 28800,
		RetryPollIntervalSeconds: 300,
		UpdatePollIntervalSeconds: 1800
	};

	/**
	 * @var {boolean} uploadCert Controls Mender form
	 */
	private uploadCert = false;

	/**
	 * @constant {Array<IOption>} protocolOptions Array of CoreUI select mender client protocol options
	 */
	private protocolOptions: Array<IOption> = [
		{
			text: this.$t('maintenance.mender.service.form.protocols.https').toString(),
			value: MenderProtocols.HTTPS,
		},
		{
			text: this.$t('maintenance.mender.service.form.protocols.wss').toString(),
			value: MenderProtocols.WSS,
		},
	];

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('integer', integer);
		extend('required', required);
		extend('min', min_value);
		extend('addr', (addr) => {
			const regex = RegExp('(http|https):\\/\\/.*');
			return regex.test(addr);
		});
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
		if (this.configuration === null) {
			return '';
		}
		return this.periodString(this.configuration.InventoryPollIntervalSeconds);
	}

	/**
	 * Calculates human readable time string for retry poll interval
	 * @return {string} Retry poll interval string
	 */
	get retryPollTime(): string {
		if (this.configuration === null) {
			return '';
		}
		return this.periodString(this.configuration.RetryPollIntervalSeconds);
	}

	/**
	 * Calculates human readable time string for update poll interval
	 * @return {string} Retry update interval string
	 */
	get updatePollTime(): string {
		if (this.configuration === null) {
			return '';
		}
		return this.periodString(this.configuration.UpdatePollIntervalSeconds);
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
	getConfig(): Promise<void> {
		if (!this.$store.getters['spinner/isEnabled']) {
			this.$store.commit('spinner/SHOW');
		}
		return FeatureConfigService.getConfig(this.featureName)
			.then((response: AxiosResponse) => {
				if (this.uploadCert) {
					this.uploadCert = false;
				}
				this.configuration = response.data;
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'maintenance.mender.service.messages.fetchFailed'));
	}

	/**
	 * Updates configuration of the Mender feature
	 */
	private save(): void {
		if (this.configuration === null) {
			return;
		}
		this.$store.commit('spinner/SHOW');
		if (this.uploadCert && this.certificate !== null) {
			const formData = new FormData();
			formData.append('certificate', this.certificate);
			MenderService.uploadCertificate(formData)
				.then((response: AxiosResponse) => {
					this.configuration.ServerCertificate = response.data;
					this.saveConfig();
				})
				.catch((error: AxiosError) => extendedErrorToast(error,'maintenance.mender.service.messages.certificateFailed'));
		} else {
			this.saveConfig();
		}
	}

	/**
	 * Saves Mender client configuration
	 */
	private saveConfig(): void {
		const config: IMenderConfig = JSON.parse(JSON.stringify(this.configuration));
		FeatureConfigService.saveConfig(this.featureName, config)
			.then(() => this.getConfig().then(() => this.$toast.success(
				this.$t('maintenance.mender.service.messages.saveSuccess').toString()
			)))
			.catch((error: AxiosError) => extendedErrorToast(error, 'maintenance.mender.service.messages.saveFailed'));
	}

	/**
	 * Converts seconds to readable period string
	 */
	private periodString(seconds: number): string {
		const duration = Duration.fromMillis(seconds * 1000).shiftTo('days', 'hours', 'minutes', 'seconds').toObject();
		const nums: Array<number> = [], units: Array<string> = [];
		if (duration.days) {
			nums.push(duration.days);
			units.push(' days');
		}
		if (duration.hours) {
			nums.push(duration.hours);
			units.push(' hours');
		}
		if (duration.minutes) {
			nums.push(duration.minutes);
			units.push(' minutes');
		}
		if (duration.seconds) {
			nums.push(duration.seconds);
			units.push(' seconds');
		}
		return this.mergeArrays(nums, units);
	}
}
</script>
