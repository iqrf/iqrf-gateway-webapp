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
			<CForm>
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
								rules='required|host'
								:custom-messages='{
									required: $t("config.smtp.errors.hostMissing"),
									host: $t("config.smtp.errors.hostInvalid"),
								}'
							>
								<CInput
									v-model='configuration.host'
									:label='$t("config.smtp.form.host")'
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
									:label='$t("config.smtp.form.port")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='errors.join(", ")'
								/>
							</ValidationProvider>
							<CSelect
								:value.sync='configuration.secure'
								:options='protocols'
								:label='$t("config.smtp.form.security")'
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
									:label='$t("forms.fields.username")'
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
								<CInput
									v-model='configuration.password'
									:type='passwordVisible ? "text" : "password"'
									:label='$t("forms.fields.password")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='errors.join(", ")'
								>
									<template #append-content>
										<span @click='passwordVisible = !passwordVisible'>
											<FontAwesomeIcon
												:icon='(passwordVisible ? ["far", "eye-slash"] : ["far", "eye"])'
											/>
										</span>
									</template>
								</CInput>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required|email'
								:custom-messages='{
									required: $t("config.smtp.errors.senderMissing"),
									email: $t("config.smtp.errors.senderInvalid"),
								}'
							>
								<CInput
									v-model='configuration.from'
									:label='$t("config.smtp.form.from")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='errors.join(", ")'
								/>
							</ValidationProvider>
						</CCol>
					</CRow>
				</fieldset>
				<CButton
					class='mr-1'
					color='primary'
					:disabled='invalid'
					@click='saveConfig'
				>
					{{ $t('forms.save') }}
				</CButton>
				<CButton
					v-c-tooltip='!hasEmail ? $t("config.smtp.messages.testDisabled") : ""'
					class='mr-1'
					color='info'
					:disabled='!configuration.enabled || !hasEmail'
					@click='test'
				>
					{{ $t('config.smtp.test') }}
				</CButton>
				<CButton
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
import {Component, Vue} from 'vue-property-decorator';
import {CCol, CForm, CInput, CRow, CSelect} from '@coreui/vue/src';
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {email, required} from 'vee-validate/dist/rules';
import {extendedErrorToast} from '@/helpers/errorToast';
import {mapGetters} from 'vuex';
import {SmtpSecurity} from '@/enums/Config/Smtp';
import ip from 'ip-regex';
import isFQDN from 'is-fqdn';
import punycode from 'punycode/';

import MailerService from '@/services/MailerService';

import {AxiosError, AxiosResponse} from 'axios';
import {IOption} from '@/interfaces/Coreui';
import {ISmtp} from '@/interfaces/Config/Smtp';

@Component({
	components: {
		CCol,
		CForm,
		CInput,
		CRow,
		CSelect,
		FontAwesomeIcon,
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
	 * @var {bool} passwordVisible Controls visibility of password field
	 */
	private passwordVisible = false;

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
		extend('host', (addr: string) => {
			if (ip.v4({exact: true}).test(addr)) {
				return true;
			}
			if (ip.v6({exact: true}).test(addr)) {
				return true;
			}
			const encoded = punycode.toASCII(addr);
			return encoded === 'localhost' || isFQDN(encoded);
		});
		extend('email', (addr: string) => {
			const encoded = punycode.toASCII(addr);
			if (!email.validate(encoded)) {
				return false;
			} 
			const domain = encoded.split('@');
			if (domain.length === 1) {
				return false;
			}
			return isFQDN(domain[1]);
		});
	}

	/**
	 * Retrieves current SMTP configuration
	 */
	mounted(): void {
		this.getConfig();
	}

	/**
	 * Retrieves smtp configuration
	 */
	private getConfig(): void {
		this.showBlockingElement();
		MailerService.getConfig()
			.then((response: AxiosResponse) => {
				const config: ISmtp = response.data;
				config.from = punycode.toUnicode(config.from);
				this.configuration = config;
				this.hideBlockingElement();
			})
			.catch((error: AxiosError) => {
				this.running = false;
				extendedErrorToast(error, 'config.smtp.messages.fetchFailed');
			});
	}

	/**
	 * Removes or converts invalid values
	 */
	private prepareConfigToSend(): ISmtp {
		const config: ISmtp = JSON.parse(JSON.stringify(this.configuration));
		if (config.clientHost === null) {
			delete config.clientHost;
		}
		config.from = punycode.toASCII(config.from);
		return config;
	}

	/**
	 * Saves SMTP configuration
	 */
	private saveConfig(): void {
		this.showBlockingElement();
		const config = this.prepareConfigToSend();
		MailerService.saveConfig(config)
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
		const config = this.prepareConfigToSend();
		MailerService.testConfig(config)
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
