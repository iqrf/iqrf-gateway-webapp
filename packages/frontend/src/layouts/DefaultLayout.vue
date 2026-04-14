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
		<TheHeader />
		<TheSidebar />
		<v-main>
			<UnverifiedEmailAlert />
			<ProxyServerOverlay v-if='showProxyOverlay' />
			<ServiceModeOverlay v-if='showServiceOverlay' />
			<v-container fluid>
				<router-view v-if='isAllowed' />
				<Unavailable v-else-if='isUnavailable' />
				<Forbidden v-else />
			</v-container>
		</v-main>
		<TheFooter />
	</div>
</template>

<script lang='ts' setup>
import { DaemonMode } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { type Feature } from '@iqrf/iqrf-gateway-webapp-client/types';
import { AccessScope } from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import { storeToRefs } from 'pinia';
import { computed, type Ref } from 'vue';
import { useRoute } from 'vue-router';

import Forbidden from '@/components/errors/Forbidden.vue';
import Unavailable from '@/components/errors/Unavailable.vue';
import ProxyServerOverlay from '@/components/layout/ProxyServerOverlay.vue';
import ServiceModeOverlay from '@/components/layout/ServiceModeOverlay.vue';
import TheFooter from '@/components/layout/TheFooter.vue';
import TheHeader from '@/components/layout/TheHeader.vue';
import TheSidebar from '@/components/layout/TheSidebar.vue';
import UnverifiedEmailAlert from '@/components/layout/UnverifiedEmailAlert.vue';
import { useDaemonStore } from '@/store/daemonSocket';
import { useFeatureStore } from '@/store/features';
import { useMonitorStore } from '@/store/monitorSocket';
import { useUserStore } from '@/store/user';
import { UpstreamStatus } from '@/types/proxy';

const daemonStore = useDaemonStore();
const featureStore = useFeatureStore();
const route = useRoute();
const userStore = useUserStore();
const monitorStore = useMonitorStore();

const { isLoggedIn } = storeToRefs(userStore);
const developmentOnly: Ref<boolean> = computed((): boolean => (route.meta.developmentOnly ?? false) as boolean);
const requiresAuth: Ref<boolean> = computed((): boolean => (route.meta.requiresAuth ?? true) as boolean);
const requiredFeature: Ref<Feature | null> = computed((): Feature | null => (route.meta.feature ?? null) as Feature | null);
const requiresProxy: Ref<boolean> = computed((): boolean => (route.meta.requiresProxy ?? false) as boolean);
const isUnavailable = computed<boolean>((): boolean => (route.meta.unavailable ?? false) as boolean);
const requiredScopes: Ref<AccessScope[]> = computed((): AccessScope[] => (route.meta.scope ?? []) as AccessScope[]);
const isAllowed: Ref<boolean> = computed((): boolean => {
	if (isUnavailable.value) {
		return false;
	}
	if (developmentOnly.value && import.meta.env.PROD) {
		return false;
	}
	if (
		(requiredFeature.value !== null && !featureStore.isEnabled(requiredFeature.value)) ||
		(requiresAuth.value && !isLoggedIn.value)
	) {
		return false;
	}
	if (!requiresAuth.value || requiredScopes.value.length === 0) {
		return true;
	}
	return requiredScopes.value.every((scope) => userStore.hasScope(scope));
});
const isServiceWhitelisted: Ref<boolean> = computed((): boolean => (route.meta.isServiceWhitelisted ?? false) as boolean);
const showProxyOverlay: Ref<boolean> = computed((): boolean => {
	if (!requiresProxy.value) {
		return false;
	}
	return daemonStore.upstreamStatus !== UpstreamStatus.READY;
});
const showServiceOverlay: Ref<boolean> = computed((): boolean => {
	if (isServiceWhitelisted.value || !requiresProxy.value || daemonStore.upstreamStatus !== UpstreamStatus.READY) {
		return false;
	}
	return monitorStore.mode === DaemonMode.Service;
});
</script>
