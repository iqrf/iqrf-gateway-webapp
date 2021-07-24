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
	<CCard>
		<CCardHeader>
			{{ $t('gateway.password.title', {user: user}) }}
		</CCardHeader>
		<CCardBody>
			<CElementCover
				v-if='running'
				:opacity='0.75'
				style='z-index: 10000'
			>
				<CSpinner color='primary' />
			</CElementCover>
			<ValidationObserver v-slot='{invalid}'>
				<CForm>
					<ValidationProvider
						v-slot='{valid, touched, errors}'
						rules='required'
						:custom-messages='{
							required: "forms.errors.password"
						}'
					>
						<CInput
							v-model='password'
							:type='visibility'
							:label='$t("forms.fields.password")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						>
							<template #append-content>
								<span @click='visibility = (visibility === "password" ? "text" : "password")'>
									<FontAwesomeIcon
										:icon='(visibility === "password" ? ["far", "eye"] : ["far", "eye-slash"])'
									/>
								</span>
							</template>
						</CInput>
					</ValidationProvider>
					<CButton 
						color='primary'
						:disabled='invalid'
						@click='submitStep(true)'
					>
						{{ $t('forms.changePassword') }}
					</CButton> <CButton
						color='secondary'
						@click='submitStep(false)'
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
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput} from '@coreui/vue/src';
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import ServiceControl from '../../pages/Gateway/ServiceControl.vue';

import {extendedErrorToast} from '../../helpers/errorToast';
import {GatewayPasswordFeature} from '../../services/FeatureService';
import {required} from 'vee-validate/dist/rules';

import GatewayService from '../../services/GatewayService';

import {AxiosError} from 'axios';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		FontAwesomeIcon,
		ServiceControl,
		ValidationObserver,
		ValidationProvider,
	},
	metaInfo: {
		title: 'install.gwPassword.title'
	}
})

/**
 * Gateway user password change component
 */
export default class InstallGatewayUser extends Vue {
	
	/**
	 * @var {string} password Password
	 */
	private password = ''

	/**
	 * @var {string} visibility Form password field visibility type
	 */
	private visibility = 'password'

	/**
	 * @var {string} user Gateway user name
	 */
	private user = 'root'

	/**
	 * @var {bool} running Indicates whether axios requests are in progress
	 */
	private running = false

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
		this.updateUser();
	}

	/**
	 * Updates gateway user name from features
	 */
	private updateUser(): void {
		const feature: GatewayPasswordFeature|undefined = this.$store.getters['features/configuration']('gatewayPass');
		if (feature !== undefined) {
			this.user = feature.user;
		}
	}

	/**
	 * Advance the install wizard step
	 * @param {boolean} change Change gw user password?
	 */
	private submitStep(change: boolean): void {
		if (change) {
			this.running = true;
			GatewayService.setGatewayPassword({password: this.password})
				.then(() => {
					this.running = false;
					this.$emit('next-step');
				})
				.catch((error: AxiosError) => {
					extendedErrorToast(error, 'gateway.password.messages.failure', {user: this.user});
					this.running = false;
				});
		} else {
			this.$emit('next-step');
		}

	}
}
</script>
