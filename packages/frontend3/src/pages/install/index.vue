<template>
	<Head>
		<title>{{ $t('install.title') }}</title>
	</Head>
	<Dashboard />
</template>

<route lang='yaml'>
name: InstallDisambiguation
meta:
  layout: Install
  requiresAuth: false
  installWizard: true
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';
import Dashboard from '@/components/install/Dashboard.vue';
</script>
