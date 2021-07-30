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
				const credentials = new UserCredentials(this.username, this.password);
				this.$store.dispatch('user/signIn', credentials)
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
