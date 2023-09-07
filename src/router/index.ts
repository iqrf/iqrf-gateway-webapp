/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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
import {UserRole} from '@/services/AuthenticationService';

import TheDashboard from '@/components/TheDashboard.vue';

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
const SystemServiceDisambiguation = () => import(/* webpackChunkName: "gateway" */ '@/pages/Gateway/SystemServiceDisambiguation.vue');
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
const UserProfile = () => import(/* webpackChunkName: "core" */'@/pages/Core/UserProfile.vue');

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
const InstallRestore = () => import(/* webpackChunkName: "install" */ '@/pages/Install/InstallRestore.vue');
const InstallSmtpConfig = () => import(/* webpackChunkName: "install" */'@/pages/Install/InstallSmtpConfig.vue');
const GatewayUserPassword = () => import(/* webpackChunkName: "install" */'@/components/Gateway/Services/GatewayUserPassword.vue');
const InstallGatewayInfo = () => import(/* webpackChunkName: "install" */ '@/pages/Install/InstallGatewayInfo.vue');
const InstallSshStatus = () => import(/* webpackChunkName: "install" */'@/pages/Install/InstallSshStatus.vue');
const MissingDependency = () => import(/* webpackChunkName: "install" */ '@/pages/Install/MissingDependency.vue');
const MissingExtension = () => import(/* webpackChunkName: "install" */ '@/pages/Install/MissingExtension.vue');
const MissingMigration = () => import(/* webpackChunkName: "install" */ '@/pages/Install/MissingMigration.vue');
const SudoError = () => import(/* webpackChunkName: "install" */ '@/pages/Install/SudoError.vue');

const ConfigDisambiguation = () => import(/* webpackChunkName: "config" */ '@/pages/Config/ConfigDisambiguation.vue');
const DaemonDisambiguation = () => import(/* WebpackChunkName: "config" */ '@/pages/Config/DaemonDisambiguation.vue');
const Interfaces = () => import(/* WebpackChunkName: "config" */ '@/pages/Config/Interfaces.vue');
const MiscConfiguration = () => import (/* WebpackChunkName: "config" */ '@/pages/Config/MiscConfiguration.vue');
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
const WebsocketList = () => import(/* webpackChunkName: "config" */ '@/pages/Config/WebsocketList.vue');
const WebsocketInterfaceForm = () => import(/* webpackChunkName: "config" */ '@/pages/Config/WebsocketInterfaceForm.vue');
const WebsocketMessagingForm = () => import(/* webpackChunkName: "config" */ '@/pages/Config/WebsocketMessagingForm.vue');
const WebsocketServiceForm = () => import(/* webpackChunkName: "config" */ '@/pages/Config/WebsocketServiceForm.vue');
const SchedulerList = () => import(/* webpackChunkName: "config" */ '@/pages/Config/SchedulerList.vue');
const SchedulerForm = () => import(/* webpackChunkName: "config" */ '@/pages/Config/SchedulerForm.vue');
const IqrfRepositoryConfig = () => import(/* webpackChunkName: "config" */'@/pages/Config/IqrfRepositoryConfig.vue');
const SmtpConfiguration = () => import(/* webpackChunkName: "config" */'@/pages/Config/SmtpConfiguration.vue');

const NetworkBase = () => import(/* webpackChunkName: "network" */ '@/pages/Network/NetworkBase.vue');
const NetworkDisambiguation = () => import(/* webpackChunkName: "network" */ '@/pages/Network/NetworkDisambiguation.vue');
const ConnectionForm = () => import(/* webpackChunkName: "network" */ '@/pages/Network/ConnectionForm.vue');
const EthernetConnections = () => import(/* webpackChunkName: "network" */ '@/pages/Network/EthernetConnections.vue');
const WifiConnections = () => import(/* webpackChunkName: "network" */ '@/pages/Network/WifiConnections.vue');
const MobileConnections = () => import(/* webpackChunkName: "network" */ '@/pages/Network/MobileConnections.vue');
const WireguardTunnels = () => import(/* webpackChunkName: "network" */ '@/pages/Network/WireguardTunnels.vue');
const WireguardTunnel = () => import(/* webpackChunkName: "network" */ '@/pages/Network/WireguardTunnel.vue');

const MaintenanceDisambiguation = () => import(/* webpackChunkName: "maintenance" */ '@/pages/Maintenance/MaintenanceDisambiguation.vue');
const BackupRestore = () => import(/* webpackChunkName: "maintenance" */ '@/pages/Maintenance/BackupRestore.vue');
const MenderDisambiguation = () => import(/* webpackChunkName: "maintenance" */ '@/pages/Maintenance/MenderDisambiguation.vue');
const MenderControl = () => import(/* webpackChunkName: "maintenance" */ '@/pages/Maintenance/MenderControl.vue');
const MenderUpdate = () => import(/* webpackChunkName: "maintenance" */ '@/pages/Maintenance/MenderUpdate.vue');
const MonitControl = () => import(/* webpackChunkName: "maintenance" */ '@/pages/Maintenance/MonitControl.vue');

import i18n from '@/plugins/i18n';
import store from '@/store';

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
				component: InstallRestore,
				path: 'restore',
			},
			{
				component: MissingMigration,
				path: 'error/missing-migration',
			},
			{
				name: 'missing-dependency',
				component: MissingDependency,
				path: 'error/missing-dependency',
				props: true,
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
						meta: {
							role: UserRole.NORMAL,
						},
					},
					{
						component: AzureCreator,
						path: 'azure',
						meta: {
							role: UserRole.NORMAL,
						},
					},
					{
						component: AwsCreator,
						path: 'aws',
						meta: {
							role: UserRole.NORMAL,
						},
					},
					{
						component: HexioCreator,
						path: 'hexio',
						meta: {
							role: UserRole.NORMAL,
						},
					},
					{
						component: IbmCreator,
						path: 'ibm-cloud',
						meta: {
							role: UserRole.NORMAL,
						},
					},
					{
						component: InteliGlueCreator,
						path: 'inteli-glue',
						meta: {
							role: UserRole.NORMAL,
						},
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
						meta: {
							role: UserRole.NORMAL,
						},
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
								meta: {
									role: UserRole.NORMAL,
								},
							},
							{
								path: 'interfaces',
								component: Interfaces,
								meta: {
									role: UserRole.NORMAL,
								},
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
										meta: {
											role: UserRole.NORMAL,
										},
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
												meta: {
													role: UserRole.NORMAL,
												},
											},
											{
												component: WebsocketInterfaceForm,
												path: 'add',
												meta: {
													role: UserRole.NORMAL,
												},
											},
											{
												component: WebsocketMessagingForm,
												path: 'add-messaging',
												meta: {
													role: UserRole.NORMAL,
												},
											},
											{
												component: WebsocketServiceForm,
												path: 'add-service',
												meta: {
													role: UserRole.NORMAL,
												},
											},
											{
												component: WebsocketInterfaceForm,
												path: 'edit/:instance',
												props: true,
												meta: {
													role: UserRole.NORMAL,
												},
											},
											{
												component: WebsocketMessagingForm,
												path: 'edit-messaging/:instance',
												props: true,
												meta: {
													role: UserRole.NORMAL,
												},
											},
											{
												component: WebsocketServiceForm,
												path: 'edit-service/:instance',
												props: true,
												meta: {
													role: UserRole.NORMAL,
												},
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
												meta: {
													role: UserRole.NORMAL,
												},
											},
											{
												component: MqMessagingForm,
												path: 'add',
												meta: {
													role: UserRole.NORMAL,
												},
											},
											{
												component: MqMessagingForm,
												path: 'edit/:instance',
												props: true,
												meta: {
													role: UserRole.NORMAL,
												},
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
												meta: {
													role: UserRole.NORMAL,
												},
											},
											{
												component: MqttMessagingForm,
												path: 'add',
												meta: {
													role: UserRole.NORMAL,
												},
											},
											{
												component: MqttMessagingForm,
												path: 'edit/:instance',
												props: true,
												meta: {
													role: UserRole.NORMAL,
												},
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
												meta: {
													role: UserRole.NORMAL,
												},
											},
											{
												component: UdpMessagingForm,
												path: 'add',
												meta: {
													role: UserRole.NORMAL,
												},
											},
											{
												component: UdpMessagingForm,
												path: 'edit/:instance',
												props: true,
												meta: {
													role: UserRole.NORMAL,
												},
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
										meta: {
											role: UserRole.NORMAL,
										},
									},
									{
										path: 'add',
										component: SchedulerForm,
										meta: {
											role: UserRole.NORMAL,
										},
									},
									{
										path: 'edit/:taskId',
										component: SchedulerForm,
										props: true,
										meta: {
											role: UserRole.NORMAL,
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
										meta: {
											role: UserRole.NORMAL,
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
												meta: {
													role: UserRole.NORMAL,
												},
											},
											{
												component: MonitorForm,
												path: 'edit/:instance',
												props: true,
												meta: {
													role: UserRole.NORMAL,
												},
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
												meta: {
													role: UserRole.NORMAL,
												},
											},
											{
												component: TracerForm,
												path: 'edit/:instance',
												props: true,
												meta: {
													role: UserRole.NORMAL,
												},
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
						meta: {
							role: UserRole.NORMAL,
						},
					},
					{
						component: ControllerConfig,
						path: 'controller',
						meta: {
							role: UserRole.NORMAL,
						},
					},
					{
						component: IqrfRepositoryConfig,
						path: 'repository',
						meta: {
							role: UserRole.NORMAL,
						},
					},
					{
						component: SmtpConfiguration,
						path: 'smtp',
						meta: {
							role: UserRole.ADMIN,
						},
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
						meta: {
							role: UserRole.BASIC,
						},
					},
					{
						component: GatewayInfo,
						path: 'info',
						meta: {
							role: UserRole.BASIC,
						},
					},
					{
						component: GatewayTime,
						path: 'date-time',
						meta: {
							role: UserRole.NORMAL,
						},
					},
					{
						component: Logs,
						path: 'log',
						meta: {
							role: UserRole.NORMAL,
						},
					},
					{
						component: DaemonMode,
						path: 'change-mode',
						meta: {
							role: UserRole.NORMAL,
						},
					},
					{
						component: PowerControl,
						path: 'power',
						meta: {
							role: UserRole.NORMAL,
						},
					},
					{
						component: IqrfServiceDisambiguation,
						path: 'iqrf-services',
						meta: {
							role: UserRole.NORMAL,
						},
					},
					{
						component: SystemServiceDisambiguation,
						path: 'system-services',
						meta: {
							role: UserRole.NORMAL,
						},
					},
					{
						component: ServiceControl,
						name: 'serviceControl',
						path: 'service/:serviceName',
						props: true,
						meta: {
							role: UserRole.NORMAL,
						},
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
						meta: {
							role: UserRole.NORMAL,
						},
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
						meta: {
							role: UserRole.NORMAL,
						},
					},
					{
						component: NetworkManager,
						path: 'network',
						meta: {
							role: UserRole.NORMAL,
						},
					},
					{
						component: StandardManager,
						path: 'standard',
						meta: {
							role: UserRole.NORMAL,
						},
					},
					{
						component: SendDpaPacket,
						path: 'send-raw',
						meta: {
							role: UserRole.NORMAL,
						},
					},
					{
						component: SendJsonRequest,
						path: 'send-json',
						meta: {
							role: UserRole.NORMAL,
						},
					},
					{
						component: TrConfiguration,
						path: 'tr-config',
						meta: {
							role: UserRole.NORMAL,
						},
					},
					{
						component: TrUpload,
						path: 'tr-upload',
						meta: {
							role: UserRole.ADMIN,
						},
					},
				]
			},
			{
				path: '/network',
				redirect: '/ip-network',
			},
			{
				path: '/network/ethernet',
				redirect: '/ip-network/ethernet'
			},
			{
				path: '/network/ethernet/add',
				redirect: '/ip-network/ethernet/add'
			},
			{
				path: '/network/ethernet/edit/:uuid',
				redirect: '/ip-network/ethernet/edit/:uuid'
			},
			{
				path: '/network/wireless',
				redirect: '/ip-network/wireless',
			},
			{
				path: '/network/wireless/add',
				redirect: '/ip-network/wireless/add',
			},
			{
				path: '/network/wireless/edit/:uuid',
				redirect: '/ip-network/wireless/edit/:uuid',
			},
			{
				path: '/network/mobile',
				redirect: '/ip-network/mobile',
			},
			{
				path: '/network/mobile/add',
				redirect: '/ip-network/mobile/add',
			},
			{
				path: '/network/mobile/edit/:uuid',
				redirect: '/ip-network/mobile/edit/:uuid',
			},
			{
				path: '/network/vpn',
				redirect: '/ip-network/vpn',
			},
			{
				path: '/network/vpn/add',
				redirect: '/ip-network/vpn/add',
			},
			{
				path: '/network/vpn/edit/:id',
				redirect: '/ip-network/vpn/edit/:id',
			},
			{
				path: '/ip-network',
				component: NetworkBase,
				children: [
					{
						component: NetworkDisambiguation,
						path: '',
						meta: {
							role: UserRole.ADMIN,
						},
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
								meta: {
									role: UserRole.ADMIN,
								},
							},
							{
								component: ConnectionForm,
								path: 'add',
								meta: {
									role: UserRole.ADMIN,
								},
							},
							{
								name: 'edit-ethernet-connection',
								component: ConnectionForm,
								path: 'edit/:uuid',
								props: true,
								meta: {
									role: UserRole.ADMIN,
								},
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
								meta: {
									role: UserRole.ADMIN,
								},
							},
							{
								name: 'add-wireless-connection',
								component: ConnectionForm,
								path: 'add',
								props: true,
								meta: {
									role: UserRole.ADMIN,
								},
							},
							{
								name: 'edit-wireless-connection',
								component: ConnectionForm,
								path: 'edit/:uuid',
								props: true,
								meta: {
									role: UserRole.ADMIN,
								},
							}
						]
					},
					{
						path: 'mobile',
						component: {
							render(c) {
								return c('router-view');
							},
						},
						children: [
							{
								component: MobileConnections,
								path: '',
								meta: {
									role: UserRole.ADMIN,
								},
							},
							{
								component: ConnectionForm,
								path: 'add',
								meta: {
									role: UserRole.ADMIN,
								},
							},
							{
								component: ConnectionForm,
								path: 'edit/:uuid',
								props: true,
								meta: {
									role: UserRole.ADMIN,
								},
							},
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
								meta: {
									role: UserRole.ADMIN,
								},
							},
							{
								component: WireguardTunnel,
								path: 'add',
								meta: {
									role: UserRole.ADMIN,
								},
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
								meta: {
									role: UserRole.ADMIN,
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
						meta: {
							role: UserRole.ADMIN,
						},
					},
					{
						component: BackupRestore,
						path: 'backup-restore',
						meta: {
							role: UserRole.ADMIN,
						},
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
								meta: {
									role: UserRole.ADMIN,
								},
							},
							{
								component: MenderControl,
								path: 'service',
								meta: {
									role: UserRole.ADMIN,
								},
							},
							{
								component: MenderUpdate,
								path: 'update',
								meta: {
									role: UserRole.ADMIN,
								},
							},
						],
					},
					{
						component: MonitControl,
						path: 'monit',
						meta: {
							role: UserRole.ADMIN,
						},
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
						name: 'userList',
						path: '',
						meta: {
							role: UserRole.ADMIN,
						},
					},
					{
						component: UserAdd,
						name: 'userAdd',
						path: 'add',
						meta: {
							role: UserRole.ADMIN,
						},
					},
					{
						component: UserEdit,
						name: 'userEdit',
						path: 'edit/:userId',
						props: (route) => {
							const userId = Number.parseInt(route.params.userId, 10);
							if (Number.isNaN(userId)) {
								return 0;
							}
							return {userId};
						},
						meta: {
							role: UserRole.ADMIN,
						},
					}
				]
			},
			{
				path: '/security',
				component: {
					render(c) {
						return c('router-view');
					}
				},
				children: [
					{
						path: '',
						component: SecurityDisambiguation,
						meta: {
							role: UserRole.ADMIN,
						},
					},
					{
						path: 'api-key',
						component: {
							render(c) {
								return c('router-view');
							}
						},
						children: [
							{
								path: '',
								component: ApiKeyList,
								meta: {
									role: UserRole.ADMIN,
								},
							},
							{
								component: ApiKeyForm,
								path: 'add',
								meta: {
									role: UserRole.ADMIN,
								},
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
								meta: {
									role: UserRole.ADMIN,
								},
							},
						],
					},
					{
						path: 'ssh-key',
						component: {
							render(c) {
								return c('router-view');
							}
						},
						children: [
							{
								path: '',
								component: SshKeyList,
								meta: {
									role: UserRole.ADMIN,
								},
							},
							{
								path: 'add',
								component: SshKeyForm,
								meta: {
									role: UserRole.ADMIN,
								},
							},
						],
					},
				]
			},
			{
				component: UserProfile,
				path: 'profile',
				meta: {
					role: UserRole.BASIC,
				},
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

const whitelist = [
	'signIn',
	'requestRecovery',
	'confirmRecovery',
	'userVerify'
];

const BAWhitelist = [
	'userList',
	'userAdd',
	'userEdit',
	'userForm',
];

router.beforeEach((to, _from, next) => {
	if (to.path.match('\\/user\\/verification\\/[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-4[0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}') !== null) {
		next();
		return;
	}
	if (!to.path.startsWith('/install/') && (typeof to.name !== 'string' || !whitelist.includes(to.name))) {
		if (!store.getters['user/isLoggedIn']) {
			store.dispatch('user/signOut').then(() => {
				let query = {...to.query};
				if (to.path !== '/' && to.path !== '/sign/in') {
					query = {...query, redirect: to.path};
				}
				next({path: '/sign/in', query: query});
			});
			return;
		}
	}
	if(to.name === 'signIn' && store.getters['user/isLoggedIn']) {
		next((to.query.redirect as string|undefined) ?? '/');
		return;
	}
	if (to.meta !== undefined && to.meta.role !== undefined) {
		const roleVal = store.getters['user/getRole'];
		if (roleVal === UserRole.BASICADMIN) {
			const name = to.name ?? '';
			if (BAWhitelist.includes(name)) {
				next();
				return;
			}
		}
		const roleIdx = Object.values(UserRole).indexOf(roleVal);
		const memberIdx = Object.values(UserRole).indexOf(to.meta.role);
		if (roleIdx > memberIdx) {
			Vue.$toast.error(
				i18n.t('forbiddenAccess').toString()
			);
			next('/');
			return;
		}
	}
	next();
});

export default router;
