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
	<v-btn
		v-if='monitCheck !== null'
		:color='color'
		variant='elevated'
		:icon='icon'
		@click='toggleMonitCheck'
	/>
</template>
<script setup lang='ts'>
import { Feature } from '@iqrf/iqrf-gateway-webapp-client/types';
import { type MonitCheck } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { mdiPlay, mdiStop } from '@mdi/js';
import { computed, onBeforeMount, ref, type Ref } from 'vue';

import { useApiClient } from '@/services/ApiClient';
import { useFeatureStore } from '@/store/features';

const featureStore = useFeatureStore();
const monitService = useApiClient().getConfigServices().getMonitService();

/// Button color
const color = computed(() => {
	if (monitCheck.value === null) {
		return 'grey';
	}
	return monitCheck.value.enabled ? 'error' : 'success';
});
/// Button icon
const icon = computed(() => {
	if (monitCheck.value === null) {
		return 'mdiLoading';
	}
	return monitCheck.value.enabled ? mdiStop : mdiPlay;
});
/// Monit check configuration
const monitCheck: Ref<MonitCheck | null> = ref(null);
/// Monit check name
const monitCheckName = 'network_ppp0';

/**
 * Fetches Monit check
 */
async function fetchMonitCheck(): Promise<void> {
	if (!featureStore.isEnabled(Feature.monit)) {
		return;
	}
	await monitService.getCheck(monitCheckName)
		.then((response: MonitCheck): MonitCheck => monitCheck.value = response);
}

/**
 * Toggles Monit check enablement
 * @param confirmed
 */
function toggleMonitCheck(confirmed = false): void {
	if (monitCheck.value === null) {
		return;
	}
}

onBeforeMount(() => {
	fetchMonitCheck();
});
</script>
