<template>
	<div>
		<ICardActionBtn
			v-if='componentProps.index !== totalErrors'
			:action='Action.Next'
			@click='componentProps.next'
		/>
		<ICardActionBtn
			v-if='componentProps.index !== 1'
			:action='Action.Previous'
			class='float-right'
			@click='componentProps.prev'
		/>
	</div>
</template>
<script setup lang='ts'>
import { Action, ICardActionBtn } from '@iqrf/iqrf-vue-ui';
import { storeToRefs } from 'pinia';
import { computed, type ComputedRef, PropType } from 'vue';

import { useInstallStore } from '@/store/install';

const installStore = useInstallStore();
const { getErrors } = storeToRefs(installStore);

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
/// Number of total errors
const totalErrors: ComputedRef<number> = computed((): number => {
	if (getErrors.value === null) {
		return 0;
	}
	return Object.values(getErrors.value).reduce((acc: number, val: boolean): number => acc + (val ? 1 : 0), 0);
});
</script>
