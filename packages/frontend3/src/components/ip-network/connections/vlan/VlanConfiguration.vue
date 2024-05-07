<template>
	<h2 class='mb-3 text-h6'>
		{{ $t("components.ipNetwork.connections.fields.vlan.title") }}
	</h2>
	<InterfaceInput v-model='configuration.vlan!.parentInterface' />
	<TextInput
		v-model.number='configuration.vlan!.id'
		:label='$t("components.ipNetwork.connections.fields.vlan.id").toString()'
		:rules='[
			(v: string|null) => ValidationRules.required(v, $t("components.ipNetwork.connections.validations.vlan.id.required")),
			(v: number) => ValidationRules.integer(v, $t("components.ipNetwork.connections.validations.vlan.id.invalid")),
			(v: number) => ValidationRules.min(v, 0, $t("components.ipNetwork.connections.validations.vlan.id.invalid")),
			(v: number) => ValidationRules.max(v, 4094, $t("components.ipNetwork.connections.validations.vlan.id.invalid")),
		]'
		required
		:prepend-inner-icon='mdiTag'
	/>
</template>

<script setup lang='ts'>
import {
	type NetworkConnectionConfiguration,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network/NetworkConnection';
import { mdiTag } from '@mdi/js';
import { type PropType } from 'vue';

import InterfaceInput
	from '@/components/ip-network/interfaces/InterfaceInput.vue';
import TextInput from '@/components/TextInput.vue';
import ValidationRules from '@/helpers/ValidationRules';

const configuration = defineModel({
	type: Object as PropType<NetworkConnectionConfiguration>,
	required: true,
});
</script>
