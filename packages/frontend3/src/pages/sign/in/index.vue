<template>
	<Head>
		<title>{{ $t('auth.sign.in.title') }}</title>
	</Head>
	<SignInForm />
</template>

<route lang='yaml'>
name: SignIn
meta:
  requiresAuth: false
</route>

<script lang='ts' setup>
import { Head } from '@vueuse/head';
import SignInForm from '@/components/auth/SignInForm.vue';
</script>
