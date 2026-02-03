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
	<IModalWindow
		v-model='show'
		persistent
		no-click-animation
		:scrim='false'
	>
		<ICard
			:action='Action.Custom'
			header-color='red'
		>
			<template #title>
				{{ $t('components.layout.overlay.proxyServer.title') }}
			</template>
			{{ $t('components.layout.overlay.proxyServer.text') }}
			<v-table
				class='simple-table mt-2'
				striped='odd'
			>
				<tbody>
					<tr>
						<th>{{ $t('components.layout.overlay.proxyServer.table.serverConnected') }}</th>
						<td><IBooleanIcon :value='proxyConnected' /></td>
					</tr>
					<tr>
						<th>{{ $t('components.layout.overlay.proxyServer.table.upstreamConnected') }}</th>
						<td><IBooleanIcon :value='upstreamReady' /></td>
					</tr>
					<tr v-if='upstreamReconnecting && upstreamReconnectTs'>
						<th>{{ $t('components.layout.overlay.proxyServer.table.upstreamReconnectDelay') }}</th>
						<td>{{ $d(upstreamReconnectTs, 'dateTime') }}</td>
					</tr>
				</tbody>
			</v-table>
			<template #actions>
				<v-spacer />
				<IActionBtn
					:action='Action.Close'
					@click='show = false'
				/>
			</template>
		</ICard>
	</IModalWindow>
</template>

<script setup lang='ts'>
import { Action, IActionBtn, IBooleanIcon, ICard, IModalWindow } from '@iqrf/iqrf-vue-ui';
import { storeToRefs } from 'pinia';
import { ref, type Ref } from 'vue';

import { useDaemonStore } from '@/store/daemonSocket';

const daemonStore = useDaemonStore();
const {
	isConnected: proxyConnected,
	isUpstreamReady: upstreamReady,
	isUpstreamReconnecting: upstreamReconnecting,
	upstreamReconnectTs,
} = storeToRefs(daemonStore);
const show: Ref<boolean> = ref(true);

</script>
