<template>
	<v-btn
		to='/profile'
		prepend-icon='mdi-account'
	>
		{{ userStore.getName }}
	</v-btn>
	<v-btn
		icon
		@click='signOut'
	>
		<v-icon>mdi-logout</v-icon>
	</v-btn>
</template>

<script lang='ts' setup>
import { useUserStore } from '@/store/user';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

const i18n = useI18n();
const userStore = useUserStore();

function signOut(): void {
	userStore.signOut();
	toast.success(
		i18n.t('core.sign.out.message').toString()
	);
}
</script>
