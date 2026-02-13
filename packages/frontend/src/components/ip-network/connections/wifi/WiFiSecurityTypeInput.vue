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
		:label='$t("components.ipNetwork.connections.form.wifi.security.type")'
		:placeholder='$t("components.ipNetwork.connections.form.wifi.security.types.null")'
		:rules='[
			(v: string|null) => ValidationRules.required(v, $t("components.ipNetwork.connections.errors.wifi.security.type.required")),
		]'
		required
		:prepend-inner-icon='mdiWifiLock'
	/>
</template>

<script setup lang='ts'>
import {
	WifiSecurityType,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import { ISelectInput, ValidationRules } from '@iqrf/iqrf-vue-ui';
import { mdiWifiLock } from '@mdi/js';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

import { type SelectItem } from '@/types/vuetify';

/// Model value
const modelValue = defineModel<string | null>({
	required: true,
});
const i18n = useI18n();
/// Wi-Fi security types
const items = computed((): SelectItem[] => [
	{
		value: WifiSecurityType.LEAP,
		title: i18n.t('components.ipNetwork.connections.form.wifi.security.types.leap'),
	},
	{
		value: WifiSecurityType.Open,
		title: i18n.t('components.ipNetwork.connections.form.wifi.security.types.open'),
	},
	{
		value: WifiSecurityType.WEP,
		title: i18n.t('components.ipNetwork.connections.form.wifi.security.types.wep'),
	},
	{
		value: WifiSecurityType.WPA_EAP,
		title: i18n.t('components.ipNetwork.connections.form.wifi.security.types.wpa-eap'),
	},
	{
		value: WifiSecurityType.WPA_PSK,
		title: i18n.t('components.ipNetwork.connections.form.wifi.security.types.wpa-psk'),
	},
]);
</script>
