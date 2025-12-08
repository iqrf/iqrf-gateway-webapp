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
	<ICard header-color='gray'>
		<template #title>
			{{ $t(`common.labels.${type}`) }}
		</template>
		<template #titleActions>
			<v-btn
				color='primary'
				:prepend-icon='mdiClipboardTextMultiple'
				size='small'
				@click='copyToClipboard'
			>
				{{ $t('common.buttons.clipboard') }}
			</v-btn>
		</template>
		<CodeEditor
			v-model='json'
			:label='$t(`common.labels.${type}`)'
			language='json'
			clearable
			disabled
		/>
	</ICard>
</template>

<script lang='ts' setup>
import { ICard } from '@iqrf/iqrf-vue-ui';
import { mdiClipboardTextMultiple } from '@mdi/js';
import { useClipboard } from '@vueuse/core';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import CodeEditor from '@/components/iqrfnet/send-json/CodeEditor.vue';

/// Component props
const componentProps = defineProps<{
	/// JSON message to display
	message: string | null;
	/// Message type (request/response)
	type: 'request' | 'response';
}>();

/// Computed JSON message for the editor
const json = computed(() => {
	return componentProps.message ?? undefined;
});
/// Clipboard composable
const { copy, copied } = useClipboard({ legacy: true });
/// i18n composable
const i18n = useI18n();

/**
 * Copies the JSON message to clipboard
 */
async function copyToClipboard(): Promise<void> {
	if (componentProps.message === null) {
		return;
	}
	await copy(componentProps.message);
	if (copied.value) {
		toast.success(
			componentProps.type === 'request'
				? i18n.t('components.iqrfnet.send-json.messageViewer.copy.request.success')
				: i18n.t('components.iqrfnet.send-json.messageViewer.copy.response.success'),
		);
	} else {
		toast.error(
			componentProps.type === 'request'
				? i18n.t('components.iqrfnet.send-json.messageViewer.copy.request.failed')
				: i18n.t('components.iqrfnet.send-json.messageViewer.copy.response.failed'),
		);
	}
}
</script>
