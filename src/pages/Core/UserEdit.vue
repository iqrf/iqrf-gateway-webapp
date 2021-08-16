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
				<CForm @submit.prevent='handleSubmit'>
					<ValidationProvider
						v-slot='{valid, touched, errors}'
						rules='required'
						:custom-messages='{
							required: "forms.errors.username",
						}'
					>
						<CInput
							v-model='username'
							:label='$t("forms.fields.username")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
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
							v-if='$store.getters["user/getRole"] === "power"'
							:value.sync='role'
							:label='$t("core.user.role")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
							:placeholder='$t("core.user.errors.role")'
							:options='[
								{value: "normal", label: $t("core.user.roles.normal")},
								{value: "power", label: $t("core.user.roles.power")},
							]'
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
							v-if='$store.getters["user/getRole"] === "power"'
							:value.sync='language'
							:label='$t("core.user.language")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
							:placeholder='$t("core.user.errors.language")'
							:options='[
								{value: "en", label: $t("core.user.languages.en")},
							]'
						/>
					</ValidationProvider>
					<div v-if='$store.getters["user/getId"] === userId'>
						<ValidationProvider
							v-slot='{valid, touched, errors}'
							:rules='newPassword !== "" ? "required" : ""'
							:custom-messages='{
								required: "core.user.errors.oldPassword",
							}'
						>
							<CInput
								v-model='oldPassword'
								:label='$t("core.user.oldPassword")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
								type='password'
								autocomplete='current-password'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{valid, touched, errors}'
							:rules='oldPassword !== "" ? "required" : ""'
							:custom-messages='{
								required: "core.user.errors.newPassword",
							}'
						>
							<CInput
								v-model='newPassword'
								:label='$t("core.user.newPassword")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
								type='password'
								autocomplete='new-password'
							/>
						</ValidationProvider>
					</div>
					<CButton color='primary' type='submit' :disabled='invalid'>
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

import {extendedErrorToast} from '../../helpers/errorToast';
import {email, required} from 'vee-validate/dist/rules';
import UserService from '../../services/UserService';

import {AxiosError, AxiosResponse} from 'axios';

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
	 * @var {string|null} email User's email
	 */
	private email: string|null = null

	/**
	 * @var {string} language User's preferred language
	 */
	private language = ''

	/**
	 * @var {boolean} loaded Indicates whether user information has been successfully retrieved
	 */
	private loaded = false

	/**
	 * @var {string} newPassword New user password
	 */
	private newPassword = ''

	/**
	 * @var {string} oldPassword Current user password
	 */
	private oldPassword = ''

	/**
	 * @var {string} role User role
	 */
	private role = ''

	/**
	 * @var {string} username User name
	 */
	private username = ''

	/**
	 * @property {number} userId User id
	 */
	@Prop({required: true}) userId!: number

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('email', email);
		extend('required', required);
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
	private handleSubmit(): void {
		this.$store.commit('spinner/SHOW');
		if (this.$store.getters['user/getId'] === this.userId &&
			this.oldPassword !== '' && this.newPassword !== '') {
			UserService.changePassword(this.oldPassword, this.newPassword)
				.then(() => {
					this.performEdit().then(() => this.signOut());
				})
				.catch((error: AxiosError) => extendedErrorToast(error, 'core.user.messages.editFailed', {user: this.userId}));
		} else {
			this.performEdit().then(() => {
				if (this.$store.getters['user/getId'] === this.userId) {
					this.signOut();
				} else {
					this.$router.push('/user/');
				}
			});
		}

	}

	/**
	 * Updates user information
	 */
	private performEdit(): Promise<void> {
		return UserService.edit(this.userId, {
			email: this.email !== '' ? this.email : null,
			username: this.username,
			language: this.language,
			role: this.role
		})
			.then(() => {
				this.$toast.success(
					this.$t(
						'core.user.messages.editSuccess',
						{username: this.username}
					).toString()
				);
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'core.user.messages.editFailed', {user: this.userId});
				return Promise.reject();
			});
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
