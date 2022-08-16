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
import {UserRole} from '@/services/AuthenticationService';

import TheDashboard from '@/components/TheDashboard.vue';

const CloudDisambiguation = () => import('@/pages/Cloud/CloudDisambiguation.vue');
const AzureCreator = () => import('@/pages/Cloud/AzureCreator.vue');
const AwsCreator = () => import('@/pages/Cloud/AwsCreator.vue');
const HexioCreator = () => import('@/pages/Cloud/HexioCreator.vue');
const IbmCreator = () => import('@/pages/Cloud/IbmCreator.vue');
const InteliGlueCreator = () => import('@/pages/Cloud/InteliGlueCreator.vue');

const GatewayDisambiguation = () => import('@/pages/Gateway/GatewayDisambiguation.vue');
const GatewayInfo = () => import('@/pages/Gateway/GatewayInfo.vue');
const GatewayTime = () => import('@/pages/Gateway/GatewayTime.vue');
const Logs = () => import('@/pages/Gateway/Logs.vue');
const DaemonMode = () => import('@/pages/Gateway/DaemonMode.vue');
const PowerControl = () => import('@/pages/Gateway/PowerControl.vue');
const IqrfServiceDisambiguation = () => import('@/pages/Gateway/IqrfServiceDisambiguation.vue');
const ServiceControl = () => import('@/pages/Gateway/ServiceControl.vue');

const MainDisambiguation = () => import('@/pages/Core/MainDisambiguation.vue');
const NotFound = () => import('@/pages/Core/NotFound.vue');
const UserAdd = () => import('@/pages/Core/UserAdd.vue');
const UserEdit = () => import('@/pages/Core/UserEdit.vue');
const UserList = () => import('@/pages/Core/UserList.vue');
const UserVerify = () => import('@/pages/Core/UserVerify.vue');
const SignIn = () => import('@/pages/Core/SignIn.vue');
const AccountBase = () => import('@/pages/Core/AccountBase.vue');
const RequestPasswordRecovery = () => import('@/pages/Core/RequestPasswordRecovery.vue');
const ConfirmPasswordRecovery = () => import('@/pages/Core/ConfirmPasswordRecovery.vue');
const UserProfile = () => import('@/pages/Core/UserProfile.vue');

const SecurityDisambiguation = () => import('@/pages/Core/SecurityDisambiguation.vue');
const ApiKeyList = () => import('@/pages/Core/ApiKeyList.vue');
const ApiKeyForm = () => import('@/pages/Core/ApiKeyForm.vue');
const SshKeyForm = () => import('@/pages/Core/SshKeyForm.vue');
const SshKeyList = () => import('@/pages/Core/SshKeyList.vue');

const IqrfNetDisambiguation = () => import('@/pages/IqrfNet/IqrfNetDisambiguation.vue');
const DeviceEnumeration = () => import('@/pages/IqrfNet/DeviceEnumeration.vue');
const NetworkManager = () => import('@/pages/IqrfNet/NetworkManager.vue');
const SendJsonRequest = () => import('@/pages/IqrfNet/SendJsonRequest.vue');
const SendDpaPacket = () => import('@/pages/IqrfNet/SendDpaPacket.vue');
const StandardManager = () => import('@/pages/IqrfNet/StandardManager.vue');
const TrConfiguration = () => import('@/pages/IqrfNet/TrConfiguration.vue');
const TrUpload = () => import('@/pages/IqrfNet/TrUpload.vue');

const InstallationBase = () => import('@/pages/Install/InstallationBase.vue');
const InstallationWizard = () => import('@/pages/Install/InstallationWizard.vue');
const InstallCreateUser = () => import('@/pages/Install/InstallCreateUser.vue');
const InstallRestore = () => import('@/pages/Install/InstallRestore.vue');
const InstallSmtpConfig = () => import('@/pages/Install/InstallSmtpConfig.vue');
const GatewayUserPassword = () => import('@/components/Gateway/Services/GatewayUserPassword.vue');
const InstallGatewayInfo = () => import('@/pages/Install/InstallGatewayInfo.vue');
const InstallSshStatus = () => import('@/pages/Install/InstallSshStatus.vue');
const MissingExtension = () => import('@/pages/Install/MissingExtension.vue');
const MissingMigration = () => import('@/pages/Install/MissingMigration.vue');
const SudoError = () => import('@/pages/Install/SudoError.vue');

const ConfigDisambiguation = () => import('@/pages/Config/ConfigDisambiguation.vue');
const DaemonDisambiguation = () => import( '@/pages/Config/DaemonDisambiguation.vue');
const Interfaces = () => import( '@/pages/Config/Interfaces.vue');
const MiscConfiguration = () => import ( '@/pages/Config/MiscConfiguration.vue');
const TranslatorConfig = () => import('@/pages/Config/TranslatorConfig.vue');
const ControllerConfig = () => import('@/pages/Config/ControllerConfig.vue');
const MonitorForm = () => import('@/pages/Config/MonitorForm.vue');
const MessagingDisambiguation = () => import('@/pages/Config/MessagingDisambiguation.vue');
const MqMessagingTable = () => import('@/pages/Config/MqMessagingTable.vue');
const MqMessagingForm = () => import('@/pages/Config/MqMessagingForm.vue');
const MqttMessagingTable = () => import('@/pages/Config/MqttMessagingTable.vue');
const MqttMessagingForm = () => import('@/pages/Config/MqttMessagingForm.vue');
const UdpMessagingTable = () => import('@/pages/Config/UdpMessagingTable.vue');
const UdpMessagingForm = () => import('@/pages/Config/UdpMessagingForm.vue');
const TracerForm = () => import('@/pages/Config/TracerForm.vue');
const WebsocketList = () => import('@/pages/Config/WebsocketList.vue');
const WebsocketInterfaceForm = () => import('@/pages/Config/WebsocketInterfaceForm.vue');
const WebsocketMessagingForm = () => import('@/pages/Config/WebsocketMessagingForm.vue');
const WebsocketServiceForm = () => import('@/pages/Config/WebsocketServiceForm.vue');
const SchedulerList = () => import('@/pages/Config/SchedulerList.vue');
const SchedulerForm = () => import('@/pages/Config/SchedulerForm.vue');
const IqrfRepositoryConfig = () => import('@/pages/Config/IqrfRepositoryConfig.vue');
const SmtpConfiguration = () => import('@/pages/Config/SmtpConfiguration.vue');

const NetworkDisambiguation = () => import('@/pages/Network/NetworkDisambiguation.vue');
const ConnectionForm = () => import('@/pages/Network/ConnectionForm.vue');
const EthernetConnections = () => import('@/pages/Network/EthernetConnections.vue');
const WifiConnections = () => import('@/pages/Network/WifiConnections.vue');
const MobileConnections = () => import('@/pages/Network/MobileConnections.vue');
const MobileConnectionForm = () => import('@/pages/Network/MobileConnectionForm.vue');
const WireguardTunnels = () => import('@/pages/Network/WireguardTunnels.vue');
const WireguardTunnel = () => import('@/pages/Network/WireguardTunnel.vue');

const MaintenanceDisambiguation = () => import('@/pages/Maintenance/MaintenanceDisambiguation.vue');
const BackupRestore = () => import('@/pages/Maintenance/BackupRestore.vue');
const PixlaControl = () => import('@/pages/Maintenance/PixlaControl.vue');
const MenderDisambiguation = () => import('@/pages/Maintenance/MenderDisambiguation.vue');
const MenderControl = () => import('@/pages/Maintenance/MenderControl.vue');
const MenderUpdate = () => import('@/pages/Maintenance/MenderUpdate.vue');
const MonitControl = () => import('@/pages/Maintenance/MonitControl.vue');

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
										path: 'edit/:id',
										component: SchedulerForm,
										props: (route) => {
											const id = Number.parseInt(route.params.id, 10);
											if (Number.isNaN(id)) {
												return 0;
											}
											return {id};
										},
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
				component: {
					render(c) {
						return c('router-view');
					}
				},
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
								component: MobileConnectionForm,
								path: 'add',
								meta: {
									role: UserRole.ADMIN,
								},
							},
							{
								component: MobileConnectionForm,
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
						component: PixlaControl,
						path: 'pixla',
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
	base: import.meta.env.VITE_BASE_URL,
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
				next({path: '/sign/in', query: {redirect: to.path}});
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
