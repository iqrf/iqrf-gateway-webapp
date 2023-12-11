<template>
	<Card header-color='gray'>
		<template #title>
			{{ $t('components.iqrfnet.send-dpa.history.title') }}
		</template>
		<template #titleActions>
			<v-btn
				:icon='mdiDelete'
				color='error'
				@click='clearMessages'
			/>
		</template>
		<v-data-table-virtual
			:headers='headers'
			:items='messages'
			height='300'
			density='compact'
			hide-no-data
		>
			<template #item.timestamp='{ item }'>
				[{{ item.requestTs }}]
			</template>
		</v-data-table-virtual>
	</Card>
</template>

<script lang='ts' setup>
import { type DpaPacketMessage } from '@iqrf/iqrf-gateway-daemon-utils';
import { mdiDelete } from '@mdi/js';
import { type PropType } from 'vue';
import { useI18n } from 'vue-i18n';

import Card from '@/components/Card.vue';

defineProps({
	messages: {
		type: Array as PropType<DpaPacketMessage[]>,
		default: () => [],
		required: true,
	},
});
const emit = defineEmits(['clear']);
const i18n = useI18n();
const headers = [
	{
		key: 'timestamp',
		title: i18n.t('components.iqrfnet.send-dpa.history.time'),
		width: '15%',
		minWidth: '15%',
		maxWidth: '15%',
		sortable: false,
	},
	{
		key: 'request',
		title: i18n.t('common.labels.request'),
		width: '30%',
		minWidth: '30%',
		maxWidth: '30%',
		sortable: false,
	},
	{
		key: 'confirmation',
		title: i18n.t('components.iqrfnet.send-dpa.history.confirmation'),
		width: '20%',
		minWidth: '20%',
		maxWidth: '20%',
		sortable: false,
	},
	{
		key: 'response',
		title: i18n.t('common.labels.response'),
		width: '35%',
		minWidth: '35%',
		maxWidth: '35%',
		sortable: false,
	},
];

function clearMessages(): void {
	emit('clear');
}
</script>
