<template>
	<v-footer
		class='status-footer px-1 py-1'
		app
		color='#1c241e'
	>
		<v-spacer />
		<StatusBar />
	</v-footer>
</template>

<script lang='ts' setup>
import StatusBar from '@/components/layout/footer/StatusBar.vue';
</script>

<style lang='scss' scoped>
.status-footer {
	position: fixed !important;
}
</style>
