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
					style='z-index: 10000'
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
								required: "forms.errors.username",
							}'
						>
							<CFormInput
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
							<CFormInput
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
								required: "forms.errors.password",
							}'
						>
							<CFormInput
								id='password'
								v-model='password'
								:label='$t("forms.fields.password")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
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
import {AxiosError} from 'axios';
import {CButton, CCard, CForm, CFormInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {Options, Vue} from 'vue-property-decorator';

import UserService from '../../services/UserService';

import {extendedErrorToast} from '../../helpers/errorToast';
import {email, required} from 'vee-validate/dist/rules';
import {sleep} from '../../helpers/sleep';
import {UserCredentials} from '../../services/AuthenticationService';

@Options({
	components: {
		CButton,
		CCard,
		CForm,
		CFormInput,
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
	 * Email
	 */
	private email = '';

	/**
	 * User name
	 */
	private username = '';

	/**
	 * User password
	 */
	private password = '';

	/**
	 * User language
	 */
	private language = 'en';

	/**
	 * User role
	 */
	private role = 'normal';

	/**
	 * @var {bool} running Indicates whether axios requests are in progress
	 */
	private running = false

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
		UserService.add(this.username, this.email, this.password, this.language, this.role)
			.then(() => {
				if (this.email.length !== 0) {
					this.$toast.success(
						this.$t('core.user.messages.verifyNotice').toString()
					);
				}
				const credentials = new UserCredentials(this.username, this.password);
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
