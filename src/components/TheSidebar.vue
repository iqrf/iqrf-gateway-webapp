<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software,
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied
See the License for the specific language governing permissions and
limitations under the License.
-->
<template>
	<CSidebar
		fixed
		:minimize='minimize'
		:show='show'
		@update:show='(value) => $store.commit("sidebar/set", ["show", value])'
	>
		<CSidebarBrand class='d-md-down-none' to='/'>
			<LogoBig class='c-sidebar-brand-full' :alt='title' />
			<LogoSmall class='c-sidebar-brand-minimized' :alt='title' />
		</CSidebarBrand>
		<CRenderFunction flat :content-to-render='getNav' />
		<SidebarIndication />
		<CSidebarMinimizer
			class='d-md-down-none'
			@click.native='$store.commit("sidebar/set", ["minimize", !minimize])'
		/>
	</CSidebar>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {
	CRenderFunction,
	CSidebar,
	CSidebarBrand,
	CSidebarMinimizer,
} from '@coreui/vue/src';
import SidebarIndication from './SidebarIndication.vue';
import {
	cibGrafana,
	cibNodeRed,
	cilBook,
	cilCloud,
	cilLan,
	cilLockLocked,
	cilStorage,
	cilSettings,
	cilSync,
	cilUser,
	cilWifiSignal4,
} from '@coreui/icons';
import ThemeManager from '@/helpers/themeManager';
import VueI18n from 'vue-i18n';
import {UserRole} from '@/services/AuthenticationService';

interface NavMemberItem {
	component?: string
	feature?: string
	href?: string
	name: VueI18n.TranslateResult
	role?: UserRole
	target?: string
	to?: string
	items?: Array<NavMemberItem>
}

interface NavMemberIcon {
	content: Array<string>
}

interface NavMember {
	_name: string
	_children?: Array<NavMember>
	feature?: string
	href?: string
	icon?: NavMemberIcon
	items?: Array<NavMemberItem>
	name: VueI18n.TranslateResult
	role?: UserRole
	target?: string
	to?: string
}

interface NavData {
	_name: string
	_children: Array<NavMember>
}

@Component({
	components: {
		CRenderFunction,
		CSidebar,
		CSidebarBrand,
		CSidebarMinimizer,
		LogoBig: ThemeManager.getSidebarLogo(),
		LogoSmall: ThemeManager.getSidebarSmallLogo(),
		SidebarIndication,
	},
})

/**
 * Sidebar component
 */
export default class TheSidebar extends Vue {
	/**
	 * Computes sidebar show state
	 * @returns {boolean} Sidebar show state
	 */
	get show(): boolean {
		return this.$store.state.sidebar.show;
	}

	/**
	 * Computes sidebar minimize state
	 * @returns {boolean} Sidebar minimize state
	 */
	get minimize(): boolean {
		return this.$store.state.sidebar.minimize;
	}

	/**
	 * Whitelist of routes for basic admin user
	 */
	private BAWhitelist = [
		'/user/',
	];

	/**
	 * Computes sidebar items by filtering predefined items
	 * @returns {Array<NavData>} Filtered sidebar items
	 */
	get getNav(): Array<NavData> {
		const data = [
			{
				_name: 'CSidebarNav',
				_children: [
					{
						_name: 'CSidebarNavDropdown',
						name: this.$t('gateway.title'),
						to: '/gateway/',
						route: '/gateway/',
						icon: {content: cilStorage},
						role: UserRole.BASIC,
						_children: [
							{
								_name: 'CSidebarNavItem',
								name: this.$t('gateway.info.title'),
								to: '/gateway/info/',
								role: UserRole.BASIC,
							},
							{
								_name: 'CSidebarNavItem',
								name: this.$t('gateway.datetime.title'),
								to: '/gateway/date-time/',
								role: UserRole.NORMAL
							},
							{
								_name: 'CSidebarNavItem',
								name: this.$t('gateway.log.title'),
								to: '/gateway/log/',
								role: UserRole.NORMAL
							},
							{
								_name: 'CSidebarNavItem',
								name: this.$t('gateway.mode.title'),
								to: '/gateway/change-mode/',
								role: UserRole.NORMAL
							},
							{
								_name: 'CSidebarNavDropdown',
								name: this.$t('service.iqrf.title'),
								to: '/gateway/iqrf-services/',
								route: '/gateway/iqrf-services/',
								role: UserRole.NORMAL,
								_children: [
									{
										_name: 'CSidebarNavItem',
										name: this.$t('service.iqrf-gateway-daemon.title'),
										to: '/gateway/service/iqrf-gateway-daemon/',
										role: UserRole.NORMAL,
									},
									{
										_name: 'CSidebarNavItem',
										name: this.$t('service.iqrf-gateway-controller.title'),
										to: '/gateway/service/iqrf-gateway-controller/',
										feature: 'iqrfGatewayController',
										role: UserRole.NORMAL,
									},
									{
										_name: 'CSidebarNavItem',
										name: this.$t('service.iqrf-gateway-translator.title'),
										to: '/gateway/service/iqrf-gateway-translator/',
										feature: 'iqrfGatewayTranslator',
										role: UserRole.NORMAL,
									},
								],
							},
							{
								_name: 'CSidebarNavItem',
								name: this.$t('service.ssh.title'),
								to: '/gateway/service/ssh/',
								feature: 'ssh',
								role: UserRole.ADMIN,
							},
							{
								_name: 'CSidebarNavItem',
								name: this.$t('service.nodered.title'),
								to: '/gateway/service/nodered/',
								feature: 'nodeRed',
								role: UserRole.NORMAL,
							},
							{
								_name: 'CSidebarNavItem',
								name: this.$t('service.tempgw.title'),
								to: '/gateway/service/tempgw/',
								feature: 'iTemp',
								role: UserRole.NORMAL,
							},
							{
								_name: 'CSidebarNavItem',
								name: this.$t('service.unattended-upgrades.title'),
								to: '/gateway/service/unattended-upgrades/',
								feature: 'unattendedUpgrades',
								role: UserRole.ADMIN,
							},
							{
								_name: 'CSidebarNavItem',
								name: this.$t('service.systemd-journald.title'),
								to: '/gateway/service/systemd-journald/',
								feature: 'journal',
								role: UserRole.ADMIN,
							},
							{
								_name: 'CSidebarNavItem',
								name: this.$t('service.apcupsd.title'),
								to: '/gateway/service/apcupsd/',
								feature: 'apcupsd',
								role: UserRole.NORMAL,
							},
							{
								_name: 'CSidebarNavItem',
								name: this.$t('gateway.power.title'),
								to: '/gateway/power/',
								role: UserRole.NORMAL,
							},
						],
					},
					{
						_name: 'CSidebarNavDropdown',
						name: this.$t('config.title'),
						to: '/config/',
						route: '/config/',
						icon: {content: cilSettings},
						role: UserRole.NORMAL,
						_children: [
							{
								_name: 'CSidebarNavDropdown',
								name: this.$t('config.daemon.title'),
								to: '/config/daemon/',
								route: '/config/daemon/',
								role: UserRole.NORMAL,
								_children: [
									{
										_name: 'CSidebarNavItem',
										name: this.$t('config.daemon.interfaces.title'),
										to: '/config/daemon/interfaces/',
										role: UserRole.NORMAL
									},
									{
										_name: 'CSidebarNavDropdown',
										name: this.$t('config.daemon.messagings.title'),
										to: '/config/daemon/messagings/',
										route: '/config/daemon/messagings/',
										role: UserRole.NORMAL,
										items: [
											{
												name: 'MQTT',
												to: '/config/daemon/messagings/mqtt/',
												role: UserRole.NORMAL,
											},
											{
												name: 'WebSocket',
												to: '/config/daemon/messagings/websocket/',
												role: UserRole.NORMAL,
											},
											{
												name: 'MQ',
												to: '/config/daemon/messagings/mq/',
												role: UserRole.NORMAL,
											},
											{
												name: 'UDP',
												to: '/config/daemon/messagings/udp/',
												role: UserRole.NORMAL,
											}
										]
									},
									{
										_name: 'CSidebarNavItem',
										name: this.$t('config.daemon.scheduler.title'),
										to: '/config/daemon/scheduler/',
										role: UserRole.NORMAL,
									},
									{
										_name: 'CSidebarNavItem',
										name: this.$t('config.daemon.misc.title'),
										to: '/config/daemon/misc/',
										role: UserRole.NORMAL,
									}
								]
							},
							{
								_name: 'CSidebarNavItem',
								name: this.$t('config.controller.title'),
								to: '/config/controller/',
								feature: 'iqrfGatewayController',
								role: UserRole.NORMAL,
							},
							{
								_name: 'CSidebarNavItem',
								name: this.$t('config.translator.title'),
								to: '/config/translator/',
								feature: 'iqrfGatewayTranslator',
								role: UserRole.NORMAL,
							},
							{
								_name: 'CSidebarNavItem',
								name: this.$t('config.repository.title'),
								to: '/config/repository/',
								feature: 'iqrfRepository',
								role: UserRole.NORMAL,
							},
							{
								_name: 'CSidebarNavItem',
								name: this.$t('config.smtp.title'),
								to: '/config/smtp/',
								role: UserRole.ADMIN,
							},
						],
					},
					{
						_name: 'CSidebarNavDropdown',
						name: this.$t('iqrfnet.title'),
						to: '/iqrfnet/',
						route: '/iqrfnet/',
						icon: {content: cilWifiSignal4},
						role: UserRole.NORMAL,
						items: [
							{
								name: this.$t('iqrfnet.sendPacket.title'),
								to: '/iqrfnet/send-raw/',
								role: UserRole.NORMAL,
							},
							{
								name: this.$t('iqrfnet.sendJson.title'),
								to: '/iqrfnet/send-json/',
								role: UserRole.NORMAL,
							},
							{
								name: this.$t('iqrfnet.trUpload.title'),
								to: '/iqrfnet/tr-upload/',
								feature: 'trUpload',
								role: UserRole.ADMIN,
							},
							{
								name: this.$t('iqrfnet.trConfiguration.title'),
								to: '/iqrfnet/tr-config/',
								role: UserRole.NORMAL,
							},
							{
								name: this.$t('iqrfnet.networkManager.title'),
								to: '/iqrfnet/network/',
								role: UserRole.NORMAL,
							},
							{
								name: this.$t('iqrfnet.standard.title'),
								to: '/iqrfnet/standard/',
								role: UserRole.NORMAL,
							},
						],
					},
					{
						_name: 'CSidebarNavDropdown',
						name: this.$t('network.title'),
						to: '/ip-network/',
						route: '/ip-network/',
						feature: 'networkManager',
						icon: {content: cilLan},
						role: UserRole.ADMIN,
						items: [
							{
								name: this.$t('network.ethernet.title'),
								to: '/ip-network/ethernet/',
								role: UserRole.ADMIN,
							},
							{
								name: this.$t('network.wireless.title'),
								to: '/ip-network/wireless/',
								role: UserRole.ADMIN,
							},
							{
								name: this.$t('network.mobile.title'),
								to: '/ip-network/mobile/',
								role: UserRole.ADMIN,
							},
							{
								name: this.$t('network.wireguard.title'),
								to: '/ip-network/vpn/',
								role: UserRole.ADMIN,
							},
						],
					},
					{
						_name: 'CSidebarNavDropdown',
						name: this.$t('cloud.title'),
						to: '/cloud/',
						route: '/cloud/',
						icon: {content: cilCloud},
						role: UserRole.NORMAL,
						items: [
							{
								name: this.$t('cloud.ibmCloud.title'),
								to: '/cloud/ibm-cloud/',
								role: UserRole.NORMAL,
							},
							{
								name: this.$t('cloud.msAzure.title'),
								to: '/cloud/azure/',
								role: UserRole.NORMAL,
							},
							{
								name: this.$t('cloud.amazonAws.title'),
								to: '/cloud/aws/',
								role: UserRole.NORMAL,
							},
							{
								name: this.$t('cloud.hexio.title'),
								to: '/cloud/hexio/',
								role: UserRole.NORMAL,
							},
							{
								name: this.$t('cloud.intelimentsInteliGlue.title'),
								to: '/cloud/inteli-glue/',
								role: UserRole.NORMAL,
							},
						],
					},
					{
						_name: 'CSidebarNavDropdown',
						name: this.$t('maintenance.title'),
						to: '/maintenance/',
						route: '/maintenance/',
						icon: {content: cilSync},
						role: UserRole.ADMIN,
						_children: [
							{
								_name: 'CSidebarNavItem',
								name: this.$t('maintenance.backup.title'),
								to: '/maintenance/backup-restore/',
								role: UserRole.ADMIN,
							},
							{
								_name: 'CSidebarNavDropdown',
								name: this.$t('maintenance.mender.title'),
								to: '/maintenance/mender/',
								route: '/maintenance/mender/',
								feature: 'mender',
								role: UserRole.ADMIN,
								_children: [
									{
										_name: 'CSidebarNavItem',
										name: this.$t('maintenance.mender.service.title'),
										to: '/maintenance/mender/service/',
										feature: 'mender',
										role: UserRole.ADMIN,
									},
									{
										_name: 'CSidebarNavItem',
										name: this.$t('maintenance.mender.update.title'),
										to: '/maintenance/mender/update/',
										feature: 'mender',
										role: UserRole.ADMIN,
									},
								],
							},
							{
								_name: 'CSidebarNavItem',
								name: this.$t('maintenance.monit.title'),
								to: '/maintenance/monit/',
								feature: 'monit',
								role: UserRole.ADMIN,
							},
						]
					},
					{
						_name: 'CSidebarNavItem',
						name: this.$t('core.grafana.title'),
						href: this.$store.getters['features/configuration']('grafana').url,
						target: '_blank',
						feature: 'grafana',
						icon: {content: cibGrafana},
						role: UserRole.BASIC,
					},
					{
						_name: 'CSidebarNavDropdown',
						name: this.$t('core.nodeRed.title'),
						icon: {content: cibNodeRed},
						feature: 'nodeRed',
						role: UserRole.BASIC,
						items: [
							{
								_name: 'CSidebarNavItem',
								name: this.$t('core.nodeRed.workflow.title'),
								href: this.$store.getters['features/configuration']('nodeRed').url,
								target: '_blank',
								feature: 'nodeRed',
								role: UserRole.BASICADMIN,
							},
							{
								_name: 'CSidebarNavItem',
								name: this.$t('core.nodeRed.dashboard.title'),
								href: this.$store.getters['features/configuration']('nodeRed').url + 'ui/',
								target: '_blank',
								feature: 'nodeRed',
								role: UserRole.BASIC,
							},
						],
					},
					{
						_name: 'CSidebarNavItem',
						name: this.$t('core.supervisor.title'),
						href: this.$store.getters['features/configuration']('supervisord').url,
						target: '_blank',
						feature: 'supervisord',
						role: UserRole.ADMIN,
					},
					{
						_name: 'CSidebarNavItem',
						name: this.$t('core.user.title'),
						to: '/user/',
						icon: {content: cilUser},
						role: UserRole.ADMIN,
					},
					{
						_name: 'CSidebarNavDropdown',
						name: this.$t('core.security.title'),
						to: '/security/',
						icon: {content: cilLockLocked},
						role: UserRole.ADMIN,
						items: [
							{
								_name: 'CSidebarNavItem',
								name: this.$t('core.security.apiKey.title'),
								to: '/security/api-key/',
								role: UserRole.ADMIN,
							},
							{
								_name: 'CSidebarNavItem',
								name: this.$t('core.security.ssh.title'),
								to: '/security/ssh-key/',
								feature: 'ssh',
								role: UserRole.ADMIN,
							},
						]
					},
					{
						_name: 'CSidebarNavItem',
						name: this.$t('core.documentation.title'),
						href: this.$store.getters['features/configuration']('docs').url,
						target: '_blank',
						feature: 'docs',
						icon: {content: cilBook},
						role: UserRole.BASIC,
					},
				],
			},
		];
		return data.filter((element: NavData) => {
			element._children = this.filterNavMembers(element._children);
			return element;
		});
	}

	/**
	 * Returns the app title
	 * @return {string} App title
	 */
	get title(): string {
		return this.$t(ThemeManager.getTitleKey()).toString();
	}

	/**
	 * Filters sidebar nav members
	 * @param {Array<NavMember>} members Member children
	 * @returns {Array<NavMember>} Member children filtered by user role and enabled features
	 */
	private filterNavMembers(members: Array<NavMember>): Array<NavMember> {
		const roleVal = this.$store.getters['user/getRole'];
		const roleIdx = Object.values(UserRole).indexOf(roleVal);
		const filteredMembers: Array<NavMember> = [];
		members.forEach((member: NavMember) => {
			let children, items = false;
			if (member.role !== undefined) {
				const memberIdx = Object.values(UserRole).indexOf(member.role);
				if (roleIdx > memberIdx) {
					if (roleVal === UserRole.BASICADMIN) {
						if (!this.BAWhitelist.includes((member.to as string))) {
							return;
						}
					} else {
						return;
					}
				}
			}
			if (member.feature !== undefined && !this.$store.getters['features/isEnabled'](member.feature)) {
				return;
			}
			if (member._children !== undefined) {
				children = true;
				member._children = this.filterNavMembers(member._children);
			}
			if (member.items !== undefined) {
				items = true;
				member.items = this.filterNavMemberItems(member.items);
			}
			if (children && member._children?.length === 0) {
				return;
			}
			if (items && member.items?.length === 0) {
				return;
			}
			filteredMembers.push(member);
		});
		return filteredMembers;
	}

	/**
	 * Filters sidebar nav items
	 * @param {Array<NavMemberItem>} items Member items
	 * @returns {Array<NavMemberItem>} Member items filtered by user role and enabled features
	 */
	private filterNavMemberItems(items: Array<NavMemberItem>): Array<NavMemberItem> {
		const filteredItems: Array<NavMemberItem> = [];
		const roleVal = this.$store.getters['user/getRole'];
		const roleIdx = Object.values(UserRole).indexOf(roleVal);
		items.forEach((item: NavMemberItem) => {
			if (item.role !== undefined) {
				const memberIdx = Object.values(UserRole).indexOf(item.role);
				if (roleIdx > memberIdx) {
					if (roleVal === UserRole.BASICADMIN) {
						if (!this.BAWhitelist.includes((item.to as string))) {
							return;
						}
					} else {
						return;
					}
				}
			}
			if (item.feature !== undefined &&
				!this.$store.getters['features/isEnabled'](item.feature)) {
				return;
			}
			filteredItems.push(item);
		});
		return filteredItems;
	}

}
</script>

<style lang='scss'>
.c-sidebar-brand-full,
.c-sidebar-brand-minimized {
	max-width: 85%;
	max-height: 85%;
}
</style>
