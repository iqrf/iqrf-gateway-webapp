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
		<CCardHeader>{{ $t('install.ssh.keys.title') }}</CCardHeader>
		<CCardBody>
			<CElementCover
				v-if='running'
				:opacity='0.75'
				style='z-index: 10000'
			>
				<CSpinner color='primary' />
			</CElementCover>
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
								required: "install.ssh.keys.errors.keyMissing",
								ssh: "install.ssh.keys.errors.keyInvalid"
							}'
						>
							<CInput
								v-model='keys[idx]'
								:label='$t("install.ssh.keys.key")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<CButton
							v-if='keys.length > 1'
							color='danger'
							@click='removeKey(idx)'
						>
							{{ $t('install.ssh.keys.remove') }}
						</CButton> <CButton
							v-if='idx === (keys.length - 1)'
							color='success'
							@click='addKey(idx - 1)'
						>
							{{ $t('install.ssh.keys.add') }}
						</CButton>
					</div>
					<CButton
						color='primary'
						:disabled='invalid'
						@click='submitStep(true)'
					>
						{{ $t('forms.save') }}
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
		title: 'install.ssh.keys.title'
	}
})

export default class InstallSshKeys extends Vue {

	/**
	 * @var {Array<string>} keyTypes
	 */
	private keyTypes: Array<string> = []

	/**
	 * @var {Array<string>} keys Array of SSH keys for key-based authentication
	 */
	private keys: Array<string> = ['']

	/**
	 * @var {bool} running Indicates whether axios requests are in progress
	 */
	private running = false

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
	}

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
	private submitStep(useKeys: boolean): void {
		if (useKeys) {
			this.running = true;
			SshService.saveSshKeys(this.keys)
				.then(() => {
					this.running = false;
					this.$emit('next-step');
				})
				.catch((error: AxiosError) => {
					extendedErrorToast(error, 'install.ssh.keys.messages.sshKeyError');
					this.running = false;
				});
		} else {
			this.$emit('next-step');
		}
	}
}
</script>
