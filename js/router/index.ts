import Vue from 'vue';
import VueRouter, {Route, RouteConfig} from 'vue-router' ;

import CloudDisambiguation from '../components/Cloud/CloudDisambiguation.vue';
import AzureCreator from '../components/Cloud/AzureCreator.vue';
import HexioCreator from '../components/Cloud/HexioCreator.vue';
import IbmCreator from '../components/Cloud/IbmCreator.vue';
import InteliGlueCreator from '../components/Cloud/InteliGlueCreator.vue';
import PixlaControl from '../components/Cloud/PixlaControl.vue';

import GatewayDisambiguation from '../components/Gateway/GatewayDisambiguation.vue';
import GatewayInfo from '../components/Gateway/GatewayInfo.vue';
import DaemonLogViewer from '../components/Gateway/DaemonLogViewer.vue';
import DaemonMode from '../components/Gateway/DaemonMode.vue';
import PowerControl from '../components/Gateway/PowerControl.vue';
import ServiceControl from '../components/Gateway/ServiceControl.vue';

import SignIn from '../components/SignIn.vue';
import NetworkManager from '../components/IqrfNet/NetworkManager.vue';
import SendDpaPacket from '../components/IqrfNet/SendDpaPacket.vue';
import ConfigMigration from '../components/Config/ConfigMigration.vue';
import TranslatorConfig from '../components/Config/TranslatorConfig.vue';
import ControllerConfig from '../components/Config/ControllerConfig.vue';
import MenderConfig from '../components/Config/MenderConfig.vue';
import IqrfInfo from '../components/Config/IqrfInfo.vue';

import UserEdit from '../components/Core/UserEdit.vue';
import UserList from '../components/Core/UserList.vue';

import SendJsonRequest from '../components/IqrfNet/SendJsonRequest.vue';
import StandardManager from '../components/IqrfNet/StandardManager.vue';

import NetworkDisambiguation from '../components/Network/NetworkDisambiguation.vue';

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
				component: IqrfInfo,
				path: '/config/iqrf-info',
				meta: {title: 'config.iqrfInfo.title'}
			},
			{
				component: ConfigMigration,
				path: '/config/migration',
				meta: {title: 'config.migration.title'}
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
	if (flashes !== null) {
		flashes.remove();
	}
	next();
});

export default router;
