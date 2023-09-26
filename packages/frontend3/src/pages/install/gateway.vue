<template>
	<Head>
		<title>{{ $t('install.gwInfo.title') }}</title>
	</Head>
	<InstallGatewayInfo />
</template>

<route lang='yaml'>
name: InstallGwInfo
meta:
  layout: Install
  requiresAuth: false
  installWizard: true
</route>

<script lang='ts' setup>
import { Head } from '@vueuse/head';
import InstallGatewayInfo from '@/components/install/InstallGatewayInfo.vue';
</script>
