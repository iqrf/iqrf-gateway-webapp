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
						<img :src='Logo' height='40' />
					</div>
					<Steps v-if='!hasUsers' class='mb-4' />
					<slot />
				</v-col>
			</v-row>
		</v-container>
	</v-main>
</template>

<script lang='ts' setup>
import Steps from '@/components/install/Steps.vue';
import Logo from '@/assets/themes/generic/logo-blue.svg?url';
import { useInstallStore } from '@/store/install';
import { storeToRefs } from 'pinia';

const installStore = useInstallStore();
const {hasUsers} = storeToRefs(installStore);
</script>

<style lang='scss' scoped>
.logo {
	padding-bottom: 2rem;
	text-align: center;
}
</style>
