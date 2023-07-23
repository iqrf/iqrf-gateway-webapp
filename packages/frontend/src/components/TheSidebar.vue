<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
	<v-navigation-drawer
		v-model='show'
		:mini-variant='minimized'
		fixed
		app
		dark
		color='#3c4b64'
	>
		<template #prepend>
			<v-list-item class='logo'>
				<router-link to='/'>
					<v-img
						:alt='title'
						:src='logo'
						contain
						height='48px'
					/>
				</router-link>
			</v-list-item>
		</template>
		<v-divider />
		<SidebarItems :items='items' />
		<template #append>
			<SidebarIndication />
			<v-list dense>
				<v-list-item style='margin-top: auto;' @click.stop='$store.commit("sidebar/toggleSize")'>
					<v-list-item-action>
						<v-icon dense>
							mdi-{{ `chevron-${minimized ? 'right' : 'left'}` }}
						</v-icon>
					</v-list-item-action>
				</v-list-item>
			</v-list>
		</template>
	</v-navigation-drawer>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import SidebarIndication from './SidebarIndication.vue';
import SidebarItems, {NavigationItem} from '@/components/SidebarItems.vue';
import ThemeManager from '@/helpers/themeManager';
import {UserRoleIndex} from '@/services/AuthenticationService';
import {LinkTarget} from '@/helpers/DisambiguationHelper';

@Component({
	components: {
		SidebarItems,
		SidebarIndication,
	},
	data: () => ({
		ThemeManager,
	}),
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
		return this.$store.getters['sidebar/isVisible'];
	}

	/**
	 * Sets sidebar visibility
	 * @param {boolean} value Visibility
	 */
	set show(value: boolean) {
		this.$store.commit('sidebar/setVisibility', value);
	}

	/**
	 * Computes sidebar minimize state
	 * @returns {boolean} Sidebar minimize state
	 */
	get minimized(): boolean {
		return this.$store.getters['sidebar/isMinimized'];
	}

	/**
	 * Returns the logo URL
	 * @returns {string} Log URL
	 */
	get logo(): string {
		return this.minimized ? ThemeManager.getSidebarSmallLogo() : ThemeManager.getSidebarLogo();
	}

	/**
	 * @constant {Array<NavigationItem>} items Navigation menu items
	 */
	private items: Array<NavigationItem> = [
		{
			title: this.$t('gateway.title').toString(),
			to: '/gateway/',
			icon: 'mdi-server',
			role: UserRoleIndex.BASIC,
			children: [
				{
					title: this.$t('gateway.info.title').toString(),
					to: '/gateway/info/',
					role: UserRoleIndex.BASIC,
				},
				{
					title: this.$t('gateway.datetime.title').toString(),
					to: '/gateway/date-time/',
					role: UserRoleIndex.NORMAL
				},
				{
					title: this.$t('gateway.log.title').toString(),
					to: '/gateway/log/',
					role: UserRoleIndex.NORMAL
				},
				{
					title: this.$t('gateway.mode.title').toString(),
					to: '/gateway/change-mode/',
					role: UserRoleIndex.NORMAL
				},
				{
					title: this.$t('service.iqrf.title').toString(),
					to: '/gateway/iqrf-services/',
					group: /^\/gateway\/service\/iqrf-.*\/$/,
					role: UserRoleIndex.NORMAL,
					children: [
						{
							title: this.$t('service.iqrf-gateway-daemon.title').toString(),
							to: '/gateway/service/iqrf-gateway-daemon/',
							role: UserRoleIndex.NORMAL,
						},
						{
							title: this.$t('service.iqrf-gateway-controller.title').toString(),
							to: '/gateway/service/iqrf-gateway-controller/',
							feature: 'iqrfGatewayController',
							role: UserRoleIndex.NORMAL,
						},
						{
							title: this.$t('service.iqrf-gateway-influxdb-bridge.title').toString(),
							to: '/gateway/service/iqrf-gateway-influxdb-bridge/',
							feature: 'iqrfGatewayInfluxdbBridge',
							role: UserRoleIndex.NORMAL,
						},
						{
							title: this.$t('service.iqrf-gateway-translator.title').toString(),
							to: '/gateway/service/iqrf-gateway-translator/',
							feature: 'iqrfGatewayTranslator',
							role: UserRoleIndex.NORMAL,
						},
					],
				},
				{
					title: this.$t('service.system.title'),
					to: '/gateway/system-services/',
					role: UserRoleIndex.NORMAL,
					children: [
						{
							title: this.$t('service.ssh.title').toString(),
							to: '/gateway/service/ssh/',
							feature: 'ssh',
							role: UserRoleIndex.ADMIN,
						},
						{
							title: this.$t('service.nodered.title').toString(),
							to: '/gateway/service/nodered/',
							feature: 'nodeRed',
							role: UserRoleIndex.NORMAL,
						},
						{
							title: this.$t('service.unattended-upgrades.title').toString(),
							to: '/gateway/service/unattended-upgrades/',
							feature: 'unattendedUpgrades',
							role: UserRoleIndex.ADMIN,
						},
						{
							title: this.$t('service.systemd-journald.title').toString(),
							to: '/gateway/service/systemd-journald/',
							feature: 'journal',
							role: UserRoleIndex.ADMIN,
						},
						{
							title: this.$t('service.apcupsd.title').toString(),
							to: '/gateway/service/apcupsd/',
							feature: 'apcupsd',
							role: UserRoleIndex.NORMAL,
						}
					]
				},
				{
					title: this.$t('gateway.power.title').toString(),
					to: '/gateway/power/',
					role: UserRoleIndex.NORMAL,
				},
			],
		},
		{
			title: this.$t('config.title').toString(),
			to: '/config/',
			icon: 'mdi-cog',
			role: UserRoleIndex.NORMAL,
			children: [
				{
					title: this.$t('config.daemon.title').toString(),
					to: '/config/daemon/',
					role: UserRoleIndex.NORMAL,
					children: [
						{
							title: this.$t('config.daemon.interfaces.title').toString(),
							to: '/config/daemon/interfaces/',
							role: UserRoleIndex.NORMAL
						},
						{
							title: this.$t('config.daemon.messagings.title').toString(),
							to: '/config/daemon/messagings/',
							role: UserRoleIndex.NORMAL,
							children: [
								{
									title: 'MQTT',
									to: '/config/daemon/messagings/mqtt/',
									role: UserRoleIndex.NORMAL,
								},
								{
									title: 'WebSocket',
									to: '/config/daemon/messagings/websocket/',
									role: UserRoleIndex.NORMAL,
								},
								{
									title: 'MQ',
									to: '/config/daemon/messagings/mq/',
									role: UserRoleIndex.NORMAL,
								},
								{
									title: 'UDP',
									to: '/config/daemon/messagings/udp/',
									role: UserRoleIndex.NORMAL,
								}
							]
						},
						{
							title: this.$t('config.daemon.scheduler.title').toString(),
							to: '/config/daemon/scheduler/',
							role: UserRoleIndex.NORMAL,
						},
						{
							title: this.$t('config.daemon.misc.title').toString(),
							to: '/config/daemon/misc/',
							role: UserRoleIndex.NORMAL,
						}
					]
				},
				{
					title: this.$t('config.controller.title').toString(),
					to: '/config/controller/',
					feature: 'iqrfGatewayController',
					role: UserRoleIndex.NORMAL,
				},
				{
					title: this.$t('config.translator.title').toString(),
					to: '/config/translator/',
					feature: 'iqrfGatewayTranslator',
					role: UserRoleIndex.NORMAL,
				},
				{
					title: this.$t('config.bridge.title').toString(),
					to: '/config/bridge/',
					feature: 'iqrfGatewayInfluxdbBridge',
					role: UserRoleIndex.NORMAL,
				},
				{
					title: this.$t('config.repository.title').toString(),
					to: '/config/repository/',
					feature: 'iqrfRepository',
					role: UserRoleIndex.NORMAL,
				},
				{
					title: this.$t('config.smtp.title').toString(),
					to: '/config/smtp/',
					role: UserRoleIndex.ADMIN,
				},
			],
		},
		{
			title: this.$t('iqrfnet.title').toString(),
			to: '/iqrfnet/',
			icon: 'mdi-broadcast',
			role: UserRoleIndex.NORMAL,
			children: [
				{
					title: this.$t('iqrfnet.sendPacket.title').toString(),
					to: '/iqrfnet/send-raw/',
					role: UserRoleIndex.NORMAL,
				},
				{
					title: this.$t('iqrfnet.sendJson.title').toString(),
					to: '/iqrfnet/send-json/',
					role: UserRoleIndex.NORMAL,
				},
				{
					title: this.$t('iqrfnet.trUpload.title').toString(),
					to: '/iqrfnet/tr-upload/',
					feature: 'trUpload',
					role: UserRoleIndex.ADMIN,
				},
				{
					title: this.$t('iqrfnet.trConfiguration.title').toString(),
					to: '/iqrfnet/tr-config/',
					role: UserRoleIndex.NORMAL,
				},
				{
					title: this.$t('iqrfnet.networkManager.title').toString(),
					to: '/iqrfnet/network/',
					role: UserRoleIndex.NORMAL,
				},
				{
					title: this.$t('iqrfnet.standard.title').toString(),
					to: '/iqrfnet/standard/',
					role: UserRoleIndex.NORMAL,
				},
			],
		},
		{
			title: this.$t('network.title').toString(),
			to: '/ip-network/',
			feature: 'networkManager',
			icon: 'mdi-lan',
			role: UserRoleIndex.ADMIN,
			children: [
				{
					title: this.$t('network.ethernet.title').toString(),
					to: '/ip-network/ethernet/',
					role: UserRoleIndex.ADMIN,
				},
				{
					title: this.$t('network.wireless.title').toString(),
					to: '/ip-network/wireless/',
					role: UserRoleIndex.ADMIN,
				},
				{
					title: this.$t('network.mobile.title').toString(),
					to: '/ip-network/mobile/',
					role: UserRoleIndex.ADMIN,
				},
				{
					title: this.$t('network.wireguard.title').toString(),
					to: '/ip-network/vpn/',
					role: UserRoleIndex.ADMIN,
				},
			],
		},
		{
			title: this.$t('cloud.title').toString(),
			to: '/cloud/',
			icon: 'mdi-cloud',
			role: UserRoleIndex.NORMAL,
			children: [
				{
					title: this.$t('cloud.ibmCloud.title').toString(),
					to: '/cloud/ibm-cloud/',
					role: UserRoleIndex.NORMAL,
				},
				{
					title: this.$t('cloud.msAzure.title').toString(),
					to: '/cloud/azure/',
					role: UserRoleIndex.NORMAL,
				},
				{
					title: this.$t('cloud.amazonAws.title').toString(),
					to: '/cloud/aws/',
					role: UserRoleIndex.NORMAL,
				},
				{
					title: this.$t('cloud.intelimentsInteliGlue.title').toString(),
					to: '/cloud/inteli-glue/',
					role: UserRoleIndex.NORMAL,
				},
			],
		},
		{
			title: this.$t('maintenance.title').toString(),
			to: '/maintenance/',
			icon: 'mdi-sync',
			role: UserRoleIndex.ADMIN,
			children: [
				{
					title: this.$t('maintenance.backup.title').toString(),
					to: '/maintenance/backup-restore/',
					role: UserRoleIndex.ADMIN,
				},
				{
					title: this.$t('maintenance.mender.title').toString(),
					to: '/maintenance/mender/',
					feature: 'mender',
					role: UserRoleIndex.ADMIN,
					children: [
						{
							title: this.$t('maintenance.mender.service.title').toString(),
							to: '/maintenance/mender/service/',
							feature: 'mender',
							role: UserRoleIndex.ADMIN,
						},
						{
							title: this.$t('maintenance.mender.update.title').toString(),
							to: '/maintenance/mender/update/',
							feature: 'mender',
							role: UserRoleIndex.ADMIN,
						},
					],
				},
				{
					title: this.$t('maintenance.monit.title').toString(),
					to: '/maintenance/monit/',
					feature: 'monit',
					role: UserRoleIndex.ADMIN,
				},
			]
		},
		{
			title: this.$t('core.grafana.title').toString(),
			href: this.$store.getters['features/configuration']('grafana').url,
			target: LinkTarget.blank,
			feature: 'grafana',
			icon: 'mdi-chart-timeline-variant',
			role: UserRoleIndex.BASIC,
		},
		{
			title: this.$t('core.nodeRed.title').toString(),
			feature: 'nodeRed',
			icon: 'mdi-code-json',
			role: UserRoleIndex.BASICADMIN,
			children: [
				{
					title: this.$t('core.nodeRed.workflow.title').toString(),
					href: this.$store.getters['features/configuration']('nodeRed').url,
					target: LinkTarget.blank,
					feature: 'nodeRed',
					role: UserRoleIndex.BASICADMIN,
				},
				{
					title: this.$t('core.nodeRed.dashboard.title').toString(),
					href: this.$store.getters['features/configuration']('nodeRed').url + 'ui/',
					target: LinkTarget.blank,
					feature: 'nodeRed',
					role: UserRoleIndex.BASIC,
				},
			]
		},
		{
			title: this.$t('core.supervisor.title').toString(),
			href: this.$store.getters['features/configuration']('supervisord').url,
			target: LinkTarget.blank,
			feature: 'supervisord',
			icon: 'mdi-toolbox',
			role: UserRoleIndex.ADMIN,
		},
		{
			title: this.$t('core.user.title').toString(),
			to: '/user/',
			icon: 'mdi-account',
			role: [UserRoleIndex.BASICADMIN, UserRoleIndex.ADMIN],
		},
		{
			title: this.$t('core.security.title').toString(),
			to: '/security/',
			icon: 'mdi-lock',
			role: UserRoleIndex.ADMIN,
			children: [
				{
					title: this.$t('core.security.apiKey.title').toString(),
					to: '/security/api-key/',
					role: UserRoleIndex.ADMIN,
				},
				{
					title: this.$t('core.security.ssh.title').toString(),
					to: '/security/ssh-key/',
					feature: 'ssh',
					role: UserRoleIndex.ADMIN,
				},
			]
		},
		{
			title: this.$t('core.documentation.title').toString(),
			href: this.$store.getters['features/configuration']('docs').url,
			target: LinkTarget.blank,
			feature: 'docs',
			icon: 'mdi-book',
			role: UserRoleIndex.BASIC,
		},
	];

	/**
	 * Returns the app title
	 * @return {string} App title
	 */
	get title(): string {
		return this.$t(ThemeManager.getTitleKey()).toString();
	}

}
</script>

<style lang='scss'>
.logo {
	justify-content: center;
}
</style>
