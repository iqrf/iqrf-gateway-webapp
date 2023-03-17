<template>
	<v-card-text>
		<pre class='log'>{{ log }}</pre>
	</v-card-text>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';

@Component({})

/**
 * Log viewer component
 */
export default class LogViewer extends Vue {
	/**
	 * @property {string|null} log Log content
	 */
	@Prop({required: true, default: null}) readonly log!: string|null;
}
</script>
