<template>
	<v-dialog
		:width='width'
		scrollable
	>
		<template #activator='{ props }'>
			<v-btn
				v-if='log !== null'
				v-bind='props'
				class='ml-2'
				color='primary'
				variant='elevated'
			>
				{{ $t('components.maintenance.mender.update.log.button') }}
			</v-btn>
		</template>
		<Card>
			<template #title>
				{{ $t('components.maintenance.mender.update.log.title') }}
			</template>
			<pre>{{ log }}</pre>
		</Card>
	</v-dialog>
</template>

<script lang='ts' setup>
import { type PropType } from 'vue';

import Card from '@/components/Card.vue';
import { getModalWidth } from '@/helpers/modal';

defineProps({
	log: {
		type: [String, null] as PropType<string | null>,
		required: true,
		default: null,
	},
});
const width = getModalWidth();
</script>
