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
	<div v-if='configuration?.wifi?.security'>
		<IPasswordInput
			v-model='configuration.wifi.security.psk'
			:label='$t("components.ipNetwork.connections.form.wifi.security.psk")'
			:rules='[
				(v: unknown) => ValidationRules.required(v, $t("components.ipNetwork.connections.errors.wifi.security.psk.required")),
			]'
			required
			:prepend-inner-icon='mdiKeyWireless'
		/>
	</div>
</template>

<script setup lang='ts'>
import {
	type NetworkConnectionConfiguration,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import {
	IPasswordInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiKeyWireless } from '@mdi/js';
import { type PropType } from 'vue';

/// Network connection configuration
const configuration = defineModel({
	type: Object as PropType<NetworkConnectionConfiguration>,
	required: true,
});
</script>
