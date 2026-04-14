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
	<ISelectInput
		v-model='modelValue'
		:items='roles'
		:label='$t("components.accessControl.roles.select")'
		:prepend-inner-icon='mdiAccountBadge'
	/>
</template>

<script setup lang='ts'>
import { RoleInfo } from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import { ISelectInput } from '@iqrf/iqrf-vue-ui';
import { mdiAccountBadge } from '@mdi/js';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const modelValue = defineModel<RoleInfo>({
	required: true,
});
const componentProps = defineProps<{
	roleList: RoleInfo[];
}>();

const i18n = useI18n();
const roles = computed(() => {
	const r = [];
	for (const role of componentProps.roleList) {
		let name: string = role.name;
		let description: string = role.description;
		if (role.systemKey) {
			name = i18n.t(`components.accessControl.roles.systemRoles.name.${role.systemKey}`);
			description = i18n.t(`components.accessControl.roles.systemRoles.description.${role.systemKey}`);
		}
		r.push({
			'name': name,
			'description': description,
			'role': role,
		});
	}
	return r;
});
</script>
