<template>
	<v-progress-linear
		v-if='signal !== undefined'
		:model-value='signal'
		:color='color'
		height='16pt'
		rounded
	>
		<span>{{ Math.ceil(signal) }}%</span>
	</v-progress-linear>
</template>

<script setup lang='ts'>
import { computed } from 'vue';

/// Signal strength indicator props
const componentProps = defineProps({
	signal: {
		type: Number,
		required: true,
	},
});

/// Indicator color
const color = computed(() => {
	if (componentProps.signal >= 67) {
		return 'success';
	} else if (componentProps.signal >= 34) {
		return 'warning';
	} else {
		return 'error';
	}
});
</script>

<style lang='scss' scoped>
span {
	color: white;
}
</style>
