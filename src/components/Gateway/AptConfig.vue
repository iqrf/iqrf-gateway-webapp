<!--
Copyright 2017-2022 IQRF Tech s.r.o.
Copyright 2019-2022 MICRORISC s.r.o.

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
	<CCard>
		<CCardHeader>
			{{ $t('service.unattended-upgrades.configuration') }}
		</CCardHeader>
		<CCardBody>
			<ValidationObserver v-slot='{invalid}'>
				<CForm @submit.prevent='updateConfig'>
					<ValidationProvider
						v-if='role === roles.ADMIN'
						v-slot='{errors, touched, valid}'
						rules='integer|min:0|required'
						:custom-messages='{
							integer: "forms.messages.integer",
							min: "service.unattended-upgrades.errors.listUpdateInterval",
							required: "service.unattended-upgrades.errors.listUpdateInterval",
						}'
					>
						<CInput
							v-model='listUpdateInterval'
							type='number'
							min='0'
							:label='$t("service.unattended-upgrades.form.listUpdateInterval")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='integer|min:0|required'
						:custom-messages='{
							integer: "forms.messages.integer",
							min: "service.unattended-upgrades.errors.upgradeInterval",
							required: "service.unattended-upgrades.errors.upgradeInterval",
						}'
					>
						<CInput
							v-model='upgradeInterval'
							type='number'
							min='0'
							:label='$t("service.unattended-upgrades.form.upgradeInterval")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-if='role === roles.ADMIN'
						v-slot='{errors, touched, valid}'
						rules='integer|min:0|required'
						:custom-messages='{
							integer: "forms.messages.integer",
							min: "service.unattended-upgrades.errors.removeInterval",
							required: "service.unattended-upgrades.errors.removeInterval",
						}'
					>
						<CInput
							v-model='removeInterval'
							type='number'
							min='0'
							:label='$t("service.unattended-upgrades.form.removeInterval")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<CInputCheckbox
						:value.sync='rebootOnKernelUpdate'
						:label='$t("service.unattended-upgrades.form.rebootOnKernelUpdate")'
					/>
					<div><i>{{ $t('service.unattended-upgrades.form.intervalNote') }}</i></div><br>
					<CButton
						color='primary'
						type='submit'
						:disabled='invalid'
					>
						{{ $t('forms.save') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput, CInputCheckbox} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {extendedErrorToast} from '@/helpers/errorToast';
import {integer, min_value, required} from 'vee-validate/dist/rules';
import {UserRole} from '@/services/AuthenticationService';

import AptService, {AptConfiguration} from '@/services/AptService';

import {AxiosError, AxiosResponse} from 'axios';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		CInputCheckbox,
		ValidationObserver,
		ValidationProvider
	},
})

/**
 * Gateway APT configuration component for service control
 */
export default class AptConfig extends Vue {

	/**
	 * @var {number} listUpdateInterval Package list update interval in days
	 */
	private listUpdateInterval = 1;

	/**
	 * @var {number} upgradeInterval Package upgrade interval in days
	 */
	private upgradeInterval = 1;

	/**
	 * @var {number} removeInterval Unnecessary package removal interval in days
	 */
	private removeInterval = 0;

	/**
	 * @var {boolean} rebootOnKernelUpdate Reboot device after updating kernel
	 */
	private rebootOnKernelUpdate = false;

	/**
	 * @var {UserRole} role User role
	 */
	private role: UserRole = UserRole.NORMAL;

	/**
	 * @var {typeof UserRole} roles User roles enum
	 */
	private roles: typeof UserRole = UserRole;

	/**
	 * Vue lifecycle hook created
	 * Defines keywords for form validation rules
	 */
	created(): void {
		extend('integer', integer);
		extend('min', min_value);
		extend('required', required);
	}

	/**
	 * Vue lifecycle hook mounted
	 * Checks for user role and sends REST API request to get unattended upgrades configuration
	 */
	mounted(): void {
		this.role = this.$store.getters['user/getRole'];
		this.getConfig();
	}

	/**
	 * Retrieves unattended upgrades configuration
	 */
	private getConfig(): Promise<void> {
		return AptService.read()
			.then((response: AxiosResponse) => this.parseConfig(response.data))
			.catch((error: AxiosError) => extendedErrorToast(error, 'service.unattended-upgrades.messages.getFailed'));
	}

	/**
	 * Parses unattended upgrades configuration and stores values
	 * @param {AptConfiguration} config Unattended upgrades configuration
	 */
	private parseConfig(config: AptConfiguration): void {
		this.listUpdateInterval = parseInt(config['APT::Periodic::Update-Package-Lists']);
		this.upgradeInterval = parseInt(config['APT::Periodic::Unattended-Upgrade']);
		this.removeInterval = parseInt(config['APT::Periodic::AutocleanInterval']);
		this.rebootOnKernelUpdate = (config['Unattended-Upgrade::Automatic-Reboot'] === 'true');
	}

	/**
	 * Creates apt configuration object and saves configuration
	 */
	private updateConfig(): void {
		const config: AptConfiguration = {
			'APT::Periodic::Update-Package-Lists': this.listUpdateInterval.toString(),
			'APT::Periodic::Unattended-Upgrade': this.upgradeInterval.toString(),
			'APT::Periodic::AutocleanInterval': this.removeInterval.toString(),
			'Unattended-Upgrade::Automatic-Reboot': this.rebootOnKernelUpdate ? 'true': 'false',
		};
		this.$store.commit('spinner/SHOW');
		AptService.write(config)
			.then(() => {
				this.getConfig().then(() => {
					this.$store.commit('spinner/HIDE');
					this.$toast.success(
						this.$t('service.unattended-upgrades.messages.saveSuccess').toString()
					);
				});
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'service.unattended-upgrades.messages.saveFailed');
			});
	}

}
</script>
