<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

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
				<h2>{{ $t('maintenance.monit.checks.title') }}</h2>
				<div class='table-responsive'>
					<table class='table'>
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
					</table>
				</div>
				<h2>{{ $t('maintenance.monit.mmonit.title') }}</h2>
				<label for='mmonitEnabled'>
					<strong>{{ $t("maintenance.monit.mmonit.enable") }}</strong>
				</label><br>
				<CSwitch
					id='mmonitEnabled'
					color='primary'
					size='lg'
					shape='pill'
					label-on='ON'
					label-off='OFF'
					:checked.sync='configuration.mmonit.enabled'
				/>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					rules='required|mmonitServer'
					:custom-messages='{
						required: $t("maintenance.monit.mmonit.messages.emptyServer"),
						mmonitServer: $t("maintenance.monit.mmonit.messages.invalidServer"),
					}'
				>
					<CInput
						v-model='configuration.mmonit.server'
						:disabled='!configuration.mmonit.enabled'
						:label='$t("maintenance.monit.mmonit.server")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='errors.join(", ")'
					/>
				</ValidationProvider>
				<h4>{{ $t('maintenance.monit.mmonit.credentials.title') }}</h4>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					rules='required'
					:custom-messages='{
						required: $t("maintenance.monit.mmonit.credentials.messages.emptyUsername"),
					}'
				>
					<CInput
						v-model='configuration.mmonit.credentials.username'
						:disabled='!configuration.mmonit.enabled'
						:label='$t("maintenance.monit.mmonit.credentials.username")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='errors.join(", ")'
					/>
				</ValidationProvider>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					rules='required'
					:custom-messages='{
						required: $t("maintenance.monit.mmonit.credentials.messages.emptyPassword"),
					}'
				>
					<PasswordInput
						v-model='configuration.mmonit.credentials.password'
						:disabled='!configuration.mmonit.enabled'
						:label='$t("maintenance.monit.mmonit.credentials.password")'
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
import {CButton, CCard, CForm, CInput, CInputCheckbox, CSwitch} from '@coreui/vue/src';
import {AxiosError, AxiosResponse} from 'axios';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required, alpha_num} from 'vee-validate/dist/rules';
import {Component, Vue} from 'vue-property-decorator';
import {z} from 'zod';

import PasswordInput from '@/components/Core/PasswordInput.vue';
import {extendedErrorToast} from '@/helpers/errorToast';
import {IMonitConfig} from '@/interfaces/Maintenance/Monit';
import MonitService from '@/services/MonitService';
import ServiceService from '@/services/ServiceService';

@Component({
	components: {
		CButton,
		CCard,
		CForm,
		CInput,
		CInputCheckbox,
		CSwitch,
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
	 * @var {IMonitConfig|null} configuration Monit configuration
	 */
	private configuration: IMonitConfig|null = null;

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
		return MonitService.getConfig()
			.then((response: AxiosResponse<IMonitConfig>) => {
				this.configuration = response.data;
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
		MonitService.saveConfig(this.configuration)
			.then(async () => {
				await ServiceService.restart('monit');
				await this.getConfig().then(() => this.$toast.success(
					this.$t('maintenance.monit.messages.saveSuccess').toString()
				));
			})
			.catch((error) => extendedErrorToast(error, 'maintenance.monit.messages.saveFailed'));
	}
}
</script>
