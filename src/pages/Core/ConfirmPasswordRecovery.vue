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
	<CCard class='p-4'>
		<h1 class='text-center'>
			{{ $t('account.recovery.title') }}
		</h1>
		<CCardBody>
			<CElementCover
				v-if='requestInProgress'
				:opacity='0.75'
				style='z-index: 10000;'
			>
				<CSpinner color='primary' />
			</CElementCover>
			<p>
				{{ $t('account.recovery.changePrompt') }}
			</p>
			<ValidationObserver v-slot='{invalid}'>
				<CForm @submit.prevent='confirmRecovery'>
					<ValidationProvider
						v-slot='{valid, touched, errors}'
						rules='required'
						:custom-messages='{
							required: $t("core.sign.in.messages.password"),
						}'
					>
						<PasswordInput
							v-model='password'
							:label='$t("forms.fields.password").toString()'
							:is-valid='touched ? valid : null'
							:invalid-feedback='errors.join(", ")'
						/>
					</ValidationProvider>
					<CButton
						color='primary'
						type='submit'
						:disabled='invalid'
					>
						{{ $t('account.recovery.changePassword') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import {CButton, CCard, CCardBody, CElementCover, CForm, CInput, CSpinner} from '@coreui/vue/src';
import {AxiosError} from 'axios';
import {Component, Vue, Prop} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';

import PasswordInput from '@/components/Core/PasswordInput.vue';
import TheWizard from '@/components/TheWizard.vue';
import {extendedErrorToast} from '@/helpers/errorToast';
import {User, UserRole} from '@/services/AuthenticationService';
import UserService from '@/services/UserService';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CElementCover,
		CForm,
		CInput,
		CSpinner,
		PasswordInput,
		TheWizard,
		ValidationObserver,
		ValidationProvider,
	},
	metaInfo: {
		title: 'account.recovery.title'
	}
})

/**
 * Account password recovery confirmation component
 */
export default class ConfirmPasswordRecovery extends Vue {

	/**
	 * @var {string} password New user password
	 */
	private password = '';

	/**
	 * @var {bool} passwordVisible Controls visibility of password field
	 */
	private passwordVisible = false;

	/**
	 * @var {bool} requestInProgress Indicates whether axios requests are in progress
	 */
	private requestInProgress = false;

	/**
	 * @property {string} recoveryId Password recovery request ID
	 */
	@Prop({required: true}) recoveryId!: string;

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
	}

	/**
	 * Sends a request for recovery email
	 */
	private confirmRecovery(): void {
		this.requestInProgress = true;
		UserService.confirmPasswordRecovery(this.recoveryId, this.password)
			.then((user: User) => {
				this.requestInProgress = false;
				if (user.role === UserRole.BASIC) {
					location.pathname = '/';
				}
				this.$store.dispatch('user/setJwt', user);
				this.$toast.success(
					this.$t('account.recovery.messages.changeSuccess').toString()
				);
				this.$router.push('/');
			})
			.catch((error: AxiosError) => {
				this.requestInProgress = false;
				extendedErrorToast(error, 'account.recovery.messages.changeFailed');
			});
	}

}
</script>

