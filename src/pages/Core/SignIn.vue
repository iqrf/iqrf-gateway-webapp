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
	<TheWizard>
		<CCard class='p-4'>
			<h1 class='text-center'>
				{{ $t('core.sign.in.title') }}
			</h1>
			<CCardBody>
				<ValidationObserver v-slot='{ invalid }'>
					<CForm @submit.prevent='handleSubmit'>
						<ValidationProvider
							v-slot='{ valid, touched, errors }'
							rules='required'
							:custom-messages='{
								required: $t("core.sign.in.messages.username"),
							}'
						>
							<CInput
								id='username'
								v-model='username'
								:label='$t("forms.fields.username")'
								:placeholder='$t("forms.fields.username")'
								autocomplete='username'
								:is-valid='touched ? valid : null'
								:invalid-feedback='errors.join(", ")'
							>
								<template #prepend-content>
									<CIcon :content='cilUser' />
								</template>
							</CInput>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ valid, touched, errors }'
							rules='required'
							:custom-messages='{
								required: $t("core.sign.in.messages.password")
							}'
						>
							<CInput
								id='password'
								v-model='password'
								:label='$t("forms.fields.password")'
								:placeholder='$t("forms.fields.password")'
								type='password'
								autocomplete='password'
								:is-valid='touched ? valid : null'
								:invalid-feedback='errors.join(", ")'
							>
								<template #prepend-content>
									<CIcon :content='cilLockLocked' />
								</template>
							</CInput>
						</ValidationProvider>
						<div style='display: flex; justify-content: space-between;'>
							<CButton color='primary' type='submit' :disabled='invalid'>
								{{ $t('core.sign.in.send') }}
							</CButton>
							<CLink
								to='/account/recovery'
							>
								{{ $t('core.sign.in.recoverPassword') }}
							</CLink>
						</div>
					</CForm>
				</ValidationObserver>
			</CCardBody>
		</CCard>
	</TheWizard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CContainer, CCard, CCardBody, CCol, CForm, CIcon, CInput, CLink, CRow} from '@coreui/vue/src';
import {cilUser, cilLockLocked} from '@coreui/icons';
import {required} from 'vee-validate/dist/rules';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {UserCredentials} from '@/services/AuthenticationService';
import {sleep} from '@/helpers/sleep';
import TheWizard from '@/components/TheWizard.vue';

@Component({
	components: {
		CContainer,
		CCard,
		CCardBody,
		CCol,
		CForm,
		CIcon,
		CInput,
		CLink,
		CRow,
		TheWizard,
		ValidationObserver,
		ValidationProvider,
	},
	data: () => ({
		cilLockLocked,
		cilUser,
	}),
	metaInfo: {
		title: 'core.sign.in.title',
	},
})

/**
 * Sign in page component
 */
export default class SignIn extends Vue {
	/**
	 * @var {string} password User password
	 */
	private password = '';

	/**
	 * @var {boolean} submitted Indicates whether the login information have been submitted
	 */
	private submitted = false;

	/**
	 * @var {string} username User name
	 */
	private username = '';

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
		this.$store.dispatch('user/signIn', credentials)
			.then(async () => {
				await sleep(500);
				let destination = (this.$route.query.redirect as string|undefined) ?? '/';
				if (destination.startsWith('/sign/in')) {
					destination = '/';
				}
				await this.$router.push(destination);
				this.$toast.success(
					this.$t('core.sign.in.messages.success').toString()
				);
				await this.$store.dispatch('repository/get');
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
