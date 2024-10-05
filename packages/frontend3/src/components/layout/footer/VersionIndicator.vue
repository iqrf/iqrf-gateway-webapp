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
		v-tooltip:bottom='`${$t("components.status.monitor.version") }: ${ daemonVersion}`'
		class='me-2'
		color='info'
		:icon='mdiInformation'
		size='small'
	/>
</template>

<script lang='ts' setup>
import { mdiInformation } from '@mdi/js';
import { storeToRefs } from 'pinia';
import { computed } from 'vue';

import { useDaemonStore } from '@/store/daemonSocket';

const daemonStore = useDaemonStore();
const { getVersion: daemonVersionRaw } = storeToRefs(daemonStore);

/// IQRF Gateway Daemon version
const daemonVersion = computed(() => {
	return daemonVersionRaw.value.length > 0 ? daemonVersionRaw.value : 'N/A';
});
</script>
