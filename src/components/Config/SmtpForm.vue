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
	<div>
		<CElementCover
			v-if='running'
			:opacity='0.75'
			style='z-index: 10000;'
		>
			<CSpinner color='primary' />
		</CElementCover>
		<ValidationObserver v-slot='{invalid}'>
			<CForm @submit.prevent='saveConfig'>
				<div class='form-group'>
					<label>
						{{ $t('config.smtp.form.enabled') }}
					</label><br>
					<CSwitch
						:checked.sync='configuration.enabled'
						color='primary'
						size='lg'
						shape='pill'
						label-on='ON'
						label-off='OFF'
					/>
				</div>
				<fieldset :disabled='!configuration.enabled'>
					<CRow>
						<CCol md='6'>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required'
								:custom-messages='{
									required: $t("config.smtp.errors.hostMissing"),
								}'
							>
								<CInput
									v-model='configuration.host'
									:label='$t("config.smtp.form.host").toString()'
									:is-valid='touched ? valid : null'
									:invalid-feedback='errors.join(", ")'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required'
								:custom-messages='{
									required: $t("config.smtp.errors.portMissing"),
								}'
							>
								<CInput
									v-model.number='configuration.port'
									:label='$t("config.smtp.form.port").toString()'
									:is-valid='touched ? valid : null'
									:invalid-feedback='errors.join(", ")'
								/>
							</ValidationProvider>
							<CSelect
								:value.sync='configuration.secure'
								:options='protocols'
								:label='$t("config.smtp.form.security").toString()'
							/>
						</CCol>
						<CCol md='6'>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required'
								:custom-messages='{
									required: $t("config.smtp.errors.usernameMissing"),
								}'
							>
								<CInput
									v-model='configuration.username'
									:label='$t("forms.fields.username").toString()'
									:is-valid='touched ? valid : null'
									:invalid-feedback='errors.join(", ")'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required'
								:custom-messages='{
									required: $t("config.smtp.errors.passwordMissing"),
								}'
							>
								<PasswordInput
									v-model='configuration.password'
									:label='$t("forms.fields.password").toString()'
									:is-valid='touched ? valid : null'
									:invalid-feedback='errors.join(", ")'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required'
								:custom-messages='{
									required: $t("config.smtp.errors.fromMissing"),
								}'
							>
								<CInput
									v-model='configuration.from'
									:label='$t("config.smtp.form.from").toString()'
									:is-valid='touched ? valid : null'
									:invalid-feedback='errors.join(", ")'
								/>
							</ValidationProvider>
						</CCol>
					</CRow>
				</fieldset>
				<CButton
					color='primary'
					type='submit'
					:disabled='invalid'
				>
					{{ $t('forms.save') }}
				</CButton> <CButton
					v-if='hasEmail'
					color='info'
					@click='test'
				>
					{{ $t('config.smtp.test') }}
				</CButton> <CButton
					v-if='$route.path.includes("/install/smtp")'
					color='secondary'
					@click='$emit("done")'
				>
					{{ $t('forms.skip') }}
				</CButton>
			</CForm>
		</ValidationObserver>
	</div>
</template>

<script lang='ts'>
import {CButton, CCol, CElementCover, CForm, CInput, CRow, CSelect, CSpinner, CSwitch} from '@coreui/vue/src';
import {AxiosError, AxiosResponse} from 'axios';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import {Component, Vue} from 'vue-property-decorator';
import {mapGetters} from 'vuex';

import PasswordInput from '@/components/Core/PasswordInput.vue';

import {extendedErrorToast} from '@/helpers/errorToast';
import {SmtpSecurity} from '@/enums/Config/Smtp';

import MailerService from '@/services/MailerService';

import {IOption} from '@/interfaces/Coreui';
import {ISmtp} from '@/interfaces/Config/Smtp';

@Component({
	components: {
		CButton,
		CCol,
		CElementCover,
		CForm,
		CInput,
		CRow,
		CSelect,
		CSpinner,
		CSwitch,
		PasswordInput,
		ValidationObserver,
		ValidationProvider,
	},
	computed: {
		...mapGetters({
			hasEmail: 'user/hasEmail',
		}),
	},
})

/**
 * SMTP configuration form component
 */
export default class SmtpForm extends Vue {

	/**
	 * @var {boolean} running Indicates that request is in progress
	 */
	private running = false;

	/**
	 * @var {ISmtp} configuration SMTP server configuration
	 */
	private configuration: ISmtp = {
		enabled: false,
		host: '',
		port: 465,
		username: '',
		password: '',
		secure: SmtpSecurity.PLAINTEXT,
		from: ''
	};

	/**
	 * @constant {Array<IOption>} protocols Array of SMTP security protocols
	 */
	private protocols: Array<IOption> = [
		{
			label: this.$t('config.smtp.form.protocols.none').toString(),
			value: SmtpSecurity.PLAINTEXT,
		},
		{
			label: this.$t('config.smtp.form.protocols.starttls').toString(),
			value: SmtpSecurity.STARTTLS,
		},
		{
			label: this.$t('config.smtp.form.protocols.tls').toString(),
			value: SmtpSecurity.TLS,
		},
	];

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
	}

	/**
	 * Retrieves current SMTP configuration
	 */
	mounted(): void {
		this.showBlockingElement();
		MailerService.getConfig()
			.then((response: AxiosResponse) => {
				this.configuration = response.data;
				this.hideBlockingElement();
			})
			.catch((error: AxiosError) => {
				this.running = false;
				extendedErrorToast(error, 'config.smtp.messages.fetchFailed');
			});
	}

	/**
	 * Saves SMTP configuration
	 */
	private saveConfig(): void {
		this.showBlockingElement();
		MailerService.saveConfig(this.configuration)
			.then(() => {
				this.hideBlockingElement();
				this.$toast.success(
					this.$t('config.smtp.messages.saveSuccess').toString()
				);
				this.$emit('done');
			})
			.catch((error: AxiosError) => {
				this.running = false;
				extendedErrorToast(error, 'config.smtp.messages.saveFailed');
			});
	}

	/**
	 * Sends SMTP configuration test mail
	 */
	private test(): void {
		this.showBlockingElement();
		MailerService.testConfig()
			.then(() => {
				this.hideBlockingElement();
				this.$toast.success(
					this.$t('config.smtp.messages.testSuccess').toString()
				);
			})
			.catch((error: AxiosError) => {
				this.running = false;
				extendedErrorToast(error, 'config.smtp.messages.testFailed');
			});
	}

	/**
	 * Shows interface blocking element depending on the location
	 */
	private showBlockingElement(): void {
		if (this.$route.path.includes('/install/smtp')) {
			this.running = true;
		} else {
			this.$store.commit('spinner/SHOW');
		}
	}

	/**
	 * Hides interface blocking element depending on the location
	 */
	private hideBlockingElement(): void {
		if (this.$route.path.includes('/install/smtp')) {
			this.running = false;
		} else {
			this.$store.commit('spinner/HIDE');
		}
	}
}
</script>
