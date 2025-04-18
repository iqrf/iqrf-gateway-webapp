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
		/>
	</Card>
</template>

<script lang='ts' setup>
import { mdiDelete } from '@mdi/js';
import { type PropType } from 'vue';
import { useI18n } from 'vue-i18n';

import Card from '@/components/layout/card/Card.vue';
import { DpaPacketTransaction } from '@/types/Iqrfnet';

defineProps({
	messages: {
		type: Array as PropType<DpaPacketTransaction[]>,
		default: () => [],
	},
});
const emit = defineEmits(['clear']);
const i18n = useI18n();
const headers = [
	{
		key: 'requestTs',
		title: i18n.t('common.labels.requestTs'),
		sortable: false,
	},
	{
		key: 'request',
		title: i18n.t('common.labels.request'),
		sortable: false,
		cellProps: {
			class: 'text-info',
		},
	},
	{
		key: 'confirmationTs',
		title: i18n.t('common.labels.confirmationTs'),
		sortable: false,
	},
	{
		key: 'confirmation',
		title: i18n.t('common.labels.confirmation'),
		sortable: false,
		cellProps: {
			class: 'text-warning',
		},
	},
	{
		key: 'responseTs',
		title: i18n.t('common.labels.responseTs'),
		sortable: false,
	},
	{
		key: 'response',
		title: i18n.t('common.labels.response'),
		sortable: false,
		cellProps: {
			class: 'text-success',
		},
	},
];

function clearMessages(): void {
	emit('clear');
}
</script>
