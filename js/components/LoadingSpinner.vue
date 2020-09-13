<template>
	<div v-if='enabled' class='loading'>
		<div class='spinner' />
		<div>{{ text }}</div>
	</div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
	name: 'LoadingSpinner',
	computed: {
		...mapGetters({
			enabled: 'spinner/isEnabled',
			text: 'spinner/text',
		}),
	},
};
</script>
