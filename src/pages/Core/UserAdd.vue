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
			<ValidationObserver v-slot='{ invalid }'>
				<CForm @submit.prevent='handleSubmit'>
					<ValidationProvider
						v-slot='{ valid, touched, errors }'
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
						v-slot='{ valid, touched, errors }'
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
						v-slot='{ valid, touched, errors }'
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
						v-if='$store.getters["user/getRole"] === "power"'
						v-slot='{ valid, touched, errors }'
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
							:options='[
								{value: "normal", label: $t("core.user.roles.normal")},
								{value: "power", label: $t("core.user.roles.power")},
							]'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-if='$store.getters["user/getRole"] === "power"'
						v-slot='{ valid, touched, errors }'
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
							:options='[
								{value: "en", label: $t("core.user.languages.en")},
							]'
						/>
					</ValidationProvider>
					<CButton color='primary' type='submit' :disabled='invalid'>
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

import {extendedErrorToast} from '../../helpers/errorToast';
import {email, required} from 'vee-validate/dist/rules';
import UserService from '../../services/UserService';

import {AxiosError} from 'axios';

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
	private email= '';

	/**
	 * @var {string} language User's preferred language
	 */
	private language = '';

	/**
	 * @var {string} password User password
	 */
	private password = '';

	/**
	 * @var {string} role User role
	 */
	private role = '';

	/**
	 * @var {string} username User name
	 */
	private username = '';

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('email', email);
		extend('required', required);
	}

	/**
	 * Creates a new user entry with default language and role if unspecified
	 */
	private handleSubmit(): void {
		const language = this.language === '' ? 'en' : this.language;
		const role = this.role === '' ? 'normal' : this.role;
		this.$store.commit('spinner/SHOW');
		UserService.add(this.username, this.email, this.password, language, role)
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
