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
	<CCard>
		<CCardHeader>
			<strong>{{ $t('core.profile.form.changePassword') }}</strong>
		</CCardHeader>
		<CCardBody>
			<CAlert color='info'>
				<CIcon size='xl' :content='infoIcon' />
				{{ $t('core.profile.messages.changePassword') }}
			</CAlert>
			<ValidationObserver v-slot='{invalid}'>
				<CForm @submit.prevent='changePassword'>
					<ValidationProvider
						v-slot='{valid, touched, errors}'
						:rules='{
							required: newPassword.length > 0,
						}'
						:custom-messages='{
							required: $t("core.user.errors.oldPassword"),
						}'
					>
						<PasswordInput
							v-model='oldPassword'
							:label='$t("core.profile.form.oldPassword").toString()'
							:is-valid='touched ? valid : null'
							:invalid-feedback='errors.join(", ")'
							autocomplete='current-password'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{valid, touched, errors}'
						:rules='{
							required: oldPassword.length > 0,
						}'
						:custom-messages='{
							required: $t("core.user.errors.newPassword"),
						}'
					>
						<PasswordInput
							v-model='newPassword'
							:label='$t("core.profile.form.newPassword").toString()'
							:is-valid='touched ? valid : null'
							:invalid-feedback='errors.join(", ")'
							autocomplete='new-password'
						/>
					</ValidationProvider>
					<CButton
						color='primary'
						type='submit'
						:disabled='invalid'
					>
						{{ $t('core.profile.form.changePassword') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
// Components
import {Component, Vue} from 'vue-property-decorator';
import {CAlert, CButton, CCard, CCardBody, CCardHeader, CForm, CIcon} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import PasswordInput from '@/components/Core/PasswordInput.vue';
// Module properties
import {cilInfo} from '@coreui/icons';
import {required} from 'vee-validate/dist/rules';
// Auxiliary functions
import {extendedErrorToast} from '@/helpers/errorToast';
// Services
import UserService from '@/services/UserService';
// Interfaces
import {AxiosError} from 'axios';

@Component({
	components: {
		CAlert,
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CIcon,
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
	 * @var {string} oldPassword Old user password
	 */
	private oldPassword = '';

	/**
	 * @var {string} newPassword New user password
	 */
	private newPassword = '';

	/**
	 * @constant {Array<string>} infoIcon Info icon
	 */
	private infoIcon: Array<string> = cilInfo;

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
		UserService.changePasswordLoggedIn(this.oldPassword, this.newPassword)
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
