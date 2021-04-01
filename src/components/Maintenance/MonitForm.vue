<template>
	<ValidationObserver
		v-if='configuration !== null'
		v-slot='{invalid}'
	>
		<hr>
		<CForm @submit.prevent='saveConfig'>
			<ValidationProvider
				v-slot='{errors, touched, valid}'
				rules='required'
				:custom-messages='{
					required: "maintenance.monit.errors.endpoint",
				}'
			>
				<CInput
					v-model='configuration.endpoint'
					:label='$t("maintenance.monit.form.endpoint")'
					:is-valid='touched ? valid : null'
					:invalid-feedback='$t(errors[0])'
				/>
			</ValidationProvider>
			<ValidationProvider
				v-slot='{errors, touched, valid}'
				rules='required|userpass'
				:custom-messages='{
					required: "maintenance.monit.errors.username",
					userpass: "maintenance.monit.errors.usernameInvalid"
				}'
			>
				<CInput
					v-model='configuration.username'
					:label='$t("maintenance.monit.form.username")'
					:is-valid='touched ? valid : null'
					:invalid-feedback='$t(errors[0])'
				/>
			</ValidationProvider>
			<ValidationProvider
				v-slot='{errors, touched, valid}'
				rules='required|userpass'
				:custom-messages='{
					required: "maintenance.monit.errors.password",
					userpass: "maintenance.monit.errors.passwordInvalid"
				}'
			>
				<CInput
					v-model='configuration.password'
					:label='$t("maintenance.monit.form.password")'
					:is-valid='touched ? valid : null'
					:invalid-feedback='$t(errors[0])'
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
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CForm, CInput} from '@coreui/vue/src';
import {ValidationObserver, ValidationProvider} from 'vee-validate';

import {extend} from 'vee-validate';
import {extendedErrorToast} from '../../helpers/errorToast';
import {required} from 'vee-validate/dist/rules';

import MaintenanceService from '../../services/MaintenanceService';

import {AxiosError, AxiosResponse} from 'axios';
import {IMonitConfig} from '../../interfaces/maintenance';

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
	 * @var {IMonitConfig|null} configuration Monit configuration
	 */
	private configuration: IMonitConfig|null = null

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
		extend('userpass', (item: string) => {
			const re = new RegExp('^[0-9a-z]+$', 'i');
			return re.test(item);
		});
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
		return MaintenanceService.getMonitConfig()
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
		MaintenanceService.saveMonitConfig(this.configuration)
			.then(() => this.getConfig().then(() => this.$toast.success(
				this.$t('maintenance.monit.messages.saveSuccess').toString()
			)))
			.catch((error) => extendedErrorToast(error, 'maintenance.monit.messages.saveFailed'));
	}
}
</script>
