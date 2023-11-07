<template>
	<Head>
		<title>{{ $t('pages.configuration.daemon.connections.udp.title') }}</title>
	</Head>
	<UdpConnectionTable />
</template>

<route lang='yaml'>
name: UdpConnectionsConfiguration
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import UdpConnectionTable from '@/components/config/daemon/connections/udp/UdpConnectionTable.vue';
</script>
