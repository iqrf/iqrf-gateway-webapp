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
	<v-card class='p-4'>
		<v-card-title>{{ $t('account.recovery.title') }}</v-card-title>
		<v-card-text>
			<v-overlay
				v-if='requestInProgress'
				:opacity='0.65'
				absolute
			>
				<v-progress-circular color='primary' indeterminate />
			</v-overlay>
			<p>
				{{ $t('account.recovery.changePrompt') }}
			</p>
			<ValidationObserver v-slot='{invalid}'>
				<form @submit.prevent='confirmRecovery'>
					<ValidationProvider
						v-slot='{valid, touched, errors}'
						rules='required'
						:custom-messages='{
							required: $t("core.sign.in.messages.password"),
						}'
					>
						<v-text-field
							v-model='password'
							:type='passwordVisible ? "text" : "password"'
							:label='$t("forms.fields.password")'
							:success='touched ? valid : null'
							:error-messages='errors'
							:append-icon='passwordVisible ? "mdi-eye" : "mdi-eye-off"'
							@click:append='passwordVisible = !passwordVisible'
						/>
					</ValidationProvider>
					<v-btn
						color='primary'
						type='submit'
						:disabled='invalid'
					>
						{{ $t('account.recovery.changePassword') }}
					</v-btn>
				</form>
			</ValidationObserver>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {AxiosError} from 'axios';
import {Component, Vue, Prop} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';

import TheWizard from '@/components/TheWizard.vue';
import {extendedErrorToast} from '@/helpers/errorToast';
import {UserRole, UserSignedIn} from '@iqrf/iqrf-gateway-webapp-client/types';
import {useApiClient} from '@/services/ApiClient';
import UrlBuilder from '@/helpers/urlBuilder';

@Component({
	components: {
		TheWizard,
		ValidationObserver,
		ValidationProvider,
	},
	metaInfo: {
		title: 'account.recovery.title'
	}
})

/**
 * Account password recovery confirmation component
 */
export default class ConfirmPasswordRecovery extends Vue {

	/**
	 * @var {string} password New user password
	 */
	private password = '';

	/**
	 * @var {bool} passwordVisible Controls visibility of password field
	 */
	private passwordVisible = false;

	/**
	 * @var {bool} requestInProgress Indicates whether axios requests are in progress
	 */
	private requestInProgress = false;

	/**
	 * @property {string} recoveryId Password recovery request ID
	 */
	@Prop({required: true}) recoveryId!: string;

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
	}

	/**
	 * Sends a request for recovery email
	 */
	private confirmRecovery(): void {
		this.requestInProgress = true;
		useApiClient().getAccountService().confirmPasswordRecovery(this.recoveryId, {
			password: this.password,
			baseUrl: (new UrlBuilder()).getBaseUrl(),
		})
			.then((user: UserSignedIn) => {
				this.requestInProgress = false;
				if (user.role === UserRole.Basic) {
					location.pathname = '/';
				}
				this.$store.dispatch('user/setJwt', user);
				this.$toast.success(
					this.$t('account.recovery.messages.changeSuccess').toString()
				);
				this.$router.push('/');
			})
			.catch((error: AxiosError) => {
				this.requestInProgress = false;
				extendedErrorToast(error, 'account.recovery.messages.changeFailed');
			});
	}

}
</script>

