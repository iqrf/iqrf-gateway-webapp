import Vue from 'vue';
import VueRouter, {Route, RouteConfig} from 'vue-router' ;

import CloudDisambiguation from '../pages/Cloud/CloudDisambiguation.vue';
import AzureCreator from '../pages/Cloud/AzureCreator.vue';
import HexioCreator from '../pages/Cloud/HexioCreator.vue';
import IbmCreator from '../pages/Cloud/IbmCreator.vue';
import InteliGlueCreator from '../pages/Cloud/InteliGlueCreator.vue';
import PixlaControl from '../pages/Cloud/PixlaControl.vue';

import GatewayDisambiguation from '../pages/Gateway/GatewayDisambiguation.vue';
import GatewayInfo from '../pages/Gateway/GatewayInfo.vue';
import DaemonLogViewer from '../pages/Gateway/DaemonLogViewer.vue';
import DaemonMode from '../pages/Gateway/DaemonMode.vue';
import PowerControl from '../pages/Gateway/PowerControl.vue';
import ServiceControl from '../pages/Gateway/ServiceControl.vue';

import SignIn from '../components/SignIn.vue';

import IqrfNetDisambiguation from '../pages/IqrfNet/IqrfNetDisambiguation.vue';
import NetworkManager from '../pages/IqrfNet/NetworkManager.vue';
import SendJsonRequest from '../pages/IqrfNet/SendJsonRequest.vue';
import SendDpaPacket from '../pages/IqrfNet/SendDpaPacket.vue';
import StandardManager from '../pages/IqrfNet/StandardManager.vue';

import ConfigDisambiguation from '../pages/Config/ConfigDisambiguation.vue';
import ConfigMigration from '../pages/Config/ConfigMigration.vue';
import TranslatorConfig from '../pages/Config/TranslatorConfig.vue';
import ControllerConfig from '../pages/Config/ControllerConfig.vue';
import MenderConfig from '../pages/Config/MenderConfig.vue';
import IqrfInfo from '../pages/Config/IqrfInfo.vue';

import UserAdd from '../pages/Core/UserAdd.vue';
import UserEdit from '../pages/Core/UserEdit.vue';
import UserList from '../pages/Core/UserList.vue';

import NetworkDisambiguation from '../pages/Network/NetworkDisambiguation.vue';

import i18n from '../i18n';
import store from '../store';

Vue.use(VueRouter);

const routes: Array<RouteConfig> = [
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
					{
						component: PixlaControl,
						path: 'pixla',
						meta: {title: 'cloud.pixla.title'},
					},
				]
			},
			{
				path: '/config',
				component: {
					render(c) {
						return c('router-view');
					}
				},
				children: [
					{
						component: ConfigDisambiguation,
						path: '',
						meta: {title: 'config.title'}
					},
					{
						component: IqrfInfo,
						path: 'iqrf-info',
						meta: {title: 'config.iqrfInfo.title'}
					},
					{
						component: ConfigMigration,
						path: 'migration',
						meta: {title: 'config.migration.title'}
					},
					{
						component: MenderConfig,
						path: 'mender',
						meta: {title: 'config.mender.description'}
					},
					{
						component: TranslatorConfig,
						path: 'translator',
						meta: {title: 'translatorConfig.description'}
					},
					{
						component: ControllerConfig,
						path: 'controller',
						meta: {title: 'controllerConfig.description'}
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
				path: '/iqrfnet',
				component: {
					render(c) {
						return c('router-view');
					}
				},
				children: [
					{
						component: IqrfNetDisambiguation,
						path: '',
						meta: {title: 'iqrfnet.title'}
					},
					{
						component: NetworkManager,
						path: 'network',
						meta: {title: 'iqrfnet.networkManager.title'}
					},
					{
						component: StandardManager,
						path: 'standard',
						meta: {title: 'iqrfnet.standard.title'}
					},
					{
						component: SendDpaPacket,
						path: 'send-raw',
						meta: {title: 'iqrfnet.sendPacket.title'},
					},
					{
						component: SendJsonRequest,
						path: 'send-json',
						meta: {title: 'iqrfnet.sendJson.title'}
					},
				]
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
						component: UserAdd,
						path: 'add',
						meta: {title: 'core.user.add.title'},
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
					// @ts-ignore
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

function metaTranslate(route: Route, type: string): string {
	let text: string = '';
	if (route.meta[type]) {
		const parts = route.meta[type].split('%');
		if (parts.length === 1) {
			text = i18n.t(route.meta[type]).toString();
		} else {
			text = i18n.t(parts[0] + route.params[parts[1]] + parts[2]).toString();
		}
	}
	return text;
}

router.beforeEach((to, from, next) => {
	if (!to.path.startsWith('/install/') && to.name !== 'signIn' &&
		!store.getters['user/isLoggedIn']) {
		store.dispatch('user/signOut').then(() => {
			next('/sign/in');
		});
		return;
	}
	if (to.name === 'legacyComponent') {
		if (from.name !== null) {
			location.replace(to.fullPath);
		} else {
			next();
		}
		return;
	}
	const titleEl = document.getElementById('title');
	if (titleEl !== null && to.meta.title !== undefined) {
		titleEl.innerText = metaTranslate(to, 'title');
	}
	const content = document.getElementById('content');
	if (content !== null) {
		content.remove();
	}
	const flashes = document.getElementById('snippet--flashes');
	if (flashes !== null && from.name !== null) {
		flashes.remove();
	}
	next();
});

export default router;
