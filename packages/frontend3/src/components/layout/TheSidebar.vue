<template>
	<v-navigation-drawer
		v-model='isVisible'
		color='#1c241e'
		:rail='isMinimized'
	>
		<SidebarItems :items='items()'/>
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
import { UserRole } from '@iqrf/iqrf-gateway-webapp-client/types';
import SidebarItems from '@/components/layout/sidebar/SidebarItems.vue';
import { storeToRefs } from 'pinia';
import { useI18n } from 'vue-i18n';
import { useSidebarStore } from '@/store/sidebar';
import { useUserStore } from '@/store/user';

import { SidebarLink } from '@/types/sidebar';
import { useFeatureStore } from '@/store/features';
import { mdiAccountDetails, mdiAccountKey, mdiApi, mdiBook, mdiChevronLeft, mdiChevronRight, mdiCog, mdiDesktopTower, mdiEmailEdit, mdiLock, mdiLogin, mdiPower, mdiSsh } from '@mdi/js';
import { computed } from 'vue';

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
				title: i18n.t('gateway.title').toString(),
				icon: mdiDesktopTower,
				children: [
					{
						title: i18n.t('gateway.power.title').toString(),
						icon: mdiPower,
						to: '/gateway/power',
					},
				],
			},
			{
				title: 'Config',
				icon: mdiCog,
				children: [
					{
						title: 'SMTP',
						icon: mdiEmailEdit,
						to: '/config/smtp',
					},
				],
			},
			{
				title: 'Management',
				icon: mdiLock,
				children: [
					{
						title: i18n.t('pages.management.apiKeys.title'),
						icon: mdiApi,
						to: '/management/api-keys',
					},
					{
						title: i18n.t('pages.management.sshKeys.title'),
						icon: mdiSsh,
						to: '/management/ssh-keys',
					},
				],
			},
		];
	} else {
		links = [
			{
				title: i18n.t('auth.sign.in.title').toString(),
				icon: mdiLogin,
				to: '/sign/in',
			},
			{
				title: i18n.t('account.recovery.title').toString(),
				icon: mdiAccountKey,
				to: '/account/recovery',
			},
		];
	}
	links.push({
		title: i18n.t('docs.title').toString(),
		href: featureStore.getConfiguration('docs')?.url,
		icon: mdiBook,
		feature: 'docs',
		to: '',
	});
	return links.filter((item: SidebarLink) => filter(item));
}

</script>
