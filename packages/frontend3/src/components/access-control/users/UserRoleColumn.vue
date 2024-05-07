<template>
	<v-chip
		:color='roleColor'
		:prepend-icon='roleIcon'
	>
		{{ $t(`components.accessControl.users.roles.${role}`) }}
	</v-chip>
</template>

<script lang='ts' setup>
import { UserRole } from '@iqrf/iqrf-gateway-webapp-client/types/User';
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
