<template>
	<Head>
		<title>{{ $t('pages.configuration.influxdb-bridge.title') }}</title>
	</Head>
	<InfluxdbBridgeConfig />
</template>

<route>
{
	"name": "InfluxdbBridgeConfiguration",
	"meta": {
		"feature": "iqrfGatewayInfluxdbBridge",
	},
}
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import InfluxdbBridgeConfig from '@/components/config/influxdb-bridge/InfluxdbBridgeConfig.vue';
</script>
