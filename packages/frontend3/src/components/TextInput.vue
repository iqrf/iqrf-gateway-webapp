<template>
	<v-text-field
		:model-value='modelValue'
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
	</v-text-field>
</template>

<script lang='ts' setup>
import { PropType } from 'vue';
import { computed } from 'vue';
import { VTextField } from 'vuetify/components';

const props = defineProps({
	modelValue: {
		type: null as unknown as PropType<string | number | null>,
		default: null,
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
	}
});

const showHint = computed(() => props.description.length > 0);

</script>
