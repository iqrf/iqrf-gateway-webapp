<template>
	<Head>
		<title>{{ $t('install.createUser.title') }}</title>
	</Head>
	<CreateUserForm />
</template>

<route lang='yaml'>
name: InstallUser
meta:
  layout: Install
  requiresAuth: false
  installWizard: true
</route>

<script lang='ts' setup>
import { Head } from '@vueuse/head';
import CreateUserForm from '@/components/install/CreateUserForm.vue';
</script>
