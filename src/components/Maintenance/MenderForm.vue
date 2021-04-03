<template>
	<ValidationObserver v-if='configuration !== null' v-slot='{invalid}'>
		<hr>
		<CForm @submit.prevent='processSubmit'>
			<ValidationProvider
				v-slot='{errors, touched, valid}'
				rules='required'
				:custom-messages='{
					required: "maintenance.mender.errors.server"
				}'
			>
				<CInput
					v-model='configuration.ServerURL'
					:label='$t("maintenance.mender.form.server")'
					:placeholder='$t("maintenance.mender.form.placeholders.server")'
					:is-valid='touched ? valid : null'
					:invalid-feedback='$t(errors[0])'
				/>
			</ValidationProvider>
			<ValidationProvider
				v-slot='{errors, touched, valid}'
				rules='required'
				:custom-messages='{
					required: "maintenance.mender.errors.tenantToken"
				}'
			>
				<CInput
					v-model='configuration.TenantToken'
					:label='$t("maintenance.mender.form.tenantToken")'
					:placeholder='$t("maintenance.mender.form.placeholders.tenantToken")'
					:is-valid='touched ? valid : null'
					:invalid-feedback='$t(errors[0])'
				/>
			</ValidationProvider>
			<ValidationProvider
				v-slot='{errors, touched, valid}'
				rules='min:0|required|integer'
				:custom-messages='{
					integer: "forms.errors.integer",
					min: "maintenance.mender.errors.inventoryPollInterval",
					required: "maintenance.mender.errors.inventoryPollInterval"
				}'
			>
				<b>
					<label for='inventoryPoll'>
						{{ $t('maintenance.mender.form.inventoryPollInterval') }}
					</label>
				</b> <CBadge color='info'>
					{{ inventoryPollTime }}
				</CBadge>
				<CInput
					id='inventoryPoll'
					v-model.number='configuration.InventoryPollIntervalSeconds'
					type='number'
					min='0'
					:is-valid='touched ? valid : null'
					:invalid-feedback='$t(errors[0])'
				/>
			</ValidationProvider>
			<ValidationProvider
				v-slot='{errors, touched, valid}'
				rules='min:0|required|integer'
				:custom-messages='{
					integer: "forms.errors.integer",
					min: "maintenance.mender.errors.retryPollInterval",
					required: "maintenance.mender.errors.retryPollInterval"
				}'
			>
				<b>
					<label for='retryPoll'>
						{{ $t('maintenance.mender.form.retryPollInterval') }}
					</label>
				</b> <CBadge color='info'>
					{{ retryPollTime }}
				</CBadge>
				<CInput
					id='retryPoll'
					v-model.number='configuration.RetryPollIntervalSeconds'
					type='number'
					min='0'
					:is-valid='touched ? valid : null'
					:invalid-feedback='$t(errors[0])'
				/>
			</ValidationProvider>
			<ValidationProvider
				v-slot='{errors, touched, valid}'
				rules='min:0|required|integer'
				:custom-messages='{
					integer: "forms.errors.integer",
					min: "maintenance.mender.errors.updatePollInterval",
					required: "maintenance.mender.errors.updatePollInterval"
				}'
			>
				<b>
					<label for='retryPoll'>
						{{ $t('maintenance.mender.form.updatePollInterval') }}
					</label>
				</b> <CBadge color='info'>
					{{ updatePollTime }}
				</CBadge>
				<CInput
					id='updatePoll'
					v-model.number='configuration.UpdatePollIntervalSeconds'
					type='number'
					min='0'
					:is-valid='touched ? valid : null'
					:invalid-feedback='$t(errors[0])'
				/>
			</ValidationProvider>
			<CButton color='primary' type='submit' :disabled='invalid'>
				{{ $t('forms.save') }}
			</CButton>
		</CForm>
	</ValidationObserver>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CBadge, CButton, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {Duration} from 'luxon';
import {integer, min_value, required} from 'vee-validate/dist/rules';

import FeatureConfigService from '../../services/FeatureConfigService';

import {AxiosError, AxiosResponse} from 'axios';
import {IMenderConfig} from '../../interfaces/maintenance';
import {extendedErrorToast} from '../../helpers/errorToast';

@Component({
	components: {
		CBadge,
		CButton,
		CForm,
		CInput,
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
	private featureName = 'mender'

	/**
	 * @var {IMenderConfig} configuration Mender configuration
	 */
	private configuration: IMenderConfig|null = null

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
	 * Converts seconds to readable period string
	 */
	private periodString(seconds: number): string {
		const duration = Duration.fromMillis(seconds * 1000);
		if (seconds >= 0 && seconds < 60) {
			return duration.toFormat('s') + ' seconds';
		} else if (seconds < 3600) {
			const tok = duration.toFormat('m ss').split(' ');
			return this.mergeArrays(tok, [' minutes', ' seconds']);
		} else if (seconds < 86400) {
			const tok = duration.toFormat('h mm ss').split(' ');
			return this.mergeArrays(tok, [' hours', ' minutes', ' seconds']);
		} else {
			const tok = duration.toFormat('d hh mm ss').split(' ');
			return this.mergeArrays(tok, [' days', ' hours', ' minutes', ' seconds']);
		}
	}

	/**
	 * Merges two arrays in alternating order into a string
	 * @param {Array<string>} array1 First array to merge
	 * @param {Array<string>} array2 Second array to merge
	 * @return {string} Time string
	 */
	private mergeArrays(array1: Array<string>, array2: Array<string>): string {
		let result: Array<string> = [];
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
				this.$store.commit('spinner/HIDE');
				this.configuration = response.data;
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'maintenance.mender.messages.fetchFailed'));
	}
	
	/**
	 * Updates configuration of the Mender feature
	 */
	processSubmit(): void {
		if (this.configuration === null) {
			return;
		}
		this.$store.commit('spinner/SHOW');
		FeatureConfigService.saveConfig(this.featureName, this.configuration)
			.then(() => this.getConfig().then(() => this.$toast.success(
				this.$t('maintenance.mender.messages.saveSuccess').toString()
			)))
			.catch((error: AxiosError) => extendedErrorToast(error, 'maintenance.mender.messages.saveFailed'));
	}

}
</script>

<style>

</style>