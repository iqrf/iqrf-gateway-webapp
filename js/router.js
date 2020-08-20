import Vue from 'vue';
import VueRouter from 'vue-router';
import GatewayInfo from './components/Gateway/GatewayInfo';
import LogViewer from './components/Gateway/LogViewer';
import SignIn from './components/SignIn';

import i18n from './i18n';

Vue.use(VueRouter);

const routes = [
	{
		component: GatewayInfo,
		path: '/gateway/info',
		meta: {
			title: 'gateway.info.title'
		},
	},
	{
		component: LogViewer,
		path: '/gateway/log',
		meta: {
			title: 'gateway.log.title'
		},
	},
	{
		path: '/sign/in',
		component: SignIn
	}
];

const router = new VueRouter({
	mode: 'history',
	routes: routes
});

router.beforeEach((to, from, next) => {
	if (to.meta.title === undefined) {
		next();
		return;
	}
	document.title = (to.meta.title ? i18n.t(to.meta.title) + ' | ' : '') + i18n.t('core.title');
	let title = document.getElementById('title');
	if (title !== null) {
		title.innerText = i18n.t(to.meta.title);
	}
	let content = document.getElementById('content');
	if (content !== null) {
		content.innerHTML = '';
	}
	next();
});

export default router;
