<template>
	<div v-if='enabled' class='loading'>
		<div class='loading-group'>
			<div class='spinner' />
			<div class='loading-text'>
				{{ text }}
			</div>
		</div>
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
