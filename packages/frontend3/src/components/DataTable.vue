<template>
	<v-data-table
		v-model:expanded='expanded'
		v-model:page='page'
		:items-per-page='pageItems'
		:headers='headers'
		:items='items'
		:items-length='items.length'
		:loading='loading'
		:hover='hover'
		:density='dense ? "compact" : "default"'
	>
		<template #bottom>
			<div
				v-if='items.length > 0'
				class='text-center pt-2'
			>
				<v-pagination
					v-model='page'
					:length='pageCount'
				/>
			</div>
		</template>
		<template v-for='(_, slot) in $slots' #[slot]='scope'>
			<slot :name='slot' v-bind='scope || {}' />
		</template>
	</v-data-table>
</template>

<script lang='ts' setup>
import { computed, ref, Ref } from 'vue';

const props = defineProps({
	headers: {
		type: Array,
		default: () => [],
		required: true,
	},
	items: {
		type: Array,
		default: () => [],
		required: true,
	},
	loading: {
		type: Boolean,
		default: false,
		required: false,
	},
	hover: {
		type: Boolean,
		default: false,
		required: false,
	},
	dense: {
		type: Boolean,
		default: false,
		required: false,
	},
});

const expanded = ref([]);
const page: Ref<number> = ref(1);
const pageCount = computed(() => {
	return Math.ceil(props.items.length / pageItems.value);
});
const pageItems: Ref<number> = ref(10);
</script>
