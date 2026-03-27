<!--
Copyright 2017-2026 IQRF Tech s.r.o.
Copyright 2019-2026 MICRORISC s.r.o.

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
	<span :class='statusTextColor'>
		{{ statusText }}
	</span>
</template>

<script lang='ts' setup>
import { IqrfGatewayDaemonWsTransportModes } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const componentProps = defineProps<{
	mode: IqrfGatewayDaemonWsTransportModes;
}>();
const i18n = useI18n();


const statusText = computed<string>(() => {
	if (componentProps.mode === IqrfGatewayDaemonWsTransportModes.Tls) {
		return i18n.t('components.config.daemon.connections.ws.transportModes.tls');
	}
	if (componentProps.mode === IqrfGatewayDaemonWsTransportModes.Both) {
		return i18n.t('components.config.daemon.connections.ws.transportModes.both');
	}
	return i18n.t('components.config.daemon.connections.ws.transportModes.plain');
});

const statusTextColor = computed<string>(() => {
	if (componentProps.mode === IqrfGatewayDaemonWsTransportModes.Tls) {
		return 'text-success';
	}
	if (componentProps.mode === IqrfGatewayDaemonWsTransportModes.Both) {
		return 'text-warning';
	}
	return 'text-red';
});
</script>
