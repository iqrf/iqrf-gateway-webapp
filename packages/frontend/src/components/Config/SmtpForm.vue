<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
			<v-form>
				<v-switch
					v-model='configuration.enabled'
					:label='$t("config.smtp.form.enabled")'
					color='primary'
					inset
					dense
				/>
				<fieldset :disabled='!configuration.enabled'>
					<v-row>
						<v-col cols='12' md='6'>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required|host'
								:custom-messages='{
									required: $t("config.smtp.errors.hostMissing"),
									host: $t("config.smtp.errors.hostInvalid"),
								}'
							>
								<v-text-field
									v-model='configuration.host'
									:label='$t("config.smtp.form.host").toString()'
									:sucess='touched ? valid : null'
									:error-messages='errors.join(", ")'
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
									:label='$t("config.smtp.form.port").toString()'
									:sucess='touched ? valid : null'
									:error-messages='errors.join(", ")'
								/>
							</ValidationProvider>
							<v-select
								v-model='configuration.secure'
								:items='protocols'
								:label='$t("config.smtp.form.security").toString()'
							/>
						</v-col>
						<v-col cols='12' md='6'>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required'
								:custom-messages='{
									required: $t("config.smtp.errors.usernameMissing"),
								}'
							>
								<v-text-field
									v-model='configuration.username'
									:label='$t("forms.fields.username").toString()'
									:sucess='touched ? valid : null'
									:error-messages='errors.join(", ")'
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
									:sucess='touched ? valid : null'
									:error-messages='errors.join(", ")'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required|email'
								:custom-messages='{
									required: $t("config.smtp.errors.senderMissing"),
									email: $t("config.smtp.errors.senderInvalid"),
								}'
							>
								<v-text-field
									v-model='configuration.from'
									:label='$t("config.smtp.form.from").toString()'
									:sucess='touched ? valid : null'
									:error-messages='errors.join(", ")'
								/>
							</ValidationProvider>
						</v-col>
					</v-row>
				</fieldset>
				<v-btn
					class='mr-1'
					color='primary'
					:disabled='invalid'
					@click='saveConfig'
				>
					{{ $t('forms.save') }}
				</v-btn>
				<v-tooltip top :disabled='hasEmail'>
					<template #activator='{on}'>
						<span v-on='on'>
							<v-btn
								class='mr-1'
								color='info'
								:disabled='!configuration.enabled || !hasEmail'
								@click='test'
							>
								{{ $t('config.smtp.test') }}
							</v-btn>
						</span>
					</template>
					<span>{{ $t('config.smtp.messages.testDisabled') }}</span>
				</v-tooltip>
				<v-btn
					v-if='$route.path.includes("/install/smtp")'
					@click='$emit("done")'
				>
					{{ $t('forms.skip') }}
				</v-btn>
			</v-form>
		</ValidationObserver>
	</div>
</template>

<script lang='ts'>
import {MailerService} from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	MailerConfig,
	MailerGetConfigResponse,
	MailerSmtpSecurity,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {AxiosError} from 'axios';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import {mapGetters} from 'vuex';
import {Component, Vue} from 'vue-property-decorator';

import PasswordInput from '@/components/Core/PasswordInput.vue';
import {extendedErrorToast} from '@/helpers/errorToast';
import {email, host} from '@/helpers/validators';
import {useApiClient} from '@/services/ApiClient';

@Component({
	components: {
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
	 * @var {MailerConfig} configuration SMTP server configuration
	 */
	private configuration: MailerConfig = {
		enabled: false,
		host: '',
		port: 465,
		username: '',
		password: '',
		secure: null,
		from: ''
	};

	/**
	 * @constant protocols Array of SMTP security protocols
	 */
	private protocols = [
		{
			text: this.$t('config.smtp.form.protocols.none').toString(),
			value: null,
		},
		{
			text: this.$t('config.smtp.form.protocols.starttls').toString(),
			value: MailerSmtpSecurity.STARTTLS,
		},
		{
			text: this.$t('config.smtp.form.protocols.tls').toString(),
			value: MailerSmtpSecurity.TLS,
		},
	];

	/**
	 * @property {MailerService} service Mailer service
   */
	private service: MailerService = useApiClient().getConfigServices().getMailerService();

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
		extend('host', host);
		extend('email', email);
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
		this.service.getConfig()
			.then((config: MailerGetConfigResponse) => {
				this.configuration = config.config;
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
		this.service.updateConfig(this.configuration)
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
		this.service.testConfig(this.configuration)
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
