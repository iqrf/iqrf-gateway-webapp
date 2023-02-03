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
		<CCard>
			<CCardBody>
				<ValidationObserver v-slot='{invalid}'>
					<CForm>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("config.repository.errors.endpointMissing"),
							}'
						>
							<CInput
								v-model='config.apiEndpoint'
								:label='$t("config.repository.form.endpoint")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='errors.join(", ")'
							/>
						</ValidationProvider>
						<div class='form-group'>
							<strong>
								<label>
									{{ $t('config.repository.form.credentials') }}
								</label>
							</strong><br>
							<CSwitch
								:checked.sync='credentials'
								color='primary'
								size='lg'
								shape='pill'
								label-on='ON'
								label-off='OFF'
							/>
						</div>
						<CRow v-if='credentials'>
							<CCol>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: $t("forms.errors.username"),
									}'
								>
									<CInput
										v-model='config.credentials.username'
										:label='$t("forms.fields.username")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
									/>
								</ValidationProvider>
							</CCol>
							<CCol>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: $t("forms.errors.password"),
									}'
								>
									<CInput
										v-model='config.credentials.password'
										:label='$t("forms.fields.password")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
									/>
								</ValidationProvider>
							</CCol>
						</CRow>
						<CButton
							color='primary'
							:disabled='invalid'
							@click='saveConfig'
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
import {CButton, CCard, CCardBody, CForm, CInput, CSwitch} from '@coreui/vue/src';
import {extend, ValidationProvider, ValidationObserver} from 'vee-validate';

import {extendedErrorToast} from '@/helpers/errorToast';
import {NavigationGuardNext, Route} from 'vue-router';
import {required} from 'vee-validate/dist/rules';

import RepositoryConfigService from '@/services/IqrfRepository/IqrfRepositoryConfigService';

import {AxiosError} from 'axios';
import {IIqrfRepositoryConfig} from '@/interfaces/Config/Misc';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CForm,
		CInput,
		CSwitch,
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
export default class IqrfRepositoryConfig extends Vue {

	/**
	 * @var {IIqrfRepositoryConfig} config IQRF repository configuration
	 */
	private config: IIqrfRepositoryConfig = {
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
		return RepositoryConfigService.get()
			.then((config: IIqrfRepositoryConfig) => {
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
	private storeConfig(config: IIqrfRepositoryConfig): void {
		this.config = {...this.config, ...config};
		this.credentials = config.credentials.username !== null;
	}

	/**
	 * Saves IQRF repository configuration
	 */
	private saveConfig(): void {
		this.$store.commit('spinner/SHOW');
		const config: IIqrfRepositoryConfig = JSON.parse(JSON.stringify(this.config));
		if (!this.credentials) {
			config.credentials = {username: null, password: null};
		}
		RepositoryConfigService.save(config)
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
