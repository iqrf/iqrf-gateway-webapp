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
		<h1>{{ $t('core.user.add') }}</h1>
		<v-card>
			<v-card-text>
				<ValidationObserver v-slot='{invalid}'>
					<v-form @submit.prevent='saveUser'>
						<ValidationProvider
							v-slot='{valid, touched, errors}'
							rules='required'
							:custom-messages='{
								required: $t("forms.errors.username"),
							}'
						>
							<v-text-field
								id='username'
								v-model='user.username'
								:label='$t("forms.fields.username")'
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
						<ValidationProvider
							v-slot='{valid, touched, errors}'
							rules='email'
							:custom-messages='{
								email: $t("forms.errors.emailFormat"),
							}'
						>
							<v-text-field
								id='email'
								v-model='user.email'
								:label='$t("forms.fields.email")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{valid, touched, errors}'
							rules='required'
							:custom-messages='{
								required: $t("core.user.errors.role"),
							}'
						>
							<v-select
								v-model='user.role'
								:label='$t("core.user.role")'
								:items='roles'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{valid, touched, errors}'
							rules='required'
							:custom-messages='{
								required: $t("core.user.errors.language"),
							}'
						>
							<v-select
								v-model='user.language'
								:label='$t("core.user.language")'
								:items='languages'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<v-btn
							color='primary'
							type='submit'
							:disabled='invalid'
						>
							{{ $t('forms.add') }}
						</v-btn>
					</v-form>
				</ValidationObserver>
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import PasswordInput from '@/components/Core/PasswordInput.vue';

import {extendedErrorToast} from '@/helpers/errorToast';
import {email} from '@/helpers/validators';
import {required} from 'vee-validate/dist/rules';

import {UserLanguage, UserRole} from '@/services/AuthenticationService';
import UserService from '@/services/UserService';

import {AxiosError} from 'axios';
import {ISelectItem} from '@/interfaces/Vuetify';
import {IUser} from '@/interfaces/Core/User';

@Component({
	components: {
		PasswordInput,
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
	 * @constant {Array<ISelectItem>} languages Language options
	 */
	private readonly languages: Array<ISelectItem> = [
		{
			value: UserLanguage.ENGLISH,
			text: this.$t('core.user.languages.en'),
		},
	];

	/**
	 * @constant {Array<ISelectItem>} roles Array of available user roles
	 */
	private roles: Array<ISelectItem> = [];

	/**
	 * Initialize validation rules and build user roles
	 */
	created(): void {
		extend('email', email);
		extend('required', required);
		const roleVal = this.$store.getters['user/getRole'];
		const roleIdx = Object.values(UserRole).indexOf(roleVal);
		const roles: Array<ISelectItem> = [];
		for (const item of Object.keys(UserRole)) {
			const itemIdx = Object.keys(UserRole).indexOf(item);
			if (itemIdx >= roleIdx) {
				roles.push({
					value: UserRole[item],
					text: this.$t(`core.user.roles.${UserRole[item]}`),
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
