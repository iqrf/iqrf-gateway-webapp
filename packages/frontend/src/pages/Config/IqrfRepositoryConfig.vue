<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

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
		<h1>{{ $t('config.repository.title') }}</h1>
		<v-card>
			<v-card-text>
				<ValidationObserver v-slot='{invalid}'>
					<v-form>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("config.repository.errors.endpointMissing"),
							}'
						>
							<v-text-field
								v-model='config.apiEndpoint'
								:label='$t("config.repository.form.endpoint")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<v-switch
							v-model='credentials'
							:label='$t("config.repository.form.credentials")'
							color='primary'
							inset
							dense
						/>
						<v-row v-if='credentials'>
							<v-col>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: $t("forms.errors.username"),
									}'
								>
									<v-text-field
										v-model='config.credentials.username'
										:label='$t("forms.fields.username")'
										:success='touched ? valid : null'
										:error-messages='errors'
									/>
								</ValidationProvider>
							</v-col>
							<v-col>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: $t("forms.errors.password"),
									}'
								>
									<v-text-field
										v-model='config.credentials.password'
										:label='$t("forms.fields.password")'
										:success='touched ? valid : null'
										:error-messages='errors'
									/>
								</ValidationProvider>
							</v-col>
						</v-row>
						<v-btn
							color='primary'
							:disabled='invalid'
							@click='saveConfig'
						>
							{{ $t('forms.save') }}
						</v-btn>
					</v-form>
				</ValidationObserver>
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {IqrfRepositoryService} from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {IqrfRepositoryConfig} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {AxiosError} from 'axios';
import {extend, ValidationProvider, ValidationObserver} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import {Component, Vue} from 'vue-property-decorator';
import {NavigationGuardNext, Route} from 'vue-router';

import {extendedErrorToast} from '@/helpers/errorToast';
import {useApiClient} from '@/services/ApiClient';

@Component({
	components: {
		ValidationObserver,
		ValidationProvider
	},
	beforeRouteEnter(to: Route, from: Route, next: NavigationGuardNext): void {
		next((vm: Vue) => {
			if (!vm.$store.getters['features/isEnabled']('iqrfRepository')) {
				vm.$toast.error(
					vm.$t('config.repository.messages.disabled').toString()
				);
				vm.$router.push(from.path);
			}
		});
	},
	metaInfo: {
		title: 'config.repository.title',
	},
})

/**
 * IQRF repository configuration component
 */
export default class IqrfRepositoryConfiguration extends Vue {

	/**
	 * @var {IqrfRepositoryConfig} config IQRF repository configuration
	 */
	private config: IqrfRepositoryConfig = {
		apiEndpoint: 'https://repository.iqrfalliance.org/api',
		credentials: {
			username: null,
			password: null,
		},
	};

	/**
	 * @var {boolean} credentials Controls credentials rendering in form
	 */
	private credentials = false;

	/**
	 * @property {IqrfRepositoryService} repositoryService IQRF repository service
   * @private
   */
	private repositoryService: IqrfRepositoryService = useApiClient().getConfigServices().getIqrfRepositoryService();

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
	}

	/**
	 * Retrieves IQRF repository config on load
	 */
	mounted(): void {
		const repositoryConfig = this.$store.getters['repository/configuration'];
		if (repositoryConfig) {
			this.storeConfig(repositoryConfig);
			return;
		}
		this.getConfig();
	}

	/**
	 * Retrieves IQRF repository configuration
	 */
	private getConfig(): Promise<void> {
		this.$store.commit('spinner/SHOW');
		return this.repositoryService.fetch()
			.then((config: IqrfRepositoryConfig) => {
				this.storeConfig(config);
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.repository.messages.fetchFailed');
			});
	}

	/**
	 * Parses and stores repository configuration
	 */
	private storeConfig(config: IqrfRepositoryConfig): void {
		this.config = {...this.config, ...config};
		this.credentials = config.credentials.username !== null;
	}

	/**
	 * Saves IQRF repository configuration
	 */
	private saveConfig(): void {
		this.$store.commit('spinner/SHOW');
		const config: IqrfRepositoryConfig = JSON.parse(JSON.stringify(this.config));
		if (!this.credentials) {
			config.credentials = {username: null, password: null};
		}
		this.repositoryService.edit(config)
			.then(() => {
				this.$store.commit('repository/SET', config);
				this.getConfig().then(() => {
					this.$store.commit('spinner/HIDE');
					this.$toast.success(
						this.$t('config.repository.messages.saveSuccess').toString()
					);
				});
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.repository.messages.saveFailed');
			});
	}

}
</script>
