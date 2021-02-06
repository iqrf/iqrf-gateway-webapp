<template>
	<CCard>
		<CCardHeader>
			{{ $t('install.rootPass.title') }}
			<CButton
				style='float: right;'
				color='primary'
				type='submit'
				size='sm'
				to='/install/user'
			>
				{{ $t('forms.skip') }}
			</CButton>
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
							:label='$t("forms.fields.password")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
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
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';

import {NavigationGuardNext, Route} from 'vue-router/types/router';
import InstallationService from '../../services/InstallationService';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		ValidationObserver,
		ValidationProvider,
	},
	beforeRouteEnter(to: Route, from: Route, next: NavigationGuardNext): void {
		next((vm: Vue) => {
			if (!vm.$store.getters['features/isEnabled']('rootpass')) {
				vm.$router.push('/install/user');
			}
		});
	},
	metaInfo: {
		title: 'install.rootPass.title'
	}
})

/**
 * Gateway root account password change component
 */
export default class InstallRootPass extends Vue {
	
	/**
	 * @var {string} password Root password
	 */
	private password = ''

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
		InstallationService.setRootPass({password: this.password})
			.then(() => {
				this.$toast.success(
					this.$t('install.rootPass.messages.success').toString()
				);
				this.$router.push('/install/user');
			})
			.catch(() => {
				this.$toast.error(
					this.$t('install.rootPass.messages.failure').toString()
				);
			});
	}
}
</script>
