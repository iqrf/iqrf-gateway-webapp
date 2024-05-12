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
		v-tooltip:bottom='$t("components.status.sessionExpiration.indicator") + ": " + remaining'
		:color='color'
		:icon='mdiTimerSand'
		size='small'
	/>
</template>

<script lang='ts' setup>
import { mdiTimerSand } from '@mdi/js';
import { storeToRefs } from 'pinia';
import { computed, onMounted, ref, type Ref , onBeforeUnmount } from 'vue';

import TimeConverter from '@/helpers/TimeConverter';
import { useUserStore } from '@/store/user';

const userStore = useUserStore();
const { getExpiration: expiration } = storeToRefs(userStore);
const now: Ref<number> = ref(0);
let interval = 0;

onMounted(() => {
	interval = window.setInterval(() => {
		now.value = Date.now();
	}, 1000);
});

onBeforeUnmount(() => {
	window.clearInterval(interval);
});

/// Remaining time of the session
const remaining = computed((): string => {
	const minutes = Math.ceil(expiration.value - (now.value / 1000));
	return TimeConverter.secondsToDuration(minutes);
});

/// Color of the icon
const color = computed((): string => {
	if (expiration.value - (now.value / 1000) > 5) {
		return 'light-green-accent-3';
	}
	return 'warning';
});
</script>
