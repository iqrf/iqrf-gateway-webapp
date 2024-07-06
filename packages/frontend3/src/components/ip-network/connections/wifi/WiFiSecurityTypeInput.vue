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
	<SelectInput
		v-model='modelValue'
		:items='items'
		:label='$t("components.ipNetwork.connections.fields.wifi.security.type").toString()'
		:rules='[
			(v: string|null) => ValidationRules.required(v, $t("components.ipNetwork.connections.validations.wifi.security.type.required")),
		]'
		required
		:prepend-inner-icon='mdiWifiLock'
	/>
</template>
<script setup lang='ts'>
import {
	WifiSecurityType,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import { mdiWifiLock } from '@mdi/js';
import { type PropType } from 'vue';
import { useI18n } from 'vue-i18n';

import SelectInput from '@/components/layout/form/SelectInput.vue';
import ValidationRules from '@/helpers/ValidationRules';
import { type SelectItem } from '@/types/vuetify';

/// Model value
const modelValue = defineModel({
	type: [String, null] as PropType<string | null>,
	required: true,
});
const i18n = useI18n();
/// Wi-Fi security types
const items: SelectItem[] =[
	{
		value: WifiSecurityType.LEAP.toString(),
		title: i18n.t('components.ipNetwork.connections.fields.wifi.security.types.leap').toString(),
	},
	{
		value: WifiSecurityType.Open.toString(),
		title: i18n.t('components.ipNetwork.connections.fields.wifi.security.types.open').toString(),
	},
	{
		value: WifiSecurityType.WEP.toString(),
		title: i18n.t('components.ipNetwork.connections.fields.wifi.security.types.wep').toString(),
	},
	{
		value: WifiSecurityType.WPA_EAP.toString(),
		title: i18n.t('components.ipNetwork.connections.fields.wifi.security.types.wpa-eap').toString(),
	},
	{
		value: WifiSecurityType.WPA_PSK.toString(),
		title: i18n.t('components.ipNetwork.connections.fields.wifi.security.types.wpa-psk').toString(),
	},
];
</script>
