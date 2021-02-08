<template>
	<div>
		<CCard>
			<CCardHeader>{{ $t('install.createUser.title') }}</CCardHeader>
			<CCardBody>
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
						<CButton color='primary' type='submit' :disabled='invalid'>
							{{ $t('forms.add') }}
						</CButton>
					</CForm>
				</ValidationObserver>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {AxiosError} from 'axios';
import {CButton, CCard, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import UserService from '../../services/UserService';
import {UserCredentials} from '../../services/AuthenticationService';
import {sleep} from '../../helpers/sleep';

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
				Promise.all([
					this.$store.dispatch('user/signIn', credentials),
					this.$store.dispatch('features/fetch'),
				])
					.then(async () => {
						await sleep(500);
						this.$router.push('/');
						this.$toast.success(
							this.$t('core.user.messages.addSuccess', {username: this.username})
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
