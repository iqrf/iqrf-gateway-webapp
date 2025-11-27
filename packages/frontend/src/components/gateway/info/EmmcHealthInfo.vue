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
		<div class='d-inline' if='emmcHealth.slc_region'>
			{{ $t('components.gateway.information.emmcHealth.slc_region') }}
			{{ emmcHealth.slc_region }}
			<v-progress-linear
				:model-value='emmcHealth.slc_region || undefined'
				:color='slc_color'
				height='10'
				rounded
			/>
		</div>
		<div class='d-inline' if='emmcHealth.mlc_region'>
			{{ $t('components.gateway.information.emmcHealth.mlc_region') }}
			{{ emmcHealth.mlc_region }}
			<v-progress-linear
				:model-value='emmcHealth.mlc_region || undefined'
				:color='mlc_color'
				height='10'
				rounded
			/>
		</div>
		<div class='d-inline' if='emmcHealth.status'>
			{{ $t('components.gateway.information.emmcHealth.preEol_info') }}
			{{ $t(`components.gateway.information.emmcHealth.preEolMessages.${emmcHealth.status}`) }}
		</div>
		<br v-if='!last'>
	</div>
</template>

<script lang='ts' setup>
import { type EmmcHealth } from '@iqrf/iqrf-gateway-webapp-client/types/Gateway';
import { computed, type PropType } from 'vue';

const componentProps = defineProps({
	emmcHealth: {
		type: Object as PropType<EmmcHealth>,
		required: true,
	},
	last: {
		type: Boolean,
		default: false,
		required: false,
	},
});

const slc_color = computed(() => {
	if (!componentProps.emmcHealth.slc_region) {
		return 'success';
	}
	const slc_percentage = Number.parseFloat(
		componentProps.emmcHealth.slc_region.replace('%', ''),
	);
	if (slc_percentage <= 10) {
		return 'error';
	} else if (slc_percentage <= 20) {
		return 'warning';
	} else {
		return 'success';
	}
});
const mlc_color = computed(() => {
	if (!componentProps.emmcHealth.mlc_region) {
		return 'success';
	}
	const mlc_percentage = Number.parseFloat(
		componentProps.emmcHealth.mlc_region.replace('%', ''),
	);
	if (mlc_percentage <= 10) {
		return 'error';
	} else if (mlc_percentage <= 20) {
		return 'warning';
	} else {
		return 'success';
	}
});


</script>
