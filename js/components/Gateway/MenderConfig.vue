<template>
	<CCard body-wrapper>
		<ValidationObserver v-if='config !== null' v-slot='{ invalid }'>
			<CForm @submit.prevent='processSubmit'>
				<ValidationProvider
					v-slot='{ errors, touched, valid }'
					rules='addr|required'
					:custom-messages='{
						addr: "gateway.mender.form.messages.invalid.server",
						required: "gateway.mender.form.messages.missing.server"
					}'
				>
					<CInput
						v-model='config.ServerURL'
						:label='$t("gateway.mender.form.server")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='$t(errors[0])'
					/>
				</ValidationProvider>
				<ValidationProvider
					v-slot='{ errors, touched, valid }'
					rules='required'
					:custom-messages='{
						required: "gateway.mender.form.messages.missing.tenantToken"
					}'
				>
					<CInput
						v-model='config.TenantToken'
						:label='$t("gateway.mender.form.tenantToken")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='$t(errors[0])'
					/>
				</ValidationProvider>
				<ValidationProvider
					v-slot='{ errors, touched, valid }'
					rules='min:0|required|integer'
					:custom-messages='{
						integer: "forms.messages.integer",
						min: "gateway.mender.form.messages.inventoryPollInterval",
						required: "gateway.mender.form.messages.inventoryPollInterval"
					}'
				>
					<CInput
						v-model.number='config.InventoryPollIntervalSeconds'
						type='number'
						min='0'
						:label='$t("gateway.mender.form.inventoryPollInterval")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='$t(errors[0])'
					/>
				</ValidationProvider>
				<ValidationProvider
					v-slot='{ errors, touched, valid }'
					rules='min:0|required|integer'
					:custom-messages='{
						integer: "forms.messages.integer",
						min: "gateway.mender.form.messages.retryPollInterval",
						required: "gateway.mender.form.messages.retryPollInterval"
					}'
				>
					<CInput
						v-model.number='config.RetryPollIntervalSeconds'
						type='number'
						min='0'
						:label='$t("gateway.mender.form.retryPollInterval")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='$t(errors[0])'
					/>
				</ValidationProvider>
				<ValidationProvider
					v-slot='{ errors, touched, valid}'
					rules='min:0|required|integer'
					:custom-messages='{
						integer: "forms.messages.integer",
						min: "gateway.mender.form.messages.updatePollInterval",
						required: "gateway.mender.form.messages.updatePollInterval"
					}'
				>
					<CInput
						v-model.number='config.UpdatePollIntervalSeconds'
						type='number'
						min='0'
						:label='$t("gateway.mender.form.updatePollInterval")'
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
</template>

<script>

import {CButton, CCard, CForm, CInput} from '@coreui/vue';
import {integer, min_value, required} from 'vee-validate/dist/rules';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {timeout} from '../../helpers/timeout';
import ConfigService from '../../services/ConfigService';

export default {
	name: 'MenderConfig',
	components: {
		CButton,
		CCard,
		CForm,
		CInput,
		ValidationObserver,
		ValidationProvider
	},
	data() {
		return {
			config: null,
			timeout: null,
		};
	},
	created() {
		extend('integer', integer);
		extend('required', required);
		extend('min', min_value);
		extend('addr', (addr) => {
			const regex = RegExp('(http|https):\\/\\/.*');
			return regex.test(addr);
		});
		this.getConfig();
	},
	methods: {
		getConfig() {
			this.timeout = timeout('forms.messages.getConfTimeout', 10000);
			this.$store.commit('spinner/SHOW');
			ConfigService.getConfig('menderConfig')
				.then((response) => {
					clearTimeout(this.timeout);
					this.$store.commit('spinner/HIDE');
					this.config = response.data;
				})
				.catch((error) => {
					clearTimeout(this.timeout);
					this.$store.commit('spinner/HIDE');
					this.handleError(error);
				});
		},
		processSubmit() {
			this.timeout = timeout('forms.messages.saveConfTimeout', 10000);
			this.$store.commit('spinner/SHOW');
			ConfigService.saveConfig('menderConfig', this.config)
				.then(() => {
					clearTimeout(this.timeout);
					this.$store.commit('spinner/HIDE');
					this.$toast.success(this.$t('forms.messages.saveSuccess'));
				})
				.catch((error) => {
					clearTimeout(this.timeout);
					this.$store.commit('spinner/HIDE');
					this.handleError(error);
				});
		},
		handleError(error) {
			if (error.response) {
				if (error.response.status === 500) {
					this.$toast.error(this.$t('forms.messages.submitServerError'));
				}
			} else {
				console.error(error.message);
			}
		}
	},
	metaInfo: {
		title: 'gateway.mender.title',
	},
};
</script>
