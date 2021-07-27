<template>
	<div>
		<h1>{{ $t('core.ssh.edit') }}</h1>
		<CCard>
			<CCardBody>
				<ValidationObserver v-slot='{invalid}'>
					<CForm @submit.prevent='saveKey'>
						<SshKeyTypes ref='types' @fetch='sshValidation' />
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required|ssh'
							:custom-messages='{
								required: "core.ssh.errors.keyMissing",
								ssh: "core.ssh.errors.keyInvalid"
							}'
						>
							<CInput
								v-model='key'
								:label='$t("core.ssh.form.key")'
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
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import SshKeyTypes from '../../components/Gateway/SshKeyTypes.vue';

import SshService from '../../services/SshService';

import {extendedErrorToast} from '../../helpers/errorToast';
import {required} from 'vee-validate/dist/rules';

import {AxiosError, AxiosResponse} from 'axios';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CForm,
		CInput,
		SshKeyTypes,
		ValidationObserver,
		ValidationProvider,
	},
	metaInfo: {
		title: 'core.ssh.edit',
	},
})

/**
 * SSH key edit component
 */
export default class SshKeyEdit extends Vue {

	/**
	 * @property {number} keyId SSH public key ID
	 */
	@Prop({required: true}) keyId!: number

	/**
	 * @var {string} key SSH public key//
	 */
	private key = ''

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
	}

	/**
	 * Initializes SSH key validation rule
	 */
	private sshValidation(): void {
		extend('ssh', (key: string) => {
			return (this.$refs.types as SshKeyTypes).validateKey(key);
		});
	}

	/**
	 * Retrieves SSH public key
	 */
	mounted(): void {
		SshService.getKey(this.keyId)
			.then((response: AxiosResponse) => this.key = response.data)
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'core.ssh.messages.fetchFailed', {id: this.keyId});
				this.$router.push('/ssh-key/');
			});
	}

	private saveKey(): void {
		// edit key axios request
	}
}
</script>
