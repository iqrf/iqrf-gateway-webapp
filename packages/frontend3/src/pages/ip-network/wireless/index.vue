<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
