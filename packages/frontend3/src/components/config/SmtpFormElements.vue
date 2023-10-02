<template>
	<div>
		<v-text-field
			v-model='configuration.host'
			:label='$t("configuration.smtp.form.host")'
			:rules='[
				(v: string|null) => ValidationRules.required(v, $t("configuration.smtp.errors.host")),
			]'
			:disabled='loading || !configuration.enabled'
			required
		/>
		<v-text-field
			v-model.number='configuration.port'
			:label='$t("configuration.smtp.form.port")'
			:rules='[
				(v: number|null) => ValidationRules.required(v, $t("configuration.smtp.errors.port")),
				(v: number) => ValidationRules.integer(v, $t("configuration.smtp.errors.port")),
				(v: number) => ValidationRules.between(v, 1, 65535, $t("configuration.smtp.errors.portInvalid")),
			]'
			:disabled='loading || !configuration.enabled'
			required
		/>
		<v-text-field
			v-model='configuration.username'
			:label='$t("configuration.smtp.form.username")'
			:rules='[
				(v: string|null) => ValidationRules.required(v, $t("configuration.smtp.errors.username")),
			]'
			:disabled='loading || !configuration.enabled'
		/>
		<PasswordInput
			v-model='configuration.password'
			:label='$t("configuration.smtp.form.password")'
			:rules='[
				(v: string|null) => ValidationRules.required(v, $t("configuration.smtp.errors.password")),
			]'
			:disabled='loading || !configuration.enabled'
		/>
		<v-select
			v-model='configuration.secure'
			:items='securityOptions'
			:label='$t("configuration.smtp.form.security")'
			:disabled='loading || !configuration.enabled'
		/>
		<v-text-field
			v-model='configuration.from'
			:label='$t("configuration.smtp.form.sender")'
			:rules='[
				(v: string|null) => ValidationRules.required(v, $t("configuration.smtp.errors.sender")),
			]'
			:disabled='loading || !configuration.enabled'
		/>
	</div>
</template>

<script lang='ts' setup>
import { computed, PropType } from 'vue';
import { useI18n } from 'vue-i18n';

import PasswordInput from '@/components/PasswordInput.vue';
import ValidationRules from '@/helpers/ValidationRules';

import { MailerConfig, MailerSmtpSecurity } from '@iqrf/iqrf-gateway-webapp-client/types/Config';

const props = defineProps({
	config: {
		type: Object as PropType<MailerConfig>,
		required: true,
	},
	loading: {
		type: Boolean,
		default: false,
		required: false,
	},
});
const configuration = computed(() => {
	return props.config;
});
const i18n = useI18n();
const securityOptions = [
	{
		title: i18n.t('configuration.smtp.form.securityLevels.none'),
		value: null
	},
	{
		title: i18n.t('configuration.smtp.form.securityLevels.tls'),
		value: MailerSmtpSecurity.STARTTLS,
	},
	{
		title: i18n.t('configuration.smtp.form.securityLevels.ssl'),
		value: MailerSmtpSecurity.TLS,
	},
];
</script>
