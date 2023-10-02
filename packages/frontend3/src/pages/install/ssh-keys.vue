<template>
	<Head>
		<title>{{ $t('pages.install.sshKeys.title') }} | {{ $t('pages.install.title') }}</title>
	</Head>
	<SshKeyAddTable :install='true' />
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
import SshKeyAddTable from '@/components/management/ssh-keys/SshKeyAddTable.vue';
</script>
