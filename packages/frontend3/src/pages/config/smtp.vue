<template>
	<Head>
		<title>{{ $t('pages.configuration.smtp.title') }}</title>
	</Head>
	<SmtpForm />
</template>

<route lang='yaml'>
name: SmtpConfig
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';
import SmtpForm from '@/components/config/smtp/SmtpForm.vue';
</script>
