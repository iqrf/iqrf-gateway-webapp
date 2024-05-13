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
	<v-progress-linear
		v-if='signal !== undefined'
		:model-value='signal'
		:color='color'
		height='16pt'
		rounded
	>
		<span>{{ Math.ceil(signal) }}%</span>
	</v-progress-linear>
</template>

<script setup lang='ts'>
import { computed } from 'vue';

/// Signal strength indicator props
const componentProps = defineProps({
	signal: {
		type: Number,
		required: true,
	},
});

/// Indicator color
const color = computed(() => {
	if (componentProps.signal >= 67) {
		return 'success';
	} else if (componentProps.signal >= 34) {
		return 'warning';
	} else {
		return 'error';
	}
});
</script>

<style lang='scss' scoped>
span {
	color: white;
}
</style>
