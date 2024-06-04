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
	<TheHeader />
	<TheSidebar />
	<v-main>
		<UnverifiedEmailAlert />
		<v-container fluid>
			<router-view v-if='isAllowed' />
			<Forbidden v-else />
		</v-container>
	</v-main>
	<TheFooter />
</template>

<script lang='ts' setup>
import { type Feature, type UserRole } from '@iqrf/iqrf-gateway-webapp-client/types';
import { storeToRefs } from 'pinia';
import { computed, type Ref } from 'vue';
import { useRoute } from 'vue-router';

import Forbidden from '@/components/errors/Forbidden.vue';
import TheFooter from '@/components/layout/TheFooter.vue';
import TheHeader from '@/components/layout/TheHeader.vue';
import TheSidebar from '@/components/layout/TheSidebar.vue';
import UnverifiedEmailAlert from '@/components/layout/UnverifiedEmailAlert.vue';
import { useFeatureStore } from '@/store/features';
import { useUserStore } from '@/store/user';

const featureStore = useFeatureStore();
const route = useRoute();
const userStore = useUserStore();

const { getRole: role } = storeToRefs(userStore);
const { isLoggedIn } = storeToRefs(userStore);
const developmentOnly: Ref<boolean> = computed((): boolean => (route.meta.developmentOnly ?? false) as boolean);
const requiresAuth: Ref<boolean> = computed((): boolean => (route.meta.requiresAuth ?? true) as boolean);
const requiredFeature: Ref<Feature | null> = computed((): Feature | null => (route.meta.feature ?? null) as Feature | null);
const requiredRoles: Ref<UserRole[]> = computed((): UserRole[] => (route.meta.roles ?? []) as UserRole[]);
const isAllowed: Ref<boolean> = computed((): boolean => {
	if (developmentOnly.value && import.meta.env.PROD) {
		return false;
	}
	if (
		(requiredFeature.value !== null && !featureStore.isEnabled(requiredFeature.value)) ||
		(requiresAuth.value && !isLoggedIn.value)
	) {
		return false;
	}
	return !requiresAuth.value || requiredRoles.value.length === 0 ||
		(role.value !== null && requiredRoles.value.includes(role.value));
});
</script>
