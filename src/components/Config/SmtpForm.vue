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
		<v-overlay
			v-if='running'
			:opacity='0.65'
			absolute
		>
			<v-progress-circular color='primary' indeterminate />
		</v-overlay>
		<ValidationObserver v-slot='{invalid}'>
			<v-form @submit.prevent='saveConfig'>
				<v-switch
					v-model='configuration.enabled'
					:label='$t("config.smtp.form.enabled")'
					color='primary'
					inset
					dense
				/>
				<fieldset :disabled='!configuration.enabled'>
					<v-row>
						<v-col md='6'>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required'
								:custom-messages='{
									required: $t("config.smtp.errors.hostMissing"),
								}'
							>
								<v-text-field
									v-model='configuration.host'
									:label='$t("config.smtp.form.host")'
									:success='touched ? valid : null'
									:error-messages='errors'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required'
								:custom-messages='{
									required: $t("config.smtp.errors.portMissing"),
								}'
							>
								<v-text-field
									v-model.number='configuration.port'
									:label='$t("config.smtp.form.port")'
									:success='touched ? valid : null'
									:error-messages='errors'
								/>
							</ValidationProvider>
							<v-select
								v-model='configuration.secure'
								:items='protocols'
								:label='$t("config.smtp.form.security")'
							/>
						</v-col>
						<v-col md='6'>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required'
								:custom-messages='{
									required: $t("config.smtp.errors.usernameMissing"),
								}'
							>
								<v-text-field
									v-model='configuration.username'
									:label='$t("forms.fields.username")'
									:success='touched ? valid : null'
									:error-messages='errors'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required'
								:custom-messages='{
									required: $t("config.smtp.errors.passwordMissing"),
								}'
							>
								<v-text-field
									v-model='configuration.password'
									:type='passwordVisible ? "text" : "password"'
									:label='$t("forms.fields.password")'
									:success='touched ? valid : null'
									:error-messages='errors'
									:append-icon='passwordVisible ? "mdi-eye" : "mdi-eye-off"'
									@click:append='passwordVisible = !passwordVisible'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required'
								:custom-messages='{
									required: $t("config.smtp.errors.fromMissing"),
								}'
							>
								<v-text-field
									v-model='configuration.from'
									:label='$t("config.smtp.form.from")'
									:success='touched ? valid : null'
									:error-messages='errors'
								/>
							</ValidationProvider>
						</v-col>
					</v-row>
				</fieldset>
				<v-btn
					color='primary'
					type='submit'
					:disabled='invalid'
				>
					{{ $t('forms.save') }}
				</v-btn> <v-btn
					v-if='hasEmail'
					color='primary'
					@click='test'
				>
					{{ $t('config.smtp.test') }}
				</v-btn> <v-btn
					v-if='$route.path.includes("/install/smtp")'
					color='primary'
					@click='$emit("done")'
				>
					{{ $t('forms.skip') }}
				</v-btn>
			</v-form>
		</ValidationObserver>
	</div>
</template>

<script lang='ts'>
import {AxiosError, AxiosResponse} from 'axios';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import {Component, Vue} from 'vue-property-decorator';
import {mapGetters} from 'vuex';

import {extendedErrorToast} from '@/helpers/errorToast';
import {SmtpSecurity} from '@/enums/Config/Smtp';

import MailerService from '@/services/MailerService';

import {IOption} from '@/interfaces/coreui';
import {ISmtp} from '@/interfaces/smtp';

@Component({
	components: {
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
			text: this.$t('config.smtp.form.protocols.none').toString(),
			value: SmtpSecurity.PLAINTEXT,
		},
		{
			text: this.$t('config.smtp.form.protocols.starttls').toString(),
			value: SmtpSecurity.STARTTLS,
		},
		{
			text: this.$t('config.smtp.form.protocols.tls').toString(),
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
