<template>
	<v-card>
		<v-card-title>
			{{ $t('maintenance.mender.update.log.title') }}
		</v-card-title>
		<v-card-text>
			<v-alert
				v-if='log.length === 0'
				type='info'
			>
				{{ $t('maintenance.mender.update.log.noLog') }}
			</v-alert>
			<pre class='log card-margin-bottom'>{{ log }}</pre>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';

@Component({})

/**
 * Mender execution log card
 */
export default class MenderUpdateLog extends Vue {

	/**
	 * @property {string} log Log
	 */
	@Prop({required: true, default: null}) log!: string;
}
</script>

<style scoped>
pre {
	overflow-x: auto;
	white-space: pre-wrap;
	white-space: -moz-pre-wrap;
	white-space: -pre-wrap;
	white-space: -o-pre-wrap;
	word-wrap: break-word;
}
</style>
