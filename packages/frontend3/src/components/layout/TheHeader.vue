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
			<HeaderActions />
		</v-app-bar>
	</div>
</template>

<script lang='ts' setup>
import { useDisplay } from 'vuetify';

import LogoSmall from '@/assets/logo-white-small.svg?url';
import Logo from '@/assets/logo-white.svg?url';
import HeaderActions from '@/components/layout/header/HeaderActions.vue';
import { useSidebarStore } from '@/store/sidebar';

const sidebarStore = useSidebarStore();
const display = useDisplay();
</script>

<style lang='scss' scoped>
.logo {
	justify-content: center;
	position: absolute;
	top: 50%;
	left: 50%;
	transform: translate(-50%, -50%);
	margin-inline-start: 0 !important;

	img {
		max-width: 100%;
		max-height: 100%;
		vertical-align: middle;
	}
}
</style>
