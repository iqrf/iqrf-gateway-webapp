<template>
	<Head>
		<title>{{ $t('pages.configuration.influxdb-bridge.title') }}</title>
	</Head>
	<InfluxdbBridgeConfig />
</template>

<route lang='yaml'>
name: InfluxdbBridgeConfiguration
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import InfluxdbBridgeConfig from '@/components/config/influxdb-bridge/InfluxdbBridgeConfig.vue';
</script>
