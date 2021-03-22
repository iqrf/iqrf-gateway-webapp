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
										required: "config.translator.errors.restAddr"
									}'
								>
									<CInput
										v-model='config.rest.addr'
										:label='$t("forms.fields.address")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
									/>
								</ValidationProvider>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required|integer|between:1,49151'
									:custom-messages='{
										integer: "forms.errors.integer",
										required: "config.translator.errors.port",
										between: "config.translator.errors.port"
									}'
								>
									<CInput
										v-model.number='config.rest.port'
										type='number'
										min='1'
										max='49151'
										:label='$t("config.translator.form.rest.port")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
									/>
								</ValidationProvider>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required|apiKey'
									:custom-messages='{
										required: "config.translator.errors.apiKey",
										apiKey: "config.translator.errors.apiKeyInvalid"
									}'
								>
									<CInput
										v-model='config.rest.api_key'
										:label='$t("config.translator.form.rest.apiKey")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
									/>
								</ValidationProvider>
							</CCol>
						</CRow>
						<legend>{{ $t("config.translator.form.mqtt.title") }}</legend>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required|clientId'
							:custom-messages='{
								required: "config.translator.errors.clientId",
								clientId: "config.translator.errors.clientIdInvalid"
							}'
						>
							<CInput
								v-model='config.mqtt.cid'
								:label='$t("forms.fields.clientId")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<CRow>
							<CCol md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: "config.translator.errors.brokerAddr"
									}'
								>
									<CInput
										v-model='config.mqtt.addr'
										:label='$t("config.daemon.messagings.mqtt.form.BrokerAddr")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
									/>
								</ValidationProvider>
							</CCol>
							<CCol md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required|integer|between:1,49151'
									:custom-messages='{
										integer: "forms.errors.integer",
										required: "config.translator.errors.port",
										between: "config.translator.errors.port"
									}'
								>
									<CInput
										v-model.number='config.mqtt.port'
										type='number'
										min='1'
										max='49151'
										:label='$t("config.translator.form.mqtt.port")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
									/>
								</ValidationProvider>
							</CCol>
							<CCol md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required|requestTopic'
									:custom-messages='{
										required: "config.translator.errors.requestTopic",
										requestTopic: "config.translator.errors.requestTopicInvalid"
									}'
								>
									<CInput
										v-model='config.mqtt.request_topic'
										:label='$t("forms.fields.requestTopic")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
									/>
								</ValidationProvider>
							</CCol>
							<CCol md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required|responseTopic'
									:custom-messages='{
										required: "config.translator.errors.responseTopic",
										responseTopic: "config.translator.errors.responseTopicInvalid"
									}'
								>
									<CInput
										v-model='config.mqtt.response_topic'
										:label='$t("forms.fields.responseTopic")'
										:is-valid='touched ? valid: null'
										:invalid-feedback='$t(errors[0])'
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
										:invalid-feedback='$t(errors[0])'
									/>
								</ValidationProvider>
							</CCol>
							<CCol md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
								>
									<CInput
										v-model='config.mqtt.pw'
										:label='$t("forms.fields.password")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
										:type='visibility'
									>
										<template #append-content>
											<span @click='visibility = (visibility === "password" ? "text" : "password")'>
												<FontAwesomeIcon
													:icon='(visibility === "password" ? ["far", "eye"] : ["far", "eye-slash"])'
												/>
											</span>
										</template>
									</CInput>
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
import {CButton, CCard, CCardBody, CCardHeader, CElementCover, CForm, CIcon, CInput, CInputCheckbox, CSwitch} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';

import {between, integer, required} from 'vee-validate/dist/rules';
import FeatureConfigService from '../../services/FeatureConfigService';

import {AxiosError, AxiosResponse} from 'axios';
import {ITranslator} from '../../interfaces/translator';
import {NavigationGuardNext, Route} from 'vue-router';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CElementCover,
		CForm,
		CIcon,
		CInput,
		CInputCheckbox,
		CSwitch,
		FontAwesomeIcon,
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
	private name = 'translator'

	/**
	 * @var {string} visibility Specifies form input type
	 */
	private visibility = 'password'

	/**
	 * @var {ITranslator|null} config IQRF Gateway Translator service configuration
	 */
	private config: ITranslator|null = null

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
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t(
						'config.translator.messages.fetchFailed',
						{error: error.response !== undefined ? error.response.data.message : error.message},
					).toString()
				);
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
					this.$toast.success(this.$t('forms.messages.saveSuccess').toString());
				});
			})
			.catch((error: AxiosError) => {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t(
						'config.translator.messages.saveFailed',
						{error: error.response !== undefined ? error.response.data.message : error.message},
					).toString()
				);
			});
	}
}
</script>
