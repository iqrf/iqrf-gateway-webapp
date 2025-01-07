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
	<CardTitleActionBtn
		v-if='componentState === ComponentState.Ready && monitCheck !== null'
		:action='monitCheck.enabled ? Action.Disable : Action.Enable'
		:color='monitCheck.enabled ? "error" : "success"'
		@click='toggleMonitCheck'
	/>
</template>
<script setup lang='ts'>
import { Feature } from '@iqrf/iqrf-gateway-webapp-client/types';
import { type MonitCheck } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { onBeforeMount, ref, type Ref } from 'vue';

import CardTitleActionBtn
	from '@/components/layout/card/CardTitleActionBtn.vue';
import { useApiClient } from '@/services/ApiClient';
import { useFeatureStore } from '@/store/features';
import { Action } from '@/types/Action';
import { ComponentState } from '@/types/ComponentState';

const featureStore = useFeatureStore();
const monitService = useApiClient().getConfigServices().getMonitService();

/// Component state
const componentState = ref(ComponentState.Loading);
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
	componentState.value = ComponentState.Loading;
	try {
		monitCheck.value = await monitService.getCheck(monitCheckName);
		componentState.value = ComponentState.Ready;
	} catch (error) {
		console.error(error);
		componentState.value = ComponentState.FetchFailed;
	}
}

/**
 * Toggles Monit check enablement
 * @param {boolean} confirmed Confirmed action
 */
function toggleMonitCheck(confirmed: boolean = false): void {
	if (monitCheck.value === null) {
		return;
	}
}

onBeforeMount(async () => await fetchMonitCheck());
</script>
