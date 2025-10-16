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
		<div class='d-inline'>
			<strong>{{ `${usage.fsName} (${usage.fsType}): ` }}</strong>
			{{ $t('components.gateway.information.usages.used') }}
			{{ usage.usage.replace('%', ' %') }}
			{{ `${usage.used} / ${usage.size}` }}
			<v-progress-linear
				:model-value='usage.usage'
				:color='color'
				height='10'
				rounded
			/>
		</div>
		<br v-if='!last'>
	</div>
</template>

<script lang='ts' setup>
import { type FileSystemUsage } from '@iqrf/iqrf-gateway-webapp-client/types/Gateway';
import { computed, type PropType } from 'vue';

const props = defineProps({
	usage: {
		type: Object as PropType<FileSystemUsage>,
		required: true,
	},
	last: {
		type: Boolean,
		default: false,
		required: false,
	},
});

const color = computed(() => {
	const percentage = Number.parseFloat(
		props.usage.usage.replace('%', ''),
	);
	if (percentage >= 90) {
		return 'error';
	} else if (percentage >= 80) {
		return 'warning';
	} else {
		return 'success';
	}
});

</script>
