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
import i18n from '../i18n';

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

const MainDisambiguation = () => import(/* webpackChunkName: "core" */ '@/pages/Core/MainDisambiguation.vue');
const NotFound = () => import(/* webpackChunkName: "core" */ '@/pages/Core/NotFound.vue');
const UserAdd = () => import(/* webpackChunkName: "core" */ '@/pages/Core/UserAdd.vue');
const UserEdit = () => import(/* webpackChunkName: "core" */ '@/pages/Core/UserEdit.vue');
const UserList = () => import(/* webpackChunkName: "core" */ '@/pages/Core/UserList.vue');
const UserVerify = () => import(/* webpackChunkName: "core" */ '@/pages/Core/UserVerify.vue');
const SignIn = () => import(/* webpackChunkName: "core" */ '@/pages/Core/SignIn.vue');
const AccountBase = () => import(/* webpackChunkName: "core" */'@/pages/Core/AccountBase.vue');
const RequestPasswordRecovery = () => import(/* webpackChunkName: "core" */'@/pages/Core/RequestPasswordRecovery.vue');
const ConfirmPasswordRecovery = () => import(/* webpackChunkName: "core" */'@/pages/Core/ConfirmPasswordRecovery.vue');

const SecurityDisambiguation = () => import(/* webpackChunkName: "core" */'@/pages/Core/SecurityDisambiguation.vue');
const ApiKeyList = () => import(/* webpackChunkName: "core" */ '@/pages/Core/ApiKeyList.vue');
const ApiKeyForm = () => import(/* webpackChunkName: "core" */ '@/pages/Core/ApiKeyForm.vue');
const SshKeyForm = () => import(/* webpackChunkName: "core" */'@/pages/Core/SshKeyForm.vue');
const SshKeyList = () => import(/* webpackChunkName: "core" */'@/pages/Core/SshKeyList.vue');

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
const InstallCreateUser = () => import(/* webpackChunkName: "install" */'@/pages/Install/InstallCreateUser.vue');
const InstallSmtpConfig = () => import(/* webpackChunkName: "install" */'@/pages/Install/InstallSmtpConfig.vue');
const GatewayUserPassword = () => import('@/components/Gateway/GatewayUserPassword.vue');
const InstallGatewayInfo = () => import(/* webpackChunkName: "install" */ '@/pages/Install/InstallGatewayInfo.vue');
const InstallSshStatus = () => import(/* webpackChunkName: "install" */'@/pages/Install/InstallSshStatus.vue');
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
const SmtpCOnfiguration = () => import(/* webpackCunkName: "config" */'@/pages/Config/SmtpConfiguration.vue');

const NetworkDisambiguation = () => import(/* webpackChunkName: "network" */ '@/pages/Network/NetworkDisambiguation.vue');
const ConnectionForm = () => import(/* webpackChunkName: "network" */ '@/pages/Network/ConnectionForm.vue');
const EthernetConnections = () => import(/* webpackChunkName: "network" */ '@/pages/Network/EthernetConnections.vue');
const WifiConnections = () => import(/* webpackChunkName: "network" */ '@/pages/Network/WifiConnections.vue');
const MobileConnections = () => import(/* webpackChunkName: "network" */ '@/pages/Network/MobileConnections.vue');
const MobileConnectionForm = () => import(/* webpackChunkName: "network" */'@/pages/Network/MobileConnectionForm.vue');
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
		component: AccountBase,
		path: '/account',
		children: [
			{
				component: RequestPasswordRecovery,
				name: 'requestRecovery',
				path: 'recovery',
			},
			{
				component: ConfirmPasswordRecovery,
				name: 'confirmRecovery',
				path: 'recovery/:recoveryId',
				props: true,
			},
			{
				name: 'userVerify',
				component: UserVerify,
				path: 'verification/:uuid',
				props: true,
			}
		]
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
				component: InstallCreateUser,
				path: 'webapp-user',
			},
			{
				component: InstallSmtpConfig,
				path: 'smtp',
			},
			{
				component: GatewayUserPassword,
				path: 'gateway-user',
			},
			{
				component: SshKeyForm,
				path: 'ssh-keys',
			},
			{
				component: InstallSshStatus,
				path: 'ssh-status',
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
		name: i18n.t('core.dashboard').toString(),
		redirect: '/',
		children: [
			{
				path: '/cloud',
				name: i18n.t('cloud.title').toString(),
				redirect: '/cloud/',
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
						name: i18n.t('cloud.msAzure.title').toString(),
					},
					{
						component: AwsCreator,
						path: 'aws',
						name: i18n.t('cloud.amazonAws.title').toString(),
					},
					{
						component: HexioCreator,
						path: 'hexio',
						name: i18n.t('cloud.hexio.title').toString(),
					},
					{
						component: IbmCreator,
						path: 'ibm-cloud',
						name: i18n.t('cloud.ibmCloud.title').toString(),
					},
					{
						component: InteliGlueCreator,
						path: 'inteli-glue',
						name: i18n.t('cloud.intelimentsInteliGlue.title').toString(),
					},
				]
			},
			{
				path: '/config',
				name: i18n.t('config.title').toString(),
				redirect: '/config/',
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
						path: 'daemon',
						name: i18n.t('config.daemon.title').toString(),
						redirect: '/config/daemon/',
						component: {
							render(c) {
								return c('router-view');
							}
						},
						children: [
							{
								component: DaemonDisambiguation,
								path: '',
							},
							{
								component: MainConfiguration,
								path: 'main',
								name: i18n.t('config.daemon.main.title').toString(),
							},
							{
								path: 'component',
								name: i18n.t('config.daemon.components.title').toString(),
								redirect: '/config/daemon/component/',
								component: {
									render(c) {
										return c('router-view');
									}
								},
								children: [
									{
										component: ComponentList,
										path: '',
									},
									{
										component: ComponentForm,
										path: 'add',
										name: i18n.t('config.daemon.components.add').toString(),
									},
									{
										component: ComponentForm,
										path: 'edit/:component',
										name: i18n.t('config.daemon.components.edit').toString(),
										props: true,
									},
								],
							},
							{
								component: Interfaces,
								path: 'interfaces',
								name: i18n.t('config.daemon.interfaces.title').toString(),
							},
							{
								path: 'messagings',
								name: i18n.t('config.daemon.messagings.title').toString(),
								redirect: '/config/daemon/messagings/',
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
										name: i18n.t('config.daemon.messagings.websocket.title').toString(),
										redirect: '/config/daemon/messagings/websocket/',
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
												name: i18n.t('config.daemon.messagings.websocket.interface.add').toString(),
											},
											{
												component: WebsocketMessagingForm,
												path: 'add-messaging',
												name: i18n.t('config.daemon.messagings.websocket.messaging.add').toString(),
											},
											{
												component: WebsocketServiceForm,
												path: 'add-service',
												name: i18n.t('config.daemon.messagings.websocket.service.add').toString(),
											},
											{
												component: WebsocketInterfaceForm,
												path: 'edit/:instance',
												name: i18n.t('config.daemon.messagings.websocket.interface.edit').toString(),
												props: true,
											},
											{
												component: WebsocketMessagingForm,
												path: 'edit-messaging/:instance',
												name: i18n.t('config.daemon.messagings.websocket.messaging.edit').toString(),
												props: true,
											},
											{
												component: WebsocketServiceForm,
												path: 'edit-service/:instance',
												name: i18n.t('config.daemon.messagings.websocket.service.edit').toString(),
												props: true,
											},
										],
									},
									{
										path: 'mq',
										name: i18n.t('config.daemon.messagings.mq.title').toString(),
										redirect: '/config/daemon/messagings/mq/',
										component: {
											render(c) {
												return c('router-view');
											}
										},
										children: [
											{
												component: MqMessagingTable,
												path: '',
											},
											{
												component: MqMessagingForm,
												path: 'add',
												name: i18n.t('config.daemon.messagings.mq.add').toString(),
											},
											{
												component: MqMessagingForm,
												path: 'edit/:instance',
												name: i18n.t('config.daemon.messagings.mq.edit').toString(),
												props: true,
											},
										],
									},
									{
										path: 'mqtt',
										name: i18n.t('config.daemon.messagings.mqtt.title').toString(),
										redirect: '/config/daemon/messagings/mqtt/',
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
												name: i18n.t('config.daemon.messagings.mqtt.add').toString(),
											},
											{
												component: MqttMessagingForm,
												path: 'edit/:instance',
												name: i18n.t('config.daemon.messagings.mqtt.edit').toString(),
												props: true,
											},
										],
									},
									{
										path: 'udp',
										name: i18n.t('config.daemon.messagings.udp.title').toString(),
										redirect: '/config/daemon/messagings/udp/',
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
												name: i18n.t('config.daemon.messagings.udp.add').toString(),
											},
											{
												component: UdpMessagingForm,
												path: 'edit/:instance',
												name: i18n.t('config.daemon.messagings.udp.edit').toString(),
												props: true,
											},
										],
									},
								],
							},
							{
								path: 'scheduler',
								name: i18n.t('config.daemon.scheduler.title').toString(),
								redirect: '/config/daemon/scheduler/',
								component: {
									render(c) {
										return c('router-view');
									}
								},
								children: [
									{
										component: SchedulerList,
										path: '',
									},
									{
										component: SchedulerForm,
										path: 'add',
										name: i18n.t('config.daemon.scheduler.add').toString(),
									},
									{
										component: SchedulerForm,
										path: 'edit/:id',
										name: i18n.t('config.daemon.scheduler.edit').toString(),
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
								name: i18n.t('config.daemon.misc.title').toString(),
								redirect: '/config/daemon/misc/',
								component: {
									render(c) {
										return c('router-view');
									}
								},
								children: [
									{
										path: '',
										component: MiscConfiguration,
										props: () => {
											return {tabName: 'json'};
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
												name: i18n.t('config.daemon.misc.monitor.add').toString(),
											},
											{
												component: MonitorForm,
												path: 'edit/:instance',
												name: i18n.t('config.daemon.misc.monitor.edit').toString(),
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
												name: i18n.t('config.daemon.misc.tracer.add').toString(),
											},
											{
												component: TracerForm,
												path: 'edit/:instance',
												name: i18n.t('config.daemon.misc.tracer.edit').toString(),
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
						name: i18n.t('config.translator.title').toString(),
					},
					{
						component: ControllerConfig,
						path: 'controller',
						name: i18n.t('config.controller.title').toString(),
					},
					{
						component: IqrfRepositoryConfig,
						path: 'repository',
						name: i18n.t('config.repository.title').toString(),
					},
					{
						component: SmtpCOnfiguration,
						path: 'smtp',
						name: i18n.t('config.smtp.title').toString(),
					},
					{
						component: ConfigMigration,
						path: 'migration',
						name: i18n.t('config.migration.title').toString(),
					},
				]
			},
			{
				path: '/gateway',
				name: i18n.t('gateway.title').toString(),
				redirect: '/gateway/',
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
						name: i18n.t('gateway.info.title').toString(),
					},
					{
						component: GatewayTime,
						path: 'date-time',
						name: i18n.t('gateway.datetime.title').toString(),
					},
					{
						component: Logs,
						path: 'log',
						name: i18n.t('gateway.log.title').toString(),
					},
					{
						component: DaemonMode,
						path: 'change-mode',
						name: i18n.t('gateway.mode.title').toString(),
					},
					{
						component: PowerControl,
						path: 'power',
						name: i18n.t('gateway.power.title').toString(),
					},
					{
						component: IqrfServiceDisambiguation,
						path: 'iqrf-services',
						name: i18n.t('service.iqrf.title').toString(),
					},
					{
						component: ServiceControl,
						name: 'Systemd service control',
						path: 'service/:serviceName',
						props: true,
					},
				]
			},
			{
				path: '/iqrfnet',
				name: i18n.t('iqrfnet.title').toString(),
				redirect: '/iqrfnet/',
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
						name: i18n.t('iqrfnet.enumeration.title').toString(),
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
						name: i18n.t('iqrfnet.networkManager.title').toString(),
					},
					{
						component: StandardManager,
						path: 'standard',
						name: i18n.t('iqrfnet.standard.title').toString(),
					},
					{
						component: SendDpaPacket,
						path: 'send-raw',
						name: i18n.t('iqrfnet.sendPacket.title').toString(),
					},
					{
						component: SendJsonRequest,
						path: 'send-json',
						name: i18n.t('iqrfnet.sendJson.title').toString(),
					},
					{
						component: TrConfiguration,
						path: 'tr-config',
						name: i18n.t('iqrfnet.trConfiguration.title').toString(),
					},
					{
						component: TrUpload,
						path: 'tr-upload',
						name: i18n.t('iqrfnet.trUpload.title').toString(),
					},
				]
			},
			{
				path: '/network',
				name: i18n.t('network.title').toString(),
				redirect: '/network/',
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
						name: i18n.t('network.ethernet.title').toString(),
						redirect: '/network/ethernet/',
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
								name: i18n.t('network.ethernet.add').toString(),
							},
							{
								name: i18n.t('network.ethernet.edit').toString(),
								component: ConnectionForm,
								path: 'edit/:uuid',
								props: true,
							},
						]
					},
					{
						path: 'wireless',
						name: i18n.t('network.wireless.title').toString(),
						redirect: '/network/wireless/',
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
								component: ConnectionForm,
								path: 'add',
								name: i18n.t('network.connection.add').toString(),
								props: true,
							},
							{
								component: ConnectionForm,
								path: 'edit/:uuid',
								name: i18n.t('network.connectin.edit').toString(),
								props: true,
							}
						]
					},
					{
						path: 'mobile',
						name: i18n.t('network.mobile.title').toString(),
						redirect: '/network/mobile/',
						component: {
							render(c) {
								return c('router-view');
							},
						},
						children: [
							{
								component: MobileConnections,
								path: '',
							},
							{
								component: MobileConnectionForm,
								path: 'add',
								name: i18n.t('network.mobile.add').toString(),
							},
							{
								component: MobileConnectionForm,
								path: 'edit/:uuid',
								name: i18n.t('network.mobile.edit').toString(),
								props: true,
							},
						]
					},
					{
						path: 'vpn',
						name: i18n.t('network.wireguard.title').toString(),
						redirect: '/network/vpn/',
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
								path: 'add',
								name: i18n.t('network.wireguard.tunnels.add').toString(),
							},
							{
								component: WireguardTunnel,
								path: 'edit/:id',
								name: i18n.t('network.wireguard.tunnels.edit').toString(),								props: (route) => {
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
				name: i18n.t('maintenance.title').toString(),
				redirect: '/maintenance/',
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
						name: i18n.t('maintenance.pixla.title').toString(),
					},
					{
						path: 'mender',
						name: i18n.t('maintenance.mender.title').toString(),
						redirect: '/maintenance/mender/',
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
								name: i18n.t('maintenance.mender.service.title').toString(),
							},
							{
								component: MenderUpdate,
								path: 'update',
								name: i18n.t('maintenance.mender.update.title').toString(),
							},
						],
					},
					{
						component: MonitControl,
						path: 'monit',
						name: i18n.t('maintenance.monit.title').toString(),
					},
				]
			},
			{
				path: '/user',
				name: i18n.t('core.user.title').toString(),
				redirect: '/user/',
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
						name: i18n.t('core.user.add').toString(),
					},
					{
						component: UserEdit,
						path: 'edit/:userId',
						name: i18n.t('core.user.edit').toString(),
						props: (route) => {
							const userId = Number.parseInt(route.params.userId, 10);
							if (Number.isNaN(userId)) {
								return 0;
							}
							return {userId};
						},
					}
				]
			},
			{
				path: '/security',
				name: i18n.t('core.security.title').toString(),
				redirect: '/security/',
				component: {
					render(c) {
						return c('router-view');
					}
				},
				children: [
					{
						path: '',
						component: SecurityDisambiguation,
					},
					{
						path: 'api-key',
						name: i18n.t('core.security.apiKey.title').toString(),
						redirect: '/security/api-key/',
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
								name: i18n.t('core.security.apiKey.add').toString(),
							},
							{
								component: ApiKeyForm,
								path: 'edit/:keyId',
								name: i18n.t('core.security.apiKey.edit').toString(),
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
						path: 'ssh-key',
						name: i18n.t('core.security.ssh.title').toString(),
						redirect: '/security/ssh-key/',
						component: {
							render(c) {
								return c('router-view');
							}
						},
						children: [
							{
								component: SshKeyList,
								path: '',

							},
							{
								component: SshKeyForm,
								path: 'add',
								name: i18n.t('core.security.ssh.add').toString(),
							},
						],
					},
				]
			},
			{
				component: MainDisambiguation,
				path: '',
			},
			{
				component: NotFound,
				path: '*',
				name: i18n.t('core.error.404.title').toString(),
			},
		],
	}
];

const router = new VueRouter({
	base: process.env.VUE_APP_BASE_URL,
	mode: 'history',
	routes: routes
});

const whitelist = [
	'signIn',
	'requestRecovery',
	'confirmRecovery',
	'userVerify'
];

router.beforeEach((to, from, next) => {
	if (to.path.match('\\/user\\/verification\\/[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-4[0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}') !== null) {
		next();
		return;
	}
	if (!to.path.startsWith('/install/') && (typeof to.name !== 'string' || !whitelist.includes(to.name))) {
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
