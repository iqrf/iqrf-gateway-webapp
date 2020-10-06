import Vue from 'vue';
import VueRouter, {RouteConfig} from 'vue-router' ;

// @ts-ignore
import TheDashboard from '../components/TheDashboard';

import CloudDisambiguation from '../pages/Cloud/CloudDisambiguation.vue';
import AzureCreator from '../pages/Cloud/AzureCreator.vue';
import AwsCreator from '../pages/Cloud/AwsCreator.vue';
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

import MainDisambiguation from '../pages/Core/MainDisambiguation.vue';
import NotFound from '../pages/Core/NotFound.vue';
import SignIn from '../pages/Core/SignIn.vue';

import IqrfNetDisambiguation from '../pages/IqrfNet/IqrfNetDisambiguation.vue';
import DeviceEnumeration from '../pages/IqrfNet/DeviceEnumeration.vue';
import NetworkManager from '../pages/IqrfNet/NetworkManager.vue';
import SendJsonRequest from '../pages/IqrfNet/SendJsonRequest.vue';
import SendDpaPacket from '../pages/IqrfNet/SendDpaPacket.vue';
import StandardManager from '../pages/IqrfNet/StandardManager.vue';
import TrConfiguration from '../pages/IqrfNet/TrConfiguration.vue';
import TrUpload from '../pages/IqrfNet/TrUpload.vue';

import InstallationBase from '../pages/Install/InstallationBase.vue';
import InstallCreateUser from '../pages/Install/InstallCreateUser.vue';
import InstallationDisambiguation from '../pages/Install/InstallationDisambiguation.vue';
import InstallGatewayInfo from '../pages/Install/InstallGatewayInfo.vue';

import ConfigDisambiguation from '../pages/Config/ConfigDisambiguation.vue';
import ConfigMigration from '../pages/Config/ConfigMigration.vue';
import TranslatorConfig from '../pages/Config/TranslatorConfig.vue';
import ControllerConfig from '../pages/Config/ControllerConfig.vue';
import MenderConfig from '../pages/Config/MenderConfig.vue';
import IqrfInfo from '../pages/Config/IqrfInfo.vue';
import IqrfRepository from '../pages/Config/IqrfRepository.vue';
import IqrfCdc from '../pages/Config/IqrfCdc.vue';
import IqrfDpa from '../pages/Config/IqrfDpa.vue';
import IqrfSpi from '../pages/Config/IqrfSpi.vue';
import IqrfUart from '../pages/Config/IqrfUart.vue';
import JsonMngMetaDataApi from '../pages/Config/JsonMngMetaDataApi.vue';
import JsonRawApi from '../pages/Config/JsonRawApi.vue';
import JsonSplitter from '../pages/Config/JsonSplitter.vue';
import MonitorForm from '../pages/Config/MonitorForm.vue';
import MonitorList from '../pages/Config/MonitorList.vue';
import MqMessagingForm from '../pages/Config/MqMessagingForm.vue';
import MqMessagingTable from '../pages/Config/MqMessagingTable.vue';
import MqttMessagingForm from '../pages/Config/MqttMessagingForm.vue';
import MqttMessagingTable from '../pages/Config/MqttMessagingTable.vue';
import UdpMessagingForm from '../pages/Config/UdpMessagingForm.vue';
import UdpMessagingTable from '../pages/Config/UdpMessagingTable.vue';
import TracerList from '../pages/Config/TracerList.vue';
import TracerForm from '../pages/Config/TracerForm.vue';
import MainConfiguration from '../pages/Config/MainConfiguration.vue';
import ComponentList from '../pages/Config/ComponentList.vue';
import ComponentForm from '../pages/Config/ComponentForm.vue';
import IqmeshServices from '../pages/Config/IqmeshServices.vue';
import WebsocketInterfaceForm from '../pages/Config/WebsocketInterfaceForm.vue';
import WebsocketMessagingForm from '../pages/Config/WebsocketMessagingForm.vue';
import WebsocketServiceForm from '../pages/Config/WebsocketServiceForm.vue';
import WebsocketList from '../pages/Config/WebsocketList.vue';
import SchedulerList from '../pages/Config/SchedulerList.vue';
import SchedulerForm from '../pages/Config/SchedulerForm.vue';

import UserAdd from '../pages/Core/UserAdd.vue';
import UserEdit from '../pages/Core/UserEdit.vue';
import UserList from '../pages/Core/UserList.vue';

import ApiKeyList from '../pages/Core/ApiKeyList.vue';
import ApiKeyForm from '../pages/Core/ApiKeyForm.vue';

import NetworkDisambiguation from '../pages/Network/NetworkDisambiguation.vue';
import ConnectionForm from '../pages/Network/ConnectionForm.vue';
import EthernetInterfaces from '../pages/Network/EthernetInterfaces.vue';

import store from '../store';

Vue.use(VueRouter);

const routes: Array<RouteConfig> = [
	{
		component: SignIn,
		name: 'signIn',
		path: '/sign/in',
	},
	{
		path: '/install',
		component: InstallationBase,
		children: [
			{
				component: InstallationDisambiguation,
				path: '',
			},
			{
				component: InstallCreateUser,
				path: 'user',
			},
			{
				component: InstallGatewayInfo,
				path: 'gateway-info',
			},
		]
	},
	{
		path: '/',
		component: TheDashboard,
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
					},
					{
						component: AzureCreator,
						path: 'azure',
					},
					{
						component: AwsCreator,
						path: 'aws',
					},
					{
						component: HexioCreator,
						path: 'hexio',
					},
					{
						component: IbmCreator,
						path: 'ibm-cloud',
					},
					{
						component: InteliGlueCreator,
						path: 'inteli-glue',
					},
					{
						component: PixlaControl,
						path: 'pixla',
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
					},
					{
						component: MainConfiguration,
						path: 'main',
					},
					{
						path: 'component',
						component: {
							render(c) {
								return c('router-view');
							}
						},
						children: [
							{
								path: '',
								component: ComponentList,
							},
							{
								component: ComponentForm,
								path: 'add',
							},
							{
								component: ComponentForm,
								path: 'edit/:component',
								props: true,
							},
						],
					},
					{
						component: IqrfCdc,
						path: 'iqrf-cdc',
					},
					{
						component: IqrfDpa,
						path: 'iqrf-dpa',
					},
					{
						component: IqrfInfo,
						path: 'iqrf-info',
					},
					{
						component: IqmeshServices,
						path: 'iqmesh',
					},
					{
						component: IqrfRepository,
						path: 'iqrf-repository',
					},
					{
						component: IqrfSpi,
						path: 'iqrf-spi',
					},
					{
						component: IqrfUart,
						path: 'iqrf-uart',
					},
					{
						component: JsonRawApi,
						path: 'json-raw-api',
					},
					{
						component: JsonMngMetaDataApi,
						path: 'json-mng-meta-data-api',
					},
					{
						component: JsonSplitter,
						path: 'json-splitter',
					},
					{
						path: 'monitor',
						component: {
							render(c) {
								return c('router-view');
							}
						},
						children: [
							{
								path: '',
								component: MonitorList,
							},
							{
								component: MonitorForm,
								path: 'add',
							},
							{
								component: MonitorForm,
								path: 'edit/:instance',
								props: true,
							},
						],
					},
					{
						path: 'scheduler',
						component: {
							render(c) {
								return c('router-view');
							}
						},
						children: [
							{
								path: '',
								component: SchedulerList,
							},
							{
								path: 'add',
								component: SchedulerForm,
							},
							{
								path: 'edit/:id',
								component: SchedulerForm,
								props: (route) => {
									const id = Number.parseInt(route.params.id, 10);
									if (Number.isNaN(id)) {
										return 0;
									}
									return {id};
								},
							}
						]
					},
					{
						path: 'mq',
						component: {
							render(c) {
								return c('router-view');
							}
						},
						children: [
							{
								path: '',
								component: MqMessagingTable,
							},
							{
								component: MqMessagingForm,
								path: 'add',
							},
							{
								component: MqMessagingForm,
								path: 'edit/:instance',
								props: true,
							},
						],
					},
					{
						path: 'mqtt',
						component: {
							render(c) {
								return c('router-view');
							}
						},
						children: [
							{
								path: '',
								component: MqttMessagingTable,
							},
							{
								component: MqttMessagingForm,
								path: 'add',
							},
							{
								component: MqttMessagingForm,
								path: 'edit/:instance',
								props: true,
							},
						],
					},
					{
						path: 'udp',
						component: {
							render(c) {
								return c('router-view');
							}
						},
						children: [
							{
								path: '',
								component: UdpMessagingTable,
							},
							{
								component: UdpMessagingForm,
								path: 'add',
							},
							{
								component: UdpMessagingForm,
								path: 'edit/:instance',
								props: true,
							},
						],
					},
					{
						path: 'websocket',
						component: {
							render(c) {
								return c('router-view');
							}
						},
						children: [
							{
								path: '',
								component: WebsocketList,
							},
							{
								component: WebsocketInterfaceForm,
								path: 'add',
							},
							{
								component: WebsocketMessagingForm,
								path: 'add-messaging',
							},
							{
								component: WebsocketServiceForm,
								path: 'add-service',
							},
							{
								component: WebsocketInterfaceForm,
								path: 'edit/:instance',
								props: true,
							},
							{
								component: WebsocketMessagingForm,
								path: 'edit-messaging/:instance',
								props: true,
							},
							{
								component: WebsocketServiceForm,
								path: 'edit-service/:instance',
								props: true,
							},
						],
					},
					{
						path: 'tracer',
						component: {
							render(c) {
								return c('router-view');
							},
						},
						children: [
							{
								path: '',
								component: TracerList,
							},
							{
								component: TracerForm,
								path: 'add',
							},
							{
								component: TracerForm,
								path: 'edit/:instance',
								props: true,
							},
						],
					},
					{
						component: ConfigMigration,
						path: 'migration',
					},
					{
						component: MenderConfig,
						path: 'mender',
					},
					{
						component: TranslatorConfig,
						path: 'translator',
					},
					{
						component: ControllerConfig,
						path: 'controller',
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
					},
					{
						component: GatewayInfo,
						path: 'info',
					},
					{
						component: DaemonLogViewer,
						path: 'log',
					},
					{
						component: DaemonMode,
						path: 'change-mode',
					},
					{
						component: PowerControl,
						path: 'power',
					},
				]
			},
			{
				component: ServiceControl,
				name: 'serviceControl',
				path: '/service/:serviceName',
				props: true,
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
					},
					{
						component: DeviceEnumeration,
						path: 'enumeration/:address',
						props: (route) => {
							const address = Number.parseInt(route.params.address, 10);
							if (Number.isNaN(address)) {
								return 0;
							}
							return {address};
						},
					},
					{
						component: NetworkManager,
						path: 'network',
					},
					{
						component: StandardManager,
						path: 'standard',
					},
					{
						component: SendDpaPacket,
						path: 'send-raw',
					},
					{
						component: SendJsonRequest,
						path: 'send-json',
					},
					{
						component: TrConfiguration,
						path: 'tr-config/:address',
						props: (route) => {
							const address = Number.parseInt(route.params.address, 10);
							if (Number.isNaN(address)) {
								return 0;
							}
							return {address};
						},
					},
					{
						component: TrUpload,
						path: 'tr-upload',
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
					},
					{
						component: EthernetInterfaces,
						path: 'ethernet',
					},
					{
						component: ConnectionForm,
						path: 'edit/:uuid',
						props: true,
					},
				]
			},
			{
				path: '/user',
				component: {
					render(c) {
						return c('router-view');
					}
				},
				children: [
					{
						component: UserList,
						path: '',
					},
					{
						component: UserAdd,
						path: 'add',
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
					},
				]
			},
			{
				path: '/api-key',
				component: {
					render(c) {
						return c('router-view');
					}
				},
				children: [
					{
						path: '',
						component: ApiKeyList,
					},
					{
						component: ApiKeyForm,
						path: 'add',
					},
					{
						component: ApiKeyForm,
						path: 'edit/:keyId',
						props: (route) => {
							const keyId = Number.parseInt(route.params.keyId, 10);
							if (Number.isNaN(keyId)) {
								return 0;
							}
							return {keyId};
						},
					},
				],
			},
			{
				component: MainDisambiguation,
				path: '',
			},
			{
				path: '*',
				component: NotFound,
			},
		],
	},
];

const router = new VueRouter({
	mode: 'history',
	routes: routes
});

router.beforeEach((to, from, next) => {
	if (!to.path.startsWith('/install/') && to.name !== 'signIn' &&
		!store.getters['user/isLoggedIn']) {
		store.dispatch('user/signOut').then(() => {
			next('/sign/in');
		});
		return;
	}
	next();
});

export default router;
