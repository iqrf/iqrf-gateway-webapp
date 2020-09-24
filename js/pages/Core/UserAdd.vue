<template>
	<CCard body-wrapper>
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
				<CSelect
					v-if='$store.getters["user/getRole"] === "power"'
					:value.sync='role'
					:label='$t("core.user.role")'
					:options='[
						{value: "normal", label: $t("core.user.roles.normal")},
						{value: "power", label: $t("core.user.roles.power")},
					]'
				/>
				<CSelect
					v-if='$store.getters["user/getRole"] === "power"'
					:value.sync='language'
					:label='$t("core.user.language")'
					:options='[
						{value: "en", label: $t("core.user.languages.en")},
					]'
				/>
				<CButton color='primary' type='submit' :disabled='invalid'>
					{{ $t('forms.add') }}
				</CButton>
			</CForm>
		</ValidationObserver>
	</CCard>
</template>

<script>
import {CButton, CCard, CForm, CInput, CSelect} from '@coreui/vue/src';
import {required,} from 'vee-validate/dist/rules';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import UserService from '../../services/UserService';

export default {
	name: 'UserAdd',
	components: {
		CButton,
		CCard,
		CForm,
		CInput,
		CSelect,
		ValidationObserver,
		ValidationProvider,
	},
	data() {
		return {
			username: null,
			password: null,
			language: null,
			role: null,
		};
	},
	created() {
		extend('required', required);
	},
	methods: {
		handleSubmit() {
			UserService.add(this.username, this.password, this.language ?? 'en', this.role ?? 'normal')
				.then(() => {
					this.$router.push('/user/');
					this.$toast.success(this.$t('core.user.messages.add.success', {username: this.username}).toString());
				}).catch((error) => {
					if (error.response.status === 409) {
						this.$toast.error(this.$t('core.user.messages.conflict.username').toString());
					}
				});
		},
	},
	metaInfo: {
		title: 'core.user.add.title',
	},
};
</script>
