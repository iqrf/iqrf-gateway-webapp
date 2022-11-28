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
		<h1>{{ $t('core.user.add') }}</h1>
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
							id='username'
							v-model='user.username'
							:label='$t("forms.fields.username")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='errors.join(", ")'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{valid, touched, errors}'
						rules='required'
						:custom-messages='{
							required: $t("forms.errors.password"),
						}'
					>
						<CInput
							id='password'
							v-model='user.password'
							:label='$t("forms.fields.password")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='errors.join(", ")'
							type='password'
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
							id='email'
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
							required: $t("core.user.errors.role"),
						}'
					>
						<CSelect
							:value.sync='user.role'
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
							:value.sync='user.language'
							:label='$t("core.user.language")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='errors.join(", ")'
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
import isFQDN from 'is-fqdn';
import punycode from 'punycode/';

import UserService from '@/services/UserService';

import {AxiosError} from 'axios';
import {IOption} from '@/interfaces/Coreui';
import {IUser} from '@/interfaces/Core/User';

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
	 * @var {IUser} user User
	 */
	private user: IUser = {
		username: '',
		password: '',
		email: null,
		language: UserLanguage.ENGLISH,
		role: UserRole.BASIC,
	};

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
	 * @constant {Array<IOption>} roles Array of available user roles
	 */
	private roles: Array<IOption> = [];

	/**
	 * Initialize validation rules and build user roles
	 */
	created(): void {
		extend('email', (addr: string) => {
			const encoded = punycode.toASCII(addr);
			if (!email.validate(encoded)) {
				return false;
			} 
			const domain = encoded.split('@');
			if (domain.length === 1) {
				return false;
			}
			return isFQDN(domain[1]);
		});
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
	 * Creates a new user entry with default language and role if unspecified
	 */
	private saveUser(): void {
		this.$store.commit('spinner/SHOW');
		UserService.add(this.user)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'core.user.messages.addSuccess',
						{username: this.user.username}
					).toString()
				);
				this.$router.push('/user/');
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'core.user.messages.addFailed'));
	}
}
</script>
