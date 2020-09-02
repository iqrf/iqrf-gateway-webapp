<template>
	<CHeader fixed with-subheader dark>
		<CToggler in-header class='ml-3 d-lg-none'
			@click='$store.commit("sidebar/toggleSidebarMobile")'
		/>
		<CToggler in-header class='ml-3 d-md-down-none'
			@click='$store.commit("sidebar/toggleSidebarDesktop")'
		/>
	</CHeader>
</template>

<script>
import {CHeader, CToggler} from '@coreui/vue';

export default {
	name: 'TheHeader',
	components: {
		CHeader,
		CToggler,
	},
};
</script>

<style scoped>

</style>
