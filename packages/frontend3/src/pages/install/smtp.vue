<template>
	<Head>
		<title>{{ $t('install.smtp.title') }}</title>
	</Head>
	<InstallSmtpForm />
</template>

<route lang='yaml'>
name: InstallSmtp
meta:
  layout: Install
  requiresAuth: false
  installWizard: true
</route>

<script lang='ts' setup>
import { Head } from '@vueuse/head';
import InstallSmtpForm from '@/components/install/InstallSmtpForm.vue';
</script>
