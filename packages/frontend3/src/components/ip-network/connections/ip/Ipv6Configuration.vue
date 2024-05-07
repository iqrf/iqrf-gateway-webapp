<template>
	<h2 class='mb-3 text-h6 text-grey'>
		{{ $t("components.ipNetwork.connections.fields.ipv6.title") }}
	</h2>
</template>

<script setup lang='ts'>
import {
	type NetworkConnectionConfiguration,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network/NetworkConnection';
import { type PropType } from 'vue';

const configuration = defineModel({
	type: Object as PropType<NetworkConnectionConfiguration>,
	required: true,
});
</script>
