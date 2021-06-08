<template>
	<CCardBody>
		<CAlert
			v-if='log === null'
			class='card-margin-bottom'
			color='danger'
		>
			{{ $t('gateway.log.messages.logNotFound') }}
		</CAlert>
		<CAlert
			v-else-if='log.length === 0'
			class='card-margin-bottom'
			color='info'
		>
			{{ $t('gateway.log.messages.logEmpty') }}
		</CAlert>
		<pre v-else class='log card-margin-bottom'>{{ log }}</pre>
	</CCardBody>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {CAlert} from '@coreui/vue/src';

@Component({
	components: {
		CAlert,
	},
})

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
