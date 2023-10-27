<template>
	<Head>
		<title>{{ $t('pages.accessControl.users.title') }}</title>
	</Head>
	<UserTable />
</template>

<route lang='yaml'>
name: Users
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import UserTable from '@/components/access-control/users/UserTable.vue';

</script>
