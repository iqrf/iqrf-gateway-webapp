<template>
	<Head>
		<title>{{ $t('pages.ipNetwork.ethernet.title') }}</title>
	</Head>
	<InterfacesTable :type='NetworkInterfaceType.ETHERNET' class='mb-3' />
	<ConnectionsTable :type='NetworkConnectionType.Ethernet' />
</template>

<route>
{
	"name": "EthernetConnections",
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
