<template>
	<v-select
		:items='items'
		v-bind='$attrs'
		:label='label'
		:hint='description'
		:persistent-hint='showHint'
	>
		<template #message='{ message }'>
			<div
				v-if='message !== description'
				class='extended-input-error'
			>
				{{ message }}
			</div>
			<div
				v-if='showHint'
				class='extended-input-description mb-4'
			>
				{{ description }}
			</div>
		</template>
		<template v-for='(_, slot) in $slots' #[slot]='scope'>
			<slot :name='slot' v-bind='scope || {}' />
		</template>
	</v-select>
</template>

<script lang='ts' setup>
import { computed } from 'vue';

import { type SelectItem } from '@/types/vuetify';

const props = defineProps({
	items: {
		type: Array<SelectItem>,
		default: () => [],
		required: true,
	},
	label: {
		type: String,
		default: '',
		required: false,
	},
	description: {
		type: String,
		default: '',
		required: false,
	},
});

const showHint = computed(() => props.description.length > 0);

</script>
