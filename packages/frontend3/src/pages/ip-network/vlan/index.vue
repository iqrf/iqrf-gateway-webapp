<template>
	<Head>
		<title>{{ $t('pages.ipNetwork.vlan.title') }}</title>
	</Head>
	<InterfacesTable :type='NetworkInterfaceType.VLAN' class='mb-3' />
	<ConnectionsTable :type='NetworkConnectionType.VLAN' />
</template>

<route>
{
	"name": "VlanConnections",
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
