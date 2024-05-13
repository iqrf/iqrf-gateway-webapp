<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
			{{ $t('components.iqrfnet.send-json.history.title') }}
		</template>
		<template #titleActions>
			<v-btn
				:icon='mdiDelete'
				color='error'
				@click='clearMessages'
			/>
		</template>
		<div v-if='messages.length > 0'>
			<SelectInput
				v-model='messageIdx'
				:items='messageOptions'
			/>
			<v-row v-if='messages.length > 0'>
				<v-col md='6'>
					<JsonMessageViewer
						:message='messages[messageIdx].request'
						type='request'
						source='sendJson'
					/>
				</v-col>
				<v-col
					v-if='messages[messageIdx].response.length > 0'
					md='6'
				>
					<JsonMessageViewer
						v-for='(rsp, i) of messages[messageIdx].response'
						:key='i'
						:message='rsp'
						:class='i < (messages[messageIdx].response.length - 1) ? "mb-2" : ""'
						type='response'
						source='sendJson'
					/>
				</v-col>
			</v-row>
		</div>
		<v-alert
			v-else
			variant='tonal'
			color='info'
		>
			{{ $t('components.iqrfnet.send-json.history.noMessages') }}
		</v-alert>
	</Card>
</template>

<script lang='ts' setup>
import { type JsonMessage } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { mdiDelete } from '@mdi/js';
import { computed, type PropType, type Ref, ref } from 'vue';

import Card from '@/components/Card.vue';
import JsonMessageViewer from '@/components/iqrfnet/send-json/JsonMessageViewer.vue';
import SelectInput from '@/components/SelectInput.vue';
const componentProps = defineProps({
	messages: {
		type: Array as PropType<JsonMessage[]>,
		default: () => [],
		required: true,
	},
});
const emit = defineEmits(['clear']);
const messageIdx: Ref<number> = ref(0);

const messageOptions = computed(() => {
	return componentProps.messages.map((item: JsonMessage, index: number) => ({
		title: `[${item.timestamp}] ${item.mType} (${item.msgId})`,
		value: index,
	}));
});

function clearMessages(): void {
	emit('clear');
}
</script>
