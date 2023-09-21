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
	<div>
		<v-card>
			<v-card-title>{{ $t('install.createUser.title') }}</v-card-title>
			<v-card-text>
				<v-overlay
					v-if='running'
					:opacity='0.65'
					absolute
				>
					<v-progress-circular color='primary' indeterminate />
				</v-overlay>
				<ValidationObserver v-slot='{invalid}'>
					<v-form @submit.prevent='handleSubmit'>
						<div class='form-group'>
							{{ $t('install.createUser.note') }}
						</div>
						<ValidationProvider
							v-slot='{ valid, touched, errors }'
							rules='required'
							:custom-messages='{
								required: $t("forms.errors.username"),
							}'
						>
							<v-text-field
								id='username'
								v-model='user.username'
								:label='$t("forms.fields.username").toString()'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ valid, touched, errors }'
							rules='email'
							:custom-messages='{
								email: $t("forms.errors.emailFormat"),
							}'
						>
							<v-text-field
								id='email'
								v-model='user.email'
								:label='$t("forms.fields.email").toString()'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{valid, touched, errors}'
							rules='required'
							:custom-messages='{
								required: $t("forms.errors.password"),
							}'
						>
							<PasswordInput
								id='password'
								v-model='user.password'
								:label='$t("forms.fields.password").toString()'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<v-btn
							color='primary'
							type='submit'
							:disabled='invalid'
						>
							{{ $t('install.createUser.createButton') }}
						</v-btn>
					</v-form>
				</ValidationObserver>
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {
	EmailSentResponse,
	UserCreate,
	UserCredentials,
	UserLanguage,
	UserRole
} from '@iqrf/iqrf-gateway-webapp-client/types';
import {AxiosError} from 'axios';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import {Component, Vue} from 'vue-property-decorator';

import PasswordInput from '@/components/Core/PasswordInput.vue';

import {email} from '@/helpers/validators';
import {sleep} from '@/helpers/sleep';
import {extendedErrorToast} from '@/helpers/errorToast';
import UrlBuilder from '@/helpers/urlBuilder';
import {useApiClient} from '@/services/ApiClient';

@Component({
	components: {
		PasswordInput,
		ValidationObserver,
		ValidationProvider,
	},
	metaInfo: {
		title: 'install.createUser.title',
	},
})

/**
 * Create user
 */
export default class InstallCreateUser extends Vue {
	/**
	 * @var {UserCreate} user User
	 */
	private user: UserCreate = {
		username: '',
		password: '',
		email: '',
		language: UserLanguage.English,
		role: UserRole.Admin,
		baseUrl: new UrlBuilder().getBaseUrl(),
	};

	/**
	 * @var {bool} running Indicates whether axios requests are in progress
	 */
	private running = false;

	/**
	 * On component creation event handler
	 */
	public created(): void {
		extend('email', email);
		extend('required', required);
	}

	/**
	 * Handle form submit
	 */
	private handleSubmit(): void {
		this.running = true;
		useApiClient().getUserService().create(this.user)
			.then((response: EmailSentResponse) => {
				if (response.emailSent) {
					this.$toast.success(
						this.$t('core.user.messages.verifyNotice').toString()
					);
				}
				const credentials: UserCredentials = {
					username: this.user.username,
					password: this.user.password as string,
				};
				this.$store.dispatch('user/signIn', credentials)
					.then(async () => {
						await sleep(500);
						this.running = false;
						this.nextStep();
					});
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'install.createUser.failure');
				this.running = false;
			});
	}

	/**
	 * Advances the installation wizard
	 */
	private nextStep(): void {
		this.$emit('next-step');
		this.$router.push('/install/smtp/');
	}
}
</script>
