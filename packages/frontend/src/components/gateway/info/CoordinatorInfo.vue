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
	<span v-if='componentState === ComponentState.Loading'>
		<v-progress-linear
			color='info'
			indeterminate
			rounded
		/>
	</span>
	<span v-else>
		<div v-if='enumData && componentState === ComponentState.Ready' class='py-2'>
			<strong>{{ `${$t('components.gateway.information.tr.moduleType')}:` }} </strong>
			{{ enumData.osRead.trMcuType.trType }}<br>
			<strong>{{ `${$t('components.gateway.information.tr.mcuType')}:` }} </strong>
			{{ enumData.osRead.trMcuType.mcuType }}<br>
			<strong>{{ `${$t('components.gateway.information.tr.moduleId')}:` }} </strong>
			{{ enumData.osRead.mid }}<br>
			<strong>{{ `${$t('components.gateway.information.tr.os')}:` }} </strong>
			{{ `${enumData.osRead.osVersion} (${enumData.osRead.osBuild})` }}<br>
			<strong>{{ `${$t('components.gateway.information.tr.dpa')}:` }} </strong>
			{{ enumData.peripheralEnumeration.dpaVer }}<br>
			<strong>{{ `${$t('components.gateway.information.tr.hwpid')}:` }} </strong>
			{{ `${enumData.peripheralEnumeration.hwpId.toString(16).padStart(4, '0')} (${enumData.peripheralEnumeration.hwpId})` }}<br>
			<strong>{{ `${$t('components.gateway.information.tr.hwpidVersion')}:` }} </strong>
			{{ `${enumData.peripheralEnumeration.hwpIdVer & 0x00FF}.${enumData.peripheralEnumeration.hwpIdVer & 0xFF00}` }}<br>
			<strong>{{ `${$t('components.gateway.information.tr.voltage')}:` }} </strong>
			{{ enumData.osRead.supplyVoltage }}<br>
		</div>
		<span v-else>
			{{ $t('components.gateway.information.messages.fetchTr.failed') }}
		</span>
	</span>
</template>

<script lang='ts' setup>
import { IqmeshServiceMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { IqmeshService } from '@iqrf/iqrf-gateway-daemon-utils/services';
import { ApiResponseIqmesh, IqmeshEnumerateDeviceResult, TApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { ComponentState } from '@iqrf/iqrf-vue-ui';
import { onBeforeMount, onBeforeUnmount, ref, type Ref } from 'vue';

import { useDaemonStore } from '@/store/daemonSocket';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const daemonStore = useDaemonStore();
const enumData: Ref<IqmeshEnumerateDeviceResult | null> = ref(null);
const msgId: Ref<string | null> = ref(null);

daemonStore.$onAction(
	({ name, after }) => {
		if (name === 'onMessage') {
			after((rsp: TApiResponse) => {
				if (rsp.data.msgId !== msgId.value) {
					return;
				}
				daemonStore.removeMessage(msgId.value);
				if (rsp.mType === IqmeshServiceMessages.EnumerateDevice) {
					handleEnumerateResponse(rsp as ApiResponseIqmesh<IqmeshEnumerateDeviceResult>);
				}
			});
		}
	},
);

onBeforeMount(() => {
	enumerate();
});

onBeforeUnmount(() => {
	daemonStore.removeMessage(msgId.value);
});

async function enumerate(): Promise<void> {
	componentState.value = ComponentState.Loading;
	const opts = new DaemonMessageOptions(
		10_000,
		'components.gateway.information.messages.fetchTr.timeout',
		() => {
			msgId.value = null;
			componentState.value = ComponentState.Idle;
		},
	);
	msgId.value = await daemonStore.sendMessage(IqmeshService.enumerate({}, { deviceAddr: 0 }, opts));
}

function handleEnumerateResponse(rsp: ApiResponseIqmesh<IqmeshEnumerateDeviceResult>): void {
	if (rsp.data.status === 0) {
		enumData.value = rsp.data.rsp;
		componentState.value = ComponentState.Ready;
	} else {
		componentState.value = ComponentState.FetchFailed;
	}
}

</script>
