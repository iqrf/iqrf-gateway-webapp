<template>
	<CCardBody>
		<pre class='log card-margin-bottom'>{{ log }}</pre>
	</CCardBody>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {CAlert, CCardBody} from '@coreui/vue/src';

@Component({
	components: {
		CAlert,
		CCardBody,
	},
})

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
