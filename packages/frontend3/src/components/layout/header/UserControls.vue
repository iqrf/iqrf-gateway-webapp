<template>
	<v-btn
		to='/profile'
		:prepend-icon='mdiAccount'
	>
		{{ userStore.getName }}
	</v-btn>
	<v-btn
		:icon='mdiLogout'
		@click='signOut'
	/>
</template>

<script lang='ts' setup>
import { mdiAccount, mdiLogout } from '@mdi/js';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { useUserStore } from '@/store/user';

const i18n = useI18n();
const userStore = useUserStore();

function signOut(): void {
	userStore.signOut();
	toast.success(
		i18n.t('auth.sign.out.message').toString(),
	);
}
</script>
