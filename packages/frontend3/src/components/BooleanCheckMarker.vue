<template>
	<v-icon
		:color='color'
		:icon='icon'
		:size='size'
	/>
</template>

<script lang='ts' setup>
import { mdiCheckCircleOutline, mdiCloseCircleOutline } from '@mdi/js';
import { computed } from 'vue';

const componentProps = defineProps({
	value: {
		type: Boolean,
		default: false,
		required: false,
	},
	size: {
		type: String,
		default: undefined,
		required: false,
	},
});

const size = computed(() => {
	return componentProps.size ?? 'large';
});
const icon = computed(() => {
	return componentProps.value ? mdiCheckCircleOutline : mdiCloseCircleOutline;
});
const color = computed(() => {
	return componentProps.value ? 'success' : 'red';
});

</script>
