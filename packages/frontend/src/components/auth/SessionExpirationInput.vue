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
	<SelectInput
		v-model='modelValue'
		:items='options'
		:label='$t("components.auth.expiration.label")'
		:prepend-inner-icon='mdiAccountClock'
	/>
</template>

<script lang='ts' setup>
import { UserSessionExpiration } from '@iqrf/iqrf-gateway-webapp-client/types';
import { mdiAccountClock } from '@mdi/js';
import { computed, ComputedRef, PropType } from 'vue';
import { useI18n } from 'vue-i18n';

import SelectInput from '@/components/layout/form/SelectInput.vue';
import { SelectItem } from '@/types/vuetify';

/// Model value
const modelValue = defineModel({
	default: UserSessionExpiration.Default,
	required: true,
	type: String as PropType<UserSessionExpiration>,
});
/// Internationalization instance
const i18n = useI18n();
/// Session expiration options
const options: ComputedRef<SelectItem[]> = computed(() => [
	{
		value: UserSessionExpiration.Default,
		title: i18n.t('components.auth.expiration.expirations.default'),
	},
	{
		value: UserSessionExpiration.Day,
		title: i18n.t('components.auth.expiration.expirations.day'),
	},
	{
		value: UserSessionExpiration.Week,
		title: i18n.t('components.auth.expiration.expirations.week'),
	},
]);
</script>
