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
		<ValidationObserver
			v-if='configuration !== null'
			v-slot='{invalid}'
		>
			<CForm @submit.prevent='saveConfig'>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					rules='required'
					:custom-messages='{
						required: $t("maintenance.monit.errors.endpoint"),
					}'
				>
					<CInput
						v-model='configuration.endpoint'
						:label='$t("maintenance.monit.form.endpoint")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='errors.join(", ")'
					/>
				</ValidationProvider>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					rules='required|alphanum'
					:custom-messages='{
						required: $t("maintenance.monit.errors.username"),
						alphanum: $t("maintenance.monit.errors.usernameInvalid"),
					}'
				>
					<CInput
						v-model='configuration.username'
						:label='$t("maintenance.monit.form.username")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='errors.join(", ")'
					/>
				</ValidationProvider>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					rules='required|alphanum'
					:custom-messages='{
						required: $t("maintenance.monit.errors.password"),
						alphanum: $t("maintenance.monit.errors.passwordInvalid"),
					}'
				>
					<CInput
						v-model='configuration.password'
						:label='$t("maintenance.monit.form.password")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='errors.join(", ")'
					/>
				</ValidationProvider>
				<CButton
					color='primary'
					type='submit'
					:disabled='invalid'
				>
					{{ $t('forms.save') }}
				</CButton>
			</CForm>
		</ValidationObserver>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CForm, CInput} from '@coreui/vue/src';
import {ValidationObserver, ValidationProvider} from 'vee-validate';

import {extend} from 'vee-validate';
import {extendedErrorToast} from '@/helpers/errorToast';
import {required, alpha_num} from 'vee-validate/dist/rules';

import FeatureConfigService from '@/services/FeatureConfigService';

import {AxiosError, AxiosResponse} from 'axios';
import {IMonitConfig} from '@/interfaces/Maintenance/Monit';

@Component({
	components: {
		CButton,
		CForm,
		CInput,
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
	 * @var {IMonitConfig|null} configuration Monit configuration
	 */
	private configuration: IMonitConfig|null = null;

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
		extend('alphanum', alpha_num);
	}

	/**
	 * Retrieves config
	 */
	mounted(): void {
		this.getConfig();
	}

	/**
	 * Retrieves and store mmonit configuration
	 */
	private getConfig(): Promise<void> {
		if (!this.$store.getters['spinner/isEnabled']) {
			this.$store.commit('spinner/SHOW');
		}
		return FeatureConfigService.getConfig(this.featureName)
			.then((response: AxiosResponse) => {
				this.configuration = response.data;
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'maintenance.monit.messages.fetchFailed'));
	}

	/**
	 * Saves new mmonit configuration
	 */
	private saveConfig(): void {
		if (this.configuration === null) {
			return;
		}
		this.$store.commit('spinner/SHOW');
		FeatureConfigService.saveConfig(this.featureName, this.configuration)
			.then(() => this.getConfig().then(() => this.$toast.success(
				this.$t('maintenance.monit.messages.saveSuccess').toString()
			)))
			.catch((error) => extendedErrorToast(error, 'maintenance.monit.messages.saveFailed'));
	}
}
</script>
