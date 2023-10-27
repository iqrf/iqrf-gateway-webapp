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
import {Feature, UserRole} from '@iqrf/iqrf-gateway-webapp-client/types';
import {
	mdiAccountGroup,
	mdiAccountKey,
	mdiApi,
	mdiBook,
	mdiChevronLeft,
	mdiChevronRight,
	mdiClockTimeFourOutline,
	mdiCog,
	mdiDesktopTower,
	mdiEmailEdit,
	mdiInformationOutline,
	mdiLogin,
	mdiPower,
	mdiSecurity,
	mdiSsh,
	mdiSwapHorizontal,
	mdiTextBoxOutline,
	mdiTools,
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
						title: i18n.t('pages.configuration.smtp.title'),
						icon: mdiEmailEdit,
						to: '/config/smtp',
					},
					{
						title: i18n.t('pages.configuration.time.title'),
						icon: mdiClockTimeFourOutline,
						to: '/config/time',
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
		title: i18n.t('docs.title'),
		href: featureStore.getConfiguration(Feature.docs)?.url,
		icon: mdiBook,
		feature: Feature.docs,
		to: '',
	});
	return links.filter((item: SidebarLink) => filter(item));
}

</script>
