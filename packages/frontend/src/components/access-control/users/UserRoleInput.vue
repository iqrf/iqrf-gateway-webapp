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
	<ISelectInput
		v-model='modelValue'
		:items='roles'
		:label='$t("components.accessControl.users.role")'
		:prepend-inner-icon='mdiAccountBadge'
	/>
</template>

<script setup lang='ts'>
import { UserRole } from '@iqrf/iqrf-gateway-webapp-client/types';
import { ISelectInput } from '@iqrf/iqrf-vue-ui';
import { mdiAccountBadge } from '@mdi/js';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const modelValue = defineModel<UserRole>({
	required: true,
});
const i18n = useI18n();
const roles = computed(() => [
	{
		title: i18n.t('components.accessControl.users.roles.admin'),
		value: UserRole.Admin,
	},
	{
		title: i18n.t('components.accessControl.users.roles.normal'),
		value: UserRole.Normal,
	},
	{
		title: i18n.t('components.accessControl.users.roles.basic'),
		value: UserRole.Basic,
	},
]);
</script>

