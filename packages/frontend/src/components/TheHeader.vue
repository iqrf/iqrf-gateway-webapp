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
	<v-app-bar
		app
		clipped-left
		dark
		color='primary'
		height='60'
	>
		<v-app-bar-nav-icon @click.stop='$store.commit("sidebar/toggleVisibility")' />
		<v-spacer />
		<v-app-bar-title class='logo'>
			<router-link to='/'>
				<img
					:alt='$t("core.title.generic").toString()'
					:src='logo'
				>
			</router-link>
		</v-app-bar-title>
		<v-spacer />
		<v-menu
			v-if='$store.getters["user/isLoggedIn"]'
			bottom
			left
			offset-y
		>
			<template #activator='{ on, attrs }'>
				<v-btn
					v-bind='attrs'
					id='user-menu-button'
					color='white'
					text
					v-on='on'
				>
					{{ $store.getters['user/getName'] }}
					<v-icon
						class='hidden-sm-and-down'
						size='14'
					>
						mdi-chevron-down
					</v-icon>
				</v-btn>
			</template>
			<v-list dense>
				<v-list-item dense to='/profile'>
					<v-list-item-icon>
						<v-icon dense>
							mdi-account
						</v-icon>
					</v-list-item-icon>
					<v-list-item-title>{{ $t('core.profile.title') }}</v-list-item-title>
				</v-list-item>
				<v-divider />
				<v-list-item dense @click='signOut'>
					<v-list-item-icon>
						<v-icon dense>
							mdi-logout
						</v-icon>
					</v-list-item-icon>
					<v-list-item-title>{{ $t('core.sign.out.title') }}</v-list-item-title>
				</v-list-item>
			</v-list>
		</v-menu>
	</v-app-bar>
</template>

<script lang='ts' setup>
import LogoSmall from '@/assets/logo-white-small.svg?url';
import Logo from '@/assets/logo-white.svg?url';
import Vue, {computed, getCurrentInstance, Ref} from 'vue';
import {useBreakpoints} from '@/helpers/displayBreakpoints';

const display = useBreakpoints();
const { proxy } = getCurrentInstance() as { proxy: Vue };

const logo: Ref<string> = computed((): string => {
	return display.width.value < 1280 ? LogoSmall : Logo;
});

function signOut(): void {
	proxy.$store.dispatch('user/signOut')
		.then(() => {
			proxy.$router.push('/sign/in');
			proxy.$toast.success(proxy.$t('core.sign.out.message').toString());
		});
}
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
