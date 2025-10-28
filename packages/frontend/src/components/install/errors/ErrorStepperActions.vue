<template>
	<div>
		<IActionBtn
			v-if='componentProps.index !== totalErrors'
			:action='Action.Next'
			container-type='card'
			@click='componentProps.next'
		/>
		<IActionBtn
			v-if='componentProps.index !== 1'
			:action='Action.Previous'
			class='float-right'
			container-type='card'
			@click='componentProps.prev'
		/>
	</div>
</template>

<script setup lang='ts'>
import { Action, IActionBtn } from '@iqrf/iqrf-vue-ui';
import { storeToRefs } from 'pinia';
import { computed, type ComputedRef, PropType } from 'vue';

import { useInstallStore } from '@/store/install';

const componentProps = defineProps({
	index: {
		type: Number,
		required: true,
	},
	prev: {
		type: Function as PropType<(...args: any[]) => any>,
		required: true,
	},
	next: {
		type: Function as PropType<(...args: any[]) => any>,
		required: true,
	},
});
const installStore = useInstallStore();
const { getErrors } = storeToRefs(installStore);

/// Number of total errors
const totalErrors: ComputedRef<number> = computed((): number => {
	if (getErrors.value === null) {
		return 0;
	}
	return Object.values(getErrors.value).reduce((acc: number, val: boolean): number => acc + (val ? 1 : 0), 0);
});
</script>
