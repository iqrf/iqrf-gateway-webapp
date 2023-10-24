<template>
	<div class='d-inline'>
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
</template>

<script lang='ts' setup>
import { computed, PropType } from 'vue';
import { UsageBase } from '@iqrf/iqrf-gateway-webapp-client/types/Gateway';

const props = defineProps({
	usage: {
		type: Object as PropType<UsageBase>,
		required: true,
	}
});

const color = computed(() => {
	const percentage = Number.parseFloat(
		props.usage.usage.replace('%', '')
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
