<template>
	<Head>
		<title>{{ $t('pages.install.sshKeys.title') }} | {{ $t('pages.install.title') }}</title>
	</Head>
	<SshKeyTable :install='true' />
</template>

<route lang='yaml'>
name: InstallSshKeys
meta:
  layout: Install
  requiresAuth: false
  installWizard: true
</route>

<script lang='ts' setup>
import { Head } from '@vueuse/head';
import SshKeyTable from '@/components/management/ssh-keys/SshKeyTable.vue';
</script>
