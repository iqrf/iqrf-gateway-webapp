<template>
	<CCardBody>
		<h1>{{ $t('install.createUser.title') }}</h1>
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
</template>

<script lang='ts'>
import Vue from 'vue';
import {AxiosError} from 'axios';
import {CButton, CCardBody, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import UserService from '../../services/UserService';

export default Vue.extend({
	name: 'InstallCreateUser',
	components: {
		CButton,
		CCardBody,
		CForm,
		CInput,
		ValidationObserver,
		ValidationProvider,
	},
	data(): any {
		return {
			username: null,
			password: null,
			language: 'en',
			role: 'normal',
		};
	},
	created() {
		extend('required', required);
	},
	methods: {
		handleSubmit() {
			UserService.add(this.username, this.password, this.language, this.role)
				.then(() => {
					this.$router.push('/sign/in/');
					this.$toast.success(
						this.$t('core.user.messages.add.success', {username: this.username})
							.toString());
				}).catch((error: AxiosError) => {
					if (error.response === undefined) {
						return;
					}
				});
		},
	},
	metaInfo: {
		title: 'install.createUser.title',
	},
});
</script>
