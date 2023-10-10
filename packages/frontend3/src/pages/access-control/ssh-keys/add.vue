<template>
	<Head>
		<title>{{ $t('pages.install.sshKeys.title') }}</title>
	</Head>
	<SshKeyAddTable />
</template>

<route lang='yaml'>
name: SshKeysAdd
</route>

<script lang='ts' setup>
import { Head } from '@vueuse/head';
import SshKeyAddTable from '@/components/access-control/ssh-keys/SshKeyAddTable.vue';
</script>
