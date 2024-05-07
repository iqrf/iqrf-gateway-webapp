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
