<template>
	<div>
		<v-text-field
			v-model='configuration.host'
			:label='$t("common.labels.hostname")'
			:rules='[
				(v: string|null) => ValidationRules.required(v, $t("components.configuration.smtp.validation.hostMissing")),
			]'
			:disabled='loading || !configuration.enabled'
			required
		/>
		<v-text-field
			v-model.number='configuration.port'
			:label='$t("common.labels.port")'
			:rules='[
				(v: number|null) => ValidationRules.required(v, $t("components.configuration.smtp.validation.portMissing")),
				(v: number) => ValidationRules.integer(v, $t("components.configuration.smtp.validation.portMissing")),
				(v: number) => ValidationRules.between(v, 1, 65535, $t("components.configuration.smtp.validation.portInvalid")),
			]'
			:disabled='loading || !configuration.enabled'
			required
		/>
		<v-text-field
			v-model='configuration.username'
			:label='$t("common.labels.username")'
			:rules='[
				(v: string|null) => ValidationRules.required(v, $t("components.configuration.smtp.validation.usernameMissing")),
			]'
			:disabled='loading || !configuration.enabled'
		/>
		<PasswordInput
			v-model='configuration.password'
			:label='$t("common.labels.password")'
			:rules='[
				(v: string|null) => ValidationRules.required(v, $t("components.configuration.smtp.validation.passwordMissing")),
			]'
			:disabled='loading || !configuration.enabled'
		/>
		<v-select
			v-model='configuration.secure'
			:items='securityOptions'
			:label='$t("components.configuration.smtp.form.security")'
			:disabled='loading || !configuration.enabled'
		/>
		<v-text-field
			v-model='configuration.from'
			:label='$t("components.configuration.smtp.form.sender")'
			:rules='[
				(v: string|null) => ValidationRules.required(v, $t("components.configuration.smtp.validation.senderMissing")),
				(v: string) => ValidationRules.email(v, $t("components.configuration.smtp.validation.senderInvalid"))
			]'
			:disabled='loading || !configuration.enabled'
		/>
	</div>
</template>

<script lang='ts' setup>
import { type MailerConfig, MailerSmtpSecurity } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { computed, type PropType } from 'vue';
import { useI18n } from 'vue-i18n';

import PasswordInput from '@/components/PasswordInput.vue';
import ValidationRules from '@/helpers/ValidationRules';


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
		title: i18n.t('components.configuration.smtp.form.securityLevels.none'),
		value: null,
	},
	{
		title: i18n.t('components.configuration.smtp.form.securityLevels.starttls'),
		value: MailerSmtpSecurity.STARTTLS,
	},
	{
		title: i18n.t('components.configuration.smtp.form.securityLevels.tls'),
		value: MailerSmtpSecurity.TLS,
	},
];
</script>
