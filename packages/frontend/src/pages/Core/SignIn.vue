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
	<TheWizard>
		<v-card class='p-4'>
			<v-card-title>{{ $t('core.sign.in.title') }}</v-card-title>
			<v-card-text>
				<ValidationObserver v-slot='{ invalid }'>
					<form @submit.prevent='handleSubmit'>
						<ValidationProvider
							v-slot='{ valid, touched, errors }'
							rules='required'
							:custom-messages='{
								required: $t("core.sign.in.messages.username"),
							}'
						>
							<v-text-field
								id='username'
								v-model='credentials.username'
								:label='$t("forms.fields.username")'
								:placeholder='$t("forms.fields.username")'
								autocomplete='username'
								:success='touched ? valid : null'
								:error-messages='errors'
								prepend-icon='mdi-account'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ valid, touched, errors }'
							rules='required'
							:custom-messages='{
								required: $t("core.sign.in.messages.password")
							}'
						>
							<v-text-field
								id='password'
								v-model='credentials.password'
								:label='$t("forms.fields.password")'
								:placeholder='$t("forms.fields.password")'
								type='password'
								autocomplete='password'
								:success='touched ? valid : null'
								:error-messages='errors'
								prepend-icon='mdi-lock-open-outline'
							/>
						</ValidationProvider>
						<div style='display: flex; justify-content: space-between;'>
							<v-btn color='primary' type='submit' :disabled='invalid'>
								{{ $t('core.sign.in.send') }}
							</v-btn>
							<v-btn text to='/account/recovery'>
								{{ $t('core.sign.in.recoverPassword') }}
							</v-btn>
						</div>
					</form>
				</ValidationObserver>
			</v-card-text>
		</v-card>
	</TheWizard>
</template>

<script lang='ts'>
import {UserCredentials} from '@iqrf/iqrf-gateway-webapp-client';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import {Component, Vue} from 'vue-property-decorator';

import PasswordInput from '@/components/Core/PasswordInput.vue';
import TheWizard from '@/components/TheWizard.vue';
import {sleep} from '@/helpers/sleep';

@Component({
	components: {
		PasswordInput,
		TheWizard,
		ValidationObserver,
		ValidationProvider,
	},
	metaInfo: {
		title: 'core.sign.in.title',
	},
})

/**
 * Sign in page component
 */
export default class SignIn extends Vue {

	/**
	 * @var {boolean} submitted Indicates whether the login information have been submitted
	 */
	private submitted = false;

	/**
	 * @property {UserCredentials} credentials User credentials
   * @private
   */
	private credentials: UserCredentials = {
		username: '',
		password: '',
	};

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('required', required);
		this.$store.commit('spinner/HIDE');
	}

	/**
	 * Attempts to log in and retrieve features available for the user's role
	 */
	private handleSubmit(): void {
		this.$store.dispatch('user/signIn', this.credentials)
			.then(async () => {
				await sleep(500);
				let destination = (this.$route.query.redirect as string|undefined) ?? '/';
				if (destination.startsWith('/sign/in')) {
					destination = '/';
				}
				await this.$router.push(destination);
				this.$toast.success(
					this.$t('core.sign.in.messages.success').toString()
				);
				await this.$store.dispatch('repository/get');
				await this.$store.dispatch('gateway/getInfo');
			})
			.catch(() => {
				this.$toast.error(
					this.$t('core.sign.in.messages.incorrectUsernameOrPassword')
						.toString()
				);
			});
		this.submitted = true;
	}
}
</script>
