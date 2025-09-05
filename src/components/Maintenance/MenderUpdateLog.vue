<template>
	<CCard>
		<CCardHeader>
			{{ $t('maintenance.mender.update.log.title') }}
		</CCardHeader>
		<CCardBody>
			<CAlert
				v-if='log.length === 0'
				class='card-margin-bottom'
				color='info'
			>
				{{ $t('maintenance.mender.update.log.noLog') }}
			</CAlert>
			<pre class='log card-margin-bottom'>{{ log }}</pre>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {CAlert, CCard, CCardBody, CCardHeader} from '@coreui/vue/src';

@Component({
	components: {
		CAlert,
		CCard,
		CCardBody,
		CCardHeader,
	}
})

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
	overflow-wrap: break-word;
}
</style>
