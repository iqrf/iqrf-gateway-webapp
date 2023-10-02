<template>
	<Head>
		<title>{{ $t('pages.management.sshKeys.title') }}</title>
	</Head>
	<SshKeyTable />
</template>

<route lang='yaml'>
name: SshKeys
</route>

<script lang='ts' setup>
import { Head } from '@vueuse/head';
import SshKeyTable from '@/components/management/ssh-keys/SshKeyTable.vue';
</script>
