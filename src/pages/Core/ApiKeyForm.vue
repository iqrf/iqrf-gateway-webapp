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
		<h1 v-if='$route.path === "/api-key/add"'>
			{{ $t('core.apiKey.add') }}
		</h1>
		<h1 v-else>
			{{ $t('core.apiKey.edit') }}
		</h1>
		<CCard>
			<CCardBody>
				<ValidationObserver v-slot='{ invalid }'>
					<CForm @submit.prevent='saveKey'>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required'
							:custom-messages='{required: "core.apiKey.errors.description"}'
						>
							<CInput
								v-model='metadata.description'
								:label='$t("core.apiKey.form.description")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<div class='form-group'>
							<CInputCheckbox
								:checked.sync='useExpiration'
								:label='$t("core.apiKey.form.expiration")'
								@change='clear'
							/>
							<Datetime
								v-if='useExpiration'
								v-model='metadata.expiration'
								type='datetime'
								:format='dateFormat'
								:min-datetime='new Date().toISOString()'
								input-class='form-control'
								:disabled='!useExpiration'
							/>
						</div>
						<CButton
							type='submit'
							color='primary'
							:disabled='invalid'
						>
							{{ submitButton }}
						</CButton>
					</CForm>
				</ValidationObserver>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CForm, CInput, CInputCheckbox} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import ApiKeyService from '../../services/ApiKeyService';
import {Datetime} from 'vue-datetime';
import {extendedErrorToast} from '../../helpers/errorToast';
import {required} from 'vee-validate/dist/rules';

import {AxiosError, AxiosResponse} from 'axios';
import {Dictionary} from 'vue-router/types/router';
import {MetaInfo} from 'vue-meta';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CForm,
		CInput,
		CInputCheckbox,
		Datetime,
		ValidationObserver,
		ValidationProvider,
	},
	metaInfo(): MetaInfo {
		return {
			title: (this as unknown as ApiKeyForm).pageTitle
		};
	}
})

/**
 * API key manager form to add or edit API key
 */
export default class ApiKeyForm extends Vue {
	/**
	 * @constant {Dictionary<string|boolean} dateFormat Date formatting options
	 */
	private dateFormat: Dictionary<string|boolean> = {
		year: 'numeric',
		month: 'short',
		day: 'numeric',
		hour12: false,
		hour: 'numeric',
		minute: 'numeric',
		second: 'numeric',
	}

	/**
	 * @var {Dictionary<string>} metadata API key metadata
	 */
	private metadata: Dictionary<string|null> = {
		description: '',
		expiration: null
	}

	/**
	 * @var {boolean} useExpiration Controls whether form expiration input is hidden or shown
	 */
	private useExpiration = false

	/**
	 * @property {number} keyId API key id
	 */
	@Prop({required: false, default: null}) keyId!: number

	/**
	 * Computes page title depending on the action (add, edit)
	 * @returns {string} Page title
	 */
	get pageTitle(): string {
		return this.$route.path === '/api-key/add' ?
			this.$t('core.apiKey.add').toString() : this.$t('core.apiKey.edit').toString();
	}

	/**
	 * Computes submit button text depending on the action (add, edit)
	 * @return {string} Button text
	 */
	get submitButton(): string {
		return this.$route.path === '/api-key/add' ?
			this.$t('forms.add').toString() : this.$t('forms.edit').toString();
	}

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('required', required);
		if (this.keyId) {
			this.getKey();
		}
	}

	/**
	 * Clears the expiration field value if it is hidden
	 */
	private clear(): void {
		if (!this.useExpiration) {
			this.metadata.expiration = null;
		}
	}

	/**
	 * Retrieves API key specified by id
	 */
	private getKey(): void {
		if (this.keyId === null) {
			return;
		}
		this.$store.commit('spinner/SHOW');
		ApiKeyService.getApiKey(this.keyId)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.metadata = response.data;
				if (this.metadata.expiration !== null) {
					this.useExpiration = true;
				}
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'core.apiKey.messages.fetchFailed', {key: this.keyId});
				this.$router.push('/api-key/');
			});
	}

	/**
	 * Creates a new API key or updates metadata of existing API key
	 */
	private saveKey(): void {
		this.$store.commit('spinner/SHOW');
		if (this.keyId !== null) {
			ApiKeyService.editApiKey(this.keyId, this.metadata)
				.then(() => this.successfulSave())
				.catch(this.handleSaveError);
		} else {
			ApiKeyService.addApiKey(this.metadata)
				.then(() => this.successfulSave())
				.catch(this.handleSaveError);
		}
	}

	/**
	 * Handles successful REST API response
	 */
	private successfulSave(): void {
		this.$store.commit('spinner/HIDE');
		if (this.$route.path === '/api-key/add') {
			this.$toast.success(
				this.$t('core.apiKey.messages.addSuccess')
					.toString()
			);
		} else {
			this.$toast.success(
				this.$t('core.apiKey.messages.editSuccess', {key: this.keyId})
					.toString()
			);
		}
		this.$router.push('/api-key/');
	}

	/**
	 * Handles REST API save error response
	 * @param {AxiosError} error Axios error
	 */
	private handleSaveError(error: AxiosError): void {
		extendedErrorToast(
			error,
			'core.apiKey.messages.' + (this.$route.path === '/api-key/add' ? 'add' : 'edit') + 'Failed',
			{key: this.keyId}
		);
	}
}
</script>
