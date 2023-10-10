<template>
	<Head>
		<title>{{ $t('pages.accessControl.sshKeys.title') }}</title>
	</Head>
	<SshKeyTable />
</template>

<route lang='yaml'>
name: SshKeys
</route>

<script lang='ts' setup>
import { Head } from '@vueuse/head';
import SshKeyTable from '@/components/access-control/ssh-keys/SshKeyTable.vue';
</script>
