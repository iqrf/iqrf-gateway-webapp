<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

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
	<CCard>
		<CCardHeader>
			{{ $t('gateway.user.title', {user: user}) }}
		</CCardHeader>
		<CCardBody>
			<CElementCover
				v-if='running'
				:opacity='0.75'
				style='z-index: 10000;'
			>
				<CSpinner color='primary' />
			</CElementCover>
			<ValidationObserver v-slot='{invalid}'>
				<CForm>
					<div
						v-if='$route.path.includes("/install")'
						class='form-group'
					>
						{{ $t('install.gwUser.note') }}
					</div>
					<div class='form-group'>
						<strong>{{ $t('gateway.user.user') }}</strong>{{ user }}
					</div>
					<ValidationProvider
						v-slot='{valid, touched, errors}'
						rules='required'
						:custom-messages='{
							required: $t("forms.errors.password"),
						}'
					>
						<PasswordInput
							v-model='password'
							:label='$t("forms.fields.password").toString()'
							:is-valid='touched ? valid : null'
							:invalid-feedback='errors.join(", ")'
						/>
					</ValidationProvider>
					<CButton
						color='primary'
						:disabled='invalid'
						@click='changePassword'
					>
						{{ $t('forms.changePassword') }}
					</CButton> <CButton
						v-if='$route.path.includes("/install")'
						color='secondary'
						@click='nextStep'
					>
						{{ $t('forms.skip') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CElementCover, CForm, CInput, CSpinner} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {required} from 'vee-validate/dist/rules';
import GatewayService from '@/services/GatewayService';

import {AxiosError} from 'axios';
import {GatewayPasswordFeature} from '@/services/FeatureService';
import {extendedErrorToast} from '@/helpers/errorToast';
import {MetaInfo} from 'vue-meta';

import PasswordInput from '@/components/Core/PasswordInput.vue';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CElementCover,
		CForm,
		CInput,
		CSpinner,
		PasswordInput,
		ValidationObserver,
		ValidationProvider,
	},
	metaInfo(): MetaInfo {
		return {
			title: (this as GatewayUserPassword).pageTitle
		};
	}
})

/**
 * Gateway user password change component
 */
export default class GatewayUserPassword extends Vue {

	/**
	 * @var {string} password Password
	 */
	private password = '';

	/**
	 * @var {string} user Gateway user name
	 */
	private user = 'root';

	/**
	 * @var {bool} running Indicates whether axios requests are in progress
	 */
	private running = false;

	/**
	 * Computes page title depending on the path
	 * @returns {string} Page title
	 */
	get pageTitle(): string {
		return this.$route.path.includes('/install') ?
			this.$t('gateway.user.title').toString(): this.$t('service.ssh.title').toString();
	}

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
		this.updateUser();
	}

	/**
	 * Updates gateway username from features
	 */
	private updateUser(): void {
		const feature: GatewayPasswordFeature|undefined = this.$store.getters['features/configuration']('gatewayPass');
		if (feature !== undefined) {
			this.user = feature.user;
		}
	}

	/**
	 * Sets new gateway root account password
	 */
	private changePassword(): void {
		if (this.$route.path.includes('/install')) {
			this.running = true;
			GatewayService.setGatewayPassword({password: this.password})
				.then(() => {
					this.running = false;
					this.nextStep();
				})
				.catch((error: AxiosError) => {
					extendedErrorToast(error, 'gateway.password.messages.failure', {user: this.user});
					this.running = false;
				});
		} else {
			this.$store.commit('spinner/SHOW');
			GatewayService.setGatewayPassword({password: this.password})
				.then(() => {
					this.$store.commit('spinner/HIDE');
					this.$toast.success(
						this.$t(
							'gateway.user.messages.success',
							{user: this.user},
						).toString()
					);
				})
				.catch((error: AxiosError) => extendedErrorToast(
					error,
					'gateway.user.messages.failure',
					{user: this.user}
				));
		}
	}

	/**
	 * Advances the installation wizard
	 */
	private nextStep(): void {
		if (this.$store.getters['features/isEnabled']('ssh')) {
			this.$emit('next-step');
			this.$router.push('/install/ssh-keys/');
		} else {
			this.$router.push('/');
			this.$toast.success(
				this.$t('install.messages.finished').toString()
			);
		}
	}
}
</script>
