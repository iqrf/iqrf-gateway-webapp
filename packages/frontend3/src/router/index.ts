import { useUserStore } from '@/store/user';
import { createRouter, createWebHistory, NavigationGuardNext, RouteLocationNormalized, RouteRecordRaw } from 'vue-router';
import { setupLayouts } from 'virtual:generated-layouts';
import generatedRoutes from 'virtual:generated-pages';


const routes: Array<RouteRecordRaw> = [
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
		let query = {...to.query};
		if (to.path !== '/' && to.path !== '/sign/in') {
			query = {...query, redirect: to.path};
		}
		return next({name: 'SignIn', query: query});
	}
	if(to.name === 'SignIn' && userStore.isLoggedIn) {
		return next((to.query.redirect as string|undefined) ?? '/');
	}
	next();
});

export default router;
