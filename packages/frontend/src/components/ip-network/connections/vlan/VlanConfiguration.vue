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
	<div v-if='configuration.vlan'>
		<h2 class='mb-3 text-h6'>
			{{ $t('components.ipNetwork.connections.form.vlan.title') }}
		</h2>
		<InterfaceInput v-model='configuration.vlan.parentInterface' />
		<ITextInput
			v-model.number='configuration.vlan.id'
			:label='$t("components.ipNetwork.connections.form.vlan.id")'
			:rules='[
				(v: string|null) => ValidationRules.required(v, $t("components.ipNetwork.connections.errors.vlan.id.required")),
				(v: number) => ValidationRules.integer(v, $t("components.ipNetwork.connections.errors.vlan.id.invalid")),
				(v: number) => ValidationRules.min(v, 1, $t("components.ipNetwork.connections.errors.vlan.id.invalid")),
				(v: number) => ValidationRules.max(v, 4094, $t("components.ipNetwork.connections.errors.vlan.id.invalid")),
			]'
			required
			:prepend-inner-icon='mdiTag'
		/>
	</div>
</template>

<script setup lang='ts'>
import {
	type NetworkConnectionConfiguration,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import {
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiTag } from '@mdi/js';

import InterfaceInput
	from '@/components/ip-network/interfaces/InterfaceInput.vue';

/// Network connection configuration
const configuration = defineModel<NetworkConnectionConfiguration>({
	required: true,
});
</script>
