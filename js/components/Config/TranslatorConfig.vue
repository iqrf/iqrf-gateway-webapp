<template>
	<div>
		<CCard body-wrapper>
			<ValidationObserver v-if='config !== null' v-slot='{ invalid }'>
				<CForm @submit.prevent='processSubmit'>
					<CRow>
						<CCol md='6'>
							<h3>{{ $t("translatorConfig.form.mqtt.title") }}</h3>
							<ValidationProvider 
								v-slot='{ errors, valid }' 
								rules='required'
								:custom-messages='{required: "translatorConfig.form.messages.missing.maddr"}'
							>
								<CInput
									v-model='config.mqtt.addr'
									:label='$t("translatorConfig.form.mqtt.addr")'
									:is-valid='valid'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<ValidationProvider 
								v-slot='{ errors, valid }' 
								rules='required|integer|port_range'
								:custom-messages='{
									required: "translatorConfig.form.messages.missing.mport",
									port_range: "translatorConfig.form.messages.invalid.port"
								}'
							>
								<CInput
									v-model='config.mqtt.port'
									:label='$t("translatorConfig.form.mqtt.port")'
									:is-valid='valid'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<ValidationProvider 
								v-slot='{ errors, valid }' 
								rules='required|client_id'
								:custom-messages='{
									required: "translatorConfig.form.messages.missing.mcid",
									client_id: "translatorConfig.form.messages.invalid.mcid",
								}'
							>
								<CInput
									v-model='config.mqtt.cid'
									:label='$t("translatorConfig.form.mqtt.cid")'
									:is-valid='valid'
									:invalid-feedback='$t(errors[0])'								
								/>
							</ValidationProvider>
							<ValidationProvider 
								v-slot='{ errors, valid }' 
								rules='topic|required'
								:custom-messages='{
									required: "translatorConfig.form.messages.missing.mtopic",
									topic: "translatorConfig.form.messages.invalid.mtopic",
								}'
							>
								<CInput
									v-model='config.mqtt.topic'
									:label='$t("translatorConfig.form.mqtt.topic")'
									:is-valid='valid'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<ValidationProvider 
								v-slot='{ errors, valid }' 
								rules='required'
								:custom-messages='{required: "translatorConfig.form.messages.missing.muser"}'
							>
								<CInput
									v-model='config.mqtt.user'
									:label='$t("translatorConfig.form.mqtt.user")'
									:is-valid='valid'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<ValidationProvider 
								v-slot='{ errors, valid }' 
								rules='required'
								:custom-messages='{required: "translatorConfig.form.messages.missing.mpw"}'
							>
								<CInput
									v-model='config.mqtt.pw'
									:label='$t("translatorConfig.form.mqtt.pw")'
									:is-valid='valid'
									:invalid-feedback='$t(errors[0])'
									:type='visibility'
								>
									<template #append-content>
										<span @click='changeVisibility'>
											<CIcon v-if='visibility ==="password"' :content='$options.icons.hidden' />
											<CIcon v-else :content='$options.icons.shown' />
										</span>
									</template>
								</CInput>
							</ValidationProvider>
						</CCol>
						<CCol md='6'>
							<h3>{{ $t("translatorConfig.form.rest.title") }}</h3>
							<ValidationProvider
								v-slot='{ errors, valid }'
								rules='required'
								:custom-messages='{required: "translatorConfig.form.messages.missing.raddr"}'
							>
								<CInput
									v-model='config.rest.addr' 
									:label='$t("translatorConfig.form.rest.addr")' 
									:is-valid='valid'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<ValidationProvider 
								v-slot='{ errors, valid }'
								rules='required|integer|port_range'
								:custom-messages='{
									required: "translatorConfig.form.messages.missing.rport",
									port_range: "translatorConfig.form.messages.invalid.port"
								}'
							>
								<CInput
									v-model='config.rest.port' 
									:label='$t("translatorConfig.form.rest.port")' 
									:is-valid='valid'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<ValidationProvider 
								v-slot='{ errors, valid }' 
								rules='api_key_r|required'
								:custom-messages='{
									required: "translatorConfig.form.messages.missing.rapi_key",
									api_key_r: "translatorConfig.form.messages.invalid.api_key"
								}'
							>
								<CInput
									v-model='config.rest.api_key' 
									:label='$t("translatorConfig.form.rest.api_key")'
									:is-valid='valid'
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
		</CCard>
	</div>
</template>

<script>

import {CButton, CCard, CForm, CIcon, CInput} from '@coreui/vue';
import { cilLockLocked, cilLockUnlocked } from '@coreui/icons';
import {integer, required} from 'vee-validate/dist/rules';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import ConfigService from '../../services/ConfigService';

extend('integer', integer);

extend('api_key_r', (key) => {
	const regex = RegExp('[./A-Za-z0-9]{22}\\.[A-Za-z0-9+/=]{44}');
	return regex.test(key);
});

extend('port_range', (port) => {
	return ((port >= 1) && (port <= 49151));
});

extend('client_id', (id) => {
	const regex = RegExp('[a-f0-9]{16}');
	return regex.test(id);
});

extend('topic', (topic) => {
	const regex = RegExp('gateway\\/[a-f0-9]{16}\\/rest\\/requests\\/\\+\\/#');
	return regex.test(topic);
});

extend('required', required);

export default {
	name: 'TranslatorConfig',
	components: {
		CButton,
		CCard,
		CForm,
		CIcon,
		CInput,
		ValidationObserver,
		ValidationProvider
	},
	data() {
		return {
			visibility: 'password',
			config: null
		};
	},
	created() {
		this.getConfig();
	},
	methods: {
		getConfig() {
			ConfigService.getConfig('translatorConfig')
				.then((response) => {
					this.config = response.data;
				})
				.catch((error) => {
					if (error.response) {
						if (error.response.status === 500) {
							this.$toast.error(this.$t('forms.messages.submitServerError'));
						}
					} else {
						console.error(error.message);
					}
				});
		},
		processSubmit() {
			ConfigService.saveConfig('translatorConfig', this.config)
				.then((response) => {
					if (response.status === 200) {
						this.$toast.success(this.$t('forms.messages.saveSuccess'));
					}
				})
				.catch((error) => {
					if (error.response) {
						if (error.response.status === 500) {
							this.$toast.error(this.$t('forms.messages.submitServerError'));
						}
					} else {
						console.error(error.message);
					}
				});
		},
		changeVisibility() {
			this.visibility = this.visibility === 'password' ? 'text' : 'password';
		}
	},
	icons: {
		hidden: cilLockLocked,
		shown: cilLockUnlocked
	}
};

</script>

<style>

</style>