<template>
	<CCard body-wrapper>
		<CForm @submit='processSubmit'>
			<CRow>
				<CCol md='6'>
					<h3>{{ $t("translatorConfig.form.mqtt.title") }}</h3>
					<CInput v-model='config.mqtt.cid' :label='$t("translatorConfig.form.mqtt.cid")' />
					<CInput v-model='config.mqtt.addr' :label='$t("translatorConfig.form.mqtt.addr")' />
					<CInput v-model='config.mqtt.port' :label='$t("translatorConfig.form.mqtt.port")' />
					<CInput v-model='config.mqtt.topic' :label='$t("translatorConfig.form.mqtt.topic")' />
					<CInput v-model='config.mqtt.user' :label='$t("translatorConfig.form.mqtt.user")' />
					<CInput v-model='config.mqtt.pw' :label='$t("translatorConfig.form.mqtt.pw")' />
				</CCol>	
				<CCol md='6'>
					<h3>{{ $t("translatorConfig.form.rest.title") }}</h3>
					<CInput v-model='config.rest.addr' :label='$t("translatorConfig.form.rest.addr")' />
					<CInput v-model='config.rest.port' :label='$t("translatorConfig.form.rest.port")' />
					<CInput v-model='config.rest.api_key' :label='$t("translatorConfig.form.rest.api_key")' />
				</CCol>
			</CRow><hr>
			<CButton color='primary' type='submit'>
				{{ $t('translatorConfig.form.saveButton') }}
			</CButton>
		</CForm>
	</CCard>
</template>

<script>

import {CCard, CForm} from '@coreui/vue';
import TranslatorConfigService from '../../services/TranslatorConfigService';

export default {
	name: 'TranslatorConfig',
	components: {
		CCard,
		CForm,
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
				});
		}
	}
};

</script>

<style>

</style>