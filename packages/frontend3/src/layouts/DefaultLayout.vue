<template>
	<v-app>
		<TheHeader />
		<TheSidebar />
		<v-main>
			<UnverifiedEmailAlert  />
			<v-container fluid>
				<router-view />
			</v-container>
		</v-main>
		<!--<TheFooter />-->
	</v-app>
</template>

<script lang='ts' setup>
//import TheFooter from '@/components/layout/TheFooter.vue';
import TheHeader from '@/components/layout/TheHeader.vue';
import TheSidebar from '@/components/layout/TheSidebar.vue';
import UnverifiedEmailAlert from '@/components/layout/UnverifiedEmailAlert.vue';
</script>

