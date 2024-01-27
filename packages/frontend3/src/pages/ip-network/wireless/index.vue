<template>
	<Head>
		<title>{{ $t('pages.ipNetwork.wireless.title') }}</title>
	</Head>
	<InterfacesTable :type='NetworkInterfaceType.WIFI' class='mb-3' />
	<ConnectionsTable :type='NetworkConnectionType.WiFi' />
</template>

<route>
{
	"name": "WiFiConnections",
	"meta": {
		"feature": "networkManager",
		"roles": ["admin"]
	}
}
</route>

<script lang='ts' setup>
import {
	NetworkConnectionType,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network/NetworkConnection';
import {
	NetworkInterfaceType,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network/NetworkInterface';
import { Head } from '@unhead/vue/components';

import ConnectionsTable
	from '@/components/ip-network/connections/ConnectionsTable.vue';
import InterfacesTable
	from '@/components/ip-network/interfaces/InterfacesTable.vue';
</script>
