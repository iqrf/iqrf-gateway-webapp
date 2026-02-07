/**
 * Copyright 2017-2026 IQRF Tech s.r.o.
 * Copyright 2019-2026 MICRORISC s.r.o.
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
import {
	createRouter,
	createWebHistory,
	type NavigationGuardNext,
	type RouteLocationNormalized,
	type Router,
	type RouteRecordRaw,
} from 'vue-router';
import {
	routes as generatedRoutes,
	handleHotUpdate,
} from 'vue-router/auto-routes';

import { useUserStore } from '@/store/user';

const routes: RouteRecordRaw[] = [
	...setupLayouts(generatedRoutes),
];

const router: Router = createRouter({
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
		next({ name: 'SignIn', query: query }); return;
	}
	if (to.name === 'SignIn' && userStore.isLoggedIn) {
		next((to.query.redirect as string|undefined) ?? '/'); return;
	}
	next();
});

// Workaround for https://github.com/vitejs/vite/issues/11804
router.onError((error: unknown, to: RouteLocationNormalized): void => {
	if (error instanceof TypeError && error.message.includes('Failed to fetch dynamically imported module')) {
		if (!localStorage.getItem('vuetify:dynamic-reload')) {
			console.warn('Reloading page to fix dynamic import error');
			localStorage.setItem('vuetify:dynamic-reload', 'true');
			location.assign(to.fullPath);
		} else {
			console.error('Dynamic import error, reloading page did not fix it', error);
		}
	} else {
		console.error(error);
	}
});

// eslint-disable-next-line promise/always-return
router.isReady().then((): void => {
	localStorage.removeItem('vuetify:dynamic-reload');
}).catch((error: unknown): void => {
	console.error(error);
});

if (import.meta.hot) {
	handleHotUpdate(router);
}

export default router;
