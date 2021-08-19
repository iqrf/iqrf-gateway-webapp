<template>
	<CCard>
		<CCardBody>
			<ValidationObserver v-slot='{invalid}'>
				<CForm @submit.prevent='saveConfig'>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='required'
						:custom-messages='{
							required: "core.smtp.errors.hostMissing"
						}'
					>
						<CInput
							v-model='configuration.host'
							:label='$t("core.smtp.form.host")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='required'
						:custom-messages='{
							required: "core.smtp.errors.portMissing"
						}'
					>
						<CInput
							v-model.number='configuration.port'
							:label='$t("core.smtp.form.port")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='required'
						:custom-messages='{
							required: "core.smtp.errors.usernameMissing"
						}'
					>
						<CInput
							v-model='configuration.username'
							:label='$t("forms.fields.username")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='required'
						:custom-messages='{
							required: "core.smtp.errors.passwordMissing"
						}'
					>
						<CInput
							v-model='configuration.password'
							:label='$t("forms.fields.password")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<CSelect
						:value.sync='configuration.protocol'
						:options='protocols'
						:label='$t("core.smtp.form.security")'
					/>
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
import {CCard, CCardBody, CForm, CInput, CSelect} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {required} from 'vee-validate/dist/rules';
import {SmtpSecurity} from '../../enums/Install/Smtp';

import {IOption} from '../../interfaces/coreui';
import {ISmtp} from '../../interfaces/install';

@Component({
	components: {
		CCard,
		CCardBody,
		CForm,
		CInput,
		CSelect,
		ValidationObserver,
		ValidationProvider,
	}
})

/**
 * SMTP configuration form component
 */
export default class SmtpForm extends Vue {

	/**
	 * @var {ISmtp} configuration SMTP server configuration
	 */
	private configuration: ISmtp = {
		host: '',
		port: 465,
		username: '',
		password: '',
		protocol: SmtpSecurity.PLAINTEXT
	}

	/**
	 * @constant {Array<IOption>} protocols Array of STMP security protocols
	 */
	private protocols: Array<IOption> = [
		{
			label: this.$t('core.smtp.form.protocols.none').toString(),
			value: SmtpSecurity.PLAINTEXT,
		},
		{
			label: this.$t('core.smtp.form.protocols.starttls').toString(),
			value: SmtpSecurity.STARTTLS,
		},
		{
			label: this.$t('core.smtp.form.protocols.tls').toString(),
			value: SmtpSecurity.TLS,
		},
	]

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
	}

	/**
	 * Saves SMTP configuration
	 */
	private saveConfig(): void {
		//
	}

}
</script>
