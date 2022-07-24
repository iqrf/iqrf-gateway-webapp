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
		<h1>{{ pageTitle }}</h1>
		<v-card>
			<v-card-text>
				<ValidationObserver v-slot='{invalid}'>
					<v-form>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("core.security.apiKey.errors.description"),
							}'
						>
							<v-text-field
								v-model='keyData.description'
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
							:date.sync='date'
							:time.sync='time'
							:min-date='new Date().toISOString()'
							:disabled='!useExpiration'
						/>
						<v-btn
							color='primary'
							:disabled='invalid'
							@click='save'
						>
							{{ submitButton }}
						</v-btn>
					</v-form>
				</ValidationObserver>
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import DateTimePicker from '@/components/DateTimePicker.vue';

import ApiKeyService from '@/services/ApiKeyService';
import {DateTime} from 'luxon';
import {extendedErrorToast} from '@/helpers/errorToast';
import {required} from 'vee-validate/dist/rules';

import {AxiosError, AxiosResponse} from 'axios';
import {IApiKey} from '@/interfaces/apiKey';
import {MetaInfo} from 'vue-meta';

@Component({
	components: {
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
	 * @var {IApiKey} keyData API key data
	 */
	private keyData: IApiKey = {
		description: '',
		expiration: null
	};

	/**
	 * @var {string} date Date string
	 */
	private date = '';

	/**
	 * @var {string} time Time string
	 */
	private time = '';

	/**
	 * @var {boolean} useExpiration Controls whether form expiration input is hidden or shown
	 */
	private useExpiration = false;

	/**
	 * @property {number} keyId API key id
	 */
	@Prop({required: false, default: null}) keyId!: number;

	/**
	 * Computes page title depending on the action (add, edit)
	 * @returns {string} Page title
	 */
	get pageTitle(): string {
		return this.$t(`core.security.apiKey.${this.$route.path === '/security/api-key/add' ? 'add' : 'edit'}`).toString();
	}

	/**
	 * Computes submit button text depending on the action (add, edit)
	 * @return {string} Button text
	 */
	get submitButton(): string {
		return this.$t(`forms.${this.$route.path === '/security/api-key/add' ? 'add' : 'edit'}`).toString();
	}

	/**
	 * Returns input icon color depending on expiration required
	 */
	get inputIconColor(): string {
		return this.useExpiration ? 'primary' : 'muted';
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
				this.keyData = response.data;
				if (this.keyData.expiration !== null) {
					const luxondate = DateTime.fromISO(this.keyData.expiration);
					this.date = luxondate.toISODate();
					this.time = luxondate.toLocaleString(DateTime.TIME_24_SIMPLE);
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
		const config = JSON.parse(JSON.stringify(this.keyData));
		if (this.useExpiration) {
			const luxondate = DateTime.fromISO(`${this.date}T${this.time}`);
			config.expiration = luxondate.toISO();
		} else {
			config.expiration = null;
		}
		if (this.keyId !== null) {
			ApiKeyService.editApiKey(this.keyId, config)
				.then(() => this.successfulSave())
				.catch(this.handleSaveError);
		} else {
			ApiKeyService.addApiKey(config)
				.then(() => this.successfulSave())
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
		} else {
			this.$toast.success(
				this.$t('core.security.apiKey.messages.editSuccess', {key: this.keyId})
					.toString()
			);
		}
		this.$router.push('/security/api-key/');
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
}
</script>
