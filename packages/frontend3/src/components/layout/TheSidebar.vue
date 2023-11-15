<template>
	<v-navigation-drawer
		v-model='isVisible'
		color='#1c241e'
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
	mdiAccountGroup,
	mdiAccountKey,
	mdiApi,
	mdiArchiveCogOutline,
	mdiBackupRestore,
	mdiBook,
	mdiChevronLeft,
	mdiChevronRight,
	mdiClockTimeFourOutline,
	mdiCog,
	mdiDesktopTower,
	mdiEmailEdit,
	mdiInformationOutline,
	mdiLedOff,
	mdiLogin,
	mdiNotificationClearAll,
	mdiPower,
	mdiProgressUpload,
	mdiProgressWrench,
	mdiSecurity,
	mdiSsh,
	mdiSwapHorizontal,
	mdiTextBoxOutline,
	mdiTools,
	mdiUpdate,
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
	if (item.feature !== undefined && !featureStore.isEnabled(item.feature)) {
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
						icon: mdiInformationOutline,
						to: '/gateway/information',
					},
					{
						title: i18n.t('pages.gateway.logs.title'),
						icon: mdiTextBoxOutline,
						to: '/gateway/logs',
					},
					{
						title: i18n.t('pages.gateway.mode.title'),
						icon: mdiSwapHorizontal,
						to: '/gateway/mode',
					},
					{
						title: i18n.t('pages.gateway.services.title'),
						icon: mdiTools,
						to: '/gateway/services',
					},
					{
						title: i18n.t('pages.gateway.power.title'),
						icon: mdiPower,
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
						icon: mdiLedOff,
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
										title: i18n.t('pages.configuration.daemon.connections.udp.title'),
										to: '/config/daemon/connections/udp',
									},
								],
							},
							{
								title: i18n.t('pages.configuration.daemon.jsonApi.title'),
								to: '/config/daemon/json-api',
							},
							{
								title: i18n.t('pages.configuration.daemon.cache.title'),
								to: '/config/daemon/cache',
							},
							{
								title: i18n.t('pages.configuration.daemon.db.title'),
								to: '/config/daemon/database',
							},
						],
					},
					{
						title: i18n.t('pages.configuration.smtp.title'),
						icon: mdiEmailEdit,
						to: '/config/smtp',
					},
					{
						title: i18n.t('pages.configuration.time.title'),
						icon: mdiClockTimeFourOutline,
						to: '/config/time',
					},
					{
						title: i18n.t('pages.configuration.journal.title'),
						icon: mdiArchiveCogOutline,
						to: '/config/journal',
						feature: Feature.journal,
					},
					{
						title: i18n.t('pages.configuration.unattendedUpgrades.title'),
						icon: mdiUpdate,
						to: '/config/unattended-upgrades',
						feature: Feature.unattendedUpgrades,
					},
					{
						title: i18n.t('pages.configuration.mender.title'),
						icon: mdiProgressWrench,
						to: '/config/mender',
						feature: Feature.mender,
					},
					{
						title: i18n.t('pages.configuration.monit.title'),
						icon: mdiNotificationClearAll,
						to: '/config/monit',
						feature: Feature.monit,
					},
				],
			},
			{
				title: i18n.t('pages.maintenance.title'),
				icon: mdiWrenchClock,
				children: [
					{
						title: i18n.t('pages.maintenance.backup.title'),
						icon: mdiBackupRestore,
						to: '/maintenance/backup',
					},
					{
						title: i18n.t('pages.maintenance.mender.title'),
						icon: mdiProgressUpload,
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
						icon: mdiAccountGroup,
						to: '/access-control/users',
						roles: [UserRole.Admin, UserRole.BasicAdmin],
					},
					{
						title: i18n.t('pages.accessControl.apiKeys.title'),
						icon: mdiApi,
						to: '/access-control/api-keys',
						roles: [UserRole.Admin],
					},
					{
						title: i18n.t('pages.accessControl.sshKeys.title'),
						icon: mdiSsh,
						to: '/access-control/ssh-keys',
						roles: [UserRole.Admin],
					},
				],
			},
		];
	} else {
		links = [
			{
				title: i18n.t('auth.sign.in.title'),
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
		title: i18n.t('pages.docs.title'),
		href: featureStore.getConfiguration(Feature.docs)?.url,
		icon: mdiBook,
		feature: Feature.docs,
		to: '',
	});
	return links.filter((item: SidebarLink) => filter(item));
}

</script>
