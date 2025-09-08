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
	<div>
		<VersionIndicator />
		<DaemonConnectionIndicator />
		<DaemonModeIndicator />
		<DaemonQueueIndicator />
		<EnumerationIndicator />
		<SessionIndicator v-if='loggedIn' />
	</div>
</template>

<script lang='ts' setup>
import { storeToRefs } from 'pinia';

import DaemonConnectionIndicator from '@/components/layout/footer/DaemonConnectionIndicator.vue';
import DaemonModeIndicator from '@/components/layout/footer/DaemonModeIndicator.vue';
import DaemonQueueIndicator from '@/components/layout/footer/DaemonQueueIndicator.vue';
import EnumerationIndicator from '@/components/layout/footer/EnumerationIndicator.vue';
import SessionIndicator from '@/components/layout/footer/SessionIndicator.vue';
import VersionIndicator from '@/components/layout/footer/VersionIndicator.vue';
import { useUserStore } from '@/store/user';

const userStore = useUserStore();
const { isLoggedIn: loggedIn } = storeToRefs(userStore);
</script>
