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
		<h1 v-if='!$route.path.includes("/install")'>
			{{ $t('core.security.ssh.add') }}
		</h1>
		<v-card>
			<v-card-title v-if='$route.path.includes("/install")'>
				{{ $t('core.security.ssh.add') }}
			</v-card-title>
			<v-card-text>
				<v-overlay
					v-if='running'
					:opacity='0.65'
					absolute
				>
					<v-progress-circular color='primary' indeterminate />
				</v-overlay>
				<div v-if='$route.path.includes("/install")'>
					{{ $t('core.security.ssh.messages.installNote') }}
				</div>
				<SshKeyTypes ref='types' @fetch='sshValidation' />
				<ValidationObserver v-slot='{invalid}'>
					<v-form @submit.prevent='saveKeys'>
						<div
							v-for='(key, idx) of keys'
							:key='idx'
						>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required'
								:custom-messages='{
									required: $t("core.security.ssh.errors.descriptionMissing")
								}'
							>
								<v-text-field
									v-model='key.description'
									:label='$t("core.security.ssh.form.description")'
									:success='touched ? valid : null'
									:error-messages='errors'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required|ssh'
								:custom-messages='{
									required: $t("core.security.ssh.errors.keyMissing"),
									ssh: $t("core.security.ssh.errors.keyInvalid"),
								}'
							>
								<v-text-field
									v-model='key.key'
									:label='$t("core.security.ssh.form.key")'
									:success='touched ? valid : null'
									:error-messages='errors'
									@change='updateDescription(idx)'
								>
									<template #append-outer>
										<v-btn
											v-if='idx === 0'
											color='success'
											small
											@click='addKey'
										>
											<v-icon>
												mdi-plus
											</v-icon>
										</v-btn>
										<v-btn
											v-else
											color='error'
											small
											@click='removeKey(idx)'
										>
											<v-icon>
												mdi-delete-outline
											</v-icon>
										</v-btn>
									</template>
								</v-text-field>
							</ValidationProvider>
						</div>
						<v-btn
							color='primary'
							:disabled='invalid'
							type='submit'
						>
							{{ $t('forms.save') }}
						</v-btn> <v-btn
							v-if='$route.path.includes("/install")'
							color='secondary'
							@click='nextStep'
						>
							{{ $t('forms.skip') }}
						</v-btn>
					</v-form>
				</ValidationObserver>
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import SshKeyTypes from '@/components/Gateway/SshKeyTypes.vue';

import {extendedErrorToast} from '@/helpers/errorToast';
import {required} from 'vee-validate/dist/rules';

import SshService from '@/services/SshService';

import {AxiosError, AxiosResponse} from 'axios';
import {ISshInput} from '@/interfaces/ssh';

@Component({
	components: {
		SshKeyTypes,
		ValidationObserver,
		ValidationProvider,
	},
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
