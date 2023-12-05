<template>
	<v-text-field
		v-bind='$attrs'
		:label='label'
		:hint='description ?? undefined'
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
	</v-text-field>
</template>

<script lang='ts' setup>
import { type PropType , computed } from 'vue';
import { VTextField } from 'vuetify/components';

const props = defineProps({
	label: {
		type: String,
		default: '',
		required: false,
	},
	description: {
		type: [String, null] as PropType<string | null>,
		default: null,
		required: false,
	},
});

const showHint = computed(() => props.description !== null && props.description.length > 0);

</script>
