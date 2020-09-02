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
							<CForm @submit.prevent='handleSubmit'>
								<h1 class='text-center'>
									{{ $t('core.sign.inForm.title') }}
								</h1>
								<CInput v-model='username' :label='$t("core.sign.inForm.username")' :placeholder='$t("core.sign.inForm.username")' autocomplete='username'>
									<template #prepend-content>
										<CIcon :content='$options.icons.user' />
									</template>
								</CInput>
								<CInput v-model='password' :label='$t("core.sign.inForm.password")' :placeholder='$t("core.sign.inForm.password")' type='password' autocomplete='password'>
									<template #prepend-content>
										<CIcon :content='$options.icons.lock' />
									</template>
								</CInput>
								<CButton color='primary' class='px-4' type='submit'>
									{{ $t('core.sign.inForm.send') }}
								</CButton>
							</CForm>
						</CCardBody>
					</CCard>
				</CCol>
			</CRow>
		</CContainer>
	</div>
</template>

<script>
import {CContainer, CCard, CCardBody, CCol, CForm, CIcon, CInput, CRow} from '@coreui/vue';
import { cilUser, cilLockLocked } from '@coreui/icons';
import AuthenticationService from '../services/AuthenticationService';
//import {ValidationObserver, ValidationProvider} from 'vee-validate';

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
		//ValidationObserver,
		//ValidationProvider,
	},
	data() {
		return {
			username: '',
			password: '',
			submitted: false,
		};
	},
	methods: {
		handleSubmit() {
			AuthenticationService.login(this.username, this.password)
				.then(() => {
					//this.$router.push('/');
					location.replace('/');
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
