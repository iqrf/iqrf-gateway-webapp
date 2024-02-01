<template>
	<Head>
		<title>{{ $t('pages.configuration.daemon.connections.mqtt.title') }}</title>
	</Head>
	<MqttConnectionTable />
</template>

<route>
{
	"name": "MqttConnectionsConfiguration",
}
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import MqttConnectionTable from '@/components/config/daemon/connections/mqtt/MqttConnectionTable.vue';
</script>
