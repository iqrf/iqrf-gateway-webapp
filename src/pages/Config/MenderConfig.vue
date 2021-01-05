<template>
	<div>
		<h1>{{ $t('config.mender.title') }}</h1>
		<CCard>
			<CCardBody>
				<ValidationObserver v-if='configuration !== null' v-slot='{ invalid }'>
					<CForm @submit.prevent='processSubmit'>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='addr|required'
							:custom-messages='{
								addr: "config.mender.errors.invalid.server",
								required: "config.mender.errors.missing.server"
							}'
						>
							<CInput
								v-model='configuration.ServerURL'
								:label='$t("config.mender.form.server")'
								:placeholder='$t("config.mender.form.serverPlaceholder")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required'
							:custom-messages='{
								required: "config.mender.errors.missing.tenantToken"
							}'
						>
							<CInput
								v-model='configuration.TenantToken'
								:label='$t("config.mender.form.tenantToken")'
								:placeholder='$t("config.mender.form.tenantTokenPlaceholder")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='min:0|required|integer'
							:custom-messages='{
								integer: "forms.messages.integer",
								min: "config.mender.errors.inventoryPollInterval",
								required: "config.mender.errors.inventoryPollInterval"
							}'
						>
							<CInput
								v-model.number='configuration.InventoryPollIntervalSeconds'
								type='number'
								min='0'
								:label='$t("config.mender.form.inventoryPollInterval")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='min:0|required|integer'
							:custom-messages='{
								integer: "forms.messages.integer",
								min: "config.mender.errors.retryPollInterval",
								required: "config.mender.errors.retryPollInterval"
							}'
						>
							<CInput
								v-model.number='configuration.RetryPollIntervalSeconds'
								type='number'
								min='0'
								:label='$t("config.mender.form.retryPollInterval")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid}'
							rules='min:0|required|integer'
							:custom-messages='{
								integer: "forms.messages.integer",
								min: "config.mender.errors.updatePollInterval",
								required: "config.mender.errors.updatePollInterval"
							}'
						>
							<CInput
								v-model.number='configuration.UpdatePollIntervalSeconds'
								type='number'
								min='0'
								:label='$t("config.mender.form.updatePollInterval")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<CButton color='primary' type='submit' :disabled='invalid'>
							{{ $t('forms.save') }}
						</CButton>
					</CForm>
				</ValidationObserver>
				<CAlert v-else color='danger'>
					{{ $t('config.mender.messages.loadFailed') }}
				</CAlert>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {AxiosError, AxiosResponse} from 'axios';
import {CAlert, CButton, CCard, CCardBody, CCardHeader, CForm, CInput} from '@coreui/vue/src';
import {integer, min_value, required} from 'vee-validate/dist/rules';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import FeatureConfigService from '../../services/FeatureConfigService';
import { NavigationGuardNext, Route } from 'vue-router';

interface IMenderConfig {
	InventoryPollIntervalSeconds: number
	RetryPollIntervalSeconds: number
	ServerURL: string
	TenantToken: string
	UpdatePollIntervalSeconds: number
}

@Component({
	components: {
		CAlert,
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
					vm.$t('config.mender.messages.disabled').toString()
				);
				vm.$router.push(from.path);
			}
		});
	},
	metaInfo: {
		title: 'config.mender.description',
	},
})

/**
 * Mender feature configuration component
 */
export default class MenderConfig extends Vue {
	/**
	 * @var {IMenderConfig} configuration Mender feature configuration
	 */
	private configuration: IMenderConfig|null = null

	/**
	 * @constant {string} name Mender feature name
	 */
	private name = 'mender'

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
		this.getConfig();
	}

	/**
	 * Retrieves configuration of the Mender feature
	 */
	getConfig(): void {
		this.$store.commit('spinner/SHOW');
		FeatureConfigService.getConfig(this.name)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.configuration = response.data;
			})
			.catch((error: AxiosError) => {
				FormErrorHandler.configError(error);
			});
	}
	
	/**
	 * Updates configuration of the Mender feature
	 */
	processSubmit(): void {
		this.$store.commit('spinner/SHOW');
		FeatureConfigService.saveConfig(this.name, this.configuration)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(this.$t('forms.messages.saveSuccess').toString());
			})
			.catch((error: AxiosError) => {
				FormErrorHandler.configError(error);
			});
	}
}
</script>
