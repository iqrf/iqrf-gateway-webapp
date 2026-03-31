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
		:label='$t("components.config.daemon.connections.ws.transportMode")'
		:prepend-inner-icon='mdiSecurityNetwork'
		:hint='hint'
		persistent-hint
	/>
</template>

<script setup lang='ts'>
import { IqrfGatewayDaemonWsTransportModes } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { ISelectInput } from '@iqrf/iqrf-vue-ui';
import { mdiSecurityNetwork } from '@mdi/js';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

import { SelectOption } from '@/types/vuetify';

const modelValue = defineModel<IqrfGatewayDaemonWsTransportModes>({
	required: true,
});
const i18n = useI18n();

const items = computed<SelectOption<IqrfGatewayDaemonWsTransportModes>[]>(() => [
	{
		title: i18n.t('components.config.daemon.connections.ws.transportModes.plain'),
		value: IqrfGatewayDaemonWsTransportModes.Plain,
	},
	{
		title: i18n.t('components.config.daemon.connections.ws.transportModes.both'),
		value: IqrfGatewayDaemonWsTransportModes.Both,
	},
	{
		title: i18n.t('components.config.daemon.connections.ws.transportModes.tls'),
		value: IqrfGatewayDaemonWsTransportModes.Tls,
	},
]);
const hint = computed<string>(() => {
	const data: Record<IqrfGatewayDaemonWsTransportModes, string> = {
		[IqrfGatewayDaemonWsTransportModes.Plain]: i18n.t('components.config.daemon.connections.ws.notes.transportModes.plain'),
		[IqrfGatewayDaemonWsTransportModes.Both]: i18n.t('components.config.daemon.connections.ws.notes.transportModes.both'),
		[IqrfGatewayDaemonWsTransportModes.Tls]: i18n.t('components.config.daemon.connections.ws.notes.transportModes.tls'),
	};
	return data[modelValue.value] ?? '';
});
</script>

