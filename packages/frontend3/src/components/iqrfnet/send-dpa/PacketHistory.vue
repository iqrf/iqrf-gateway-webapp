<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software,
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied
See the License for the specific language governing permissions and
limitations under the License.
-->

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
import { type DpaPacketMessage } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { mdiDelete } from '@mdi/js';
import { type PropType } from 'vue';
import { useI18n } from 'vue-i18n';

import Card from '@/components/layout/card/Card.vue';

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
