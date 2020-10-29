<template>
	<router-view />
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';

@Component({})

export default class App extends Vue {
	/**
	 * Main app watch function
	 */
	private unwatch: CallableFunction = () => {return;}

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		this.unwatch = this.$store.watch(
			(state, getter) => getter.isSocketConnected,
			(newVal, oldVal) => {
				if (!oldVal && newVal) {
					this.$store.dispatch('getVersion');
					this.unwatch();
				}
			}
		);
	}
}
</script>
