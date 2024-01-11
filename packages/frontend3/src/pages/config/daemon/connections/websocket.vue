<template>
	<Head>
		<title>{{ $t('pages.configuration.daemon.connections.ws.title') }}</title>
	</Head>
	<WebsocketConnectionTable />
</template>

<route lang='yaml'>
name: WebsocketConnectionsConfiguration
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import WebsocketConnectionTable from '@/components/config/daemon/connections/websocket/WebsockeConnectionTable.vue';
</script>
