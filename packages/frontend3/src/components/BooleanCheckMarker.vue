<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
