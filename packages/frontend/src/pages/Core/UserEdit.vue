<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

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
								v-model='user.username'
								:label='$t("forms.fields.username")'
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
						<v-checkbox
							v-model='changePassword'
							:label='$t("core.user.changePassword")'
							:hide-details='changePassword'
						/>
						<div v-if='changePassword'>
							<ValidationProvider
								v-slot='{ valid, touched, errors }'
								rules='required'
								:custom-messages='{
									required: $t("core.user.errors.newPassword"),
								}'
							>
								<PasswordInput
									v-model='password'
									:label='$t("core.user.newPassword").toString()'
									autocomplete='new-password'
									:success='touched ? valid : null'
									:error-messages='errors'
								/>
							</ValidationProvider>
						</div>
						<v-btn
							color='primary'
							type='submit'
							:disabled='invalid'
						>
							{{ $t('forms.save') }}
						</v-btn>
					</v-form>
				</ValidationObserver>
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {UserService} from '@iqrf/iqrf-gateway-webapp-client/services/Security';
import {
	UserEdit,
	UserInfo,
	UserLanguage,
	UserRole,
} from '@iqrf/iqrf-gateway-webapp-client/types';
import {AxiosError} from 'axios';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {Component, Prop, Vue} from 'vue-property-decorator';
import {required} from 'vee-validate/dist/rules';

import PasswordInput from '@/components/Core/PasswordInput.vue';
import {extendedErrorToast} from '@/helpers/errorToast';
import {email} from '@/helpers/validators';
import {ISelectItem} from '@/interfaces/Vuetify';
import {useApiClient} from '@/services/ApiClient';
import UrlBuilder from '@/helpers/urlBuilder';

@Component({
	components: {
		PasswordInput,
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
export default class UserEditForm extends Vue {
	/**
	 * @property {number} userId User id
	 */
	@Prop({required: true}) userId!: number;

	/**
	 * @var {UserEdit} user User
	 */
	private user: UserEdit = {
		username: '',
		email: '',
		language: UserLanguage.English,
		role: UserRole.Basic,
	};

	/**
	 * @var {Array<ISelectItem>} roles Array of available user roles
	 */
	private roles: Array<ISelectItem> = [];

	/**
	 * @constant {Array<ISelectItem>} languages Language options
	 */
	private readonly languages: Array<ISelectItem> = [
		{
			value: UserLanguage.Czech,
			text: this.$t('core.user.languages.cs'),
		},
		{
			value: UserLanguage.English,
			text: this.$t('core.user.languages.en'),
		},
	];

	/**
	 * @var {boolean} changePassword Change password
	 */
	private changePassword: boolean = false;

	/**
	 * @var {string} newPassword New user password
	 */
	private password = '';

	/**
	 * @private {UserService} service User service
	 */
	private service: UserService = useApiClient().getSecurityServices().getUserService();

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
		this.service.get(this.userId)
			.then((user: UserInfo) => {
				this.user = {
					username: user.username,
					email: user.email,
					language: user.language,
					role: user.role,
					baseUrl: new UrlBuilder().getBaseUrl(),
				};
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
		const user: UserEdit = {...this.user};
		if (this.changePassword) {
			user.password = this.password;
		}
		this.service.update(this.userId, this.user)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'core.user.messages.editSuccess',
						{username: user.username}
					).toString()
				);
				if (this.userId === this.$store.getters['user/getId']) {
					this.$store.dispatch('user/updateInfo');
				}
				this.$router.push('/user/');
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'core.user.messages.editFailed', {user: user.username});
			});
	}

}
</script>
