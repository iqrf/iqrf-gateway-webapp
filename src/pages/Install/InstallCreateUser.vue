<template>
	<CCard>
		<CCardHeader>{{ $t('install.createUser.title') }}</CCardHeader>
		<CCardBody>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm @submit.prevent='handleSubmit'>
					<ValidationProvider
						v-slot='{ valid, touched, errors }'
						rules='required'
						:custom-messages='{
							required: "core.user.messages.missing.username",
						}'
					>
						<CInput
							v-model='username'
							:label='$t("core.user.username")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ valid, touched, errors }'
						rules='required'
						:custom-messages='{
							required: "core.user.messages.missing.password",
						}'
					>
						<CInput
							v-model='password'
							:label='$t("core.user.password")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
							type='password'
						/>
					</ValidationProvider>
					<CButton color='primary' type='submit' :disabled='invalid'>
						{{ $t('forms.add') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {AxiosError} from 'axios';
import {CButton, CCard, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import UserService from '../../services/UserService';
import {UserCredentials} from '../../services/AuthenticationService';

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
	 * On component creation event handler
	 */
	public created(): void {
		extend('required', required);
	}

	/**
	 * Handle form submit
	 */
	private handleSubmit(): void {
		UserService.add(this.username, this.password, this.language, this.role)
			.then(() => {
				const credentials: UserCredentials = new UserCredentials(this.username, this.password);
				this.$store.dispatch('user/signIn', credentials)
					.then(() => {
						this.$router.push('/');
						this.$toast.success(
							this.$t('core.user.messages.add.success', {username: this.username})
								.toString());
					});
			}).catch((error: AxiosError) => {
				if (error.response === undefined) {
					return;
				}
			});
	}
}
</script>
