import Vue from 'vue';
import VueRouter from 'vue-router';
import GatewayInfo from './components/Gateway/GatewayInfo';
import LogViewer from './components/Gateway/LogViewer';
import SignIn from './components/SignIn';

import i18n from './i18n';
import ServiceControl from './components/ServiceControl';

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
		component: ServiceControl,
		name: 'serviceControl',
		path: '/service/:serviceName',
		props: true,
		meta: {
			title: 'service.%serviceName%.title'
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
	let title = '';
	if (to.meta.title) {
		let parts = to.meta.title.split('%');
		if (parts.length === 1) {
			title = i18n.t(to.meta.title);
		} else {
			title = i18n.t(parts[0] + to.params[parts[1]] + parts[2]);
		}
	}
	document.title = (to.meta.title ? title + ' | ' : '') + i18n.t('core.title');
	let titleEl = document.getElementById('title');
	if (titleEl !== null) {
		titleEl.innerText = title;
	}
	let content = document.getElementById('content');
	if (content !== null) {
		content.innerHTML = '';
	}
	next();
});

export default router;
