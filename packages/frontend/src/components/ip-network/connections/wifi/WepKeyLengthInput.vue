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
	<ISelectInput
		v-model='modelValue'
		:items='items'
		:label='$t("components.ipNetwork.connections.form.wifi.security.wep.length")'
		:placeholder='$t("components.ipNetwork.connections.form.wifi.security.wep.lengths.null")'
		required
		:prepend-inner-icon='mdiKey'
	/>
</template>

<script setup lang='ts'>
import {
	WepKeyLen,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import { ISelectInput } from '@iqrf/iqrf-vue-ui';
import { mdiKey } from '@mdi/js';
import { computed, type ComputedRef } from 'vue';
import { useI18n } from 'vue-i18n';

import { type SelectItem } from '@/types/vuetify';

/// Model value
const modelValue = defineModel<WepKeyLen | null>({
	required: true,
});

const i18n = useI18n();
/// WEP key lengths
const items: ComputedRef<SelectItem[]> = computed((): SelectItem[] => [
	{
		value: WepKeyLen.BIT128,
		title: i18n.t('components.ipNetwork.connections.form.wifi.security.wep.lengths.128bit'),
	},
	{
		value: WepKeyLen.BIT64,
		title: i18n.t('components.ipNetwork.connections.form.wifi.security.wep.lengths.64bit'),
	},
]);
</script>
