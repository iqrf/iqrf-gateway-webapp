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
	<div v-if='configuration.serial'>
		<h2 class='mb-3 text-h6'>
			{{ $t("components.ipNetwork.connections.form.serial.title") }}
		</h2>
		<v-text-field
			v-model.number='configuration.serial.baudRate'
			:label='$t("components.ipNetwork.connections.form.serial.baudRate")'
			:rules='[
				(v: unknown) => ValidationRules.required(v, $t("components.ipNetwork.connections.errors.serial.baudRate.required")),
				(v: unknown) => ValidationRules.numeric(v, $t("components.ipNetwork.connections.errors.serial.baudRate.numerical")),
			]'
			required
			:prepend-inner-icon='mdiSerialPort'
		/>
	</div>
</template>

<script setup lang='ts'>
import {
	type NetworkConnectionConfiguration,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import { ValidationRules } from '@iqrf/iqrf-vue-ui';
import { mdiSerialPort } from '@mdi/js';
import { type PropType } from 'vue';

/// Network connection configuration
const configuration = defineModel({
	type: Object as PropType<NetworkConnectionConfiguration>,
	required: true,
});
</script>
