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
import { computed, type PropType } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import CodeEditor from '@/components/iqrfnet/send-json/CodeEditor.vue';

const componentProps = defineProps({
	message: {
		type: [String, null] as PropType<string | null>,
		required: true,
	},
	type: {
		type: String,
		required: true,
	},
});

const json = computed(() => {
	return componentProps.message ?? undefined;
});
const { copy, copied } = useClipboard({ legacy: true });
const i18n = useI18n();

async function copyToClipboard(): Promise<void> {
	if (componentProps.message === null) {
		return;
	}
	await copy(componentProps.message);
	if (copied.value) {
		toast.success(i18n.t('commands.messages.copySuccess'));
	} else {
		toast.error(i18n.t('commands.messages.copyFailure'));
	}
}
</script>
