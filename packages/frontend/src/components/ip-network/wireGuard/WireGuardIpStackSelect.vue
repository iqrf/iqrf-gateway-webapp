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
	<v-select
		v-model='modelValue'
		:label='$t("components.ipNetwork.wireGuard.tunnels.configuration.form.stack")'
		:items='options'
	/>
</template>

<script setup lang="ts">
import {
	WireGuardIpStack,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import { computed } from 'vue';

/// Component model value
const modelValue = defineModel<WireGuardIpStack|undefined>({
	required: true,
});

/// Select options
const options = computed(() => [
	{
		title: 'IPv4',
		value: WireGuardIpStack.IPV4,
	},
	{
		title: 'IPv6',
		value: WireGuardIpStack.IPV6,
	},
	{
		title: 'Dual Stack',
		value: WireGuardIpStack.DUAL,
	},
]);
</script>


