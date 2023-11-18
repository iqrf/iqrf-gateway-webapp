<template>
	<Head>
		<title>{{ $t('pages.configuration.daemon.logging.title') }}</title>
	</Head>
	<LoggingTable />
</template>

<route lang='yaml'>
name: LoggingConfiguration
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import LoggingTable from '@/components/config/daemon/logging/LoggingTable.vue';
</script>
