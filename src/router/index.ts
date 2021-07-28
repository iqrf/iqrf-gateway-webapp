/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
import Vue from 'vue';
import VueRouter, {RouteConfig} from 'vue-router';

import TheDashboard from '../components/TheDashboard.vue';

const CloudDisambiguation = () => import(/* webpackChunkName: "cloud" */ '@/pages/Cloud/CloudDisambiguation.vue');
const AzureCreator = () => import(/* webpackChunkName: "cloud" */ '@/pages/Cloud/AzureCreator.vue');
const AwsCreator = () => import(/* webpackChunkName: "cloud" */ '@/pages/Cloud/AwsCreator.vue');
const HexioCreator = () => import(/* webpackChunkName: "cloud" */ '@/pages/Cloud/HexioCreator.vue');
const IbmCreator = () => import(/* webpackChunkName: "cloud" */ '@/pages/Cloud/IbmCreator.vue');
const InteliGlueCreator = () => import(/* webpackChunkName: "cloud" */ '@/pages/Cloud/InteliGlueCreator.vue');

const GatewayDisambiguation = () => import(/* webpackChunkName: "gateway" */ '@/pages/Gateway/GatewayDisambiguation.vue');
const GatewayInfo = () => import(/* webpackChunkName: "gateway" */ '@/pages/Gateway/GatewayInfo.vue');
const GatewayTime = () => import(/* webpackChunkName: "gateway" */ '@/pages/Gateway/GatewayTime.vue');
const Logs = () => import(/* webpackChunkName: "gateway" */ '@/pages/Gateway/Logs.vue');
const DaemonMode = () => import(/* webpackChunkName: "gateway" */ '@/pages/Gateway/DaemonMode.vue');
const PowerControl = () => import(/* webpackChunkName: "gateway" */ '@/pages/Gateway/PowerControl.vue');
const IqrfServiceDisambiguation = () => import(/* webpackChunkName: "gateway" */ '@/pages/Gateway/IqrfServiceDisambiguation.vue');
const ServiceControl = () => import(/* webpackChunkName: "gateway" */ '@/pages/Gateway/ServiceControl.vue');

const ApiKeyList = () => import(/* webpackChunkName: "core" */ '@/pages/Core/ApiKeyList.vue');
const ApiKeyForm = () => import(/* webpackChunkName: "core" */ '@/pages/Core/ApiKeyForm.vue');
const MainDisambiguation = () => import(/* webpackChunkName: "core" */ '@/pages/Core/MainDisambiguation.vue');
const NotFound = () => import(/* webpackChunkName: "core" */ '@/pages/Core/NotFound.vue');
const UserAdd = () => import(/* webpackChunkName: "core" */ '@/pages/Core/UserAdd.vue');
const UserEdit = () => import(/* webpackChunkName: "core" */ '@/pages/Core/UserEdit.vue');
const UserList = () => import(/* webpackChunkName: "core" */ '@/pages/Core/UserList.vue');
const SignIn = () => import(/* webpackChunkName: "core" */ '@/pages/Core/SignIn.vue');
const SshKeyList = () => import(/* webpackChunkName: "core" */'@/pages/Core/SshKeyList.vue');
const SshKeyAdd = () => import(/* webpackChunkName: "core" */'@/pages/Core/SshKeyAdd.vue');

const IqrfNetDisambiguation = () => import(/* webpackChunkName: "iqrfNet" */ '@/pages/IqrfNet/IqrfNetDisambiguation.vue');
const DeviceEnumeration = () => import(/* webpackChunkName: "iqrfNet" */ '@/pages/IqrfNet/DeviceEnumeration.vue');
const NetworkManager = () => import(/* webpackChunkName: "iqrfNet" */ '@/pages/IqrfNet/NetworkManager.vue');
const SendJsonRequest = () => import(/* webpackChunkName: "iqrfNet" */ '@/pages/IqrfNet/SendJsonRequest.vue');
const SendDpaPacket = () => import(/* webpackChunkName: "iqrfNet" */ '@/pages/IqrfNet/SendDpaPacket.vue');
const StandardManager = () => import(/* webpackChunkName: "iqrfNet" */ '@/pages/IqrfNet/StandardManager.vue');
const TrConfiguration = () => import(/* webpackChunkName: "iqrfNet" */ '@/pages/IqrfNet/TrConfiguration.vue');
const TrUpload = () => import(/* webpackChunkName: "iqrfNet" */ '@/pages/IqrfNet/TrUpload.vue');

const InstallationBase = () => import(/* webpackChunkName: "install" */ '@/pages/Install/InstallationBase.vue');
const InstallationWizard = () => import(/* webpackChunkName: "install" */ '@/pages/Install/InstallationWizard.vue');
const InstallGatewayInfo = () => import(/* webpackChunkName: "install" */ '@/pages/Install/InstallGatewayInfo.vue');
const MissingExtension = () => import(/* webpackChunkName: "install" */ '@/pages/Install/MissingExtension.vue');
const MissingMigration = () => import(/* webpackChunkName: "install" */ '@/pages/Install/MissingMigration.vue');
const SudoError = () => import(/* webpackChunkName: "install" */ '@/pages/Install/SudoError.vue');

const ConfigDisambiguation = () => import(/* webpackChunkName: "config" */ '@/pages/Config/ConfigDisambiguation.vue');
const DaemonDisambiguation = () => import(/* WebpackChunkName: "config" */ '@/pages/Config/DaemonDisambiguation.vue');
const Interfaces = () => import(/* WebpackChunkName: "config" */ '@/pages/Config/Interfaces.vue');
const MiscConfiguration = () => import (/* WebpackChunkName: "config" */ '@/pages/Config/MiscConfiguration.vue');
const ConfigMigration = () => import(/* webpackChunkName: "config" */ '@/pages/Config/ConfigMigration.vue');
const TranslatorConfig = () => import(/* webpackChunkName: "config" */ '@/pages/Config/TranslatorConfig.vue');
const ControllerConfig = () => import(/* webpackChunkName: "config" */ '@/pages/Config/ControllerConfig.vue');
const MonitorForm = () => import(/* webpackChunkName: "config" */ '@/pages/Config/MonitorForm.vue');
const MessagingDisambiguation = () => import(/* webpackChunkName: "config" */ '@/pages/Config/MessagingDisambiguation.vue');
const MqMessagingTable = () => import(/* webpackChunkName: "config" */ '@/pages/Config/MqMessagingTable.vue');
const MqMessagingForm = () => import(/* webpackChunkName: "config" */ '@/pages/Config/MqMessagingForm.vue');
const MqttMessagingTable = () => import(/* webpackChunkName: "config" */ '@/pages/Config/MqttMessagingTable.vue');
const MqttMessagingForm = () => import(/* webpackChunkName: "config" */ '@/pages/Config/MqttMessagingForm.vue');
const UdpMessagingTable = () => import(/* webpackChunkName: "config" */ '@/pages/Config/UdpMessagingTable.vue');
const UdpMessagingForm = () => import(/* webpackChunkName: "config" */ '@/pages/Config/UdpMessagingForm.vue');
const TracerForm = () => import(/* webpackChunkName: "config" */ '@/pages/Config/TracerForm.vue');
const MainConfiguration = () => import(/* webpackChunkName: "config" */ '@/pages/Config/MainConfiguration.vue');
const ComponentList = () => import(/* webpackChunkName: "config" */ '@/pages/Config/ComponentList.vue');
const ComponentForm = () => import(/* webpackChunkName: "config" */ '@/pages/Config/ComponentForm.vue');
const WebsocketList = () => import(/* webpackChunkName: "config" */ '@/pages/Config/WebsocketList.vue');
const WebsocketInterfaceForm = () => import(/* webpackChunkName: "config" */ '@/pages/Config/WebsocketInterfaceForm.vue');
const WebsocketMessagingForm = () => import(/* webpackChunkName: "config" */ '@/pages/Config/WebsocketMessagingForm.vue');
const WebsocketServiceForm = () => import(/* webpackChunkName: "config" */ '@/pages/Config/WebsocketServiceForm.vue');
const SchedulerList = () => import(/* webpackChunkName: "config" */ '@/pages/Config/SchedulerList.vue');
const SchedulerForm = () => import(/* webpackChunkName: "config" */ '@/pages/Config/SchedulerForm.vue');
const IqrfRepositoryConfig = () => import(/* webpackChunkName: "config" */'@/pages/Config/IqrfRepositoryConfig.vue');

const NetworkDisambiguation = () => import(/* webpackChunkName: "network" */ '@/pages/Network/NetworkDisambiguation.vue');
const ConnectionForm = () => import(/* webpackChunkName: "network" */ '@/pages/Network/ConnectionForm.vue');
const EthernetConnections = () => import(/* webpackChunkName: "network" */ '@/pages/Network/EthernetConnections.vue');
const WifiConnections = () => import(/* webpackChunkName: "network" */ '@/pages/Network/WifiConnections.vue');
const WireguardTunnels = () => import(/* webpackChunkName: "network" */ '@/pages/Network/WireguardTunnels.vue');
const WireguardTunnel = () => import(/* webpackChunkName: "network" */ '@/pages/Network/WireguardTunnel.vue');

const MaintenanceDisambiguation = () => import(/* webpackChunkName: "maintenance" */ '@/pages/Maintenance/MaintenanceDisambiguation.vue');
const PixlaControl = () => import(/* webpackChunkName: "maintenance" */ '@/pages/Maintenance/PixlaControl.vue');
const MenderDisambiguation = () => import(/* webpackChunkName: "maintenance" */ '@/pages/Maintenance/MenderDisambiguation.vue');
const MenderControl = () => import(/* webpackChunkName: "maintenance" */ '@/pages/Maintenance/MenderControl.vue');
const MenderUpdate = () => import(/* webpackChunkName: "maintenance" */ '@/pages/Maintenance/MenderUpdate.vue');
const MonitControl = () => import(/* webpackChunkName: "maintenance" */ '@/pages/Maintenance/MonitControl.vue');

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
				component: InstallationWizard,
				path: '',
			},
			{
				component: InstallGatewayInfo,
				path: 'gateway-info',
			},
			{
				component: MissingMigration,
				path: 'error/missing-migration',
			},
			{
				name: 'missing-extension',
				component: MissingExtension,
				path: 'error/missing-extension',
				props: true,
			},
			{
				name: 'sudo-error',
				component: SudoError,
				path: 'error/sudo-error',
				props: true,
			}
		]
	},
	{
		path: '/service/:serviceName',
		redirect: '/gateway/service/:serviceName',
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
						path: 'main',
						redirect: 'daemon/main',
					},
					{
						path: 'component',
						redirect: (store.getters['user/getRole'] === 'power' ? 'daemon/component' : 'daemon'),
					},
					{
						path: 'component/add',
						redirect: 'daemon/component/add',
					},
					{
						path: 'component/edit/:component',
						redirect: 'daemon/component/edit/:component',
					},
					{
						path: 'iqrf-cdc',
						redirect: 'daemon/interfaces',
					},
					{
						path: 'iqrf-dpa',
						redirect: 'daemon/interfaces',
					},
					{
						path: 'iqrf-spi',
						redirect: 'daemon/interfaces',
					},
					{
						path: 'iqrf-uart',
						redirect: 'daemon/interfaces',
					},
					{
						path: 'iqrf-info',
						redirect: {
							name: 'misc'
						},
					},
					{
						path: 'iqrf-repository',
						redirect: {
							name: 'misc'
						},
					},
					{
						path: 'json-raw-api',
						redirect: {
							name: 'misc'
						},
					},
					{
						path: 'json-mng-meta-data-api',
						redirect: {
							name: 'misc'
						},
					},
					{
						path: 'json-splitter',
						redirect: {
							name: 'misc'
						},
					},
					{
						path: 'monitor',
						redirect: {
							name: 'misc'
						},
					},
					{
						path: 'monitor/add',
						redirect: 'daemon/misc/monitor/add',
					},
					{
						path: 'monitor/edit/:instance',
						redirect: 'daemon/misc/monitor/edit/:instance',
					},
					{
						path: 'mq',
						redirect: 'daemon/messagings/mq',
					},
					{
						path: 'mq/add',
						redirect: 'daemon/messagings/mq/add',
					},
					{
						path: 'mq/edit/:instance',
						redirect: 'daemon/messagings/mq/edit/:instance',
					},
					{
						path: 'mqtt',
						redirect: 'daemon/messagings/mqtt'
					},
					{
						path: 'mqtt/add',
						redirect: 'daemon/messagings/mqtt/add',
					},
					{
						path: 'mqtt/edit/:instance',
						redirect: 'daemon/messagings/mqtt/edit/:instance',
					},
					{
						path: 'udp',
						redirect: 'daemon/messagings/udp',
					},
					{
						path: 'udp/add',
						redirect: 'daemon/messagings/udp/add',
					},
					{
						path: 'udp/edit/:instance',
						redirect: 'daemon/messagings/udp/edit/:instance',
					},
					{
						path: 'websocket',
						redirect: 'daemon/messagings/websocket',
					},
					{
						path: 'websocket/add',
						redirect: 'daemon/messagings/websocket/add',
					},
					{
						path: 'websocket/edit/:instnace',
						redirect: 'daemon/messagings/weboscket/edit/:instance',
					},
					{
						path: 'websocket/add-messaging',
						redirect: 'daemon/messagings/websocket/add-messaging',
					},
					{
						path: 'websocket/edit-messaging/:instance',
						redirect: 'daemon/messagings/websocket/edit-messaging/:instance',
					},
					{
						path: 'websocket/add-service',
						redirect: 'daemon/messagings/websocket/add-service',
					},
					{
						path: 'websocket/edit-service/:instance',
						redirect: 'daemon/messagings/websocket/edit-service/:instance',
					},
					{
						path: 'scheduler',
						redirect: 'daemon/scheduler',
					},
					{
						path: 'scheduler/add',
						redirect: 'daemon/scheduler/add',
					},
					{
						path: 'scheduler/edit/:id',
						redirect: 'daemon/scheduler/edit/:id',
					},
					{
						path: 'tracer',
						redirect: {
							name: 'misc'
						}
					},
					{
						path: 'tracer/add',
						redirect: 'daemon/misc/tracer/add',
					},
					{
						path: 'tracer/edit/:instance',
						redirect: 'daemon/misc/tracer/edit/:instance',
					},
					{
						path: 'daemon',
						component: {
							render(c) {
								return c('router-view');
							}
						},
						children: [
							{
								path: '',
								component: DaemonDisambiguation,
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
										component: ComponentList
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
								path: 'interfaces',
								component: Interfaces,
							},
							{
								path: 'messagings',
								component: {
									render(c) {
										return c('router-view');
									}
								},
								children: [
									{
										component: MessagingDisambiguation,
										path: '',
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
												component: WebsocketList,
												path: '',
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
												component: MqttMessagingTable,
												path: '',
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
												component: UdpMessagingTable,
												path: '',
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
								path: 'misc',
								component: {
									render(c) {
										return c('router-view');
									}
								},
								children: [
									{
										name: 'misc',
										path: '',
										component: MiscConfiguration,
										props: (route) => {
											if (route.params.tabName !== undefined) {
												return {tabName: route.params.tabName};
											}
											if (route.redirectedFrom === undefined) {
												return {tabName: 'json'};
											}
											const redirect = (route.redirectedFrom.endsWith('/') ? route.redirectedFrom.slice(0, -1): route.redirectedFrom);
											if (redirect.endsWith('json-mng-meta-data-api') || redirect.endsWith('json-raw-api') || redirect.endsWith('json-splitter')) {
												return {tabName: 'json'};
											}
											if (redirect.endsWith('iqrf-repository')) {
												return {tabName: 'repository'};
											}
											if (redirect.endsWith('iqrf-info')) {
												return {tabName: 'db'};
											}
											return {tabName: redirect.split('/').pop()};
										},
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
										path: 'tracer',
										component: {
											render(c) {
												return c('router-view');
											}
										},
										children: [
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
								],
							},
						]
					},
					{
						component: TranslatorConfig,
						path: 'translator',
					},
					{
						component: ControllerConfig,
						path: 'controller',
					},
					{
						component: IqrfRepositoryConfig,
						path: 'repository',
					},
					{
						component: ConfigMigration,
						path: 'migration',
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
						component: GatewayTime,
						path: 'date-time',
					},
					{
						component: Logs,
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
					{
						component: IqrfServiceDisambiguation,
						path: 'iqrf-services'
					},
					{
						component: ServiceControl,
						name: 'serviceControl',
						path: 'service/:serviceName',
						props: true,
					},
				]
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
						path: 'ethernet',
						component: {
							render(c) {
								return c('router-view');
							}
						},
						children: [
							{
								component: EthernetConnections,
								path: '',
							},
							{
								component: ConnectionForm,
								path: 'add',
							},
							{
								name: 'edit-ethernet-connection',
								component: ConnectionForm,
								path: 'edit/:uuid',
								props: true,
							},
						]
					},
					{
						path: 'wireless',
						component: {
							render(c) {
								return c('router-view');
							}
						},
						children: [
							{
								component: WifiConnections,
								path: '',
							},
							{
								name: 'add-wireless-connection',
								component: ConnectionForm,
								path: 'add',
								props: true,
							},
							{
								name: 'edit-wireless-connection',
								component: ConnectionForm,
								path: 'edit/:uuid',
								props: true,
							}
						]
					},
					{
						path: 'vpn',
						component: {
							render(c) {
								return c('router-view');
							}
						},
						children: [
							{
								component: WireguardTunnels,
								path: '',
							},
							{
								component: WireguardTunnel,
								path: 'add'
							},
							{
								component: WireguardTunnel,
								path: 'edit/:id',
								props: (route) => {
									const id = Number.parseInt(route.params.id, 10);
									if (Number.isNaN(id)) {
										return 0;
									}
									return {id};
								},
							},
						],
					},
				]
			},
			{
				path: '/maintenance',
				component: {
					render(c) {
						return c('router-view');
					}
				},
				children: [
					{
						component: MaintenanceDisambiguation,
						path: '',
					},
					{
						component: PixlaControl,
						path: 'pixla',
					},
					{
						path: 'mender',
						component: {
							render(c) {
								return c('router-view');
							}
						},
						children: [
							{
								component: MenderDisambiguation,
								path: '',
							},
							{
								component: MenderControl,
								path: 'service',
							},
							{
								component: MenderUpdate,
								path: 'update',
							},
						],
					},
					{
						component: MonitControl,
						path: 'monit',
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
				path: '/ssh-key',
				component: {
					render(c) {
						return c('router-view');
					}
				},
				children: [
					{
						path: '',
						component: SshKeyList,
					},
					{
						path: 'add',
						component: SshKeyAdd,
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
	}
];

const router = new VueRouter({
	base: process.env.VUE_APP_BASE_URL,
	mode: 'history',
	routes: routes
});

router.beforeEach((to, from, next) => {
	if (!to.path.startsWith('/install/') && to.name !== 'signIn') {
		if (!store.getters['user/isLoggedIn']) {
			store.dispatch('user/signOut').then(() => {
				next({path: '/sign/in', query: {redirect: to.path}});
			});
			return;
		}
	}
	if(to.name === 'signIn' && store.getters['user/isLoggedIn']) {
		next((to.query.redirect as string|undefined) ?? '/');
		return;
	}
	next();
});

export default router;
