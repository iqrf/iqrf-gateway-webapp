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
		:label='$t("components.ipNetwork.connections.form.ipv4.method")'
		:placeholder='$t("components.ipNetwork.connections.form.ipv4.methods.null")'
		required
		:prepend-inner-icon='mdiWrenchCog'
	/>
</template>
<script setup lang='ts'>
import {
	IPv4ConfigurationMethod,
	NetworkConnectionType,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import { mdiWrenchCog } from '@mdi/js';
import { computed, type ComputedRef, type PropType } from 'vue';
import { useI18n } from 'vue-i18n';

import SelectInput from '@/components/layout/form/SelectInput.vue';
import { type SelectItem } from '@/types/vuetify';

/// Model value
const modelValue = defineModel({
	type: [String, null] as PropType<IPv4ConfigurationMethod | null>,
	required: true,
});
/// Define props
const componentProps = defineProps({
	type: {
		type: [String, null] as PropType<NetworkConnectionType | null>,
		default: null,
		required: false,
	},
});

const i18n = useI18n();
/// Network interface items
const items: ComputedRef<SelectItem[]> = computed((): SelectItem[] => {
	let methods: IPv4ConfigurationMethod[] = [];
	if (componentProps.type === NetworkConnectionType.GSM) {
		methods = [
			IPv4ConfigurationMethod.AUTO,
			IPv4ConfigurationMethod.DISABLED,
		];
	} else {
		methods = [
			IPv4ConfigurationMethod.AUTO,
			IPv4ConfigurationMethod.MANUAL,
			IPv4ConfigurationMethod.SHARED,
		];
	}
	return methods.map((method: IPv4ConfigurationMethod): SelectItem => ({
		value: method.toString(),
		title: i18n.t(`components.ipNetwork.connections.form.ipv4.methods.${method.toString()}`),
	}));
});
</script>
