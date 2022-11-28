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
								v-model='username'
								:label='$t("forms.fields.username")'
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
								required: $t("forms.errors.password"),
							}'
						>
							<CInput
								id='password'
								v-model='password'
								:label='$t("forms.fields.password")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='errors.join(", ")'
								type='password'
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
import {CButton, CCard, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import UserService from '@/services/UserService';

import {extendedErrorToast} from '@/helpers/errorToast';
import {email, required} from 'vee-validate/dist/rules';
import {sleep} from '@/helpers/sleep';
import {UserCredentials, UserLanguage, UserRole} from '@/services/AuthenticationService';

import isFQDN from 'is-fqdn';
import punycode from 'punycode/';

import {AxiosError, AxiosResponse} from 'axios';
import {IUser} from '@/interfaces/Core/User';

@Component({
	components: {
		CButton,
		CCard,
		CForm,
		CInput,
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
		role: UserRole.BASIC,
	};

	/**
	 * @var {bool} running Indicates whether axios requests are in progress
	 */
	private running = false;

	/**
	 * On component creation event handler
	 */
	public created(): void {
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
						await sleep(500);
						this.running = false;
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
