<template>
	<CCard body-wrapper>
		<ValidationObserver v-if='loaded' v-slot='{ invalid }'>
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
				<div v-if='$store.getters["user/getId"] === userId'>
					<ValidationProvider
						v-slot='{ valid, touched, errors }'
						:rules='newPassword !== null ? "required" : ""'
						:custom-messages='{
							required: "core.user.messages.missing.oldPassword",
						}'
					>
						<CInput
							v-model='oldPassword'
							:label='$t("core.user.oldPassword")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
							type='password'
							autocomplete='current-password'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ valid, touched, errors }'
						:rules='oldPassword !== null ? "required" : ""'
						:custom-messages='{
							required: "core.user.messages.missing.newPassword",
						}'
					>
						<CInput
							v-model='newPassword'
							:label='$t("core.user.newPassword")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
							type='password'
							autocomplete='new-password'
						/>
					</ValidationProvider>
				</div>
				<CButton color='primary' type='submit' :disabled='invalid'>
					{{ $t('forms.save') }}
				</CButton>
			</CForm>
		</ValidationObserver>
	</CCard>
</template>

<script>
import {CButton, CCard, CForm, CInput, CSelect} from '@coreui/vue';
import {required,} from 'vee-validate/dist/rules';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import UserService from '../../services/UserService';

export default {
	name: 'UserEdit',
	components: {
		CButton,
		CCard,
		CForm,
		CInput,
		CSelect,
		ValidationObserver,
		ValidationProvider,
	},
	props: {
		userId: {
			type: Number,
			required: true,
		}
	},
	data() {
		return {
			loaded: false,
			username: null,
			language: null,
			role: null,
			oldPassword: null,
			newPassword: null,
		};
	},
	created() {
		extend('required', required);
		this.$store.commit('spinner/SHOW');
		UserService.get(this.userId)
			.then((response) => {
				this.loaded = true;
				this.username = response.data.username;
				this.language = response.data.language;
				this.role = response.data.role;
				this.$store.commit('spinner/HIDE');
			})
			.catch((error) => {
				this.loaded = false;
				this.$store.commit('spinner/HIDE');
				if (error.response.status === 404) {
					this.$router.push('/user/');
					this.$toast.error(this.$t('core.user.messages.notFound'));
				}
			});
	},
	methods: {
		handleSubmit() {
			if (this.$store.getters['user/getId'] === this.userId &&
					this.oldPassword !== null && this.newPassword !== null) {
				UserService.changePassword(this.oldPassword, this.newPassword)
					.then(() => {
						this.performEdit();
						this.$store.commit('user/SIGN_OUT');
					})
					.catch(() => {
						this.$toast.error(this.$t('core.user.messages.invalid.oldPassword'));
					});
			} else {
				this.performEdit();
				if (this.$store.getters['user/getId'] === this.userId) {
					this.$store.commit('user/SIGN_OUT');
				}
			}

		},
		performEdit() {
			return UserService.edit(this.userId, {
				username: this.username,
				language: this.language,
				role: this.role,
			})
				.then(() => {
					this.$router.push('/user/');
					this.$toast.success(this.$t('core.user.messages.edit.success', {username: this.username}));
				})
				.catch((error) => {
					if (error.response.status === 409) {
						this.$toast.error(this.$t('core.user.messages.conflict.username'));
					}
				});
		}
	},
};
</script>
