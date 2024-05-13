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
	<v-main>
		<v-container fluid class='fill-height'>
			<v-row align='center' justify='center'>
				<v-col
					cols='12'
					sm='8'
					md='6'
					lg='4'
				>
					<div class='logo'>
						<img :src='Logo' height='40'>
					</div>
					<Steps v-if='!hasUsers' class='mb-4' />
					<slot />
				</v-col>
			</v-row>
		</v-container>
	</v-main>
</template>

<script lang='ts' setup>
import { storeToRefs } from 'pinia';

import Logo from '@/assets/logo-blue.svg?url';
import Steps from '@/components/install/Steps.vue';
import { useInstallStore } from '@/store/install';


const installStore = useInstallStore();
const { hasUsers } = storeToRefs(installStore);
</script>

<style lang='scss' scoped>
.logo {
	padding-bottom: 2rem;
	text-align: center;
}
</style>
