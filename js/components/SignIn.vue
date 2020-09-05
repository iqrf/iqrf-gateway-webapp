<template>
	<div class='c-app flex-row align-items-center'>
		<CContainer>
			<CRow class='justify-content-center'>
				<CCol md='8'>
					<div class='py-5 text-center'>
						<img :alt='$t("core.title")' class='mx-auto logo' src='/img/logo-blue.svg'>
					</div>
					<CCard class='p-4'>
						<CCardBody>
							<ValidationObserver v-slot='{ invalid }'>
								<CForm @submit.prevent='handleSubmit'>
									<h1 class='text-center'>
										{{ $t('core.sign.inForm.title') }}
									</h1>
									<ValidationProvider
										v-slot='{ valid, touched, errors }'
										rules='required'
										:custom-messages='{required: "core.sign.inForm.messages.username"}'
									>
										<CInput
											v-model='username'
											:label='$t("core.sign.inForm.username")'
											:placeholder='$t("core.sign.inForm.username")'
											autocomplete='username'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										>
											<template #prepend-content>
												<CIcon :content='$options.icons.user' />
											</template>
										</CInput>
									</ValidationProvider>
									<ValidationProvider
										v-slot='{ valid, touched, errors }'
										rules='required'
										:custom-messages='{required: "core.sign.inForm.messages.password"}'
									>
										<CInput
											v-model='password'
											:label='$t("core.sign.inForm.password")'
											:placeholder='$t("core.sign.inForm.password")'
											type='password'
											autocomplete='password'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										>
											<template #prepend-content>
												<CIcon :content='$options.icons.lock' />
											</template>
										</CInput>
									</ValidationProvider>
									<CButton color='primary' type='submit' :disabled='invalid'>
										{{ $t('core.sign.inForm.send') }}
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

<script>
import {CContainer, CCard, CCardBody, CCol, CForm, CIcon, CInput, CRow} from '@coreui/vue';
import {cilUser, cilLockLocked} from '@coreui/icons';
import AuthenticationService from '../services/AuthenticationService';
import {required} from 'vee-validate/dist/rules';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

export default {
	name: 'SignIn',
	components: {
		CContainer,
		CCard,
		CCardBody,
		CCol,
		CForm,
		CIcon,
		CInput,
		CRow,
		ValidationObserver,
		ValidationProvider,
	},
	data() {
		return {
			username: '',
			password: '',
			submitted: false,
		};
	},
	created() {
		extend('required', required);
	},
	methods: {
		handleSubmit() {
			AuthenticationService.login(this.username, this.password)
				.then(() => {
					this.$router.push('/');
					this.$toast.success(this.$t('core.sign.inForm.messages.success'));
				})
				.catch((reason) => (console.error(reason)));
			this.submitted = true;
		}
	},
	icons: {
		user: cilUser,
		lock: cilLockLocked,
	},
};
</script>

<style scoped>
.logo {
	min-width: 320px;
}
</style>
