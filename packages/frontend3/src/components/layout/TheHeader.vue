<template>
	<div>
		<v-app-bar color='primary'>
			<v-app-bar-nav-icon @click='sidebarStore.toggleVisibility()' />
			<v-app-bar-title class='logo'>
				<router-link to='/'>
					<img v-if='display.mobile.value' :src='LogoSmall'>
					<img v-else :src='Logo'>
				</router-link>
			</v-app-bar-title>
			<v-spacer />
			<ThemeSelect v-if='!display.mobile.value' />
			<LocaleSelect v-if='!display.mobile.value' />
			<UserControlsMenu v-if='userStore.isLoggedIn && display.mobile.value' />
			<UserControls v-else-if='userStore.isLoggedIn' />
		</v-app-bar>
	</div>
</template>

<script lang='ts' setup>
import { useDisplay } from 'vuetify';

import LogoSmall from '@/assets/logo-white-small.svg?url';
import Logo from '@/assets/logo-white.svg?url';
import LocaleSelect from '@/components/layout/header/LocaleSelect.vue';
import ThemeSelect from '@/components/layout/header/ThemeSelect.vue';
import UserControls from '@/components/layout/header/UserControls.vue';
import UserControlsMenu from '@/components/layout/header/UserControlsMenu.vue';
import { useSidebarStore } from '@/store/sidebar';
import { useUserStore } from '@/store/user';

const userStore = useUserStore();
const sidebarStore = useSidebarStore();
const display = useDisplay();
</script>

<style lang='scss' scoped>
.logo {
	img {
		max-width: 100%;
		max-height: 100%;
		vertical-align: middle;
	}

	justify-content: center;
	position: absolute;
	top: 50%;
	left: 50%;
	transform: translate(-50%, -50%);
	margin-inline-start: 0 !important;
}
</style>
