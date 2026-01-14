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
	<div>
		<EmmcRegionHealthInfo :region-health='emmcHealth.slcRegion' :region-name='$t("components.gateway.information.emmcHealth.slcRegion")' />
		<EmmcRegionHealthInfo :region-health='emmcHealth.mlcRegion' :region-name='$t("components.gateway.information.emmcHealth.mlcRegion")' />
		<div class='d-inline'>
			{{ $t('components.gateway.information.emmcHealth.preEolInfo') }}
			{{ $t(`components.gateway.information.emmcHealth.preEolMessages.${emmcHealth.status}`) }}
		</div>
		<br v-if='!last'>
	</div>
</template>

<script lang='ts' setup>
import { type EmmcHealth } from '@iqrf/iqrf-gateway-webapp-client/types/Gateway';

import EmmcRegionHealthInfo from './EmmcRegionHealthInfo.vue';

const componentProps = withDefaults(
	defineProps<{
		/// EMMC health information
		emmcHealth: EmmcHealth;
		/// Is last item
		last?: boolean;
	}>(),
	{
		last: false,
	},
);
</script>
