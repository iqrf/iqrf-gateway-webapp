<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

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
		v-model='isVisible'
		theme='dark'
		:rail='isMinimized'
	>
		<SidebarItems :items='items()' />
		<template #append>
			<v-list>
				<v-list-item density='compact' style='margin-top: auto;' @click.stop='sidebarStore.toggleSize()'>
					<v-list-item-action>
						<v-icon color='white' :icon='sidebarToggleIcon' />
					</v-list-item-action>
				</v-list-item>
			</v-list>
		</template>
	</v-navigation-drawer>
</template>

<script lang='ts' setup>
import { Feature } from '@iqrf/iqrf-gateway-webapp-client/types';
import { AccessScope } from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import {
	mdiAccountKey,
	mdiBook,
	mdiBroadcast,
	mdiChevronLeft,
	mdiChevronRight,
	mdiCog,
	mdiDesktopTower,
	mdiIpNetwork,
	mdiLogin,
	mdiSecurity,
	mdiTools,
	mdiWizardHat,
	mdiWrenchClock,
} from '@mdi/js';
import { storeToRefs } from 'pinia';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

import SidebarItems from '@/components/layout/sidebar/SidebarItems.vue';
import { useFeatureStore } from '@/store/features';
import { useSidebarStore } from '@/store/sidebar';
import { useUserStore } from '@/store/user';
import { type SidebarLink } from '@/types/sidebar';

const i18n = useI18n();

const featureStore = useFeatureStore();

const userStore = useUserStore();
const { isLoggedIn } = storeToRefs(userStore);

const sidebarStore = useSidebarStore();
const { isMinimized, isVisible } = storeToRefs(sidebarStore);

const sidebarToggleIcon = computed(() => {
	return isMinimized.value ? mdiChevronRight : mdiChevronLeft;
});

/**
 * Filters sidebar items based on user roles and feature flags
 * @param {SidebarLink} item Sidebar item
 * @return {boolean} True if the item should be displayed
 */
function filter(item: SidebarLink): boolean {
	if (item.children !== undefined) {
		item.children = item.children.filter((child: SidebarLink) => filter(child));
		if (item.children.length === 0) {
			return false;
		}
	}
	if (
		((item.developmentOnly ?? false) && import.meta.env.PROD) ||
		(item.feature !== undefined && !featureStore.isEnabled(item.feature))
	) {
		return false;
	}
	if (item.scopes === undefined) {
		return true;
	}
	return item.scopes.every((scope) => userStore.hasScope(scope));
}

function items(): SidebarLink[] {
	let links: SidebarLink[];
	if (isLoggedIn.value) {
		links = [
			{
				title: i18n.t('pages.gateway.title'),
				icon: mdiDesktopTower,
				children: [
					{
						title: i18n.t('pages.gateway.information.title'),
						to: '/gateway/information',
						scopes: [AccessScope.gateway_information_read],
					},
					{
						title: i18n.t('pages.gateway.logs.title'),
						to: '/gateway/logs',
						scopes: [AccessScope.gateway_diagnostic_read],
					},
					{
						title: i18n.t('pages.gateway.mode.title'),
						to: '/gateway/mode',
						scopes: [AccessScope.config_iqrfGatewayDaemon_read],
					},
					{
						title: i18n.t('pages.gateway.services.title'),
						to: '/gateway/services',
						scopes: [AccessScope.gateway_service_read],
					},
					{
						title: i18n.t('pages.gateway.power.title'),
						to: '/gateway/power',
						scopes: [AccessScope.gateway_power_read],
					},
				],
			},
			{
				title: i18n.t('pages.config.title'),
				icon: mdiCog,
				children: [
					{
						title: i18n.t('pages.config.controller.title'),
						to: '/config/controller',
						feature: Feature.iqrfGatewayController,
						scopes: [AccessScope.config_iqrfGatewayController_read],
					},
					{
						title: i18n.t('pages.config.daemon.title'),
						to: '/config/daemon',
						scopes: [AccessScope.config_iqrfGatewayDaemon_read],
						children: [
							{
								title: i18n.t('pages.config.daemon.interfaces.title'),
								to: '/config/daemon/interfaces',
								children: [
									{
										title: i18n.t('pages.config.daemon.interfaces.dpa.title'),
										to: '/config/daemon/interfaces/dpa',
									},
									{
										title: i18n.t('pages.config.daemon.interfaces.uart.title'),
										to: '/config/daemon/interfaces/uart',
									},
									{
										title: i18n.t('pages.config.daemon.interfaces.spi.title'),
										to: '/config/daemon/interfaces/spi',
									},
									{
										title: i18n.t('pages.config.daemon.interfaces.cdc.title'),
										to: '/config/daemon/interfaces/cdc',
									},
								],
							},
							{
								title: i18n.t('pages.config.daemon.connections.title'),
								to: '/config/daemon/connections',
								children: [
									{
										title: i18n.t('pages.config.daemon.connections.mqtt.title'),
										to: '/config/daemon/connections/mqtt',
									},
									{
										title: i18n.t('pages.config.daemon.connections.ws.title'),
										to: '/config/daemon/connections/websocket',
									},
									{
										title: i18n.t('pages.config.daemon.connections.udp.title'),
										to: '/config/daemon/connections/udp',
									},
								],
							},
							{
								title: i18n.t('pages.config.daemon.scheduler.title'),
								to: '/config/daemon/scheduler',
							},
							{
								title: i18n.t('pages.config.daemon.json-api.title'),
								to: '/config/daemon/json-api',
							},
							{
								title: i18n.t('pages.config.daemon.repository.title'),
								to: '/config/daemon/repository',
							},
							{
								title: i18n.t('pages.config.daemon.db.title'),
								to: '/config/daemon/database',
							},
							{
								title: i18n.t('pages.config.daemon.logging.title'),
								to: '/config/daemon/logging',
							},
							{
								title: i18n.t('pages.config.daemon.monitoring.title'),
								to: '/config/daemon/monitoring',
							},
							{
								title: i18n.t('pages.config.daemon.data-collecting.title'),
								to: '/config/daemon/data-collecting',
							},
						],
					},
					{
						title: i18n.t('pages.config.ws-proxy.title'),
						to: '/config/ws-proxy',
						scopes: [AccessScope.config_translator_read],
					},
					{
						title: i18n.t('pages.config.influxdb-bridge.title'),
						to: '/config/influxdb-bridge',
						feature: Feature.iqrfGatewayInfluxdbBridge,
						scopes: [AccessScope.config_iqrfGatewayInfluxdbBridge_read],
					},
					{
						title: i18n.t('pages.config.iqrf-repository.title'),
						to: '/config/iqrf-repository',
						feature: Feature.iqrfRepository,
						scopes: [AccessScope.config_iqrfRepository_read],
					},
					{
						title: i18n.t('pages.config.smtp.title'),
						to: '/config/smtp',
						scopes: [AccessScope.config_mailer_read],
					},
					{
						title: i18n.t('pages.config.time.title'),
						to: '/config/time',
						scopes: [AccessScope.config_time_read],
					},
					{
						title: i18n.t('pages.config.journal.title'),
						to: '/config/journal',
						feature: Feature.journal,
						scopes: [AccessScope.config_journal_read],
					},
					{
						title: i18n.t('pages.config.unattendedUpgrades.title'),
						to: '/config/unattended-upgrades',
						feature: Feature.unattendedUpgrades,
						scopes: [AccessScope.config_automaticUpgrades_read],
					},
					{
						title: i18n.t('pages.config.mender.title'),
						to: '/config/mender',
						feature: Feature.mender,
						scopes: [AccessScope.config_mender_read],
					},
					{
						title: i18n.t('pages.config.monit.title'),
						to: '/config/monit',
						feature: Feature.monit,
						scopes: [AccessScope.config_monit_read],
					},
				],
			},
			{
				title: i18n.t('pages.iqrfnet.title'),
				icon: mdiBroadcast,
				children: [
					{
						title: i18n.t('pages.iqrfnet.send-dpa.title'),
						to: '/iqrfnet/send-dpa',
						scopes: [AccessScope.iqrfNetwork_trUpload_execute],
					},
					{
						title: i18n.t('pages.iqrfnet.send-json.title'),
						to: '/iqrfnet/send-json',
						scopes: [AccessScope.iqrfNetwork_trUpload_execute],
					},
					// temporarily disabled, to be re-enabled in future release
					//  {
					//	  title: i18n.t('pages.iqrfnet.upload.title'),
					//	  to: '/iqrfnet/upload',
					//	  feature: Feature.trUpload,
					//    scopes: [AccessScope.iqrfNetwork_trUpload_execute],
					//  },
					{
						title: i18n.t('pages.iqrfnet.tr-config.title'),
						to: '/iqrfnet/tr-config',
						scopes: [AccessScope.iqrfNetwork_trUpload_execute],
					},
					{
						title: i18n.t('pages.iqrfnet.network-manager.title'),
						to: '/iqrfnet/network-manager',
						scopes: [AccessScope.iqrfNetwork_trUpload_execute],
					},
					{
						title: i18n.t('pages.iqrfnet.standard-manager.title'),
						to: '/iqrfnet/standard-manager',
						scopes: [AccessScope.config_iqrfGatewayDaemon_read],
					},
				],
			},
			{
				title: i18n.t('pages.ipNetwork.title'),
				icon: mdiIpNetwork,
				children: [
					{
						title: i18n.t('pages.ipNetwork.ethernet.title'),
						to: '/ip-network/ethernet',
						scopes: [AccessScope.ipNetwork_physicalConnections_read],
					},
					{
						title: i18n.t('pages.ipNetwork.wireless.title'),
						to: '/ip-network/wireless',
						scopes: [AccessScope.ipNetwork_physicalConnections_read],
					},
					{
						title: i18n.t('pages.ipNetwork.mobile.title'),
						to: '/ip-network/mobile',
						scopes: [AccessScope.ipNetwork_physicalConnections_read],
					},
					{
						title: i18n.t('pages.ipNetwork.vlan.title'),
						to: '/ip-network/vlan',
						scopes: [AccessScope.ipNetwork_physicalConnections_read],
					},
					{
						title: i18n.t('pages.ipNetwork.wireGuard.title'),
						to: '/ip-network/wireguard',
						scopes: [AccessScope.ipNetwork_vpns_read],
					},
				],
				feature: Feature.networkManager,
			},
			{
				title: i18n.t('pages.maintenance.title'),
				icon: mdiWrenchClock,
				children: [
					{
						title: i18n.t('pages.maintenance.backup.title'),
						to: '/maintenance/backup',
						scopes: [AccessScope.gateway_backup_execute],
					},
					{
						title: i18n.t('pages.maintenance.mender.title'),
						to: '/maintenance/mender-update',
						feature: Feature.mender,
						scopes: [AccessScope.gateway_mender_execute],
					},
				],
			},
			{
				title: i18n.t('pages.accessControl.title'),
				icon: mdiSecurity,
				children: [
					{
						title: i18n.t('pages.accessControl.users.title'),
						to: '/access-control/users',
						scopes: [AccessScope.security_users_read],
					},
					{
						title: i18n.t('pages.accessControl.roles.title'),
						to: '/access-control/roles',
						scopes: [AccessScope.security_role_read],
					},
					{
						title: i18n.t('pages.accessControl.apiKeys.title'),
						to: '/access-control/api-keys',
						scopes: [AccessScope.security_apiKeys_read],
					},
					{
						title: i18n.t('pages.accessControl.sshKeys.title'),
						to: '/access-control/ssh-keys',
						scopes: [AccessScope.security_sshkeys_read],
					},
					{
						title: i18n.t('pages.accessControl.daemonAccessTokens.title'),
						to: '/access-control/daemon-access-tokens',
						scopes: [AccessScope.security_daemonAccessTokens_read],
					},
					{
						title: i18n.t('pages.accessControl.mosquittoUsers.title'),
						to: '/access-control/mosquitto-users',
						scopes: [AccessScope.security_mosquittoUsers_read],
						feature: Feature.mosquittoPlugin,
					},
				],
			},
			{
				title: i18n.t('pages.install.title'),
				icon: mdiWizardHat,
				to: '/install',
				// TODO - add access scope?
				// roles: [UserRole.Admin],
				developmentOnly: true,
			},
		];
	} else {
		links = [
			{
				title: i18n.t('pages.auth.sign.in.title'),
				icon: mdiLogin,
				to: '/sign/in',
			},
			{
				title: i18n.t('pages.account.recovery.title'),
				icon: mdiAccountKey,
				to: '/account/recovery',
			},
		];
	}
	links.push(
		{
			title: i18n.t('pages.dev.title'),
			to: '/dev',
			icon: mdiTools,
			children: [
				{
					title: i18n.t('pages.dev.openApi.title'),
					to: '/dev/openApi',
					developmentOnly: true,
				},
			],
			developmentOnly: true,
		},
		{
			title: i18n.t('pages.docs.title'),
			href: featureStore.getConfiguration(Feature.docs)?.url,
			icon: mdiBook,
			feature: Feature.docs,
			target: '_blank',
			to: '',
		},
	);
	return links.filter((item: SidebarLink) => filter(item));
}

</script>
