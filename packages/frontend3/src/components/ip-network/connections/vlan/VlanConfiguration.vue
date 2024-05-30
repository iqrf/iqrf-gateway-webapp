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
import TextInput from '@/components/layout/form/TextInput.vue';
import ValidationRules from '@/helpers/ValidationRules';

const configuration = defineModel({
	type: Object as PropType<NetworkConnectionConfiguration>,
	required: true,
});
</script>
