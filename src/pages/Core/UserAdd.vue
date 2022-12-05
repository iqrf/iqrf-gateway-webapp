<!--
Copyright 2017-2022 IQRF Tech s.r.o.
Copyright 2019-2022 MICRORISC s.r.o.

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
		<h1>{{ $t('core.user.add') }}</h1>
		<CCard body-wrapper>
			<ValidationObserver v-slot='{invalid}'>
				<CForm @submit.prevent='saveUser'>
					<ValidationProvider
						v-slot='{valid, touched, errors}'
						rules='required'
						:custom-messages='{
							required: "forms.errors.username",
						}'
					>
						<CInput
							id='username'
							v-model='username'
							:label='$t("forms.fields.username")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{valid, touched, errors}'
						rules='required'
						:custom-messages='{
							required: "forms.errors.password",
						}'
					>
						<CInput
							id='password'
							v-model='password'
							:label='$t("forms.fields.password")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
							type='password'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{valid, touched, errors}'
						rules='email'
						:custom-messages='{
							email: "forms.errors.emailFormat",
						}'
					>
						<CInput
							id='email'
							v-model='email'
							:label='$t("forms.fields.email")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{valid, touched, errors}'
						rules='required'
						:custom-messages='{
							required: "core.user.errors.role",
						}'
					>
						<CSelect
							:value.sync='role'
							:label='$t("core.user.role")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
							:placeholder='$t("core.user.errors.role")'
							:options='roles'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{valid, touched, errors}'
						rules='required'
						:custom-messages='{
							required: "core.user.errors.language",
						}'
					>
						<CSelect
							:value.sync='language'
							:label='$t("core.user.language")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
							:placeholder='$t("core.user.errors.language")'
							:options='languages'
						/>
					</ValidationProvider>
					<CButton
						color='primary'
						type='submit'
						:disabled='invalid'
					>
						{{ $t('forms.add') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CForm, CInput, CSelect} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {extendedErrorToast} from '@/helpers/errorToast';
import {email, required} from 'vee-validate/dist/rules';
import {UserLanguage, UserRole} from '@/services/AuthenticationService';

import UserService from '@/services/UserService';

import {AxiosError} from 'axios';
import {IOption} from '@/interfaces/coreui';

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
		title: 'core.user.add',
	}
})

/**
 * User manager form to add a new user
 */
export default class UserAdd extends Vue {

	/**
	 * @var {string} email User's email
	 */
	private email = '';


	/**
	 * @var {string} username User name
	 */
	private username = '';

	/**
	 * @var {string} password User password
	 */
	private password = '';

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
	 * @var {UserRole} role User role
	 */
	private role: UserRole = UserRole.BASIC;

	/**
	 * @constant {Array<IOption>} roles Array of available user roles
	 */
	private roles: Array<IOption> = [];

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
					label: this.$t('core.user.roles.' + UserRole[item]),
				});
			}
		}
		this.roles = roles;
	}

	/**
	 * Creates a new user entry with default language and role if unspecified
	 */
	private saveUser(): void {
		this.$store.commit('spinner/SHOW');
		UserService.add(this.username, this.email, this.password, this.language, this.role)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'core.user.messages.addSuccess',
						{username: this.username}
					).toString()
				);
				this.$router.push('/user/');
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'core.user.messages.addFailed'));
	}
}
</script>
