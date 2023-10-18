<template>
	<Head>
		<title>{{ $t('pages.install.sshService.title') }} | {{ $t('pages.install.title') }}</title>
	</Head>
	<SshService />
</template>

<route lang='yaml'>
name: InstallSshService
meta:
  layout: Install
  requiresAuth: false
  installWizard: true
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';
import SshService from '@/components/install/SshService.vue';
</script>
