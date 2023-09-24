<template>
	<v-text-field
		:model-value='modelValue'
		v-bind='$attrs'
		:label='label'
		:hint='description'
		:persistent-hint='showHint'
		:append-inner-icon='visibilityIcon'
		:type='inputType'
		@click:append-inner='show = !show'
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
import { computed, ref, Ref } from 'vue';
import { VTextField } from 'vuetify/components';
import { mdiEye, mdiEyeOff } from '@mdi/js';

const show: Ref<boolean> = ref(false);
const props = defineProps({
	modelValue: {
		type: String,
		default: '',
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
const visibilityIcon = computed(() => {
	return show.value ? mdiEye : mdiEyeOff;
});
const inputType = computed(() => {
	return show.value ? 'text' : 'password';
});
const showHint = computed(() => props.description.length > 0);

</script>
