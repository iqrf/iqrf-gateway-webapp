<template>
	<Head>
		<title>{{ $t('pages.configuration.unattendedUpgrades.title') }}</title>
	</Head>
	<AptConfig />
</template>

<route lang='yaml'>
name: AptConfiguration
meta:
  feature: 'unattendedUpgrades'
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import AptConfig from '@/components/config/unattended-upgrades/AptConfig.vue';
</script>
