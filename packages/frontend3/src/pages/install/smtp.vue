<template>
	<Head>
		<title>{{ $t('pages.install.smtp.title') }} | {{ $t('pages.install.title') }}</title>
	</Head>
	<SmtpForm :install='true' />
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
import SmtpForm from '@/components/config/SmtpForm.vue';
</script>
