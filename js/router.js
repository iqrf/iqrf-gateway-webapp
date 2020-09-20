import Vue from 'vue';
import VueRouter from 'vue-router';

import CloudDisambiguation from './components/Cloud/CloudDisambiguation';
import AzureCreator from './components/Cloud/AzureCreator';
import HexioCreator from './components/Cloud/HexioCreator';
import IbmCreator from './components/Cloud/IbmCreator';
import InteliGlueCreator from './components/Cloud/InteliGlueCreator';

import GatewayDisambiguation from './components/Gateway/GatewayDisambiguation';
import GatewayInfo from './components/Gateway/GatewayInfo';
import DaemonLogViewer from './components/Gateway/DaemonLogViewer';
import DaemonMode from './components/Gateway/DaemonMode';
import PowerControl from './components/Gateway/PowerControl';
import ServiceControl from './components/Gateway/ServiceControl';

import SignIn from './components/SignIn';
import NetworkManager from './components/IqrfNet/NetworkManager';
import SendDpaPacket from './components/IqrfNet/SendDpaPacket';
import TranslatorConfig from './components/Config/TranslatorConfig';
import ControllerConfig from './components/Config/ControllerConfig';
import MenderConfig from './components/Config/MenderConfig';

import UserEdit from './components/Core/UserEdit';
import UserList from './components/Core/UserList';

import SendJsonRequest from './components/IqrfNet/SendJsonRequest';
import StandardManager from './components/IqrfNet/StandardManager';

import NetworkDisambiguation from './components/Network/NetworkDisambiguation';

import i18n from './i18n.ts';
import store from './store';

Vue.use(VueRouter);

const routes = [
	{
		component: SignIn,
		name: 'signIn',
		path: '/sign/in',
	},
	{
		path: '/',
		component: {
			render(c) {
				return c('router-view');
			}
		},
		children: [
			{
				path: '/cloud',
				component: {
					render(c) {
						return c('router-view');
					}
				},
				children: [
					{
						component: CloudDisambiguation,
						path: '',
						meta: {title: 'cloud.title'}
					},

					{
						component: AzureCreator,
						path: 'azure',
						meta: {title: 'cloud.msAzure.form.title'}
					},
					{
						component: HexioCreator,
						path: 'hexio',
						meta: {title: 'cloud.hexio.form.title'}
					},
					{
						component: IbmCreator,
						path: 'ibm-cloud',
						meta: {title: 'cloud.ibmCloud.form.title'}
					},
					{
						component: InteliGlueCreator,
						path: 'inteli-glue',
						meta: {title: 'cloud.intelimentsInteliGlue.form.title'},
					},
				]
			},
			{
				path: '/gateway',
				component: {
					render(c) {
						return c('router-view');
					}
				},
				children: [
					{
						component: GatewayDisambiguation,
						path: '',
						meta: {title: 'gateway.title'}
					},
					{
						component: GatewayInfo,
						path: 'info',
						meta: {title: 'gateway.info.title'},
					},
					{
						component: DaemonLogViewer,
						path: 'log',
						meta: {title: 'gateway.log.title'},
					},
					{
						component: DaemonMode,
						path: 'change-mode',
						meta: {title: 'gateway.mode.title'},
					},
					{
						component: PowerControl,
						path: 'power',
						meta: {title: 'gateway.power.title'},
					},
				]
			},
			{
				component: ServiceControl,
				name: 'serviceControl',
				path: '/service/:serviceName',
				props: true,
				meta: {title: 'service.%serviceName%.title'},
			},
			{
				component: NetworkManager,
				path: '/iqrfnet/network',
				meta: {title: 'iqrfnet.networkManager.title'}
			},
			{
				component: StandardManager,
				path: '/iqrfnet/standard',
				meta: {title: 'iqrfnet.standard.title'}
			},
			{
				component: SendDpaPacket,
				path: '/iqrfnet/send-raw',
				meta: {title: 'iqrfnet.sendPacket.title'},
			},
			{
				component: SendJsonRequest,
				path: '/iqrfnet/send-json',
				meta: {title: 'iqrfnet.sendJson.title'}
			},
			{
				component: MenderConfig,
				path: '/config/mender',
				meta: {title: 'config.mender.description'}
			},
			{
				component: TranslatorConfig,
				path: '/config/translator',
				meta: {title: 'translatorConfig.description'}
			},
			{
				component: ControllerConfig,
				path: '/config/controller',
				meta: {title: 'controllerConfig.description'}
			},
			{
				path: '/network',
				component: {
					render(c) {
						return c('router-view');
					}
				},
				children: [
					{
						component: NetworkDisambiguation,
						path: '',
						meta: {title: 'network.title'}
					}
				]
			},
			{
				path: '/user',
				component: {
					render(c) {
						return c('router-view');
					}
				},
				meta: {title: 'core.user.title'},
				children: [
					{
						component: UserList,
						path: '',
						meta: {title: 'core.user.title'},
					},
					{
						component: UserEdit,
						path: 'edit/:userId',
						props: (route) => {
							const userId = Number.parseInt(route.params.userId, 10);
							if (Number.isNaN(userId)) {
								return 0;
							}
							return {userId};
						},
						meta: {title: 'core.user.edit.title'},
					},
				]
			},
			{
				path: '*',
				name: 'legacyComponent',
				component: {
					metaInfo: {titleTemplate: null},
				},
			},
		],
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
	if (!to.path.startsWith('/install/') && to.name !== 'signIn' &&
		!store.getters['user/isLoggedIn']) {
		store.dispatch('user/signOut').then(() => {
			// next('/sign/in');
			location.replace('/sign/in');
		});
		return;
	}
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
	let titleEl = document.getElementById('title');
	if (titleEl !== null) {
		titleEl.innerText = to.meta.title;
	}
	let content = document.getElementById('content');
	if (content !== null) {
		content.remove();
	}
	let flashes = document.getElementById('snippet--flashes');
	if (flashes !== null) {
		flashes.remove();
	}
	next();
});

export default router;
