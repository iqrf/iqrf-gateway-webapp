<template>
	<Head>
		<title>{{ $t('pages.configuration.monit.title') }}</title>
	</Head>
	<MonitConfig />
</template>

<route lang='yaml'>
name: MonitConfiguration
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import MonitConfig from '@/components/config/monit/MonitConfig.vue';
</script>
