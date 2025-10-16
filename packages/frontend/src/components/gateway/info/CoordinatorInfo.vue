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
	<div class='py-2'>
		<strong>{{ `${$t('components.gateway.information.tr.moduleType')}:` }} </strong>
		{{ data.osRead.trMcuType.trType }}<br>
		<strong>{{ `${$t('components.gateway.information.tr.mcuType')}:` }} </strong>
		{{ data.osRead.trMcuType.mcuType }}<br>
		<strong>{{ `${$t('components.gateway.information.tr.moduleId')}:` }} </strong>
		{{ `${data.osRead.mid} [${Number.parseInt(data.osRead.mid, 16)}]` }}<br>
		<strong>{{ `${$t('components.gateway.information.tr.os')}:` }} </strong>
		{{ `${data.osRead.osVersion} (${data.osRead.osBuild})` }}<br>
		<strong>{{ `${$t('components.gateway.information.tr.dpa')}:` }} </strong>
		{{ data.peripheralEnumeration.dpaVer }}<br>
		<strong>{{ `${$t('components.gateway.information.tr.hwpid')}:` }} </strong>
		{{ `${data.peripheralEnumeration.hwpId.toString(16).padStart(4, '0')} [${data.peripheralEnumeration.hwpId}]` }}<br>
		<strong>{{ `${$t('components.gateway.information.tr.hwpidVersion')}:` }} </strong>
		{{ `${data.peripheralEnumeration.hwpIdVer & 0x00FF}.${data.peripheralEnumeration.hwpIdVer & 0xFF00}` }}<br>
		<strong>{{ `${$t('components.gateway.information.tr.voltage')}:` }} </strong>
		{{ data.osRead.supplyVoltage }}<br>
	</div>
</template>

<script lang='ts' setup>
import { PropType } from 'vue';

import { DeviceEnumeration } from '@/types/DaemonApi/Iqmesh';

defineProps({
	data: {
		type: Object as PropType<DeviceEnumeration>,
		required: true,
	},
});

</script>
