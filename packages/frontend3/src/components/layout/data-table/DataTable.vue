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
	<v-data-table
		:expanded='expanded'
		:page='page'
		:items-per-page='itemsPerPage'
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
	items: {
		type: Array,
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
