<template>
	<CCard>
		<CCardHeader>
			{{ $t('service.unattended-upgrades.configuration') }}
		</CCardHeader>
		<CCardBody>
			<ValidationObserver v-slot='{invalid}'>
				<CForm @submit.prevent='updateConfig'>
					<ValidationProvider
						v-if='powerUser'
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
						v-if='powerUser'
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
					<CButton color='primary' type='submit' :disabled='invalid'>
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
import {integer, min_value, required} from 'vee-validate/dist/rules';
import AptService, {AptConfigurationExtended} from '../../services/AptService';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import ToastClear from '../../helpers/ToastClear';
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
	 * @var {boolean} enabled Unattended upgrades enabled
	 */
	private enabled = false

	/**
	 * @var {number} listUpdateInterval Package list update interval in days
	 */
	private listUpdateInterval = 1

	/**
	 * @var {number} upgradeInterval Package upgrade interval in days
	 */
	private upgradeInterval = 1

	/**
	 * @var {number} removeInterval Unnecessary package removal interval in days
	 */
	private removeInterval = 0

	/**
	 * @var {boolean} rebootOnKernelUpdate Reboot device after updating kernel
	 */
	private rebootOnKernelUpdate = false

	/**
	 * @var {boolean} powerUser Indicates whether user role is power user
	 */
	private powerUser = false

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
		if (this.$store.getters['user/getRole'] === 'power') {
			this.powerUser = true;
		}
		this.getConfig();
	}

	/**
	 * Retrieves unattended upgrades configuration
	 */
	private getConfig(): Promise<void> {
		return AptService.read()
			.then((response: AxiosResponse) => this.parseConfig(response.data))
			.catch((error: AxiosError) => FormErrorHandler.configError(error));
	}

	/**
	 * Parses unattended upgrades configuration and stores values
	 * @param {AptConfigurationExtended} config Unattended upgrades configuration
	 */
	private parseConfig(config: AptConfigurationExtended): void {
		this.listUpdateInterval = parseInt(config['APT::Periodic::Update-Package-Lists']);
		this.upgradeInterval = parseInt(config['APT::Periodic::Unattended-Upgrade']);
		this.removeInterval = parseInt(config['APT::Periodic::AutocleanInterval']);
		this.rebootOnKernelUpdate = (config['Unattended-Upgrade::Automatic-Reboot'] === 'true');
		this.enabled = (config['APT::Periodic::Enable'] === '1');
	}

	/**
	 * Creates apt configuration object and saves configuration
	 */
	private updateConfig(): void {
		const config: AptConfigurationExtended = {
			'APT::Periodic::Enable': this.enabled ? '1' : '0',
			'APT::Periodic::Update-Package-Lists': this.listUpdateInterval.toString(),
			'APT::Periodic::Unattended-Upgrade': this.upgradeInterval.toString(),
			'APT::Periodic::AutocleanInterval': this.removeInterval.toString(),
			'Unattended-Upgrade::Automatic-Reboot': this.rebootOnKernelUpdate ? 'true': 'false',
		};
		AptService.write(config)
			.then(() => {
				this.getConfig().then(() => ToastClear.success('service.unattended-upgrades.messages.success'));
			})
			.catch(() => ToastClear.error('service.unattended-upgrdes.messages.failure'));
	}

}
</script>
