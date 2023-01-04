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
	<CCard body-wrapper>
		<ValidationObserver v-slot='{invalid}'>
			<CForm @submit.prevent='processSubmit'>
				<h5>{{ $t('maintenance.mender.service.form.connection') }}</h5>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					rules='required|serverUrl'
					:custom-messages='{
						required: $t("maintenance.mender.service.errors.serverMissing"),
						serverUrl: $t("maintenance.mender.service.errors.serverInvalid")
					}'
				>
					<CInput
						v-model='config.client.ServerURL'
						:label='$t("maintenance.mender.service.form.client.server")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='errors.join(", ")'
						@change='serverUrlFixup'
					/>
				</ValidationProvider>
				<CInput
					v-model='config.client.ServerCertificate'
					:label='$t("maintenance.mender.service.form.client.cert")'
					:disabled='uploadCert'
				/>
				<div class='form-group'>
					<label for='uploadSwitch'>
						{{ $t('maintenance.mender.service.form.client.uploadCert') }}
					</label><br>
					<CSwitch
						ref='uploadSwitch'
						color='primary'
						size='lg'
						shape='pill'
						label-on='ON'
						label-off='OFF'
						:checked.sync='uploadCert'
					/>
				</div>
				<CInputFile
					v-if='uploadCert'
					ref='formCert'
					accept='.crt'
					:label='$t("maintenance.mender.service.form.client.newCert")'
					@input='checkInput'
					@click='checkInput'
				/>
				<hr>
				<h5>{{ $t('maintenance.mender.service.form.inventory') }}</h5>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					rules='required'
					:custom-messages='{
						required: $t("maintenance.mender.service.errors.tenantToken"),
					}'
				>
					<CInput
						v-model='config.client.TenantToken'
						:label='$t("maintenance.mender.service.form.client.tenantToken")'
						:placeholder='$t("maintenance.mender.service.form.placeholders.tenantToken")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='errors.join(", ")'
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
					<CInput
						id='inventoryPoll'
						v-model.number='config.client.InventoryPollIntervalSeconds'
						type='number'
						min='0'
						:label='$t("maintenance.mender.service.form.client.inventoryPollInterval")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='errors.join(", ")'
					>
						<template #append-content>
							<CBadge color='info'>
								{{ inventoryPollTime }}
							</CBadge>
						</template>
					</CInput>
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
					<CInput
						id='retryPoll'
						v-model.number='config.client.RetryPollIntervalSeconds'
						type='number'
						min='0'
						:label='$t("maintenance.mender.service.form.client.retryPollInterval")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='errors.join(", ")'
					>
						<template #append-content>
							<CBadge color='info'>
								{{ retryPollTime }}
							</CBadge>
						</template>
					</CInput>
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
					<CInput
						v-model.number='config.client.UpdatePollIntervalSeconds'
						type='number'
						min='0'
						:label='$t("maintenance.mender.service.form.client.updatePollInterval")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='errors.join(", ")'
					>
						<template #append-content>
							<CBadge color='info'>
								{{ updatePollTime }}
							</CBadge>
						</template>
					</CInput>
				</ValidationProvider>
				<hr>
				<h4>{{ $t('maintenance.mender.service.form.features') }}</h4>
				<CInputCheckbox
					:checked.sync='config.connect.FileTransfer'
					:label='$t("maintenance.mender.service.form.connect.fileTransfer")'
				/>
				<CInputCheckbox
					:checked.sync='config.connect.PortForward'
					:label='$t("maintenance.mender.service.form.connect.portForward")'
				/>
				<CButton
					color='primary'
					type='submit'
					:disabled='invalid || (uploadCert && inputEmpty)'
				>
					{{ $t('forms.save') }}
				</CButton>
			</CForm>
		</ValidationObserver>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CBadge, CButton, CForm, CInput, CInputCheckbox, CInputFile, CSelect} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {Duration} from 'luxon';
import {extendedErrorToast} from '@/helpers/errorToast';
import {integer, min_value, required} from 'vee-validate/dist/rules';
import {menderServerUrl} from '@/helpers/validationRules/Maintenance';

import FeatureConfigService from '@/services/FeatureConfigService';

import {AxiosError, AxiosResponse} from 'axios';
import {IMenderConfig} from '@/interfaces/Maintenance/Mender';
import MenderService from '@/services/MenderService';

@Component({
	components: {
		CBadge,
		CButton,
		CForm,
		CInput,
		CInputCheckbox,
		CInputFile,
		CSelect,
		ValidationObserver,
		ValidationProvider,
	}
})

/**
 * Mender configuration form component
 */
export default class MenderForm extends Vue {

	/**
	 * @constant {string} featureName Feature name
	 */
	private featureName = 'mender';

	/**
	 * @var {IMenderConfig} configuration Mender configuration
	 */
	private config: IMenderConfig = {
		client: {
			ServerURL: 'https://hosted.mender.io',
			ServerCertificate: '',
			TenantToken: 'dummy',
			InventoryPollIntervalSeconds: 28800,
			RetryPollIntervalSeconds: 300,
			UpdatePollIntervalSeconds: 1800,
		},
		connect: {
			FileTransfer: true,
			PortForward: true,
		},
	};

	/**
	 * @var {boolean} uploadCert Controls Mender form
	 */
	private uploadCert = false;

	/**
	 * @var {boolean} inputEmpty Empty status of the cert input
	 */
	private inputEmpty = true;

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
		return this.periodString(this.config.client.InventoryPollIntervalSeconds);
	}

	/**
	 * Calculates human readable time string for retry poll interval
	 * @return {string} Retry poll interval string
	 */
	get retryPollTime(): string {
		return this.periodString(this.config.client.RetryPollIntervalSeconds);
	}

	/**
	 * Calculates human readable time string for update poll interval
	 * @return {string} Retry update interval string
	 */
	get updatePollTime(): string {
		return this.periodString(this.config.client.UpdatePollIntervalSeconds);
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
				this.config = response.data;
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'maintenance.mender.service.messages.fetchFailed'));
	}

	/**
	 * Extracts files from form file input
	 */
	private getInputFiles(): FileList {
		const input = ((this.$refs.formCert as CInputFile).$el.children[1] as HTMLInputElement);
		return (input.files as FileList);
	}

	/**
	 * Checks if certificate input is empty
	 */
	private checkInput(): void {
		const files = this.getInputFiles();
		this.inputEmpty = files.length === 0;
	}

	/**
	 * Updates configuration of the Mender feature
	 */
	private processSubmit(): void {
		this.$store.commit('spinner/SHOW');
		if (this.uploadCert) {
			const formData = new FormData();
			formData.append('certificate', this.getInputFiles()[0]);
			MenderService.uploadCertificate(formData)
				.then((response: AxiosResponse) => {
					this.config.client.ServerCertificate = response.data;
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
		const config: IMenderConfig = JSON.parse(JSON.stringify(this.config));
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

	private serverUrlFixup(): void {
		const server = this.config.client.ServerURL;
		if (!/^https?:\/\//i.test(server)) {
			this.config.client.ServerURL = `https://${server}`;
		}
	}
}
</script>
