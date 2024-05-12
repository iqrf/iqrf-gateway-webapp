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
		v-tooltip:bottom='$t("components.status.monitor.notified") + ": " + $d(date, "long")'
		class='me-2'
		:color='color'
		:icon='mdiConnection'
		size='small'
	/>
</template>

<script lang='ts' setup>
import { mdiConnection } from '@mdi/js';
import { DateTime } from 'luxon';
import { storeToRefs } from 'pinia';
import { computed, type Ref } from 'vue';

import { useMonitorStore } from '@/store/monitorSocket';

const monitorStore = useMonitorStore();
const { connected } = storeToRefs(monitorStore);

/// Color of the icon
const color: Ref<string> = computed((): string => {
	return connected.value ? 'light-green-accent-3' : 'red-accent-3';
});

/// Date of the last notification
const date: Ref<Date> = computed((): Date => {
	return DateTime.fromSeconds(monitorStore.lastTimestamp).toJSDate();
});
</script>
