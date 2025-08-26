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
		<CCard>
			<CCardHeader>{{ $t('install.createUser.title') }}</CCardHeader>
			<CCardBody>
				<CElementCover
					v-if='running'
					:opacity='0.75'
					style='z-index: 10000;'
				>
					<CSpinner color='primary' />
				</CElementCover>
				<ValidationObserver v-slot='{invalid}'>
					<CForm @submit.prevent='handleSubmit'>
						<div class='form-group'>
							{{ $t('install.createUser.note') }}
						</div>
						<ValidationProvider
							v-slot='{ valid, touched, errors }'
							rules='required'
							:custom-messages='{
								required: $t("forms.errors.username"),
							}'
						>
							<CInput
								id='username'
								v-model='user.username'
								:label='$t("forms.fields.username").toString()'
								:is-valid='touched ? valid : null'
								:invalid-feedback='errors.join(", ")'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ valid, touched, errors }'
							rules='email'
							:custom-messages='{
								email: $t("forms.errors.emailFormat"),
							}'
						>
							<CInput
								id='email'
								v-model='user.email'
								:label='$t("forms.fields.email").toString()'
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
							<PasswordInput
								id='password'
								v-model='user.password'
								:label='$t("forms.fields.password").toString()'
								:is-valid='touched ? valid : null'
								:invalid-feedback='errors.join(", ")'
							/>
						</ValidationProvider>
						<CButton color='primary' type='submit' :disabled='invalid'>
							{{ $t('install.createUser.createButton') }}
						</CButton>
					</CForm>
				</ValidationObserver>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CElementCover, CForm, CInput, CSpinner} from '@coreui/vue/src';
import {AxiosError, AxiosResponse} from 'axios';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';

import PasswordInput from '@/components/Core/PasswordInput.vue';
import {extendedErrorToast} from '@/helpers/errorToast';
import {sleep} from '@/helpers/sleep';
import {email} from '@/helpers/validators';
import {IUser} from '@/interfaces/Core/User';
import {UserCredentials, UserLanguage, UserRole} from '@/services/AuthenticationService';
import UserService from '@/services/UserService';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CElementCover,
		CForm,
		CInput,
		CSpinner,
		PasswordInput,
		ValidationObserver,
		ValidationProvider,
	},
	metaInfo: {
		title: 'install.createUser.title',
	},
})

/**
 * Create user
 */
export default class InstallCreateUser extends Vue {
	/**
	 * @var {IUser} user User
	 */
	private user: IUser = {
		username: '',
		password: '',
		email: '',
		language: UserLanguage.ENGLISH,
		role: UserRole.ADMIN,
	};

	/**
	 * @var {bool} running Indicates whether axios requests are in progress
	 */
	private running = false;

	/**
	 * On component creation event handler
	 */
	public created(): void {
		extend('email', email);
		extend('required', required);
	}

	/**
	 * Handle form submit
	 */
	private handleSubmit(): void {
		this.running = true;
		UserService.add(this.user)
			.then((response: AxiosResponse) => {
				if (response.data.emailSent === true) {
					this.$toast.success(
						this.$t('core.user.messages.verifyNotice').toString()
					);
				}
				const credentials = new UserCredentials(this.user.username, (this.user.password as string));
				this.$store.dispatch('user/signIn', credentials)
					.then(async () => {
						//await sleep(500);
						this.running = false;
						try {
							this.$store.dispatch('repository/get');
							this.$store.dispatch('gateway/getInfo');
						} catch {
							// not a problem
						}
						this.nextStep();
					});
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'install.createUser.failure');
				this.running = false;
			});
	}

	/**
	 * Advances the installation wizard
	 */
	private nextStep(): void {
		this.$emit('next-step');
		this.$router.push('/install/smtp/');
	}
}
</script>
