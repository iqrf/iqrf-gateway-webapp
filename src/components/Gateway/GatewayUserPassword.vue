<template>
	<CCard>
		<CCardHeader>
			{{ $t('gateway.password.title') }}
		</CCardHeader>
		<CCardBody>
			<ValidationObserver v-slot='{invalid}'>
				<CForm @submit.prevent='handleSubmit'>
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
						type='submit'
						:disabled='invalid'
					>
						{{ $t('forms.save') }}
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
})

/**
 * Gateway user password change component
 */
export default class GatewayUserPassword extends Vue {
	
	/**
	 * @var {string} password Password
	 */
	private password = ''

	/**
	 * @var {string} visibility Form password field visibility type
	 */
	private visibility = 'password'

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
	}

	/**
	 * Sets new gateway root account password
	 */
	private handleSubmit(): void {
		GatewayService.setGatewayPassword({password: this.password})
			.then(() => {
				this.$toast.success(
					this.$t('gateway.password.messages.success').toString()
				);
			})
			.catch((error: AxiosError) => {
				this.$toast.error(
					this.$t(
						'gateway.password.messages.failure',
						{error: error.response !== undefined ? error.response.data.message : error.message}
					).toString()
				);
			});
	}
}
</script>
