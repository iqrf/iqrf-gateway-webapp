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
	<div class='c-app flex-row align-items-center'>
		<CContainer>
			<CRow class='justify-content-center'>
				<CCol md='8'>
					<div class='py-5'>
						<LogoBlue :alt='$t("core.title")' width='100%' height='32pt' />
					</div>
					<CCard class='p-4'>
						<CCardBody>
							<ValidationObserver v-slot='{ invalid }'>
								<CForm @submit.prevent='handleSubmit'>
									<h1 class='text-center'>
										{{ $t('core.sign.in.title') }}
									</h1>
									<ValidationProvider
										v-slot='{ valid, touched, errors }'
										rules='required'
										:custom-messages='{required: "core.sign.in.messages.username"}'
									>
										<CInput
											id='username'
											v-model='username'
											:label='$t("forms.fields.username")'
											:placeholder='$t("forms.fields.username")'
											autocomplete='username'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										>
											<template #prepend-content>
												<CIcon :content='icons.user' />
											</template>
										</CInput>
									</ValidationProvider>
									<ValidationProvider
										v-slot='{ valid, touched, errors }'
										rules='required'
										:custom-messages='{required: "core.sign.in.messages.password"}'
									>
										<CInput
											id='password'
											v-model='password'
											:label='$t("forms.fields.password")'
											:placeholder='$t("forms.fields.password")'
											type='password'
											autocomplete='password'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										>
											<template #prepend-content>
												<CIcon :content='icons.lock' />
											</template>
										</CInput>
									</ValidationProvider>
									<CButton color='primary' type='submit' :disabled='invalid'>
										{{ $t('core.sign.in.send') }}
									</CButton>
								</CForm>
							</ValidationObserver>
						</CCardBody>
					</CCard>
				</CCol>
			</CRow>
		</CContainer>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CContainer, CCard, CCardBody, CCol, CForm, CIcon, CInput, CRow} from '@coreui/vue/src';
import {cilUser, cilLockLocked} from '@coreui/icons';
import {required} from 'vee-validate/dist/rules';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import LogoBlue from '../../assets/logo-blue.svg';
import {UserCredentials} from '../../services/AuthenticationService';
import {sleep} from '../../helpers/sleep';
import {Dictionary} from 'vue-router/types/router';

@Component({
	components: {
		CContainer,
		CCard,
		CCardBody,
		CCol,
		CForm,
		CIcon,
		CInput,
		CRow,
		LogoBlue,
		ValidationObserver,
		ValidationProvider,
	},
	metaInfo: {
		title: 'core.sign.in.title',
	},
})

/**
 * Sign in page component
 */
export default class SignIn extends Vue {
	/**
	 * @constant {Dictionary<Array<string>>} icons Dictionary of CoreUI icons
	 */
	private icons: Dictionary<Array<string>> = {
		user: cilUser,
		lock: cilLockLocked,
	}

	/**
	 * @var {string} password User password
	 */
	private password = ''

	/**
	 * @var {boolean} submitted Indicates whether the login information have been submitted
	 */
	private submitted = false

	/**
	 * @var {string} username User name
	 */
	private username = ''

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('required', required);
		this.$store.commit('spinner/HIDE');
	}

	/**
	 * Attempts to log in and retrieve features available for the user's role
	 */
	private handleSubmit(): void {
		const credentials = new UserCredentials(this.username, this.password);
		Promise.all([
			this.$store.dispatch('user/signIn', credentials),
			this.$store.dispatch('features/fetch'),
		])
			.then(async () => {
				await sleep(500);
				this.$router.push((this.$route.query.redirect as string|undefined) ?? '/');
				this.$toast.success(
					this.$t('core.sign.in.messages.success').toString()
				);
			})
			.catch(() => {
				this.$toast.error(
					this.$t('core.sign.in.messages.incorrectUsernameOrPassword')
						.toString()
				);
			});
		this.submitted = true;
	}
}
</script>
