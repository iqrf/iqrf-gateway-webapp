/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

import { setupLayouts } from 'virtual:generated-layouts';
import generatedRoutes from 'virtual:generated-pages';
import { createRouter, createWebHistory, type NavigationGuardNext, type RouteLocationNormalized, type RouteRecordRaw } from 'vue-router';

import { useUserStore } from '@/store/user';

const routes: RouteRecordRaw[] = [
	...setupLayouts(generatedRoutes),
];

const router = createRouter({
	history: createWebHistory(import.meta.env.BASE_URL),
	routes: routes,
});

router.beforeEach((to: RouteLocationNormalized, _from: RouteLocationNormalized, next: NavigationGuardNext) => {
	const userStore = useUserStore();
	const requiresAuth: boolean = (to.meta.requiresAuth ?? true) === true;
	const install: boolean = (to.meta.installWizard ?? false) === true;
	if (!install && requiresAuth && !userStore.isLoggedIn) {
		let query = { ...to.query };
		if (to.path !== '/' && to.path !== '/sign/in') {
			query = { ...query, redirect: to.path };
		}
		return next({ name: 'SignIn', query: query });
	}
	if(to.name === 'SignIn' && userStore.isLoggedIn) {
		return next((to.query.redirect as string|undefined) ?? '/');
	}
	next();
});

export default router;
