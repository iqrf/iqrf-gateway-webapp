<template>
	<v-data-table
		v-model:expanded='expanded'
		v-model:page='page'
		:items-per-page='itemsPerPage'
		:headers='headers'
		:items='items'
		:items-length='items.length'
		:loading='loading'
		:hover='hover'
		:density='dense ? "compact" : "default"'
	>
		<template #bottom>
			<div
				v-if='items.length > 0 && !hidePagination'
				class='text-center pt-1'
			>
				<v-pagination
					v-model='page'
					density='comfortable'
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
import { computed, ref, type Ref } from 'vue';

const componentProps = defineProps({
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
	itemsPerPage: {
		type: Number,
		default: 10,
		required: false,
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
	hidePagination: {
		type: Boolean,
		default: false,
		required: false,
	},
});

const expanded = ref([]);
const page: Ref<number> = ref(1);
const pageCount = computed(() => {
	return Math.ceil(componentProps.items.length / componentProps.itemsPerPage);
});
</script>
