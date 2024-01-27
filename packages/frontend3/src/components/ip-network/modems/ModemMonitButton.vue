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
import {
	type MonitCheck,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config/Monit';
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
		.then((response: MonitCheck): MonitCheck => (monitCheck.value = response));
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
