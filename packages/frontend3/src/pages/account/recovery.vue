<template>
	<Head>
		<title>{{ $t('account.recovery.title') }}</title>
	</Head>
	<AccountRecoveryForm />
</template>

<route lang='yaml'>
name: AccountRecovery
meta:
  requiresAuth: false
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import AccountRecoveryForm from '@/components/account/AccountRecoveryForm.vue';

</script>
