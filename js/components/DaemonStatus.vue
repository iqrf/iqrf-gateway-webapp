<template>
	<span>{{this.status}}</span>
</template>

<script>
import { mapState } from 'vuex'
export default {
	name: 'daemon-status',
	computed: mapState({
		status: state => state.daemonStatus.operationMode
	}),
}
</script>

<style scoped>

</style>
