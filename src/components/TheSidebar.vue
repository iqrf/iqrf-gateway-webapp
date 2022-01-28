<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

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
	cilToggleOff,
	cilUser,
	cilWifiSignal4,
} from '@coreui/icons';
import ThemeManager from '../helpers/themeManager';
import VueI18n from 'vue-i18n';

interface NavMemberItem {
	component?: string
	feature?: string
	href?: string
	name: VueI18n.TranslateResult
	roles: Array<string>
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
	roles?: Array<string>
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
						roles: ['power', 'normal', 'iqaros'],
						_children: [
							{
								_name: 'CSidebarNavItem',
								name: this.$t('gateway.info.title'),
								to: '/gateway/info/',
								roles: ['power', 'normal', 'iqaros'],
							},
							{
								_name: 'CSidebarNavItem',
								name: this.$t('gateway.datetime.title'),
								to: '/gateway/date-time/',
								roles: ['power', 'normal', 'iqaros'],
							},
							{
								_name: 'CSidebarNavItem',
								name: this.$t('gateway.log.title'),
								to: '/gateway/log/',
								roles: ['power', 'normal', 'iqaros'],
							},
							{
								_name: 'CSidebarNavItem',
								name: this.$t('gateway.mode.title'),
								to: '/gateway/change-mode/',
								roles: ['power', 'normal', 'iqaros'],
							},
							{
								_name: 'CSidebarNavDropdown',
								name: this.$t('service.iqrf.title'),
								to: '/gateway/iqrf-services/',
								route: '/gateway/iqrf-services/',
								roles: ['power', 'normal', 'iqaros'],
								_children: [
									{
										_name: 'CSidebarNavItem',
										name: this.$t('service.iqrf-gateway-daemon.title'),
										to: '/gateway/service/iqrf-gateway-daemon/',
										roles: ['power', 'normal', 'iqaros'],
									},
									{
										_name: 'CSidebarNavItem',
										name: this.$t('service.iqrf-gateway-controller.title'),
										to: '/gateway/service/iqrf-gateway-controller/',
										feature: 'iqrfGatewayController',
										roles: ['power', 'normal', 'iqaros'],
									},
									{
										_name: 'CSidebarNavItem',
										name: this.$t('service.iqrf-gateway-translator.title'),
										to: '/gateway/service/iqrf-gateway-translator/',
										feature: 'iqrfGatewayTranslator',
										roles: ['power', 'normal', 'iqaros'],
									},
								],
							},
							{
								_name: 'CSidebarNavItem',
								name: this.$t('service.ssh.title'),
								to: '/gateway/service/ssh/',
								feature: 'ssh',
								roles: ['power', 'normal', 'iqaros'],
							},
							{
								_name: 'CSidebarNavItem',
								name: this.$t('service.tempgw.title'),
								to: '/gateway/service/tempgw/',
								feature: 'iTemp',
								roles: ['power', 'normal', 'iqaros'],
							},
							{
								_name: 'CSidebarNavItem',
								name: this.$t('service.unattended-upgrades.title'),
								to: '/gateway/service/unattended-upgrades/',
								feature: 'unattendedUpgrades',
								roles: ['power', 'normal', 'iqaros'],
							},
							{
								_name: 'CSidebarNavItem',
								name: this.$t('service.systemd-journald.title'),
								to: '/gateway/service/systemd-journald/',
								feature: 'systemdJournal',
								roles: ['power', 'normal', 'iqaros'],
							},
							{
								_name: 'CSidebarNavItem',
								name: this.$t('gateway.power.title'),
								to: '/gateway/power/',
								roles: ['power', 'normal', 'iqaros'],
							},
							{
								_name: 'CSidebarNavItem',
								name: this.$t('gateway.backup.title'),
								to: '/gateway/backup/',
								roles: ['power', 'normal', 'iqaros'],
							}
						],
					},
					{
						_name: 'CSidebarNavDropdown',
						name: this.$t('config.title'),
						to: '/config/',
						route: '/config/',
						icon: {content: cilSettings},
						roles: ['power', 'normal', 'iqaros'],
						_children: [
							{
								_name: 'CSidebarNavDropdown',
								name: this.$t('config.daemon.title'),
								to: '/config/daemon/',
								route: '/config/daemon/',
								roles: ['power', 'normal', 'iqaros'],
								_children: [
									{
										_name: 'CSidebarNavItem',
										name: this.$t('config.daemon.main.title'),
										to: '/config/daemon/main/',
										roles: ['power'],
										_attrs: {class: 'menu-level-2'},
									},
									{
										_name: 'CSidebarNavItem',
										name: this.$t('config.daemon.components.title'),
										to: '/config/daemon/component/',
										roles: ['power'],
									},
									{
										_name: 'CSidebarNavItem',
										name: this.$t('config.daemon.interfaces.title'),
										to: '/config/daemon/interfaces/',
										roles: ['power', 'normal', 'iqaros'],
									},
									{
										_name: 'CSidebarNavDropdown',
										name: this.$t('config.daemon.messagings.title'),
										to: '/config/daemon/messagings/',
										route: '/config/daemon/messagings/',
										roles: ['power', 'normal', 'iqaros'],
										items: [
											{
												name: 'MQTT',
												to: '/config/daemon/messagings/mqtt/',
												roles: ['power', 'normal', 'iqaros'],
											},
											{
												name: 'WebSocket',
												to: '/config/daemon/messagings/websocket/',
												roles: ['power', 'normal', 'iqaros'],
											},
											{
												name: 'MQ',
												to: '/config/daemon/messagings/mq/',
												roles: ['power', 'normal', 'iqaros'],
											},
											{
												name: 'UDP',
												to: '/config/daemon/messagings/udp/',
												roles: ['power', 'normal', 'iqaros'],
											}
										]
									},
									{
										_name: 'CSidebarNavItem',
										name: this.$t('config.daemon.scheduler.title'),
										to: '/config/daemon/scheduler/',
										roles: ['power', 'normal', 'iqaros'],
									},
									{
										_name: 'CSidebarNavItem',
										name: this.$t('config.daemon.misc.title'),
										to: '/config/daemon/misc/',
										roles: ['power', 'normal', 'iqaros'],
									}
								]
							},
							{
								_name: 'CSidebarNavItem',
								name: this.$t('config.controller.title'),
								to: '/config/controller/',
								feature: 'iqrfGatewayController',
								roles: ['power', 'normal', 'iqaros'],
							},
							{
								_name: 'CSidebarNavItem',
								name: this.$t('config.translator.title'),
								to: '/config/translator/',
								feature: 'iqrfGatewayTranslator',
								roles: ['power', 'normal', 'iqaros'],
							},
							{
								_name: 'CSidebarNavItem',
								name: this.$t('config.repository.title'),
								to: '/config/repository/',
								feature: 'iqrfRepository',
								role: ['power', 'normal', 'iqaros'],
							},
							{
								_name: 'CSidebarNavItem',
								name: this.$t('config.smtp.title'),
								to: '/config/smtp/',
								roles: ['power', 'normal', 'iqaros'],
							},
						],
					},
					{
						_name: 'CSidebarNavDropdown',
						name: this.$t('iqrfnet.title'),
						to: '/iqrfnet/',
						route: '/iqrfnet/',
						icon: {content: cilWifiSignal4},
						roles: ['power', 'normal', 'iqaros'],
						items: [
							{
								name: this.$t('iqrfnet.sendPacket.title'),
								to: '/iqrfnet/send-raw/',
								roles: ['power', 'normal', 'iqaros'],
							},
							{
								name: this.$t('iqrfnet.sendJson.title'),
								to: '/iqrfnet/send-json/',
								roles: ['power', 'normal', 'iqaros'],
							},
							{
								name: this.$t('iqrfnet.trUpload.title'),
								to: '/iqrfnet/tr-upload/',
								feature: 'trUpload',
								roles: ['power', 'normal', 'iqaros'],
							},
							{
								name: this.$t('iqrfnet.trConfiguration.title'),
								to: '/iqrfnet/tr-config/',
								roles: ['power', 'normal', 'iqaros'],
							},
							{
								name: this.$t('iqrfnet.networkManager.title'),
								to: '/iqrfnet/network/',
								roles: ['power', 'normal', 'iqaros'],
							},
							{
								name: this.$t('iqrfnet.standard.title'),
								to: '/iqrfnet/standard/',
								roles: ['power', 'normal', 'iqaros'],
							},
						],
					},
					{
						_name: 'CSidebarNavDropdown',
						name: this.$t('network.title'),
						to: '/network/',
						route: '/network/',
						feature: 'networkManager',
						icon: {content: cilLan},
						roles: ['power', 'normal', 'iqaros'],
						items: [
							{
								name: this.$t('network.ethernet.title'),
								to: '/network/ethernet',
								roles: ['power', 'normal', 'iqaros'],
							},
							{
								name: this.$t('network.wireless.title'),
								to: '/network/wireless',
								roles: ['power', 'normal', 'iqaros'],
							},
							{
								name: this.$t('network.mobile.title'),
								to: '/network/mobile',
								roles: ['power', 'normal', 'iqaros'],
							},
							{
								name: this.$t('network.wireguard.title'),
								to: '/network/vpn',
								roles: ['power', 'normal', 'iqaros'],
							},
						],
					},
					{
						_name: 'CSidebarNavDropdown',
						name: this.$t('cloud.title'),
						to: '/cloud/',
						route: '/cloud/',
						icon: {content: cilCloud},
						roles: ['power', 'normal', 'iqaros'],
						items: [
							{
								name: this.$t('cloud.ibmCloud.title'),
								to: '/cloud/ibm-cloud/',
								roles: ['power', 'normal', 'iqaros'],
							},
							{
								name: this.$t('cloud.msAzure.title'),
								to: '/cloud/azure/',
								roles: ['power', 'normal', 'iqaros'],
							},
							{
								name: this.$t('cloud.amazonAws.title'),
								to: '/cloud/aws/',
								roles: ['power', 'normal', 'iqaros'],
							},
							{
								name: this.$t('cloud.hexio.title'),
								to: '/cloud/hexio/',
								roles: ['power', 'normal', 'iqaros'],
							},
							{
								name: this.$t('cloud.intelimentsInteliGlue.title'),
								to: '/cloud/inteli-glue/',
								roles: ['power', 'normal', 'iqaros'],
							},
						],
					},
					{
						_name: 'CSidebarNavDropdown',
						name: this.$t('maintenance.title'),
						to: '/maintenance/',
						route: '/maintenance/',
						icon: {content: cilSync},
						_children: [
							{
								_name: 'CSidebarNavItem',
								name: this.$t('maintenance.pixla.title'),
								to: '/maintenance/pixla/',
								feature: 'pixla',
								roles: ['power', 'normal', 'iqaros'],
							},
							{
								_name: 'CSidebarNavDropdown',
								name: this.$t('maintenance.mender.title'),
								to: '/maintenance/mender/',
								feature: 'mender',
								roles: ['power', 'normal', 'iqaros'],
								_children: [
									{
										_name: 'CSidebarNavItem',
										name: this.$t('maintenance.mender.service.title'),
										to: '/maintenance/mender/service/',
										feature: 'mender',
										roles: ['power', 'normal', 'iqaros'],
									},
									{
										_name: 'CSidebarNavItem',
										name: this.$t('maintenance.mender.update.title'),
										to: '/maintenance/mender/update/',
										feature: 'mender',
										roles: ['power', 'normal', 'iqaros'],
									},
								],
							},
							{
								_name: 'CSidebarNavItem',
								name: this.$t('maintenance.monit.title'),
								to: '/maintenance/monit/',
								feature: 'monit',
								roles: ['power', 'normal', 'iqaros'],
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
					},
					{
						_name: 'CSidebarNavDropdown',
						name: this.$t('core.nodeRed.title'),
						feature: 'nodeRed',
						icon: {content: cibNodeRed},
						roles: ['power', 'normal', 'iqaros'],
						items: [
							{
								name: this.$t('core.nodeRed.workflow.title'),
								href: this.$store.getters['features/configuration']('nodeRed').url,
								target: '_blank',
								roles: ['power', 'normal', 'iqaros'],
							},
							{
								name: this.$t('core.nodeRed.dashboard.title'),
								href: this.$store.getters['features/configuration']('nodeRed').url + 'ui/',
								target: '_blank',
								roles: ['power', 'normal', 'iqaros'],
							},
						],
					},
					{
						_name: 'CSidebarNavItem',
						name: this.$t('core.supervisor.title'),
						href: this.$store.getters['features/configuration']('supervisord').url,
						target: '_blank',
						feature: 'supervisord',
						icon: {content: cilToggleOff},
					},
					{
						_name: 'CSidebarNavItem',
						name: this.$t('core.user.title'),
						to: '/user/',
						icon: {content: cilUser},
						roles: ['power', 'normal', 'iqaros'],
					},
					{
						_name: 'CSidebarNavDropdown',
						name: this.$t('core.security.title'),
						to: '/security/',
						icon: {content: cilLockLocked},
						roles: ['power', 'normal', 'iqaros'],
						items: [
							{
								name: this.$t('core.security.apiKey.title'),
								to: '/security/api-key/',
								roles: ['power', 'normal', 'iqaros']
							},
							{
								_name: 'CSidebarNavItem',
								name: this.$t('core.security.ssh.title'),
								to: '/security/ssh-key/',
								feature: 'ssh',
								roles: ['power', 'normal', 'iqaros'],
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
		let filteredMembers: Array<NavMember> = [];
		members.forEach((member: NavMember) => {
			let children, items = false;
			if (member.roles !== undefined && !member.roles.includes(this.$store.getters['user/getRole'])) {
				return;
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
	 * @param {Array<NavMemberItems>} items Member items
	 * @returns {Array<NavMemberItems>} Member items filtered by user role and enabled features
	 */
	private filterNavMemberItems(items: Array<NavMemberItem>): Array<NavMemberItem> {
		let filteredItems: Array<NavMemberItem> = [];
		items.forEach((item: NavMemberItem) => {
			if (item.roles !== undefined &&
				!item.roles.includes(this.$store.getters['user/getRole'])) {
				return;
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
.c-sidebar-brand-full, .c-sidebar-brand-minimized {
	max-width: 85%;
	max-height: 85%;
}
</style>
