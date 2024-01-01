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
	<v-card>
		<v-card-title>
			{{ $t('core.profile.form.changePassword') }}
		</v-card-title>
		<v-card-text>
			<v-alert type='info' text>
				{{ $t('core.profile.messages.changePassword') }}
			</v-alert>
			<ValidationObserver v-slot='{invalid}'>
				<v-form @submit.prevent='changePassword'>
					<ValidationProvider
						v-slot='{valid, touched, errors}'
						:rules='{
							required: passwordChange.new.length > 0,
						}'
						:custom-messages='{
							required: $t("core.user.errors.oldPassword"),
						}'
					>
						<PasswordInput
							v-model='passwordChange.old'
							:label='$t("core.profile.form.oldPassword")'
							:success='touched ? valid : null'
							:error-messages='errors'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{valid, touched, errors}'
						:rules='{
							required: passwordChange.old.length > 0,
						}'
						:custom-messages='{
							required: $t("core.user.errors.newPassword"),
						}'
					>
						<PasswordInput
							v-model='passwordChange.new'
							:label='$t("core.profile.form.newPassword")'
							:success='touched ? valid : null'
							:error-messages='errors'
						/>
					</ValidationProvider>
					<v-btn
						color='primary'
						type='submit'
						:disabled='invalid'
					>
						{{ $t('core.profile.form.changePassword') }}
					</v-btn>
				</v-form>
			</ValidationObserver>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {UserPasswordChange} from '@iqrf/iqrf-gateway-webapp-client/types';
import {AxiosError} from 'axios';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import {Component, Vue} from 'vue-property-decorator';

// Components
import PasswordInput from '@/components/Core/PasswordInput.vue';
// Auxiliary functions
import {extendedErrorToast} from '@/helpers/errorToast';
import UrlBuilder from '@/helpers/urlBuilder';
import {useApiClient} from '@/services/ApiClient';

@Component({
	components: {
		PasswordInput,
		ValidationObserver,
		ValidationProvider,
	},
})

/**
 * User profile password change component
 */
export default class UserProfilePassword extends Vue {

	/**
	 * @property {UserPasswordChange} passwordChange Password change data
   * @private
   */
	private passwordChange: UserPasswordChange = {
		old: '',
		new: '',
		baseUrl: new UrlBuilder().getBaseUrl(),
	};

	/**
	 * Retrieves user ID
	 */
	created(): void {
		extend('required', required);
	}

	/**
	 * Changes password of the current user
	 */
	private changePassword(): void {
		this.$store.commit('spinner/SHOW');
		useApiClient().getAccountService().changePassword(this.passwordChange)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('core.profile.messages.changePasswordSuccess').toString()
				);
				this.signOut();
			})
			.catch((err: AxiosError) => extendedErrorToast(err, 'core.profile.messages.changePasswordFailed'));
	}

	/**
	 * Performs signout
	 */
	private signOut(): void {
		this.$store.dispatch('user/signOut')
			.then(() => {
				this.$router.push({
					path: '/sign/in',
					query: {redirect: this.$route.path}
				});
			});
	}
}
</script>
