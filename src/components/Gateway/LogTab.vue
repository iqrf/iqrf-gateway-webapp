<template>
	<v-card>
		<v-card-text>
			<v-alert
				v-if='log === null'
				type='error'
			>
				{{ $t('gateway.log.messages.logNotFound') }}
			</v-alert>
			<v-alert
				v-else-if='log.length === 0'
				class='card-margin-bottom'
				type='info'
			>
				{{ $t('gateway.log.messages.logEmpty') }}
			</v-alert>
			<pre v-else class='log card-margin-bottom'>{{ log }}</pre>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';

@Component({})

/**
 * Log viewer
 */
export default class LogTab extends Vue {

	/**
	 * @property {string|null} log Log content
	 */
	@Prop({required: true, default: null}) readonly log!: string|null;
}
</script>
