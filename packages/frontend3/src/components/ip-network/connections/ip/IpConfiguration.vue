<template>
	<h2 class='mb-3 text-h6'>
		{{ $t("components.ipNetwork.connections.fields.ip.title") }}
	</h2>
	<v-row>
		<v-col :cols='12' :md='6'>
			<Ipv4Configuration v-model='configuration' />
		</v-col>
		<v-col :cols='12' :md='6'>
			<Ipv6Configuration v-model='configuration' />
		</v-col>
	</v-row>
</template>

<script setup lang='ts'>
import {
	type NetworkConnectionConfiguration,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network/NetworkConnection';
import { type PropType } from 'vue';

import Ipv4Configuration
	from '@/components/ip-network/connections/ip/Ipv4Configuration.vue';
import Ipv6Configuration
	from '@/components/ip-network/connections/ip/Ipv6Configuration.vue';

const configuration = defineModel({
	type: Object as PropType<NetworkConnectionConfiguration>,
	required: true,
});
</script>
