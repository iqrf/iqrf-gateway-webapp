<template>
	<div v-if='enabled' class='blocking'>
		<div class='blocking-text'>
			{{ text }}
		</div>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {mapGetters} from 'vuex';

@Component({
	computed: {
		...mapGetters({
			enabled: 'blocking/isEnabled',
			text: 'blocking/text'
		}),
	}
})

/**
 * Blocking component
 */
export default class Blocking extends Vue {}
</script>

<style scoped>
.blocking {
	position: fixed;
	display: flex;
	flex-direction: column;
	justify-content: center;
	align-items: center;
	z-index: 10000;
	width: 100%;
	height: 100%;
	background-color: rgba(0, 0, 0, 0.75);
}

.blocking-text {
	z-index: 10001;
	color: white;
	font-size: 20pt;
	text-align: center;
	overflow: auto;
	word-wrap: break-word;
	max-width: 50%;
}
</style>