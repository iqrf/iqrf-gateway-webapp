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
	<v-alert
		v-if='bothIpStacksDisabled'
		type='error'
	>
		{{ $t("components.ipNetwork.connections.errors.ip.disabledBothIpStacks") }}
	</v-alert>
</template>

<script setup lang='ts'>
import {
	IPv4ConfigurationMethod,
	IPv6ConfigurationMethod,
	type NetworkConnectionConfiguration,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import { computed, type ComputedRef, type PropType } from 'vue';

/// Network connection configuration
const configuration = defineModel({
	type: Object as PropType<NetworkConnectionConfiguration>,
	required: true,
});
/// Are both IP stacks disabled?
const bothIpStacksDisabled: ComputedRef<boolean> = computed(() =>
	configuration.value.ipv4.method === IPv4ConfigurationMethod.DISABLED &&
	configuration.value.ipv6.method === IPv6ConfigurationMethod.DISABLED,
);
</script>
