<template>
	<SelectInput
		v-model='modelValue'
		:items='items'
		:loading='componentState === ComponentState.Loading'
		:label='$t("components.ipNetwork.connections.fields.generic.interface").toString()'
		:rules='[
			(v: string|null) => ValidationRules.required(v, $t("components.ipNetwork.connections.validations.generic.interface.required")),
		]'
		required
		:prepend-inner-icon='mdiExpansionCardVariant'
	/>
</template>
<script setup lang='ts'>
import {
	type NetworkInterface, type NetworkInterfaceType,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network/NetworkInterface';
import { mdiExpansionCardVariant } from '@mdi/js';
import { onBeforeMount, type PropType, type Ref, ref } from 'vue';

import SelectInput from '@/components/SelectInput.vue';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';
import { type SelectItem } from '@/types/vuetify';

/// Component properties
const componentProps = defineProps({
	type: {
		type: [String, null] as PropType<NetworkInterfaceType | null>,
		required: false,
		default: null,
	},

});
/// Model value
const modelValue = defineModel({
	type: [String, null] as PropType<string | null>,
	required: true,
});
/// Component state
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
/// Network interface items
const items: Ref<SelectItem[]> = ref([]);
/// Network interface service
const service = useApiClient().getNetworkServices().getNetworkInterfaceService();

/**
 * Fetches network interfaces
 */
async function fetchInterfaces(): Promise<void> {
	componentState.value = ComponentState.Loading;
	/// @todo Add error handling
	await service.list(componentProps.type)
		.then((interfaces: NetworkInterface[]) => {
			interfaces.forEach((item: NetworkInterface) => {
				let label = item.name;
				if (item.manufacturer !== null && item.model !== null) {
					label = item.name + ' (' + item.manufacturer + ' ' + item.model + ')';
				}
				items.value.push({ title: label, value: item.name });
			});
			componentState.value = ComponentState.Ready;
		});
}

onBeforeMount(async (): Promise<void> => {
	await fetchInterfaces();
});
</script>
