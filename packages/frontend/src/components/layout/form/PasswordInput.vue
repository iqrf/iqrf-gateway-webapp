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
	<v-text-field
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
import { mdiEye, mdiEyeOff } from '@mdi/js';
import { computed, ref, type Ref } from 'vue';
import { VTextField } from 'vuetify/components';

const show: Ref<boolean> = ref(false);
const props = defineProps({
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
const visibilityIcon = computed(() => {
	return show.value ? mdiEye : mdiEyeOff;
});
const inputType = computed(() => {
	return show.value ? 'text' : 'password';
});
const showHint = computed(() => props.description.length > 0);

</script>
