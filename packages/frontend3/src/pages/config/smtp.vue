<template>
	<Head>
		<title>{{ $t('configuration.smtp.title') }}</title>
	</Head>
	<SmtpForm />
</template>

<route lang='yaml'>
name: SmtpConfig
</route>

<script lang='ts' setup>
import { Head } from '@vueuse/head';
import SmtpForm from '@/components/config/SmtpForm.vue';
</script>
