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
	<v-select
		v-model='modelValue'
		:items='options'
		:label='$t("components.config.smtp.form.security")'
		:disabled='componentProps.disabled'
		:prepend-inner-icon='mdiSecurity'
	/>
</template>

<script setup lang='ts'>
import {
	MailerSmtpSecurity,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { mdiSecurity } from '@mdi/js';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

/// SMTP Security type model value
const modelValue = defineModel<MailerSmtpSecurity | null>({
	required: true,
});

/// SMTP Security type component properties
const componentProps = withDefaults(
	defineProps<{
		/// Is the input disabled
		disabled?: boolean;
	}>(),
	{
		disabled: false,
	},
);

const i18n = useI18n();

/// SMTP Security type options
const options = computed(() => [
	{
		title: i18n.t('components.config.smtp.form.securityLevels.none'),
		value: null,
	},
	{
		title: i18n.t('components.config.smtp.form.securityLevels.tls'),
		value: MailerSmtpSecurity.STARTTLS,
	},
	{
		title: i18n.t('components.config.smtp.form.securityLevels.ssl'),
		value: MailerSmtpSecurity.TLS,
	},
]);

</script>
