<template>
	<span>{{ status }}</span>
</template>

<script>
import { mapState } from 'vuex';
export default {
	name: 'DaemonStatus',
	computed: mapState({
		status: state => state.daemonStatus.operationMode,
	}),
};
</script>

<style scoped>

</style>
