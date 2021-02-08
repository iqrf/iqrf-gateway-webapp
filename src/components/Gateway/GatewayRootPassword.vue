<template>
	<CCard>
		<CCardHeader>
			{{ $t('gateway.rootPass.title') }}
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
									<CIcon :content='(visibility === "password" ? icons.show : icons.hide)' />
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
import {cilLockLocked, cilLockUnlocked} from '@coreui/icons';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import ServiceControl from '../../pages/Gateway/ServiceControl.vue';

import GatewayService from '../../services/GatewayService';

import {Dictionary} from 'vue-router/types/router';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		ServiceControl,
		ValidationObserver,
		ValidationProvider,
	},
})

/**
 * Gateway root password change component
 */
export default class GatewayRootPassword extends Vue {
	
	/**
	 * @var {string} password Root password
	 */
	private password = ''

	/**
	 * @var {string} visibility Form password field visibility type
	 */
	private visibility = 'password'

	/**
	 * @constant {Dictionary<Array<string>>} icons Dictionary of CoreUI icons
	 */
	private icons: Dictionary<Array<string>> = {
		hide: cilLockLocked,
		show: cilLockUnlocked
	}

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
		GatewayService.setRootPass({password: this.password})
			.then(() => {
				this.$toast.success(
					this.$t('gateway.rootPass.messages.success').toString()
				);
			})
			.catch(() => {
				this.$toast.error(
					this.$t('gateway.rootPass.messages.failure').toString()
				);
			});
	}
}
</script>
