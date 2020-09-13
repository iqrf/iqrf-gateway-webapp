import Vue from 'vue';
import VueRouter from 'vue-router';
import GatewayInfo from './components/Gateway/GatewayInfo';
import DaemonLogViewer from './components/Gateway/DaemonLogViewer';
import DaemonMode from './components/Gateway/DaemonMode';
import PowerControl from './components/Gateway/PowerControl';
import SignIn from './components/SignIn';
import NetworkManager from './components/IqrfNet/NetworkManager';
import SendDpaPacket from './components/IqrfNet/SendDpaPacket';
import TranslatorConfig from './components/Config/TranslatorConfig';
import ControllerConfig from './components/Config/ControllerConfig';
import MenderConfig from './components/Gateway/MenderConfig';

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
				},
			},
			{
				component: DaemonLogViewer,
				path: '/gateway/log/',
				meta: {
					title: 'gateway.log.title',
				},
			},
			{
				component: MenderConfig,
				path: '/gateway/mender/',
				meta: {
					title: 'gateway.mender.title'
				}
			},
			{
				component: DaemonMode,
				path: '/gateway/change-mode/',
				meta: {
					title: 'gateway.mode.title',
				},
			},
			{
				component: PowerControl,
				path: '/gateway/power/',
				meta: {
					title: 'gateway.power.title',
				},
			},
			{
				component: ServiceControl,
				name: 'serviceControl',
				path: '/service/:serviceName/',
				props: true,
				meta: {
					title: 'service.%serviceName%.title',
				},
			},
			{
				component: NetworkManager,
				path: '/iqrfnet/network/',
				meta: {
					title: 'iqrfnet.networkManager.title'
				}
			},
			{
				component: SendDpaPacket,
				path: '/iqrfnet/send-raw/',
				meta: {
					title: 'iqrfnet.sendPacket.title',
				},
			},
			{
				component: TranslatorConfig,
				path: '/config/translator/',
				meta: {
					title: 'translatorConfig.description'
				}
			},
			{
				component: ControllerConfig,
				path: '/config/controller/',
				meta: {
					title: 'controllerConfig.description'
				}
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
