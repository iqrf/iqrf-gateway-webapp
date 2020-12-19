<template>
	<div>
		<h1>{{ $t('config.translator.title') }}</h1>
		<CCard>
			<CCardBody>
				<ValidationObserver v-if='config !== null' v-slot='{ invalid }'>
					<CForm @submit.prevent='processSubmit'>
						<CRow>
							<CCol md='6'>
								<h3>{{ $t("config.translator.form.mqtt.title") }}</h3>
								<ValidationProvider 
									v-slot='{ errors, touched, valid }' 
									rules='required'
									:custom-messages='{required: "config.translator.errors.missing.maddr"}'
								>
									<CInput
										v-model='config.mqtt.addr'
										:label='$t("config.daemon.messagings.mqtt.form.BrokerAddr")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
									/>
								</ValidationProvider>
								<ValidationProvider 
									v-slot='{ errors, touched, valid }' 
									rules='required|integer|between:1,49151'
									:custom-messages='{
										integer: "config.translator.errors.integer",
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
								<ValidationProvider 
									v-slot='{ errors, touched, valid }' 
									rules='required|client_id'
									:custom-messages='{
										required: "config.translator.errors.missing.mcid",
										client_id: "config.translator.errors.invalid.mcid",
									}'
								>
									<CInput
										v-model='config.mqtt.cid'
										:label='$t("forms.fields.clientId")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'								
									/>
								</ValidationProvider>
								<ValidationProvider 
									v-slot='{ errors, touched, valid }' 
									rules='topic|required'
									:custom-messages='{
										required: "config.translator.errors.missing.mtopic",
										topic: "config.translator.errors.invalid.mtopic",
									}'
								>
									<CInput
										v-model='config.mqtt.topic'
										:label='$t("forms.fields.requestTopic")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
									/>
								</ValidationProvider>
								<ValidationProvider 
									v-slot='{ errors, touched, valid }'
								>
									<CInput
										v-model='config.mqtt.user'
										autocomplete='off'
										:label='$t("forms.fields.username")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
									/>
								</ValidationProvider>
								<ValidationProvider 
									v-slot='{ errors, touched, valid }'
								>
									<CInput
										v-model='config.mqtt.pw'
										autocomplete='off'
										:label='$t("forms.fields.password")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
										:type='visibility'
									>
										<template #append-content>
											<span @click='changeVisibility'>
												<CIcon
													v-if='visibility ==="password"'
													:content='icons.hidden'
												/>
												<CIcon v-else :content='icons.shown' />
											</span>
										</template>
									</CInput>
								</ValidationProvider>
							</CCol>
							<CCol md='6'>
								<h3>{{ $t("config.translator.form.rest.title") }}</h3>
								<ValidationProvider
									v-slot='{ errors, touched, valid }'
									rules='required'
									:custom-messages='{required: "config.translator.errors.missing.raddr"}'
								>
									<CInput
										v-model='config.rest.addr' 
										:label='$t("forms.fields.address")' 
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
									/>
								</ValidationProvider>
								<ValidationProvider 
									v-slot='{ errors, touched, valid }'
									rules='required|integer|between:1,49151'
									:custom-messages='{
										integer: "config.translator.errors.integer",
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
									v-slot='{ errors, touched, valid }' 
									rules='api_key_r|required'
									:custom-messages='{
										required: "config.translator.errors.missing.rapi_key",
										api_key_r: "config.translator.errors.invalid.api_key"
									}'
								>
									<CInput
										v-model='config.rest.api_key' 
										:label='$t("config.translator.form.rest.api_key")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
									/>
								</ValidationProvider>
							</CCol>
						</CRow>
						<CButton color='primary' type='submit' :disabled='invalid'>
							{{ $t('forms.save') }}
						</CButton>
					</CForm>
				</ValidationObserver>
				<CAlert v-else color='danger'>
					{{ $t('config.translator.messages.loadFailed') }}
				</CAlert>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CAlert, CButton, CCard, CCardBody, CCardHeader, CForm, CIcon, CInput} from '@coreui/vue/src';
import {cilLockLocked, cilLockUnlocked} from '@coreui/icons';
import {between, integer, required} from 'vee-validate/dist/rules';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import FeatureConfigService from '../../services/FeatureConfigService';
import {NavigationGuardNext, Route} from 'vue-router';
import {Dictionary} from 'vue-router/types/router';
import {AxiosError, AxiosResponse} from 'axios';
import {ITranslator} from '../../interfaces/translator';

@Component({
	components: {
		CAlert,
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CIcon,
		CInput,
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
	 * @constant {Dictionary<Array<string>>} icons Dictionary of CoreUI Icons
	 */
	private icons: Dictionary<Array<string>> = {
		hidden: cilLockLocked,
		shown: cilLockUnlocked
	}

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		extend('api_key_r', (key) => {
			const regex = RegExp('^[./A-Za-z0-9]{22}\\.[A-Za-z0-9+/=]{44}$');
			return regex.test(key);
		});
		extend('client_id', (id) => {
			const regex = RegExp('^[A-Fa-f0-9]{16}$');
			return regex.test(id);
		});
		extend('topic', (topic) => {
			const regex = RegExp('^gateway\\/[A-Fa-f0-9]{16}\\/rest\\/requests\\/\\+\\/#$');
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
	private getConfig(): void {
		this.$store.commit('spinner/SHOW');
		FeatureConfigService.getConfig(this.name)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.config = response.data;
			})
			.catch((error: AxiosError) => {
				FormErrorHandler.configError(error);
			});
	}

	/**
	 * Updates configuration of IQRF Gateway Translator
	 */
	private processSubmit(): void {
		this.$store.commit('spinner/SHOW');
		FeatureConfigService.saveConfig(this.name, this.config)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(this.$t('forms.messages.saveSuccess').toString());
			})
			.catch((error: AxiosError) => {
				FormErrorHandler.configError(error);
			});
	}

	/**
	 * Changes password input field visibility
	 */
	private changeVisibility(): void {
		this.visibility = this.visibility === 'password' ? 'text' : 'password';
	}
}
</script>
