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
