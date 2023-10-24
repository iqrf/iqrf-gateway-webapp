<template>
	<Head>
		<title>{{ $t('pages.gateway.logs.title') }}</title>
	</Head>
	<LogViewer />
</template>

<route lang='yaml'>
name: 'GatewayLogs'
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';
import LogViewer from '@/components/gateway/logs/LogViewer.vue';
</script>
