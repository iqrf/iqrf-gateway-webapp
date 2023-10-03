<template>
	<Head>
		<title>{{ $t('pages.management.users.title') }}</title>
	</Head>
	<UserTable />
</template>

<route lang='yaml'>
name: Users
</route>

<script lang='ts' setup>
import { Head } from '@vueuse/head';
import UserTable from '@/components/management/users/UserTable.vue';

</script>
