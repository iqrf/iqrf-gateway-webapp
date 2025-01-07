<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software,
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied
See the License for the specific language governing permissions and
limitations under the License.
-->

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
	NetworkInterfaceType,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import { Head } from '@unhead/vue/components';

import ConnectionsTable
	from '@/components/ip-network/connections/ConnectionsTable.vue';
import InterfacesTable
	from '@/components/ip-network/interfaces/InterfacesTable.vue';
</script>
