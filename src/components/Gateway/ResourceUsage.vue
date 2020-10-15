<template>
	<div class='d-inline'>
		{{ $t('gateway.info.usages.used') }}
		{{ usage.used }} / {{ usage.size }}
		<div class='progress'>
			<div
				class='progress-bar usage-progress-bar'
				role='progressbar'
				:style='{ width: usage.usage }'
			>
				{{ usage.used }} ({{ usage.usage }})
			</div>
		</div>
	</div>
</template>

<script lang='ts'>
import Vue from 'vue';

export default Vue.extend({
	name: 'ResourceUsage',
	props: {
		usage: {
			type: Object,
			required: true,
		}
	},
});
</script>
