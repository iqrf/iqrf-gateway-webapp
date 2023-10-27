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
import { Head } from '@unhead/vue/components';

import SmtpForm from '@/components/config/smtp/SmtpForm.vue';
</script>
