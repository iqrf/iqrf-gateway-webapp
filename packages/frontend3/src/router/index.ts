import { Feature } from '@iqrf/iqrf-gateway-webapp-client/types';
import { setupLayouts } from 'virtual:generated-layouts';
import generatedRoutes from 'virtual:generated-pages';
import { createRouter, createWebHistory, type NavigationGuardNext, type RouteLocationNormalized, type RouteRecordRaw } from 'vue-router';

import { useFeatureStore } from '@/store/features';
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
	const featureStore = useFeatureStore();
	const requiresAuth: boolean = (to.meta.requiresAuth ?? true) === true;
	const install: boolean = (to.meta.installWizard ?? false) === true;
	if (!install && requiresAuth && !userStore.isLoggedIn) {
		let query = {...to.query};
		if (to.path !== '/' && to.path !== '/sign/in') {
			query = {...query, redirect: to.path};
		}
		return next({name: 'SignIn', query: query});
	}
	if(to.name === 'SignIn' && userStore.isLoggedIn) {
		return next((to.query.redirect as string|undefined) ?? '/');
	}
	const feature: string|undefined = (to.meta.feature as string) ?? undefined;
	if (feature && feature in Feature && !featureStore.isEnabled(feature as Feature)) {
		return next('/');
	}
	next();
});

export default router;
