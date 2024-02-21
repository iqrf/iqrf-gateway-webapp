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

import Logo from '@/assets/themes/generic/gateway-logo-light-blue.svg?url';
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
