<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

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
	<span v-if='loading'>
		<v-progress-linear
			color='info'
			indeterminate
			rounded
		/>
	</span>
	<span v-else>
		<span v-if='loaded'>
			<strong>{{ $t('gateway.info.tr.moduleType') }}: </strong>
			{{ trMcuType.trType }}<br>
			<strong>{{ $t('gateway.info.tr.mcuType') }}: </strong>
			{{ trMcuType.mcuType }}<br>
			<strong>{{ $t('gateway.info.tr.moduleId') }}: </strong>
			{{ osRead.mid }}<br>
			<strong>{{ $t('gateway.info.tr.os') }}: </strong>
			{{ osRead.osVersion }} ({{ osRead.osBuild }})<br>
			<strong>{{ $t('gateway.info.tr.dpa') }}: </strong>
			{{ enumeration.dpaVer }}<br>
			<strong>{{ $t('gateway.info.tr.hwpid') }}: </strong>
			{{ enumeration.hwpId }} ({{ enumeration.hwpId.toString(16).padStart(4, '0') }})<br>
			<strong>{{ $t('gateway.info.tr.hwpidVersion') }}: </strong>
			{{ enumeration.hwpIdVer }}<br>
			<strong>{{ $t('gateway.info.tr.voltage') }}: </strong>
			{{ osRead.supplyVoltage }}<br>
		</span>
		<span v-else>
			{{ $t('gateway.info.tr.error') }}
		</span>
	</span>
</template>

<script lang='ts' setup>
import { ref, Ref } from 'vue';
import { useDaemonStore } from '@/store/daemonSocket';
import { DaemonApiResponse, DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils';
import { DeviceEnumeration, OsRead, PeripheralEnumeration, TrMcuType } from '@/types/DaemonApi/Iqmesh';
import { IqmeshNetworkService } from '@/services/DaemonApi/IqmeshNetworkService';
import { onMounted } from 'vue';

const daemonStore = useDaemonStore();
const service: IqmeshNetworkService = new IqmeshNetworkService();
const loading: Ref<boolean> = ref(false);
const loaded: Ref<boolean> = ref(false);
const enumeration: Ref<PeripheralEnumeration | null> = ref(null);
const osRead: Ref<OsRead | null> = ref(null);
const trMcuType: Ref<TrMcuType |null> = ref(null);
let msgId = '';

daemonStore.$onAction(
	({ name, after}) => {
		if (name === 'onMessage') {
			after((rsp: DaemonApiResponse) => {
				if (rsp.data.msgId !== msgId) {
					return;
				}
				daemonStore.removeMessage(msgId);
				try {
					const data = rsp.data.rsp as DeviceEnumeration;
					enumeration.value = data.peripheralEnumeration;
					osRead.value = data.osRead;
					trMcuType.value = data.osRead.trMcuType;
					loaded.value = true;
					loading.value = false;
				} catch (e) {
					loaded.value = false;
					loading.value = false;
				}
			});
		}
	}
);

onMounted(() => {
	enumerate();
});

function enumerate(): void {
	const options = new DaemonMessageOptions(
		null,
		5000,
		'todo',
		() => {
			msgId = '';
			loaded.value = false;
			loading.value = false;
		}
	);
	service.deviceEnumeration(0, options)
		.then((id: string) => {
			msgId = id;
			loaded.value = false;
			loading.value = true;
		});
}

</script>
