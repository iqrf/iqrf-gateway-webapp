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
		:color='roleColor'
		:prepend-icon='roleIcon'
	>
		{{ $t(`components.accessControl.users.roles.${role}`) }}
	</v-chip>
</template>

<script lang='ts' setup>
import { UserRole } from '@iqrf/iqrf-gateway-webapp-client/types';
import { mdiAccount, mdiAccountBadge, mdiAccountSearch, mdiShieldAccount } from '@mdi/js';
import { computed } from 'vue';

interface Props {
	role: UserRole
}

const props = defineProps<Props>();

const roleColor = computed(() => {
	switch (props.role) {
		case UserRole.Admin:
			return 'deep-purple';
		case UserRole.Normal:
			return 'indigo';
		case UserRole.BasicAdmin:
			return 'cyan';
		default:
			return 'teal';
	}
});
const roleIcon = computed(() => {
	switch (props.role) {
		case UserRole.Admin:
			return mdiShieldAccount;
		case UserRole.Normal:
			return mdiAccount;
		case UserRole.BasicAdmin:
			return mdiAccountBadge;
		default:
			return mdiAccountSearch;
	}
});

</script>
