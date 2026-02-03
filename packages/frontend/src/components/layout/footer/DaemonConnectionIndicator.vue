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
	<v-tooltip location='bottom'>
		<template #activator='{ props }'>
			<v-icon
				v-bind='props'
				class='me-2'
				:color='color'
				:icon='mdiConnection'
				size='small'
			/>
		</template>
		<div class='d-flex justify-space-between'>
			<span class='text-left'>{{ $t('components.status.daemonApi.proxy.status') }}:&nbsp;</span>
			<span class='text-right'>{{ proxyStatusText }}</span>
		</div>
		<div class='d-flex justify-space-between'>
			<span class='text-left'>{{ $t('components.status.daemonApi.upstream.status') }}:&nbsp;</span>
			<span class='text-right'>{{ upstreamStatusText }}</span>
		</div>
		<div
			v-if='upstreamStatus === UpstreamStatus.RECONNECTING && upstreamReconnectTs !== null'
			class='d-flex justify-space-between'
		>
			<span class='text-left'>{{ $t('components.status.daemonApi.upstream.nextAttempt') }}:&nbsp;</span>
			<span class='text-right'>{{ $d(new Date(upstreamReconnectTs), 'dateTime') }}</span>
		</div>
	</v-tooltip>
</template>

<script lang='ts' setup>
import { mdiConnection } from '@mdi/js';
import { storeToRefs } from 'pinia';
import { computed, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';

import { useDaemonStore } from '@/store/daemonSocket';
import { UpstreamStatus, upstreamStatusI18nKeys } from '@/types/proxy';

const i18n = useI18n();

const daemonSocket = useDaemonStore();
const {
	isConnected: proxyConnected,
	upstreamStatus,
	upstreamReconnectTs,
} = storeToRefs(daemonSocket);

const proxyStatusText = computed(() => {
	if (proxyConnected.value) {
		return i18n.t('components.status.daemonApi.proxy.values.connected');
	}
	return i18n.t('components.status.daemonApi.proxy.values.reconnecting');
});

const upstreamStatusText = computed(() => {
	return i18n.t(`components.status.daemonApi.upstream.values.${upstreamStatusI18nKeys[upstreamStatus.value]}`);
});

/// Color of the icon
const color: Ref<string> = computed((): string => {
	if (!proxyConnected.value) {
		return 'red-accent-3';
	}
	if (upstreamStatus.value !== UpstreamStatus.READY) {
		return 'warning';
	}
	return 'light-green-accent-3';
});

</script>
