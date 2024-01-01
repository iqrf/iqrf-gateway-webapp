<template>
	<Head>
		<title>{{ $t('account.recovery.title') }}</title>
	</Head>
	<AccountRecoveryConfirmForm :uuid='uuid' />
</template>

<route lang='yaml'>
name: AccountRecoveryConfirmation
meta:
  requiresAuth: false
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';
import { validate as uuidValidate, version as uuidVersion } from 'uuid';

import AccountRecoveryConfirmForm
	from '@/components/account/AccountRecoveryConfirmForm.vue';

const componentProps = defineProps({
	uuid: {
		type: String,
		required: true,
		validator(value: string): boolean {
			return uuidValidate(value) && uuidVersion(value) === 4;
		},
	},
});
</script>
