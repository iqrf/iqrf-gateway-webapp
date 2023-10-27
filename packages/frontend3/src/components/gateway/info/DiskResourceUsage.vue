<template>
	<div class='d-inline'>
		<strong>{{ usage.fsName }} ({{ usage.fsType }}):</strong>
		{{ $t('components.gateway.information.usages.used') }}
		{{ usage.usage.replace('%', ' %') }}
		({{ usage.used }} / {{ usage.size }})
		<v-progress-linear
			:model-value='usage.usage'
			:color='color'
			height='10'
			rounded
		/>
	</div>
	<br v-if='!last'>
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
		return 'danger';
	} else if (percentage >= 80) {
		return 'warning';
	} else {
		return 'success';
	}
});

</script>
