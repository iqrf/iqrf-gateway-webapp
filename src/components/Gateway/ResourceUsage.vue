<template>
	<div class='d-inline'>
		{{ $t('gateway.info.usages.used') }}
		{{ usage.usage.replace('%', ' %') }}
		({{ usage.used }} / {{ usage.size }})
		<div class='progress'>
			<div
				:class='className'
				role='progressbar'
				:style='{ width: usage.usage }'
			/>
		</div>
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';

/**
 * Resource usage data
 */
export interface UsageData {

	/**
	 * Usage as percentage
	 */
	usage: string;

	/**
	 * Used size
	 */
	used: string;

	/**
	 * Total size
	 */
	size: string;

}

@Component({})

/**
 * Resource usage component for gateway information
 */
export default class ResourceUsage extends Vue {
	/**
	 * @property {Dictionary<string>} usage Dictionary of gateway device resource usage
	 */
	@Prop({ required: true }) usage!: UsageData

	/**
	 * Returns CSS classes for the progress bar
	 */
	get className(): string {
		const usage = Number.parseFloat(this.usage.usage.replace('%', ''));
		let className = 'progress-bar usage-progress-bar';
		if (usage >= 90) {
			className += ' bg-danger';
		} else if (usage >= 80) {
			className += ' bg-warning';
		}
		return className;
	}
}
</script>

<style lang='scss'>
.table-striped tbody tr:nth-of-type(2n+1) .progress {
	background-color: white;
}
</style>
