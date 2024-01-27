<template>
	<Head>
		<title>{{ $t('pages.ipNetwork.wireGuard.title') }}</title>
	</Head>
	<WireGuardTunnelsTable />
</template>

<route>
{
	"name": "WireGuardConnections",
	"meta": {
		"feature": "networkManager",
		"roles": ["admin"]
	}
}
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import WireGuardTunnelsTable
	from '@/components/ip-network/wireGuard/WireGuardTunnelsTable.vue';
</script>
