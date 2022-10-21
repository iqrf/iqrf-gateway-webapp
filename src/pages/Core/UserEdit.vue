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
		<h1>{{ $t('core.user.edit') }}</h1>
		<CCard body-wrapper>
			<ValidationObserver v-slot='{invalid}'>
				<CForm @submit.prevent='saveUser'>
					<ValidationProvider
						v-slot='{valid, touched, errors}'
						rules='required'
						:custom-messages='{
							required: $t("forms.errors.username"),
						}'
					>
						<CInput
							v-model='username'
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
							v-model='email'
							:label='$t("forms.fields.email")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='errors.join(", ")'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{valid, touched, errors}'
						rules='required'
						:custom-messages='{
							required: $t("core.user.errors.role"),
						}'
					>
						<CSelect
							:value.sync='role'
							:label='$t("core.user.role")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='errors.join(", ")'
							:placeholder='$t("core.user.errors.role")'
							:options='roles'
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
							:value.sync='language'
							:label='$t("core.user.language")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='errors.join(", ")'
							:placeholder='$t("core.user.errors.language")'
							:options='languages'
						/>
					</ValidationProvider>
					<CInput
						v-model='password'
						:label='$t("core.user.newPassword")'
						type='password'
						autocomplete='new-password'
					/>
					<CButton
						color='primary'
						type='submit'
						:disabled='invalid'
					>
						{{ $t('forms.save') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {CButton, CCard, CForm, CInput, CSelect} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {extendedErrorToast} from '@/helpers/errorToast';
import {email, required} from 'vee-validate/dist/rules';
import {UserLanguage, UserRole} from '@/services/AuthenticationService';
import UserService from '@/services/UserService';

import {AxiosError, AxiosResponse} from 'axios';
import {IOption} from '@/interfaces/Coreui';

@Component({
	components: {
		CButton,
		CCard,
		CForm,
		CInput,
		CSelect,
		ValidationObserver,
		ValidationProvider,
	},
	metaInfo: {
		title: 'core.user.edit',
	}
})

/**
 * User manager form to edit an existing user
 */
export default class UserEdit extends Vue {
	/**
	 * @var {string} username User name
	 */
	private username = '';

	/**
	 * @var {string|null} email User's email
	 */
	private email: string|null = null;

	/**
	 * @var {UserRole} role User role
	 */
	private role: UserRole = UserRole.BASIC;

	/**
	 * @constant {Array<IOption>} roles Array of available user roles
	 */
	private roles: Array<IOption> = [];

	/**
	 * @var {UserLanguage} language User's preferred language
	 */
	private language: UserLanguage = UserLanguage.ENGLISH;

	/**
	 * @constant {Array<IOption>} languages Language options
	 */
	private languages: Array<IOption> = [
		{
			value: UserLanguage.ENGLISH,
			label: this.$t('core.user.languages.en'),
		},
	];

	/**
	 * @var {string} newPassword New user password
	 */
	private password = '';

	/**
	 * @property {number} userId User id
	 */
	@Prop({required: true}) userId!: number;

	/**
	 * Initialize validation rules and build user roles
	 */
	created(): void {
		extend('email', email);
		extend('required', required);
		const roleVal = this.$store.getters['user/getRole'];
		const roleIdx = Object.values(UserRole).indexOf(roleVal);
		const roles: Array<IOption> = [];
		for (const item of Object.keys(UserRole)) {
			const itemIdx = Object.keys(UserRole).indexOf(item);
			if (itemIdx >= roleIdx) {
				roles.push({
					value: UserRole[item],
					label: this.$t(`core.user.roles.${UserRole[item]}`),
				});
			}
		}
		this.roles = roles;
	}

	/**
	 * Retrieves component data
	 */
	mounted(): void {
		this.getUser();
	}

	/**
	 * Retrieves user configuration
	 */
	private getUser(): void {
		this.$store.commit('spinner/SHOW');
		UserService.get(this.userId)
			.then((response: AxiosResponse) => {
				this.username = response.data.username;
				this.language = response.data.language;
				this.role = response.data.role;
				this.email = response.data.email;
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'core.user.messages.fetchFailed', {user: this.userId});
				this.$router.push('/user/');
			});
	}

	/**
	 * Updates user's password, if the old password is valid, the rest of the settings are then updated and signout is performed
	 */
	private saveUser(): void {
		this.$store.commit('spinner/SHOW');
		const user = {
			email: this.email !== '' ? this.email : null,
			username: this.username,
			language: this.language,
			role: this.role
		};
		if (this.password !== '') {
			Object.assign(user, {password: this.password});
		}
		UserService.edit(this.userId, user)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'core.user.messages.editSuccess',
						{username: this.username}
					).toString()
				);
				this.$router.push('/user/');
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'core.user.messages.editFailed', {user: this.username});
			});
	}

}
</script>
