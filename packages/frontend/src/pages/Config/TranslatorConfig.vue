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
		<CCard>
			<CCardBody>
				<ValidationObserver v-if='config !== null' v-slot='{invalid}'>
					<CForm @submit.prevent='processSubmit'>
						<CRow>
							<CCol>
								<h3>{{ $t("config.translator.form.rest.title") }}</h3>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: $t("config.translator.errors.restAddr"),
									}'
								>
									<CInput
										v-model='config.rest.addr'
										:label='$t("forms.fields.address")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
									/>
								</ValidationProvider>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required|integer|between:1,49151'
									:custom-messages='{
										integer: $t("forms.errors.integer"),
										required: $t("config.translator.errors.port"),
										between: $t("config.translator.errors.port"),
									}'
								>
									<CInput
										v-model.number='config.rest.port'
										type='number'
										min='1'
										max='49151'
										:label='$t("config.translator.form.rest.port")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
									/>
								</ValidationProvider>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required|apiKey'
									:custom-messages='{
										required: $t("config.translator.errors.apiKey"),
										apiKey: $t("config.translator.errors.apiKeyInvalid"),
									}'
								>
									<CInput
										v-model='config.rest.api_key'
										:label='$t("config.translator.form.rest.apiKey")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
									/>
								</ValidationProvider>
							</CCol>
						</CRow>
						<legend>{{ $t("config.translator.form.mqtt.title") }}</legend>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required|clientId'
							:custom-messages='{
								required: $t("config.translator.errors.clientId"),
								clientId: $t("config.translator.errors.clientIdInvalid"),
							}'
						>
							<CInput
								v-model='config.mqtt.cid'
								:label='$t("forms.fields.clientId")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='errors.join(", ")'
							/>
						</ValidationProvider>
						<CRow>
							<CCol md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: $t("config.translator.errors.brokerAddr"),
									}'
								>
									<CInput
										v-model='config.mqtt.addr'
										:label='$t("config.daemon.messagings.mqtt.form.BrokerAddr")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
									/>
								</ValidationProvider>
							</CCol>
							<CCol md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required|integer|between:1,49151'
									:custom-messages='{
										integer: $t("forms.errors.integer"),
										required: $t("config.translator.errors.port"),
										between: $t("config.translator.errors.port"),
									}'
								>
									<CInput
										v-model.number='config.mqtt.port'
										type='number'
										min='1'
										max='49151'
										:label='$t("config.translator.form.mqtt.port")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
									/>
								</ValidationProvider>
							</CCol>
							<CCol md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required|requestTopic'
									:custom-messages='{
										required: $t("config.translator.errors.requestTopic"),
										requestTopic: $t("config.translator.errors.requestTopicInvalid"),
									}'
								>
									<CInput
										v-model='config.mqtt.request_topic'
										:label='$t("forms.fields.requestTopic")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
									/>
								</ValidationProvider>
							</CCol>
							<CCol md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required|responseTopic'
									:custom-messages='{
										required: $t("config.translator.errors.responseTopic"),
										responseTopic: $t("config.translator.errors.responseTopicInvalid"),
									}'
								>
									<CInput
										v-model='config.mqtt.response_topic'
										:label='$t("forms.fields.responseTopic")'
										:is-valid='touched ? valid: null'
										:invalid-feedback='errors.join(", ")'
									/>
								</ValidationProvider>
							</CCol>
							<CCol md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
								>
									<CInput
										v-model='config.mqtt.user'
										:label='$t("forms.fields.username")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
									/>
								</ValidationProvider>
							</CCol>
							<CCol md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
								>
									<PasswordInput
										v-model='config.mqtt.pw'
										:label='$t("forms.fields.password").toString()'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
									/>
								</ValidationProvider>
							</CCol>
						</CRow>
						<CRow>
							<CCol>
								<h3 style='font-size: 1.5rem; float: left;'>
									{{ $t('config.daemon.messagings.tlsTitle') }}
								</h3>
								<CSwitch
									color='primary'
									size='lg'
									shape='pill'
									label-on='ON'
									label-off='OFF'
									:checked.sync='config.mqtt.tls.enabled'
									style='float: right;'
								/>
							</CCol>
						</CRow>
						<CRow v-if='config.mqtt.tls.enabled'>
							<CCol md='6'>
								<CInput
									v-model='config.mqtt.tls.trust_store'
									:label='$t("config.translator.form.mqtt.tls.trustStore")'
								/>
							</CCol>
							<CCol md='6'>
								<CInput
									v-model='config.mqtt.tls.key_store'
									:label='$t("config.translator.form.mqtt.tls.keyStore")'
								/>
							</CCol>
							<CCol md='6'>
								<CInput
									v-model='config.mqtt.tls.private_key'
									:label='$t("config.translator.form.mqtt.tls.privateKey")'
								/>
							</CCol>
							<CCol md='6'>
								<CInputCheckbox
									:checked.sync='config.mqtt.tls.require_broker_certificate'
									:label='$t("config.translator.form.mqtt.tls.requireBrokerCert")'
								/>
							</CCol>
						</CRow>
						<CButton color='primary' type='submit' :disabled='invalid'>
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
import {CButton, CCard, CCardBody, CCardHeader, CCol, CElementCover, CForm, CIcon, CInput, CInputCheckbox, CRow, CSwitch} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {between, integer, required} from 'vee-validate/dist/rules';

import PasswordInput from '@/components/Core/PasswordInput.vue';

import {extendedErrorToast} from '@/helpers/errorToast';
import FeatureConfigService from '@/services/FeatureConfigService';

import {AxiosError, AxiosResponse} from 'axios';
import {ITranslator} from '@/interfaces/Config/Translator';
import {NavigationGuardNext, Route} from 'vue-router';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CCol,
		CElementCover,
		CForm,
		CIcon,
		CInput,
		CInputCheckbox,
		CRow,
		CSwitch,
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
