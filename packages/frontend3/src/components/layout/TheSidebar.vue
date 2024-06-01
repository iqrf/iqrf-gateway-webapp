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
import { Feature, UserRole } from '@iqrf/iqrf-gateway-webapp-client/types';
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

function filter(item: SidebarLink): boolean {
	const role: UserRole | null = userStore.getRole;
	if (item.children !== undefined) {
		item.children = item.children.filter((child: SidebarLink) => filter(child));
		if (item.children.length === 0) {
			return false;
		}
	}
	if ((item.developmentOnly ?? false) && import.meta.env.PROD ||
		item.feature !== undefined && !featureStore.isEnabled(item.feature)) {
		return false;
	}
	return !(item.roles !== undefined && role !== null && (Array.isArray(item.roles) && !item.roles.includes(role)));
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
					},
					{
						title: i18n.t('pages.gateway.logs.title'),
						to: '/gateway/logs',
					},
					{
						title: i18n.t('pages.gateway.mode.title'),
						to: '/gateway/mode',
					},
					{
						title: i18n.t('pages.gateway.services.title'),
						to: '/gateway/services',
					},
					{
						title: i18n.t('pages.gateway.power.title'),
						to: '/gateway/power',
					},
				],
			},
			{
				title: i18n.t('pages.configuration.title'),
				icon: mdiCog,
				children: [
					{
						title: i18n.t('pages.configuration.controller.title'),
						to: '/config/controller',
						feature: Feature.iqrfGatewayController,
					},
					{
						title: i18n.t('pages.configuration.daemon.title'),
						to: '/config/daemon',
						children: [
							{
								title: i18n.t('pages.configuration.daemon.interfaces.title'),
								to: '/config/daemon/interfaces',
								children: [
									{
										title: i18n.t('pages.configuration.daemon.interfaces.dpa.title'),
										to: '/config/daemon/interfaces/dpa',
									},
									{
										title: i18n.t('pages.configuration.daemon.interfaces.uart.title'),
										to: '/config/daemon/interfaces/uart',
									},
									{
										title: i18n.t('pages.configuration.daemon.interfaces.spi.title'),
										to: '/config/daemon/interfaces/spi',
									},
									{
										title: i18n.t('pages.configuration.daemon.interfaces.cdc.title'),
										to: '/config/daemon/interfaces/cdc',
									},
								],
							},
							{
								title: i18n.t('pages.configuration.daemon.connections.title'),
								to: '/config/daemon/connections',
								children: [
									{
										title: i18n.t('pages.configuration.daemon.connections.mqtt.title'),
										to: '/config/daemon/connections/mqtt',
									},
									{
										title: i18n.t('pages.configuration.daemon.connections.ws.title'),
										to: '/config/daemon/connections/websocket',
									},
									{
										title: i18n.t('pages.configuration.daemon.connections.udp.title'),
										to: '/config/daemon/connections/udp',
									},
								],
							},
							{
								title: i18n.t('pages.configuration.daemon.scheduler.title'),
								to: '/config/daemon/scheduler',
							},
							{
								title: i18n.t('pages.configuration.daemon.json-api.title'),
								to: '/config/daemon/json-api',
							},
							{
								title: i18n.t('pages.configuration.daemon.repository.title'),
								to: '/config/daemon/repository',
							},
							{
								title: i18n.t('pages.configuration.daemon.db.title'),
								to: '/config/daemon/database',
							},
							{
								title: i18n.t('pages.configuration.daemon.logging.title'),
								to: '/config/daemon/logging',
							},
						],
					},
					{
						title: i18n.t('pages.configuration.influxdb-bridge.title'),
						to: '/config/influxdb-bridge',
						feature: Feature.iqrfGatewayInfluxdbBridge,
					},
					{
						title: i18n.t('pages.configuration.smtp.title'),
						to: '/config/smtp',
					},
					{
						title: i18n.t('pages.configuration.time.title'),
						to: '/config/time',
					},
					{
						title: i18n.t('pages.configuration.journal.title'),
						to: '/config/journal',
						feature: Feature.journal,
					},
					{
						title: i18n.t('pages.configuration.unattendedUpgrades.title'),
						to: '/config/unattended-upgrades',
						feature: Feature.unattendedUpgrades,
					},
					{
						title: i18n.t('pages.configuration.mender.title'),
						to: '/config/mender',
						feature: Feature.mender,
					},
					{
						title: i18n.t('pages.configuration.monit.title'),
						to: '/config/monit',
						feature: Feature.monit,
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
					},
					{
						title: i18n.t('pages.iqrfnet.send-json.title'),
						to: '/iqrfnet/send-json',
					},
					{
						title: i18n.t('pages.iqrfnet.upload.title'),
						to: '/iqrfnet/upload',
					},
					{
						title: i18n.t('pages.iqrfnet.tr-config.title'),
						to: '/iqrfnet/tr-config',
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
					},
					{
						title: i18n.t('pages.ipNetwork.wireless.title'),
						to: '/ip-network/wireless',
					},
					{
						title: i18n.t('pages.ipNetwork.mobile.title'),
						to: '/ip-network/mobile',
					},
					{
						title: i18n.t('pages.ipNetwork.vlan.title'),
						to: '/ip-network/vlan',
					},
					{
						title: i18n.t('pages.ipNetwork.wireGuard.title'),
						to: '/ip-network/wireguard',
					},
				],
				roles: [UserRole.Admin],
				feature: Feature.networkManager,
			},
			{
				title: i18n.t('pages.maintenance.title'),
				icon: mdiWrenchClock,
				children: [
					{
						title: i18n.t('pages.maintenance.backup.title'),
						to: '/maintenance/backup',
					},
					{
						title: i18n.t('pages.maintenance.mender.title'),
						to: '/maintenance/mender-update',
						feature: Feature.mender,
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
						roles: [UserRole.Admin, UserRole.BasicAdmin],
					},
					{
						title: i18n.t('pages.accessControl.apiKeys.title'),
						to: '/access-control/api-keys',
						roles: [UserRole.Admin],
					},
					{
						title: i18n.t('pages.accessControl.sshKeys.title'),
						to: '/access-control/ssh-keys',
						roles: [UserRole.Admin],
					},
				],
			},
		];
	} else {
		links = [
			{
				title: i18n.t('pages.auth.signIn.title'),
				icon: mdiLogin,
				to: '/sign/in',
			},
			{
				title: i18n.t('account.recovery.title'),
				icon: mdiAccountKey,
				to: '/account/recovery',
			},
		];
	}
	links.push({
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
	});
	links.push({
		title: i18n.t('pages.docs.title'),
		href: featureStore.getConfiguration(Feature.docs)?.url,
		icon: mdiBook,
		feature: Feature.docs,
		to: '',
	});
	return links.filter((item: SidebarLink) => filter(item));
}

</script>
