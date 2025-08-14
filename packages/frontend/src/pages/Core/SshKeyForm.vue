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
	<div>
		<h1 v-if='!$route.path.includes("/install")'>
			{{ $t('core.security.ssh.add') }}
		</h1>
		<CCard>
			<CCardHeader v-if='$route.path.includes("/install")'>
				{{ $t('core.security.ssh.add') }}
			</CCardHeader>
			<CCardBody>
				<CElementCover
					v-if='running'
					:opacity='0.75'
					style='z-index: 10000;'
				>
					<CSpinner color='primary' />
				</CElementCover>
				<div
					v-if='$route.path.includes("/install")'
					class='form-group'
				>
					{{ $t('core.security.ssh.messages.installNote') }}
				</div>
				<SshKeyTypes ref='types' @fetch='sshValidation' />
				<ValidationObserver v-slot='{invalid}'>
					<CForm>
						<CRow
							v-for='(key, idx) of keys'
							:key='idx'
							form
						>
							<CCol sm='12' md='3'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: $t("core.security.ssh.errors.descriptionMissing")
									}'
								>
									<CInput
										v-model='key.description'
										:label='$t("core.security.ssh.form.description")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
									/>
								</ValidationProvider>
							</CCol>
							<CCol sm='12' md='9'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required|ssh'
									:custom-messages='{
										required: $t("core.security.ssh.errors.keyMissing"),
										ssh: $t("core.security.ssh.errors.keyInvalid"),
									}'
								>
									<CInput
										v-model='key.key'
										:label='$t("core.security.ssh.form.key")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
										@change='updateDescription(idx)'
									>
										<template #prepend-content>
											<span
												class='text-success'
												@click='addKey()'
											>
												<FontAwesomeIcon :icon='["far", "plus-square"]' size='xl' />
											</span>
										</template>
										<template #append-content>
											<span
												v-if='keys.length > 1'
												class='text-danger'
												@click='removeKey(idx)'
											>
												<FontAwesomeIcon :icon='["far", "trash-alt"]' size='lg' />
											</span>
										</template>
									</CInput>
								</ValidationProvider>
							</CCol>
							<hr>
						</CRow>
						<CButton
							color='primary'
							:disabled='invalid'
							@click='saveKeys'
						>
							{{ $t('forms.save') }}
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
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import SshKeyTypes from '@/components/Gateway/SshKeyTypes.vue';
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';
import {cilPlus} from '@coreui/icons';

import {extendedErrorToast} from '@/helpers/errorToast';
import {required} from 'vee-validate/dist/rules';

import SshService from '@/services/SshService';

import {AxiosError, AxiosResponse} from 'axios';
import {ISshInput} from '@/interfaces/Core/SshKey';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		FontAwesomeIcon,
		SshKeyTypes,
		ValidationObserver,
		ValidationProvider,
	},
	data: () => ({
		cilPlus,
	}),
	metaInfo: {
		title: 'core.security.ssh.add',
	},
})

/**
 *
 */
export default class SshKeyForm extends Vue {

	/**
	 * @var {Array<string>} keys Array of SSH keys for key-based authentication
	 */
	private keys: Array<ISshInput> = [
		{
			description: '',
			key: '',
		},
	];

	/**
	 * @var {boolean} running Indicates whether an axios request is being processed
	 */
	private running = false;

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
		this.keys.push({description: '', key: ''});
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
		if (this.$route.path.includes('/install')) {
			this.running = true;
			SshService.saveSshKeys(this.keys)
				.then((response: AxiosResponse) => {
					this.running = false;
					if (response.status === 200) {
						this.$toast.info(
							this.$t('core.security.ssh.messages.savePartialSuccess', {keys: response.data.failedKeys.join(', ')}).toString()
						);
					}
					this.nextStep();
				})
				.catch((error: AxiosError) => {
					extendedErrorToast(error, 'core.security.ssh.messages.saveFailed');
					this.running = false;
				});
		} else {
			this.$store.commit('spinner/SHOW');
			SshService.saveSshKeys(this.keys)
				.then((response: AxiosResponse) => {
					this.$store.commit('spinner/HIDE');
					if (response.status === 201) {
						this.$toast.success(this.$t('core.security.ssh.messages.saveSuccess').toString());
					} else if (response.status === 200) {
						this.$toast.info(
							this.$t('core.security.ssh.messages.savePartialSuccess', {keys: response.data.failedKeys.join(', ')}).toString()
						);
					}
					this.$router.push('/security/ssh-key/');
				})
				.catch((error: AxiosError) => {
					extendedErrorToast(error, 'core.security.ssh.messages.saveFailed');
				});
		}
	}

	/**
	 * Advance the install wizard
	 */
	private nextStep(): void {
		this.$emit('next-step');
		this.$router.push('/install/ssh-status/');
	}

	/**
	 * Updates the key description if it is empty
	 * @param index Key index
	 */
	private updateDescription(index: number): void {
		if (index >= this.keys.length || this.keys[index].description.length !== 0) {
			return;
		}
		this.keys[index].description = this.keys[index].key.split(' ').slice(2).join(' ');
	}

}
</script>
