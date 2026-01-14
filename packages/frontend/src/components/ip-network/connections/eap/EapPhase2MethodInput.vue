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
		v-if='items.length > 0'
		v-model='modelValue'
		:items='items'
		:label='$t("components.ipNetwork.connections.form.eap.phaseTwoMethod")'
		:placeholder='$t("components.ipNetwork.connections.form.eap.phaseTwoMethods.null")'
		required
		:prepend-inner-icon='mdiKey'
	/>
</template>

<script setup lang='ts'>
import {
	EapPhaseOneMethod,
	EapPhaseTwoMethod,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import { mdiKey } from '@mdi/js';
import { computed, type ComputedRef } from 'vue';
import { useI18n } from 'vue-i18n';

import SelectInput from '@/components/layout/form/SelectInput.vue';
import { type SelectItem } from '@/types/vuetify';

/// Model value
const modelValue = defineModel<EapPhaseTwoMethod | null>({
	required: true,
});
/// Define props
const componentProps = withDefaults(
	defineProps<{
		phaseOne?: EapPhaseOneMethod | null;
	}>(),
	{
		phaseOne: null,
	},
);

const i18n = useI18n();
/// EAP phase two methods
const items: ComputedRef<SelectItem[]> = computed((): SelectItem[] => {
	let methods: EapPhaseTwoMethod[];
	switch (componentProps.phaseOne) {
		case EapPhaseOneMethod.FAST:
			methods = [EapPhaseTwoMethod.GTC, EapPhaseTwoMethod.MSCHAPV2];
			break;
		case EapPhaseOneMethod.PEAP:
			methods = [EapPhaseTwoMethod.GTC, EapPhaseTwoMethod.MD5, EapPhaseTwoMethod.MSCHAPV2];
			break;
		case EapPhaseOneMethod.LEAP:
		case EapPhaseOneMethod.PWD:
		case EapPhaseOneMethod.TLS:
			methods = [];
			break;
		case EapPhaseOneMethod.TTLS:
			methods = [EapPhaseTwoMethod.MSCHAPV2];
			break;
		default:
			methods = Object.values(EapPhaseTwoMethod);
	}
	return methods.map((method: EapPhaseTwoMethod): SelectItem => ({
		value: method.toString(),
		title: i18n.t(`components.ipNetwork.connections.form.eap.phaseTwoMethods.${method.toString()}`),
	}));
});
</script>
