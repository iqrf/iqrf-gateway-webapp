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
		<h1>{{ $t('config.translator.title') }}</h1>
		<v-card>
			<v-card-text>
				<ValidationObserver v-if='config !== null' v-slot='{invalid}'>
					<v-form @submit.prevent='processSubmit'>
						<h5>{{ $t("config.translator.form.rest.title") }}</h5>
						<v-row>
							<v-col cols='12' md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: $t("config.translator.errors.restAddr"),
									}'
								>
									<v-text-field
										v-model='config.rest.addr'
										:label='$t("forms.fields.address")'
										:success='touched ? valid : null'
										:error-messages='errors'
									/>
								</ValidationProvider>
							</v-col>
							<v-col cols='12' md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required|integer|between:1,49151'
									:custom-messages='{
										integer: $t("forms.errors.integer"),
										required: $t("config.translator.errors.port"),
										between: $t("config.translator.errors.port"),
									}'
								>
									<v-text-field
										v-model.number='config.rest.port'
										type='number'
										min='1'
										max='49151'
										:label='$t("config.translator.form.rest.port")'
										:success='touched ? valid : null'
										:error-messages='errors'
									/>
								</ValidationProvider>
							</v-col>
						</v-row>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required|apiKey'
							:custom-messages='{
								required: $t("config.translator.errors.apiKey"),
								apiKey: $t("config.translator.errors.apiKeyInvalid"),
							}'
						>
							<v-text-field
								v-model='config.rest.api_key'
								:label='$t("config.translator.form.rest.apiKey")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<h5>{{ $t("config.translator.form.mqtt.title") }}</h5>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required|clientId'
							:custom-messages='{
								required: $t("config.translator.errors.clientId"),
								clientId: $t("config.translator.errors.clientIdInvalid"),
							}'
						>
							<v-text-field
								v-model='config.mqtt.cid'
								:label='$t("forms.fields.clientId")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<v-row>
							<v-col cols='12' md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: $t("config.translator.errors.brokerAddr"),
									}'
								>
									<v-text-field
										v-model='config.mqtt.addr'
										:label='$t("config.daemon.messagings.mqtt.form.BrokerAddr")'
										:success='touched ? valid : null'
										:error-messages='errors'
									/>
								</ValidationProvider>
							</v-col>
							<v-col cols='12' md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required|integer|between:1,49151'
									:custom-messages='{
										integer: $t("forms.errors.integer"),
										required: $t("config.translator.errors.port"),
										between: $t("config.translator.errors.port"),
									}'
								>
									<v-text-field
										v-model.number='config.mqtt.port'
										type='number'
										min='1'
										max='49151'
										:label='$t("config.translator.form.mqtt.port")'
										:success='touched ? valid : null'
										:error-messages='errors'
									/>
								</ValidationProvider>
							</v-col>
						</v-row>
						<v-row>
							<v-col cols='12' md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required|requestTopic'
									:custom-messages='{
										required: $t("config.translator.errors.requestTopic"),
										requestTopic: $t("config.translator.errors.requestTopicInvalid"),
									}'
								>
									<v-text-field
										v-model='config.mqtt.request_topic'
										:label='$t("forms.fields.requestTopic")'
										:success='touched ? valid : null'
										:error-messages='errors'
									/>
								</ValidationProvider>
							</v-col>
							<v-col cols='12' md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required|responseTopic'
									:custom-messages='{
										required: $t("config.translator.errors.responseTopic"),
										responseTopic: $t("config.translator.errors.responseTopicInvalid"),
									}'
								>
									<v-text-field
										v-model='config.mqtt.response_topic'
										:label='$t("forms.fields.responseTopic")'
										:success='touched ? valid : null'
										:error-messages='errors'
									/>
								</ValidationProvider>
							</v-col>
						</v-row>
						<v-row>
							<v-col cols='12' md='6'>
								<v-text-field
									v-model='config.mqtt.user'
									:label='$t("forms.fields.username")'
								/>
							</v-col>
							<v-col cols='12' md='6'>
								<PasswordInput
									v-model='config.mqtt.pw'
									:label='$t("forms.fields.password")'
								/>
							</v-col>
						</v-row>
						<v-switch
							v-model='config.mqtt.tls.enabled'
							:label='$t("config.daemon.messagings.tlsTitle")'
							color='primary'
							inset
							dense
						/>
						<div v-if='config.mqtt.tls.enabled'>
							<v-row>
								<v-col cols='12' md='6'>
									<v-text-field
										v-model='config.mqtt.tls.trust_store'
										:label='$t("config.translator.form.mqtt.tls.trustStore")'
									/>
								</v-col>
								<v-col cols='12' md='6'>
									<v-text-field
										v-model='config.mqtt.tls.key_store'
										:label='$t("config.translator.form.mqtt.tls.keyStore")'
									/>
								</v-col>
							</v-row>
							<v-row>
								<v-col cols='12' md='6'>
									<v-text-field
										v-model='config.mqtt.tls.private_key'
										:label='$t("config.translator.form.mqtt.tls.privateKey")'
									/>
								</v-col>
								<v-col cols='12' md='6'>
									<v-checkbox
										v-model='config.mqtt.tls.require_broker_certificate'
										:label='$t("config.translator.form.mqtt.tls.requireBrokerCert")'
									/>
								</v-col>
							</v-row>
						</div>
						<v-btn
							color='primary'
							type='submit'
							:disabled='invalid'
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
import {Component, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import PasswordInput from '@/components/Core/PasswordInput.vue';

import {between, integer, required} from 'vee-validate/dist/rules';
import {extendedErrorToast} from '@/helpers/errorToast';
import FeatureConfigService from '@/services/FeatureConfigService';

import {AxiosError, AxiosResponse} from 'axios';
import {ITranslator} from '@/interfaces/Config/Translator';
import {NavigationGuardNext, Route} from 'vue-router';

@Component({
	components: {
		PasswordInput,
		ValidationObserver,
		ValidationProvider
	},
	beforeRouteEnter(to: Route, from: Route, next: NavigationGuardNext): void {
		next((vm: Vue) => {
			if (!vm.$store.getters['features/isEnabled']('iqrfGatewayTranslator')) {
				vm.$toast.error(
					vm.$t('config.translator.messages.disabled').toString()
				);
				vm.$router.push(from.path);
			}
		});
	},
	metaInfo: {
		title: 'config.translator.description',
	},
})

/**
 * IQRF Gateway Translator configuration component
 */
export default class TranslatorConfig extends Vue {
	/**
	 * @constant {string} name Name of translator service
	 */
	private name = 'translator';

	/**
	 * @var {ITranslator|null} config IQRF Gateway Translator service configuration
	 */
	private config: ITranslator|null = null;

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		extend('apiKey', (key) => {
			const regex = RegExp('^[./A-Za-z0-9]{22}\\.[A-Za-z0-9+/=]{44}$');
			return regex.test(key);
		});
		extend('clientId', (id) => {
			const regex = RegExp('^[A-Fa-f0-9]{16}$');
			return regex.test(id);
		});
		extend('requestTopic', (topic) => {
			const regex = RegExp('^gateway\\/[0-9a-fA-F]{16}\\/rest\\/requests\\/$');
			return regex.test(topic);
		});
		extend('responseTopic', (topic) => {
			const regex = RegExp('^gateway\\/[0-9a-fA-F]{16}\\/rest\\/responses\\/');
			return regex.test(topic);
		});
	}

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		this.getConfig();
	}

	/**
	 * Retrieves configuration of IQRF Gateway Translator
	 */
	private getConfig(): Promise<void> {
		if (!this.$store.getters['spinner/isEnabled']) {
			this.$store.commit('spinner/SHOW');
		}
		return FeatureConfigService.getConfig(this.name)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.config = response.data;
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.translator.messages.fetchFailed');
				this.$router.push('/');
			});
	}

	/**
	 * Updates configuration of IQRF Gateway Translator
	 */
	private processSubmit(): void {
		this.$store.commit('spinner/SHOW');
		FeatureConfigService.saveConfig(this.name, this.config)
			.then(() => {
				this.getConfig().then(() => {
					this.$store.commit('spinner/HIDE');
					this.$toast.success(
						this.$t('config.translator.messages.saveSuccess').toString()
					);
				});
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'config.translator.messages.saveFailed'));
	}
}
</script>
