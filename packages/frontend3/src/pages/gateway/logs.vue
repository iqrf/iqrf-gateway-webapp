<template>
	<Head>
		<title>{{ $t('pages.gateway.logs.title') }}</title>
	</Head>
	<LogSelector />
</template>

<route lang='yaml'>
name: 'GatewayLogs'
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import LogSelector from '@/components/gateway/logs/LogSelector.vue';
</script>
