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
			<strong>{{ $t('core.profile.form.editProfile') }}</strong>
		</CCardHeader>
		<CCardBody>
			<ValidationObserver v-slot='{invalid}'>
				<CForm @submit.prevent='save'>
					<ValidationProvider
						v-slot='{valid, touched, errors}'
						rules='required'
						:custom-messages='{
							required: $t("forms.errors.username"),
						}'
					>
						<CInput
							v-model='user.username'
							:label='$t("forms.fields.username")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='errors.join(", ")'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{valid, touched, errors}'
						rules='email'
						:custom-messages='{
							email: $t("forms.errors.emailFormat"),
						}'
					>
						<CInput
							v-model='user.email'
							:label='$t("forms.fields.email")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='errors.join(", ")'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{valid, touched, errors}'
						rules='required'
						:custom-messages='{
							required: $t("core.user.errors.language"),
						}'
					>
						<CSelect
							:value.sync='user.language'
							:label='$t("core.user.language")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='errors.join(", ")'
							:options='languageOptions'
						/>
					</ValidationProvider>
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
// Components
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput, CSelect} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
// Module properties
import {required} from 'vee-validate/dist/rules';
import {email} from '@/helpers/validators';
// Auxiliary functions
import {extendedErrorToast} from '@/helpers/errorToast';
// Services
import UserService from '@/services/UserService';
// Interfaces
import {AxiosError} from 'axios';
import {IUserBase, UserLanguage, UserRole} from '@/services/AuthenticationService';
import {IOption} from '@/interfaces/Coreui';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		CSelect,
		ValidationObserver,
		ValidationProvider,
	},
})

/**
 * User profile edit form component
 */
export default class UserProfileForm extends Vue {

	/**
	 * @var {IUserBase} user User
	 */
	private user: IUserBase = {
		username: '',
		email: '',
		language: UserLanguage.ENGLISH,
		role: UserRole.BASIC,
	};

	/**
	 * @constant {Array<IOption>} languageOptions Language options for CoreUI select
	 */
	private languageOptions: Array<IOption> = [
		{
			value: UserLanguage.ENGLISH,
			label: this.$t('core.user.languages.en'),
		},
	];

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('email', email);
		extend('required', required);
		this.getUser();
	}

	/**
	 * Retrieves information about user from store
	 */
	private getUser(): void {
		this.user = this.$store.getters['user/get'];
	}

	/**
	 * Saves changes to user profile
	 */
	private save(): void {
		this.$store.commit('spinner/SHOW');
		UserService.editLoggedIn(this.user)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('core.profile.messages.saveSuccess').toString()
				);
				this.$store.dispatch('user/updateInfo');
			})
			.catch((err: AxiosError) => extendedErrorToast(err, 'core.profile.messages.saveFailed'));
	}
}
</script>
