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
		<h1>{{ $t('core.ssh.add') }}</h1>
		<CCard>
			<CCardBody>
				<SshKeyTypes ref='types' @fetch='sshValidation' />
				<ValidationObserver v-slot='{invalid}'>
					<CForm>
						<div
							v-for='(k, idx) of keys'
							:key='idx'
							class='form-group'
						>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required|ssh'
								:custom-messages='{
									required: "core.ssh.errors.keyMissing",
									ssh: "core.ssh.errors.keyInvalid"
								}'
							>
								<CInput
									v-model='keys[idx]'
									:label='$t("core.ssh.form.key")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<CButton
								v-if='keys.length > 1'
								color='danger'
								@click='removeKey(idx)'
							>
								{{ $t('core.ssh.form.delete') }}
							</CButton> <CButton
								v-if='idx === (keys.length - 1)'
								color='success'
								@click='addKey(idx - 1)'
							>
								{{ $t('core.ssh.form.add') }}
							</CButton>
						</div>
						<CButton
							color='primary'
							:disabled='invalid'
							@click='saveKeys'
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
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import SshKeyTypes from '../../components/Gateway/SshKeyTypes.vue';

import {extendedErrorToast} from '../../helpers/errorToast';
import {required} from 'vee-validate/dist/rules';

import SshService from '../../services/SshService';

import {AxiosError} from 'axios';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		SshKeyTypes,
		ValidationObserver,
		ValidationProvider,
	},
	metaInfo: {
		title: 'core.ssh.add',
	},
})

/**
 * 
 */
export default class SshKeyAdd extends Vue {

	/**
	 * @var {Array<string>} keyTypes
	 */
	private keyTypes: Array<string> = []

	/**
	 * @var {Array<string>} keys Array of SSH keys for key-based authentication
	 */
	private keys: Array<string> = ['']

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
	}

	/**
	 * Initializes SSH validation rule
	 */
	private sshValidation(): void {
		extend('ssh', (key: string) => {
			return (this.$refs.types as SshKeyTypes).validateKey(key);
		});
	}

	/**
	 * Inserts new SSH key
	 */
	private addKey(): void {
		this.keys.push('');
	}

	/**
	 * Removes SSH key
	 */
	private removeKey(index: number): void {
		this.keys.splice(index, 1);
	}

	/**
	 * Advances the install wizard step
	 */
	private saveKeys(): void {
		this.$store.commit('spinner/SHOW');
		SshService.saveSshKeys(this.keys)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(this.$t('core.ssh.messages.saveSuccess').toString());
				this.$router.push('/ssh-key/');
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'core.ssh.messages.saveFailed');
			});
	}
}
</script>
