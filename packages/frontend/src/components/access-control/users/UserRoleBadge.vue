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
	<v-chip
		:color='color'
		:prepend-icon='icon'
	>
		{{ text }}
	</v-chip>
</template>

<script lang='ts' setup>
import { UserRole } from '@iqrf/iqrf-gateway-webapp-client/types';
import {
	mdiAccount,
	mdiAccountEye,
	mdiHelp,
	mdiShieldAccount,
} from '@mdi/js';
import { computed, ComputedRef } from 'vue';
import { useI18n } from 'vue-i18n';

const componentProps = defineProps<{
	role: UserRole;
}>();
const i18n = useI18n();

/// Badge color for the given role
const color: ComputedRef<string> = computed((): string => {
	const data: Record<UserRole, string> = {
		[UserRole.Admin]: 'deep-purple',
		[UserRole.Normal]: 'indigo',
		[UserRole.Basic]: 'teal',
	};
	return data[componentProps.role] ?? 'grey';
});

/// Badge icon for the given role
const icon: ComputedRef<string> = computed((): string => {
	const data: Record<UserRole, string> = {
		[UserRole.Admin]: mdiShieldAccount,
		[UserRole.Normal]: mdiAccount,
		[UserRole.Basic]: mdiAccountEye,
	};
	return data[componentProps.role] ?? mdiHelp;
});

/// Badge text for the given role
const text: ComputedRef<string> = computed((): string => {
	const data: Record<UserRole, string> = {
		[UserRole.Admin]: i18n.t('components.accessControl.users.roles.admin'),
		[UserRole.Normal]: i18n.t('components.accessControl.users.roles.normal'),
		[UserRole.Basic]: i18n.t('components.accessControl.users.roles.basic'),
	};
	return data[componentProps.role] ?? '';
});
</script>
