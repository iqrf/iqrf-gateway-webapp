import Vue from 'vue';
import VueRouter from 'vue-router';
import GatewayInfo from './components/Gateway/GatewayInfo';
import DaemonLogViewer from './components/Gateway/DaemonLogViewer';
import DaemonMode from './components/Gateway/DaemonMode';
import PowerControl from './components/Gateway/PowerControl';
import SignIn from './components/SignIn';
import SendDpaPacket from './components/IqrfNet/SendDpaPacket';

import i18n from './i18n';
import ServiceControl from './components/Gateway/ServiceControl';

Vue.use(VueRouter);

const routes = [
	{
		component: SignIn,
		path: '/sign/in/',
		meta: {
			title: 'core.sign.inForm.title',
		},
	},
	{
		path: '*',
		component: {
			render(c) {
				return c('router-view');
			}
		},
		children: [
			{
				component: GatewayInfo,
				path: '/gateway/info/',
				meta: {
					title: 'gateway.info.title',
					description: 'gateway.info.description',
				},
			},
			{
				component: DaemonLogViewer,
				path: '/gateway/log/',
				meta: {
					title: 'gateway.log.title',
					description: 'gateway.log.description',
				},
			},
			{
				component: DaemonMode,
				path: '/gateway/change-mode/',
				meta: {
					title: 'gateway.mode.title',
					description: 'gateway.mode.description',
				},
			},
			{
				component: PowerControl,
				path: '/gateway/power/',
				meta: {
					title: 'gateway.power.title',
					description: 'gateway.power.description',
				},
			},
			{
				component: ServiceControl,
				name: 'serviceControl',
				path: '/service/:serviceName/',
				props: true,
				meta: {
					title: 'service.%serviceName%.title',
					description: 'service.%serviceName%.description',
				},
			},
			{
				component: SendDpaPacket,
				path: '/iqrfnet/send-raw/',
				meta: {
					title: 'iqrfnet.sendPacket.title',
					description: 'iqrfnet.sendPacket.description',
				},
			},
			{
				path: '*',
				name: 'legacyComponent',
			}
		]
	},
];

const router = new VueRouter({
	mode: 'history',
	routes: routes
});

function metaTranslate(route, type) {
	let text = '';
	if (route.meta[type]) {
		let parts = route.meta[type].split('%');
		if (parts.length === 1) {
			text = i18n.t(route.meta[type]);
		} else {
			text = i18n.t(parts[0] + route.params[parts[1]] + parts[2]);
		}
	}
	return text;
}

router.beforeEach((to, from, next) => {
	if (to.name === 'legacyComponent' && from.name !== null) {
		location.replace(to.fullPath);
	}
	if (to.meta.title === undefined) {
		next();
		return;
	}
	if (to.meta.title) {
		to.meta.title = metaTranslate(to, 'title');
	}
	document.title = to.meta.title + (to.meta.title ? ' | ' : '') + i18n.t('core.title');
	let titleEl = document.getElementById('title');
	if (titleEl !== null) {
		titleEl.innerText = to.meta.title;
	}
	let content = document.getElementById('content');
	if (content !== null) {
		content.remove();
	}
	next();
});

export default router;
