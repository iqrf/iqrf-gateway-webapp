<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
			:label='$t("components.common.fields.username")'
			:rules='[
				(v: string|null) => ValidationRules.required(v, $t("components.common.validations.username.required")),
			]'
			:disabled='loading || !configuration.enabled'
		/>
		<PasswordInput
			v-model='configuration.password'
			:label='$t("components.common.fields.password")'
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
