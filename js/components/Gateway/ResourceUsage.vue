<template>
	<div class='usage'>
		{{ $t('gateway.info.usages.used') }}
		{{ usage.used }} / {{ usage.size }}
		<div class='progress'>
			<div class='progress-bar usage-progress-bar' role='progressbar' :style='{ width: usage.usage }'>
				{{ usage.used }} ({{ usage.usage }})
			</div>
		</div>
	</div>
</template>

<script>
export default {
	name: 'ResourceUsage',
	props: {
		usage: {
			type: Object,
			required: true,
		}
	},
};
</script>

<style scoped>
.usage {
	display: inline;
}
</style>
