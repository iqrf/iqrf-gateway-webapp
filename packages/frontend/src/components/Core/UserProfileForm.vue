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
			{{ $t('core.profile.form.editProfile') }}
		</v-card-title>
		<v-card-text>
			<ValidationObserver v-slot='{invalid}'>
				<form @submit.prevent='save'>
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
							required: $t("core.user.errors.language"),
						}'
					>
						<v-select
							v-model='user.language'
							:label='$t("core.user.language")'
							:success='touched ? valid : null'
							:error-messages='errors'
							:items='languageOptions'
						/>
					</ValidationProvider>
					<v-btn
						color='primary'
						type='submit'
						:disabled='invalid'
					>
						{{ $t('forms.save') }}
					</v-btn>
				</form>
			</ValidationObserver>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {
	UserEdit,
	UserLanguage,
	UserRole
} from '@iqrf/iqrf-gateway-webapp-client/types/User';
import {AxiosError} from 'axios';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import {Component, Vue} from 'vue-property-decorator';

// Module properties
import {email} from '@/helpers/validators';
// Auxiliary functions
import {extendedErrorToast} from '@/helpers/errorToast';
// Services
import {useApiClient} from '@/services/ApiClient';
// Interfaces
import {ISelectItem} from '@/interfaces/Vuetify';
import UrlBuilder from '@/helpers/urlBuilder';

@Component({
	components: {
		ValidationObserver,
		ValidationProvider,
	},
})

/**
 * User profile edit form component
 */
export default class UserProfileForm extends Vue {

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
	 * @constant {Array<ISelectItem>} languageOptions Language select options
	 */
	private languageOptions: Array<ISelectItem> = [
		{
			value: UserLanguage.Czech,
			text: this.$t('core.user.languages.cs').toString(),
		},
		{
			value: UserLanguage.English,
			text: this.$t('core.user.languages.en').toString(),
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
		this.user.baseUrl = new UrlBuilder().getBaseUrl();
	}

	/**
	 * Saves changes to user profile
	 */
	private save(): void {
		this.$store.commit('spinner/SHOW');
		useApiClient().getAccountService().edit(this.user)
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
