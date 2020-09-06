<template>
	<div>
		<CCard body-wrapper>
			<ValidationObserver v-if='config !== null' v-slot='{ invalid }'>
				<CForm @submit.prevent='processSubmit'>
					<CRow>
						<CCol md='6'>
							<h3>{{ $t("translatorConfig.form.mqtt.title") }}</h3>
							<ValidationProvider v-slot='{ valid }' rules='required'>
								<CInput
									v-model='config.mqtt.addr'
									:label='$t("translatorConfig.form.mqtt.addr")'
									:is-valid='valid'
								/>
							</ValidationProvider>
							<ValidationProvider v-slot='{ valid }' rules='required|integer'>
								<CInput
									v-model='config.mqtt.port'
									:label='$t("translatorConfig.form.mqtt.port")'
									:is-valid='valid'
								/>
							</ValidationProvider>
							<ValidationProvider v-slot='{ valid }' rules='required'>
								<CInput
									v-model='config.mqtt.cid'
									:label='$t("translatorConfig.form.mqtt.cid")'
									:is-valid='valid'
								/>
							</ValidationProvider>
							<ValidationProvider v-slot='{ valid }' rules='topic|required'>
								<CInput
									v-model='config.mqtt.topic'
									:label='$t("translatorConfig.form.mqtt.topic")'
									:is-valid='valid'
								/>
							</ValidationProvider>
							<ValidationProvider v-slot='{ valid }' rules='required'>
								<CInput
									v-model='config.mqtt.user'
									:label='$t("translatorConfig.form.mqtt.user")'
									:is-valid='valid'
								/>
							</ValidationProvider>
							<ValidationProvider v-slot='{ valid }' rules='required'>
								<CInput
									v-model='config.mqtt.pw'
									:label='$t("translatorConfig.form.mqtt.pw")'
									:is-valid='valid'
								/>
							</ValidationProvider>
						</CCol>
						<CCol md='6'>
							<h3>{{ $t("translatorConfig.form.rest.title") }}</h3>
							<ValidationProvider v-slot='{ valid }' rules='required'>
								<CInput
									v-model='config.rest.addr' 
									:label='$t("translatorConfig.form.rest.addr")' 
									:is-valid='valid'
								/>
							</ValidationProvider>
							<ValidationProvider v-slot='{ valid }' rules='required|integer'>
								<CInput
									v-model='config.rest.port' 
									:label='$t("translatorConfig.form.rest.port")' 
									:is-valid='valid'
								/>
							</ValidationProvider>
							<ValidationProvider v-slot='{ valid }' rules='api_key_r|required'>
								<CInput
									v-model='config.rest.api_key' 
									:label='$t("translatorConfig.form.rest.api_key")'
									:is-valid='valid'
								/>
							</ValidationProvider>
						</CCol>
					</CRow><hr>
					<CButton color='primary' type='submit' :disabled='invalid'>
						{{ $t('translatorConfig.form.saveButton') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCard>
	</div>
</template>

<script>

import {CCard, CForm} from '@coreui/vue';
import {integer, required} from 'vee-validate/dist/rules';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import TranslatorConfigService from '../../services/TranslatorConfigService';

extend('integer', integer);

extend('api_key_r', (key) => {
	const regex = RegExp('[./A-Za-z0-9]{22}\\.[A-Za-z0-9+/=]{44}');
	return regex.test(key);
});

extend('topic', (topic) => {
	const regex = RegExp('gateway\\/[a-f0-9]{16}\\/rest\\/requests\\/\\+\\/#');
	return regex.test(topic);
});

extend('required', required);

export default {
	name: 'TranslatorConfig',
	components: {
		CCard,
		CForm,
		ValidationObserver,
		ValidationProvider
	},
	data() {
		return {
			config: null
		};
	},
	created() {
		this.getConfig();
	},
	methods: {
		getConfig() {
			TranslatorConfigService.getConfig()
				.then((response) => {
					this.config = response.data;
				})
				.catch((error) => {
					if (error.response) {
						if (error.response.status === 500) {
							this.$toast.error(this.$t('translatorConfig.form.submitServerError'));
						}
					} else {
						console.error(error.message);
					}
				});
		},

		processSubmit() {
			TranslatorConfigService.saveConfig(this.config)
				.then((response) => {
					if (response.status === 200) {
						this.$toast.success(this.$t('translatorConfig.form.saveSuccess'));
					}
				})
				.catch((error) => {
					if (error.response) {
						if (error.response.status === 500) {
							this.$toast.error(this.$t('translatorConfig.form.submitServerError'));
						}
					} else {
						console.error(error.message);
					}
				});
		}
	}
};

</script>

<style>

</style>