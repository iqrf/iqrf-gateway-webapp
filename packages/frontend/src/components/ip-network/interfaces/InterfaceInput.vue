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
	<SelectInput
		v-model='modelValue'
		:items='items'
		:loading='componentState === ComponentState.Loading'
		:label='$t("components.ipNetwork.connections.form.generic.interface")'
		:rules='[
			(v: string|null) => ValidationRules.required(v, $t("components.ipNetwork.connections.errors.generic.interface.required")),
		]'
		required
		:prepend-inner-icon='mdiExpansionCardVariant'
	/>
</template>

<script setup lang='ts'>
import {
	type NetworkInterface, type NetworkInterfaceType,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import { ComponentState, ValidationRules } from '@iqrf/iqrf-vue-ui';
import { mdiExpansionCardVariant } from '@mdi/js';
import { onBeforeMount, type PropType, ref, type Ref } from 'vue';

import SelectInput from '@/components/layout/form/SelectInput.vue';
import { useApiClient } from '@/services/ApiClient';
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
	const interfaces: NetworkInterface[] = await service.list(componentProps.type);
	for (const item of interfaces) {
		let label = item.name;
		if (item.manufacturer !== null && item.model !== null) {
			label = `${item.name} (${item.manufacturer} ${item.model})`;
		}
		items.value.push({ title: label, value: item.name });
	}
	componentState.value = ComponentState.Ready;
}

onBeforeMount(async (): Promise<void> => {
	await fetchInterfaces();
});
</script>
