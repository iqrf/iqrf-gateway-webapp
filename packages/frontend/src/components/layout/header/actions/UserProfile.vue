<template>
	<v-list-item
		v-if='mobile'
		:prepend-icon='mdiAccount'
		:title='name ?? undefined'
		to='/profile'
	/>
	<v-btn
		v-else
		to='/profile'
		:prepend-icon='mdiAccount'
	>
		{{ name }}
	</v-btn>
</template>

<script lang='ts' setup>
import { mdiAccount } from '@mdi/js';
import { storeToRefs } from 'pinia';

import { useUserStore } from '@/store/user';

/// Component props
defineProps({
	mobile: {
		type: Boolean,
		required: false,
		default: false,
	},
});
const userStore = useUserStore();
const { getName: name } = storeToRefs(userStore);
</script>
