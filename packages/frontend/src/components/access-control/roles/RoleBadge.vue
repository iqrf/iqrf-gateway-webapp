<!--
Copyright 2017-2026 IQRF Tech s.r.o.
Copyright 2019-2026 MICRORISC s.r.o.

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
import { RoleInfo } from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import {
	mdiAccount,
	mdiAccountEye,
	mdiShieldAccount,
} from '@mdi/js';
import { computed, ComputedRef } from 'vue';
import { useI18n } from 'vue-i18n';

const componentProps = defineProps<{
	role: RoleInfo;
}>();
const i18n = useI18n();

/// Badge color for the given role
const color: ComputedRef<string> = computed((): string => {
	const data: Partial<Record<string, string>> = {
		admin: 'deep-purple',
		normal: 'indigo',
		viewer: 'teal',
	};
	if (!componentProps.role.systemKey) {
		return 'grey';
	}
	return data[componentProps.role.systemKey] ?? 'grey';
});

/// Badge icon for the given role
const icon: ComputedRef<string> = computed((): string => {
	const data: Partial<Record<string, string>> = {
		admin: mdiShieldAccount,
		normal: mdiAccount,
		viewer: mdiAccountEye,
	};
	if (!componentProps.role.systemKey) {
		return mdiAccount;
	}
	return data[componentProps.role.systemKey] ?? mdiAccount;
});

/// Badge text for the given role
const text: ComputedRef<string> = computed((): string => {
	const data: Partial<Record<string, string>> = {
		admin: i18n.t('components.accessControl.roles.systemRoles.name.admin'),
		normal: i18n.t('components.accessControl.roles.systemRoles.name.normal'),
		viewer: i18n.t('components.accessControl.roles.systemRoles.name.viewer'),
	};
	if (!componentProps.role.systemKey) {
		return componentProps.role.name;
	}
	return data[componentProps.role.systemKey] ?? componentProps.role.name;
});
</script>
