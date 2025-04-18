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
		<h1>{{ pageTitle }}</h1>
		<v-card>
			<v-card-text>
				<ValidationObserver v-slot='{invalid}'>
					<v-form @submit.prevent='save'>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("core.security.apiKey.errors.description"),
							}'
						>
							<v-text-field
								v-model='metadata.description'
								:label='$t("core.security.apiKey.form.description")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<v-checkbox
							v-model='useExpiration'
							:label='$t("core.security.apiKey.form.expiration")'
							dense
						/>
						<DateTimePicker
							:datetime.sync='datetime'
							:min-date='new Date().toISOString()'
							:disabled='!useExpiration'
						/>
						<v-btn
							color='primary'
							:disabled='invalid'
							type='submit'
						>
							{{ submitButton }}
						</v-btn>
					</v-form>
				</ValidationObserver>
			</v-card-text>
		</v-card>
		<ApiKeyDisplayModal
			ref='displayModal'
			:api-key='generatedKey'
			@closed='displayModalClose()'
		/>
	</div>
</template>

<script lang='ts'>
import {ApiKeyService} from '@iqrf/iqrf-gateway-webapp-client/services/Security';
import {ApiKeyCreated, ApiKeyInfo} from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import {AxiosError} from 'axios';
import {DateTime} from 'luxon';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import {MetaInfo} from 'vue-meta';
import {Component, Prop, Ref, Vue} from 'vue-property-decorator';

import ApiKeyDisplayModal from '@/components/Core/ApiKeyDisplayModal.vue';
import DateTimePicker from '@/components/DateTimePicker.vue';
import {extendedErrorToast} from '@/helpers/errorToast';
import {useApiClient} from '@/services/ApiClient';

@Component({
	components: {
		ApiKeyDisplayModal,
		DateTimePicker,
		ValidationObserver,
		ValidationProvider,
	},
	metaInfo(): MetaInfo {
		return {
			title: (this as ApiKeyForm).pageTitle
		};
	}
})

/**
 * API key manager form to add or edit API key
 */
export default class ApiKeyForm extends Vue {

	/**
	 * @var {ApiKeyInfo} metadata API key metadata
	 */
	private metadata: ApiKeyInfo = {
		description: '',
		expiration: null
	};

	/**
	 * @var {boolean} useExpiration Controls whether form expiration input is hidden or shown
	 */
	private useExpiration = false;

	/**
	 * @var {Date} datetime Datetime object
	 */
	private datetime = new Date(0);

	/**
	 * @property {ApiKeyService} service API key service
	 * @private
	 */
	private service: ApiKeyService = useApiClient().getSecurityServices().getApiKeyService();

	/**
	 * @var {string|null} generatedKey Generated key for one-time display
	 */
	private generatedKey: string|null = null;

	/**
	 * @property {number} keyId API key id
	 */
	@Prop({required: false, default: null}) keyId!: number;

	/**
	 * @property {ApiKeyDisplayModal} setLaiResult Set LAI voltage result
	 */
	@Ref('displayModal') readonly displayModal!: ApiKeyDisplayModal;

	/**
	 * Computes page title depending on the action (add, edit)
	 * @returns {string} Page title
	 */
	get pageTitle(): string {
		return this.$route.path === '/security/api-key/add' ?
			this.$t('core.security.apiKey.add').toString() : this.$t('core.security.apiKey.edit').toString();
	}

	/**
	 * Computes submit button text depending on the action (add, edit)
	 * @return {string} Button text
	 */
	get submitButton(): string {
		return this.$route.path === '/security/api-key/add' ?
			this.$t('forms.add').toString() : this.$t('forms.edit').toString();
	}

	/**
	 * Initializes validation rules and retrieves API key
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
		this.service.get(this.keyId)
			.then((response: ApiKeyInfo) => {
				this.$store.commit('spinner/HIDE');
				this.metadata = response;
				if (response.expiration !== null) {
					this.datetime = response.expiration.toJSDate();
					this.useExpiration = true;
				}
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'core.security.apiKey.messages.fetchFailed', {key: this.keyId});
				this.$router.push('/security/api-key/');
			});
	}

	/**
	 * Creates a new API key or updates metadata of existing API key
	 */
	private save(): void {
		this.$store.commit('spinner/SHOW');
		const config = {...this.metadata};
		delete config.id;
		config.expiration = this.useExpiration ? DateTime.fromJSDate(this.datetime) : null;
		if (this.keyId !== null) {
			this.service.update(this.keyId, config)
				.then(() => this.successfulSave())
				.catch(this.handleSaveError);
		} else {
			this.service.create(config)
				.then((data: ApiKeyCreated) => {
					this.generatedKey = data.key;
					this.successfulSave();
				})
				.catch(this.handleSaveError);
		}
	}

	/**
	 * Handles successful REST API response
	 */
	private successfulSave(): void {
		this.$store.commit('spinner/HIDE');
		if (this.$route.path === '/security/api-key/add') {
			this.$toast.success(
				this.$t('core.security.apiKey.messages.addSuccess')
					.toString()
			);
			this.displayModal.open();
		} else {
			this.$toast.success(
				this.$t('core.security.apiKey.messages.editSuccess', {key: this.keyId})
					.toString()
			);
			this.$router.push('/security/api-key/');
		}
	}

	/**
	 * Handles REST API save error response
	 * @param {AxiosError} error Axios error
	 */
	private handleSaveError(error: AxiosError): void {
		extendedErrorToast(
			error,
			'core.security.apiKey.messages.' + (this.$route.path === '/security/api-key/add' ? 'add' : 'edit') + 'Failed',
			{key: this.keyId}
		);
	}

	/**
	 * Clears generated key and navigates back to api key list
	 */
	private displayModalClose(): void {
		this.generatedKey = null;
		this.$router.push('/security/api-key/');
	}
}
</script>
