<template>
	<div>
		<h1>{{ $t('cloud.mender.title') }}</h1>
		<CCard body-wrapper>
			<table class='table table-striped'>
				<tbody>
					<tr>
						<th>{{ $t('service.status') }}</th>
						<td v-if='missing'>
							{{ $t('service.states.missing') }}
						</td>
						<td v-if='unsupported'>
							{{ $t('service.states.unsupported') }}
						</td>
						<td v-if='service !== null'>
							<div>
								{{ $t('states.' + (service.enabled ? 'enabled' : 'disabled')) }},
								{{ $t('service.states.' + (service.active ? 'active' : 'inactive')) }}
							</div>
						</td>
						<td
							v-if='service !== null'
							style='text-align: right; white-space:nowrap;'
						>
							<CButton
								v-if='!service.enabled'
								color='success'
								size='sm'
								@click='enable()'
							>
								{{ $t('service.actions.enable') }}
							</CButton> <CButton
								v-if='service.enabled'
								color='danger'
								size='sm'
								@click='disable()'
							>
								{{ $t('service.actions.disable') }}
							</CButton> <CButton
								color='primary'
								size='sm'
								@click='restart()'
							>
								{{ $t('service.actions.restart') }}
							</CButton>
						</td>
					</tr>
				</tbody>
			</table>
			<ValidationObserver v-if='configuration !== null' v-slot='{invalid}'>
				<CForm @submit.prevent='processSubmit'>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='required'
						:custom-messages='{
							required: "cloud.mender.errors.server"
						}'
					>
						<CInput
							v-model='configuration.ServerURL'
							:label='$t("cloud.mender.form.server")'
							:placeholder='$t("cloud.mender.form.placeholders.server")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='required'
						:custom-messages='{
							required: "cloud.mender.errors.tenantToken"
						}'
					>
						<CInput
							v-model='configuration.TenantToken'
							:label='$t("cloud.mender.form.tenantToken")'
							:placeholder='$t("cloud.mender.form.placeholders.tenantToken")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='min:0|required|integer'
						:custom-messages='{
							integer: "forms.errors.integer",
							min: "cloud.mender.errors.inventoryPollInterval",
							required: "cloud.mender.errors.inventoryPollInterval"
						}'
					>
						<b>
							<label for='inventoryPoll'>
								{{ $t('cloud.mender.form.inventoryPollInterval') }}
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
							min: "cloud.mender.errors.retryPollInterval",
							required: "cloud.mender.errors.retryPollInterval"
						}'
					>
						<b>
							<label for='retryPoll'>
								{{ $t('cloud.mender.form.retryPollInterval') }}
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
							min: "cloud.mender.errors.updatePollInterval",
							required: "cloud.mender.errors.updatePollInterval"
						}'
					>
						<b>
							<label for='retryPoll'>
								{{ $t('cloud.mender.form.updatePollInterval') }}
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
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {Duration} from 'luxon';
import {integer, min_value, required} from 'vee-validate/dist/rules';
import FeatureConfigService from '../../services/FeatureConfigService';
import ServiceService from '../../services/ServiceService';

import {AxiosError, AxiosResponse} from 'axios';
import {NavigationGuardNext, Route} from 'vue-router';
import {ServiceStatus} from '../../services/ServiceService';


interface IMenderConfig {
	InventoryPollIntervalSeconds: number
	RetryPollIntervalSeconds: number
	ServerURL: string
	TenantToken: string
	UpdatePollIntervalSeconds: number
}

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		ValidationObserver,
		ValidationProvider
	},
	beforeRouteEnter(to: Route, from: Route, next: NavigationGuardNext): void {
		next((vm: Vue) => {
			if (!vm.$store.getters['features/isEnabled']('mender')) {
				vm.$toast.error(
					vm.$t('service.mender-client.messages.disabled').toString()
				);
				vm.$router.push(from.path);
			}
		});
	},
	metaInfo: {
		title: 'cloud.mender.title',
	},
})

/**
 * Mender service and configuration component
 */
export default class MenderControl extends Vue {
	/**
	 * @var {IMenderConfig} configuration Mender feature configuration
	 */
	private configuration: IMenderConfig|null = null

	/**
	 * @constant {string} featureName Mender feature name
	 */
	private featureName = 'mender'

	/**
	 * @constant {string} serviceName Mender service name 
	 */
	private serviceName = 'mender-client'

	/**
	 * @var {boolean} missing Indicates that Mender service is not installed
	 */
	private missing = false

	/**
	 * @var {boolean} unsupported Indicates that Mender service is unsupported
	 */
	private unsupported = false

	/**
	 * @var {ServiceStatus|null} service Mender service status object
	 */
	private service: ServiceStatus|null = null

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
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		this.getStatus();
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
	 * Retrieves status of the Pixla service
	 */
	private getStatus(): Promise<void> {
		return ServiceService.getStatus(this.serviceName)
			.then((status: ServiceStatus) => {
				this.service = status;
				this.unsupported = false;
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t(
						'cloud.mender.messages.serviceStatusFailed',
						{error: error.response === undefined ? error.message : error.response.data.message},
					).toString()
				);
			});
	}

	/**
	 * Enables the Mender service
	 */
	private enable(): void {
		this.$store.commit('spinner/SHOW');
		ServiceService.enable(this.serviceName)
			.then(() => this.serviceSuccess('service.' + this.serviceName + '.messages.enable'))
			.catch((error: AxiosError) => {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t(
						'service.messages.enableFailed',
						{error: error.response ? error.response.data.message : error.message, service: 'Mender'}
					).toString()
				);
			});
	}

	/**
	 * Disables the Mender service
	 */
	private disable(): void {
		this.$store.commit('spinner/SHOW');
		ServiceService.disable(this.serviceName)
			.then(() => this.serviceSuccess('service.' + this.serviceName + '.messages.disable'))
			.catch((error: AxiosError) => {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t(
						'service.messages.disableFailed',
						{error: error.response ? error.response.data.message : error.message, service: 'Mender'}
					).toString()
				);
			});
	}

	/**
	 * Restarts the Mender service
	 */
	private restart(): void {
		this.$store.commit('spinner/SHOW');
		ServiceService.restart(this.serviceName)
			.then(() => this.serviceSuccess('service.' + this.serviceName + '.messages.restart'))
			.catch((error: AxiosError) => {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t(
						'service.messages.restartFailed',
						{error: error.response ? error.response.data.message : error.message, service: 'Mender'}
					).toString()
				);
			});
	}

	/**
	 * Handles successful Service operations
	 * @param {string} message Toast message
	 */
	private serviceSuccess(message: string): void {
		this.getStatus().then(() => {
			this.$toast.success(
				this.$t(message).toString()
			);
		});
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
			.catch((error: AxiosError) => {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t(
						'cloud.mender.messages.fetchFailed',
						{error: error.response === undefined ? error.message : error.response.data.message}
					).toString()
				);
			});
	}
	
	/**
	 * Updates configuration of the Mender feature
	 */
	processSubmit(): void {
		this.$store.commit('spinner/SHOW');
		FeatureConfigService.saveConfig(this.featureName, this.configuration)
			.then(() => {
				this.getConfig().then(() => this.$toast.success(
					this.$t('cloud.mender.messages.saveSuccess').toString())
				);
			})
			.catch((error: AxiosError) => {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t(
						'cloud.mender.messages.saveFailed',
						{error: error.response === undefined ? error.message : error.response.data.message}
					).toString()
				);
			});
	}
}
</script>
