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
	<ValidationObserver v-if='configuration !== null' v-slot='{invalid}'>
		<hr>
		<CForm @submit.prevent='processSubmit'>
			<ValidationProvider
				v-slot='{errors, touched, valid}'
				rules='required'
				:custom-messages='{
					required: "maintenance.mender.service.errors.server"
				}'
			>
				<CInput
					v-model='configuration.ServerURL'
					:label='$t("maintenance.mender.service.form.server")'
					:placeholder='$t("maintenance.mender.service.form.placeholders.server")'
					:is-valid='touched ? valid : null'
					:invalid-feedback='$t(errors[0])'
				/>
			</ValidationProvider>
			<CSelect
				:value.sync='configuration.ClientProtocol'
				:label='$t("maintenance.mender.service.form.protocol")'
				:options='protocolOptions'
			/>
			<ValidationProvider
				v-slot='{errors, touched, valid}'
				rules='required'
				:custom-messages='{
					required: "maintenance.mender.service.errors.tenantToken"
				}'
			>
				<CInput
					v-model='configuration.TenantToken'
					:label='$t("maintenance.mender.service.form.tenantToken")'
					:placeholder='$t("maintenance.mender.service.form.placeholders.tenantToken")'
					:is-valid='touched ? valid : null'
					:invalid-feedback='$t(errors[0])'
				/>
			</ValidationProvider>
			<ValidationProvider
				v-slot='{errors, touched, valid}'
				rules='min:0|required|integer'
				:custom-messages='{
					integer: "forms.errors.integer",
					min: "maintenance.mender.service.errors.inventoryPollInterval",
					required: "maintenance.mender.service.errors.inventoryPollInterval"
				}'
			>
				<b>
					<label for='inventoryPoll'>
						{{ $t('maintenance.mender.service.form.inventoryPollInterval') }}
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
					min: "maintenance.mender.service.errors.retryPollInterval",
					required: "maintenance.mender.service.errors.retryPollInterval"
				}'
			>
				<b>
					<label for='retryPoll'>
						{{ $t('maintenance.mender.service.form.retryPollInterval') }}
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
					min: "maintenance.mender.service.errors.updatePollInterval",
					required: "maintenance.mender.service.errors.updatePollInterval"
				}'
			>
				<b>
					<label for='retryPoll'>
						{{ $t('maintenance.mender.service.form.updatePollInterval') }}
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
import {CBadge, CButton, CForm, CInput, CSelect} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {Duration} from 'luxon';
import {integer, min_value, required} from 'vee-validate/dist/rules';
import {MenderProtocols} from '../../enums/Maintenance/Mender';

import FeatureConfigService from '../../services/FeatureConfigService';

import {AxiosError, AxiosResponse} from 'axios';
import {IMenderConfig} from '../../interfaces/maintenance';
import {extendedErrorToast} from '../../helpers/errorToast';
import { IOption } from '../../interfaces/coreui';

@Component({
	components: {
		CBadge,
		CButton,
		CForm,
		CInput,
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
	private featureName = 'mender'

	/**
	 * @var {IMenderConfig} configuration Mender configuration
	 */
	private configuration: IMenderConfig|null = null

	/**
	 * @constant {Array<IOption>} protocolOptions Array of CoreUI select mender client protocol options
	 */
	private protocolOptions: Array<IOption> = [
		{
			label: this.$t('maintenance.mender.service.form.protocols.https').toString(),
			value: MenderProtocols.HTTPS,
		},
		{
			label: this.$t('maintenance.mender.service.form.protocols.wss').toString(),
			value: MenderProtocols.WSS,
		},
	]

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
		const duration = Duration.fromMillis(seconds * 1000).shiftTo('days', 'hours', 'minutes', 'seconds').toObject();
		let nums: Array<number> = [], units: Array<string> = [];
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

	/**
	 * Merges two arrays in alternating order into a string
	 * @param {Array<string>} array1 First array to merge
	 * @param {Array<string>} array2 Second array to merge
	 * @return {string} Time string
	 */
	private mergeArrays(array1: Array<number>, array2: Array<string>): string {
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
			.catch((error: AxiosError) => extendedErrorToast(error, 'maintenance.mender.service.messages.fetchFailed'));
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
				this.$t('maintenance.mender.service.messages.saveSuccess').toString()
			)))
			.catch((error: AxiosError) => extendedErrorToast(error, 'maintenance.mender.service.messages.saveFailed'));
	}

}
</script>

<style>

</style>