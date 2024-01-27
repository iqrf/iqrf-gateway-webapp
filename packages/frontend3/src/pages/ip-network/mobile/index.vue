<template>
	<Head>
		<title>{{ $t('pages.ipNetwork.mobile.title') }}</title>
	</Head>
	<ModemTable class='mb-3' />
	<ConnectionsTable :type='NetworkConnectionType.GSM' />
</template>

<route>
{
	"name": "MobileConnections",
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
import { Head } from '@unhead/vue/components';

import ConnectionsTable
	from '@/components/ip-network/connections/ConnectionsTable.vue';
import ModemTable from '@/components/ip-network/modems/ModemTable.vue';
</script>
